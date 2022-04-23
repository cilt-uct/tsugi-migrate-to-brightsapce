<?php
require_once('../config.php');
include 'tool-config.php';

use \Tsugi\Core\LTIX;

header('Content-Type: application/json');

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$result = array_merge(array( 
    'ext_sakai_server' => $LAUNCH->ltiRawParameter('ext_sakai_server','none')
    ,'ext_sakai_serverid' => $LAUNCH->ltiRawParameter('ext_sakai_serverid','none') 
    ,'instructor' => $USER->instructor
    ,'siteid' => $LAUNCH->ltiRawParameter('context_id','none')
    ,'ownerEid' => $LAUNCH->ltiRawParameter('lis_person_sourcedid','none') 
    ,'ownerEmail' => $USER->email
    ,'organizer' => $USER->displayname
    ,'language' => 'eng'
    ,'title' => $LAUNCH->context->title
    ,'description' => $LAUNCH->context->title
    ,'publisher' => 'University of Cape Town'
    ,'done' => 0
    ,'msg'  => 'Application failure.'
    ,'version' => '2.0'
), $_POST);

if ($result['course'] == 'none') {
    $result['course'] = '';
}

$out = array(
    'done' => 0
    ,'msg'  => 'Application failure.'
);

if ($USER->instructor) {

    $cmd = NULL;
    $filename = realpath('tmp') ."/". $result['siteid'] .".json";
    $fp = fopen($filename, 'w');
    fwrite($fp, json_encode($result));
    fclose($fp);

    switch ($result['type']) {
       case "create":
            $cmd = $tool['script-add'] .' '. $filename;
            break;
        case "remove":
            $cmd = $tool['script-remove'] .' '. $result['ext_sakai_server'] .' '. $result['siteid'];
        default: break;
    }

    if (!is_null($cmd)) {
        $result['cmd'] = $cmd; 
        $result['raw'] = shell_exec($cmd);
        $result['out'] = json_decode($result['raw']);

        if (json_last_error() === JSON_ERROR_NONE) { 
            $out['msg'] = $result['out']->out;
            $out['done'] = $result['out']->success;
        } else {
            $out['done'] = 0;
            $out['msg'] = json_last_error_msg();
        } 
    }
} else {
    $out['done'] = 0;
    $out['msg']  = 'Must be an instructor to complete this operation.';
}

// update output json file
$result['msg'] = $out['msg'];
$result['done'] = $out['done'];

$fp = fopen($filename, 'w');
fwrite($fp, json_encode($result));
fclose($fp);

echo json_encode($out);