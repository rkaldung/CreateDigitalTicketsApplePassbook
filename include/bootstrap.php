<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);
/**
 * Disable Varnish cache - From AppFog forum
 * Pragma: no-cache
 * Cache-Control: s-maxage=0, max-age=0, must-revalidate, no-cache
 */
header('Pragma: no-cache');
header('Cache-Control: s-maxage=0, max-age=0, must-revalidate, no-cache');
header('content-type: text/html; charset=UTF-8');

// Basic definitions
define(
    'APPLE_CERTIFICATE',
    dirname(__FILE__) . '/../data/Certificate/AppleWWDRCA.pem'
);

$config = array();
$config['app'] = array(
    'name' => 'My Passbook (test) Server',
    'templates.path' => dirname(__FILE__) . '/../templates/',
    'log.enabled' => true,
    'log.level' => 3, // Equivalent to \Slim\Log::INFO
    'passes.path' => 'templates/passes',
    'passes.store' => 'data/passes',
    'passes.passType' => 'YourPassType',
    'passes.data' => array(
        // The name of this key must match with the corresponding
        // keys in pass.json
        'passTypeIdentifier' => 'pass.your.passTypeID',
        'teamIdentifier' => 'YourTeamIdentifier',
        'organizationName' => 'Your Company Name',
        'description' => 'Your Pass Description',
        'logoText' => 'YourLogo',
        'foregroundColor' => 'rgb(nnn, nnn, nnn)',
        'backgroundColor' => 'rgb(nnn, nnn, nnn)',
    ),
    'passes.certfile' => dirname(__FILE__) . '/../data/Certificate/YourPassCertificate.pem',
    'passes.certpass' => 'YourCertificatePassword',
    'smtp.host' => 'mail.yoursite.com',
    'smtp.port' => 25,
    'smtp.username' => 'info@yoursite.com',
    'smtp.password' => 'Secret',
    'smtp.from' => array('info@yoursite.com' => 'Your Name / Your Company'),
);

// Default config = local dev
$configDir = dirname(__FILE__) . '/../config';
$configFile = "$configDir/local.php";

// Check for the AppFog env
if ($services = getenv('VCAP_SERVICES')) {
    $services = json_decode($services, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        exit("Unable to load VCAP_SERVICES");
    }
    $configFile = "$configDir/appfog.php";
}

// Check other mode file
$modeFile = dirname(__FILE__) . '/../.mode';
if (is_readable($modeFile)) {
    $mode = trim(file_get_contents($modeFile));
    $configFile = "$configDir/$mode.php";
}

// Try to load the config file
if (is_readable($configFile)) {
    require_once $configFile;
    if (!empty($config['app']['mode'])) {
        $config['app']['mode'] = $mode;
    }
} else {
    exit("Unable to load config file");
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';

// Init ORM
ORM::configure(sprintf('mysql:host=%s;dbname=%s', $config['db']['host'], $config['db']['name']));
ORM::configure('username', $config['db']['user']);
ORM::configure('password', $config['db']['password']);

// Enables bulk actions (eg. delete) on multiple records
ORM::configure('return_result_sets', true);


// Init App
$app = new Slim\Slim($config['app']);

// Init Logging
$app->log = $app->getLog();
$app->log->setWriter(
    new Slim\Extras\Log\DateTimeFileWriter(
        array('path' => dirname(__FILE__) . '/../data/logs')
    )
);


// Init Headers detection
// Please Note: Apache is required
// If you are using another web server you must adapt the following code
// Grabs the real HTTP headers from the web server and extract the "Authorization"
$app->hook('slim.before', function () use($app) {

    // Replace this line with your server's version
    $headers = apache_request_headers();
    if (!empty($headers['Authorization'])) {
        $env = $app->environment();
        $env['Authorization'] = $headers['Authorization'];
    }
}, 5);
