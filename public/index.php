<?php
require_once '../include/bootstrap.php';

use MyPassbook\Pass as Pass;
use MyPassbook\Subscriber as Subscriber;

$app->get('/', function() use ($app) {
    $title = 'Get Your PHPMaster Membership Card Now!';
    $app->render(
        'main.php',
        array(
            'title' => sprintf('%s | %s', $title, $app->config('name')
        )
    ));
});

// Creates the pass and send it by email
$app->post('/', function() use ($app) {
    $title = 'Get Your PHPMaster Membership Card Now!';
    $errors = array();

    // Sanitize and validate POSTed data server side
    $memberName = filter_input(INPUT_POST, 'membername', FILTER_SANITIZE_STRING);
    if (!filter_var(
        $memberName,
        FILTER_VALIDATE_REGEXP,
        array('options' => array('regexp' => "/[\w\-'\s]+/"))
    )) {
        $errors['membername'] = 'Invalid or empty member name';
    }

    $memberMail = filter_input(INPUT_POST, 'membermail', FILTER_SANITIZE_EMAIL);
    if (!filter_var($memberMail, FILTER_VALIDATE_EMAIL)) {
        $errors['membermail'] = 'Invalid or empty email address';
    }
    
    $memberFavFunction = filter_input(INPUT_POST, 'memberfunction', FILTER_SANITIZE_STRING);

    $memberSubscription = time();

    // Verify that email is unique
    $subscriber = Model::factory('\MyPassbook\Subscriber')
        ->where_equal('email', $memberMail)
        ->find_one();
    
    if ($subscriber !== false) {
        $errors['membermail'] = sprintf("The email address '%s' is not available", $memberMail);
    }

    // Obtain the thumbnail from gravatar
    $memberThumbnail = null;
    if (empty($errors['membermail'])) {
        $memberThumbnail = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($memberMail))) . "?s=60";
    }
    
    $passDownloadUrl = '/unknown';
    
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    if ($action === 'getpass') {
        
        // Insert new subscriber
        try {
            $subscriber = Model::factory('\MyPassbook\Subscriber')->create(
                array(
                    'name' => $memberName,
                    'email' => $memberMail,
                    'created' => date('Y-m-d H:i:s', $memberSubscription),
                    'function' => $memberFavFunction,
                    'picture' => $memberThumbnail
                )
            );
            $subscriber->save();
        }
        catch (\Exception $e) {
            $errors['subscriber'] = 'Error creating subscriber profile';
        }
        
        // Process Pass data
        try {
            // Load pass from template
            $data = json_decode(
                file_get_contents(
                    $app->config('passes.path') . '/' . $app->config('passes.passType') . '.raw' . '/pass.json'
                ),
                true
            );
            if (!$data) {
                throw new \Exception('Error loading Pass data');
            }
            
            // Insert pass global data
            foreach ($app->config('passes.data') as $key => $value) {
                $data[$key] = $value;
            }

            $env = $app->environment();
            
            // Insert pass subscriber data
            $data['serialNumber'] = $subscriber->id;
            $data['webServiceURL'] = sprintf('https://%s/%s/', $env['SERVER_NAME'], $app->request()->getRootUri());
            $data['authenticationToken'] = md5($subscriber->id);
            $data['barcode']['message'] = $subscriber->id;
            $data['generic']['primaryFields'][0]['value'] = $subscriber->name;
            $data['generic']['secondaryFields'][0]['value'] = date('Y', $memberSubscription);
            $data['generic']['auxiliaryFields'][0]['value'] = $subscriber->function . '()';
            $data['generic']['backFields'][0]['value'] = $subscriber->id;
            $data['generic']['backFields'][1]['value'] = $subscriber->created;
            $data['generic']['backFields'][2]['value'] = $subscriber->email;
            
            // Pack and sign the Pass
            if ($pass = $subscriber->createPass($app->config('passes.passType'), $data)) {
                $pass->pack(
                    $app->config('passes.path'),
                    $app->config('passes.store'),
                    $app->config('passes.certfile'),
                    $app->config('passes.certpass')
                );

                try {
                    // Compose full pass download url
                    $passDownloadUrl = sprintf(
                        '%s://%s/%s/data/passes/%s.pkpass',
                        $env['slim.url_scheme'],
                        $env['SERVER_NAME'],
                        $app->request()->getRootUri(),
                        $pass->filename()
                    );

                    // Compose message
                    $message = Swift_Message::newInstance()
                        ->setSubject('Your PHPMaster Membership Card')
                        ->setFrom($app->config('smtp.from'))
                        ->setTo(array($subscriber->email => $subscriber->name))
                        ->setBody('Your Pass is attached or can be downloaded from: ' . $passDownloadUrl)
                        ->attach(Swift_Attachment::fromPath(
                            $app->config('passes.store') . '/' . $pass->filename() . '.pkpass')
                         );
                    
                    // Send Message
                    $transport = Swift_SmtpTransport::newInstance($app->config('smtp.host'), $app->config('smtp.port'))
                      ->setUsername($app->config('smtp.username'))
                      ->setPassword($app->config('smtp.password'));
                    $mailer = Swift_Mailer::newInstance($transport);
                    $result = $mailer->send($message);
                    if (!$result) {
                        $errors['mail'] = 'There were problems sending your pass by mail';
                        $errors['mail'] .= sprintf(' (%s)', $e->getMessage());
                    }

                }
                catch (Exception $e) {
                    $errors['mail'] = 'There were problems sending your pass by mail';
                    $errors['mail'] .= sprintf(' (%s)', $e->getMessage());
                }

            }
            else {
                $errors['pass'] = 'Unable to create pass';
            }
        }
        catch (\Exception $e) {
            $errors['pass'] = $e->getMessage();
        }

        // Delete the subscription and pass
        // we could also use transactions here
        if (!empty($errors['pass'])) {
            @unlink($app->config('passes.store') . '/' . $pass->filename() . '.pkpass');
            $pass->delete();
        }

        // Display result page with success or errors
        $app->render('pass.php', array(
            'title' => sprintf('%s | %s', $title, $app->config('name')),
            'errors' => $errors,
            'memberName' => $memberName,
            'memberMail' => $memberMail,
            'passDownloadUrl' => $passDownloadUrl
        ));
        return;
    }
    
    // Default action: preview
    $app->render('main.php', array(
        'title' => sprintf('%s | %s', $title, $app->config('name')),
        'errors' => $errors,
        'memberName' => $memberName,
        'memberMail' => $memberMail,
        'memberSubscription' => $memberSubscription,
        'memberFavFunction' => $memberFavFunction,
        'memberThumbnail' => $memberThumbnail
    ));
});

// Manages the registration web service API
$app->post('/v1/devices/:deviceId/registrations/:passTypeId/:serialNo', function($deviceId, $passTypeId, $serialNo) use ($app) {
    
    $app->log->info('Received Registration From: ' . $_SERVER['REMOTE_ADDR']);
    
    // Check Auth Header
    // Authorization: ApplePass <authToken>
    $env = $app->environment();
    $auth = (isset($env['Authorization'])) ? $env['Authorization'] : null;
    if ($auth == null) {
        $app->halt(401, 'Unauthorized');
    }
    $app->log->info('Auth: ' . $auth);
    
    // Extract Token
    $auth = explode(' ', $auth);
    $authToken = (isset($auth[1]))? trim($auth[1]): null;
    if ($authToken == null) {
        $app->halt(401, 'Unauthorized');
    }
    $app->log->info('Token: ' . $authToken);
    
    // Check auth token in database
    $pass = Model::factory('\MyPassbook\Pass')->where('auth_token', $authToken)->find_one();
    if ($pass == false) {
        $app->log->warn('Unauthorized Token: ' . $authToken);
        $app->halt(401, 'Unauthorized');
    }

    // Check JSON payload
    // {"pushToken":<pushToken>}
    $payload = $app->request()->getBody();
    $app->log->debug('Payload: ' . $payload);
    if (!empty($payload)) {
        $json = json_decode($payload, true);
        $app->log->debug('JSON: ' . print_r($json, true));
        if (json_last_error() !== JSON_ERROR_NONE) {
            $app->log->error('Unable to extract Push Token');
        }
        $pushToken = $json['pushToken'];
        $app->log->info('Push Token: ' . $pushToken);
    }
    $app->log->info('DeviceID: ' . $deviceId);
    $app->log->info('PassTypeID: ' . $passTypeId);
    $app->log->info('SerialNO: ' . $serialNo);
    
    // Try to register device
    if (!empty($deviceId) && !empty($pushToken)) {
        $device = Model::factory('\MyPassbook\Device')->create();
        $device->id = $deviceId;
        $device->push_token = $pushToken;
        $device->save();
        
        // Try to register relation
        if (!empty($device->id)) {
            $relation = Model::factory('\MyPassbook\DevicePass')->create();
            $relation->device_id = $device->id;
            $relation->pass_id = $pass->id;
            $relation->pass_type = $pass->type;
            if (!$relation->save()) {
                $app->log->error('Errors while saving Device ID');
            }
        }
        else {
            $app->log->error('Errors while saving Device ID');
        }
    }
    else {
        $app->log->error('Unable to create Device record, no Device ID or Push Token found');
    }

    $app->log->info('END Registration Request');
    
});

// Manages the unregistration web service API
$app->delete('/v1/devices/:deviceId/registrations/:passTypeId/:serialNo', function($deviceId, $passTypeId, $serialNo) use ($app) {
    $app->log->info('Received UNRegistration From: ' . $_SERVER['REMOTE_ADDR']);

    // Check Auth Header
    // Authorization: ApplePass <authToken>
    $env = $app->environment();
    $auth = (isset($env['Authorization'])) ? $env['Authorization'] : null;
    if ($auth == null) {
        $app->halt(401, 'Unauthorized');
    }
    $app->log->info('Auth: ' . $auth);
    
    // Extract Token
    $auth = explode(' ', $auth);
    $authToken = (isset($auth[1]))? trim($auth[1]): null;
    if ($authToken == null) {
        $app->halt(401, 'Unauthorized');
    }
    $app->log->info('Token: ' . $authToken);
    
    // Check auth token in database
    $pass = Model::factory('\MyPassbook\Pass')->where('auth_token', $authToken)->find_one();
    if ($pass == false) {
        $app->log->warn('Unauthorized Token: ' . $authToken);
        $app->halt(401, 'Unauthorized');
    }
    $app->log->info('DeviceID: ' . $deviceId);
    $app->log->info('PassTypeID: ' . $passTypeId);
    $app->log->info('SerialNO: ' . $serialNo);

    try {
        if (!$pass->delete()) {
            $app->log->error('Unable to delete pass ' . $pass->id . ' with auth token ' . $authToken);
        }
    }
    catch (\Exception $e) {
        $app->log->error($e->getMessage());
    }
    
    $app->log->info('END UNRegistration Request');
});

$app->run();
