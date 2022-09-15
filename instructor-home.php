<?php
require_once "../config.php";
include "tool-config.php";
include 'src/Template.php';

require_once "dao/MigrateDAO.php";

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
$course_providers  = $LAUNCH->ltiRawParameter('lis_course_section_sourcedid','none');
$context_id = $LAUNCH->ltiRawParameter('context_id','none');
$context_title = $LAUNCH->ltiRawParameter('context_title','No Title');
$provider = "none";

if ($course_providers != $context_id) {
    // So we might have some providers to show
    $list = explode('+', $course_providers);
        
    if (count($list) == 1) {
        $provider = $list[0];
    } else {
        $provider = $list;
    }
}

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
$current_migration = $migrationDAO->getMigration($LINK->id, $USER->id, $site_id, $provider, false, $context_title);

$menu = false; // We are not using a menu

$workflow = $current_migration['workflow'] ? json_decode($current_migration['workflow']) : [];

$title = $CONTEXT->title;

// $title = 'Pan-African Ensemble 2021';
// $provider = array('MUZ1366H,2021','MUZ2366H,2021','MUZ3366H,2021');

// $title = 'Med Gen 2 PTY5006S,2021';
// $provider = 'none';

// $title = 'EDN4507F,2022 Test';
// $provider = 'EDN4507F,2022';

$provider_details = get_provider_object($provider, $title);

$context = [
    'instructor' => $USER->instructor, 
    'styles'     => [ addSession('static/css/app.min.css'), ],
    'scripts'    => [ addSession('static/js/jquery.email.multiple.js'), addSession('static/js/jquery.validate.min.js'),  ],
    

    'title'      => $title,
    'site_id'    => $site_id,
    'current_email' => $USER->email,
    'email'      => $current_migration['state'] == 'init' ? $USER->email : $current_migration['email'],
    'name'       => $current_migration['state'] == 'init' ? $USER->displayname : $current_migration['displayname'],
    'notifications' => $current_migration['notification'],

                 // 'init','starting','exporting','running','importing','completed','error','admin'
    'state'      => $current_migration['state'],
    'workflow'   => $workflow,
    'years'      => range(date("Y")+1, date("Y")+2),
    'submit'     => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'fetch_workflow' => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'fetch_report'   => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/get_report.php')) ),
    
    'has_report' => $current_migration['report'] == "1",
    'provider'   => $provider,
    'provider_details'=> $provider_details,
    
    'current_provider' => $current_migration['provider'],
    'current_dept'     => $current_migration['dept'],
    'current_term'     => $current_migration['term'],

    'departments' => $departments
    // 'current'    => $current_migration
];

if (!$USER->instructor) {
    header('Location: ' . addSession('student-home.php'));
}

// Start of the output
$OUTPUT->header();

Template::view('templates/header.html', $context);

$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);

if ($tool['debug']) {
    echo '<pre>'; print_r($context); echo '</pre>';
}

Template::view('templates/instructor-body.html', $context);

$OUTPUT->footerStart();

Template::view('templates/instructor-footer.html', $context);

$OUTPUT->footerEnd();

?>