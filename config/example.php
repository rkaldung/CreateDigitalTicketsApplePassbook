<?php
$config['db'] = array(
    'host' => '127.0.0.1',
    'name' => 'mypassbook',
    'user' => 'mypassbook',
    'password' => 'SomePassword',
);

$config['app']['passes.passType'] = 'YourPassType';

$config['app']['passes.data'] = array(
    // The name of this key must match with the corresponding
    // keys in pass.json
    'passTypeIdentifier' => 'pass.your.passTypeID',
    'teamIdentifier' => 'YourTeamID',
    'organizationName' => 'Your Company',
    'description' => 'Your Pass Description',
    'logoText' => 'YourLogo',
    'foregroundColor' => 'rgb(nnn, nnn, nnn)',
    'backgroundColor' => 'rgb(nnn, nnn, nnn)',
);

$config['app']['passes.certfile'] = dirname(__FILE__) . '/../data/Certificate/YourCertificate.pem';
$config['app']['passes.certpass'] = 'PasswordForCertificate';
$config['app']['smtp.host'] = 'mail.yoursite.com';
$config['app']['smtp.port'] = 25;
$config['app']['smtp.username'] = 'info@yoursite.com';
$config['app']['smtp.password'] = 'Secret';
$config['app']['smtp.from'] = array('info@yoursite.com' => 'Your Name / Company');
