<?php
require_once '../include/bootstrap.php';

echo 'Current User: ', exec('whoami'), '<br>'; 

$dataDir = dirname(__FILE__) . '/../data';

$sql = file_get_contents("$dataDir/sql/mypassbook.sql");
try {
    if (!ORM::raw_execute($sql)) {
        throw new Exception('SQL execution failed.');
    }

    if (!is_dir("$dataDir/passes")) {
        if (!@mkdir("$dataDir/passes")) {
           throw new Exception('Unable to create passes directory.');
        } 
    }
    else {
        // Delete .pkpass files
        exec("rm -rf $dataDir/passes/*", $output, $success);
        if ($success != 0) {
           throw new Exception('Unable to delete .pkpass files.');
        }
    }

    if (!is_dir("$dataDir/logs")) {
        if (!@mkdir("$dataDir/logs")) {
           throw new Exception('Unable to create logs directory.');
        } 
    }
    else {
        // Delete log files
        exec("rm -rf $dataDir/logs/*", $output, $success);
        if ($success != 0) {
           throw new Exception('Unable to delete log files.');
        }
    }

    echo 'SUCCESS!';
}
catch (Exception $e) {
    exit($e->getMessage());
}
