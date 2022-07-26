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

if (!$USER->instructor) {
    header('Location: ' . addSession('student-home.php'));
}

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
$course_providers  = $LAUNCH->ltiRawParameter('lis_course_section_sourcedid','none');
$context_id = $LAUNCH->ltiRawParameter('context_id','none');
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

$debug = $tool['debug'] == true || $LAUNCH->ltiRawParameter('custom_debug', false) == true;

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
$current_migration = $migrationDAO->getMigration($LINK->id, $USER->id, $site_id, $provider, true);
$sites = $migrationDAO->getMigrationsPerLink($LINK->id);

$menu = false; // We are not using a menu

$context = [
    'instructor' => $USER->instructor, 
    'styles'     => [ addSession('static/css/app.css'), ],
    'scripts'    => [ ],
    'debug'      => $debug,
    'custom_debug' => $LAUNCH->ltiRawParameter('custom_debug', false),
    'tool_debug' => $tool['debug'],
    'title'      => $CONTEXT->title,
    'current_email' => $USER->email,
    'email'      => $current_migration['state'] == 'init' ? $USER->email : $current_migration['email'],
    'name'       => $current_migration['state'] == 'init' ? $USER->displayname : $current_migration['displayname'],
    'notifications' => $current_migration['notification'],
    'state'       => $current_migration['state'],
    'sites'      => $sites,
    'submit'     => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'fetch_workflow'     => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'provider'   => $provider,
    // 'current'    => $current_migration
];

// Start of the output
$OUTPUT->header();

Template::view('templates/header.html', $context);

$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);

echo "<!--<h2>Admin</h2>-->";

if ($debug) {
    echo '<pre>'; print_r($context); echo '</pre>';
}

Template::view('templates/admin-body.html', $context);

$OUTPUT->footerStart();

Template::view('templates/admin-footer.html', $context);

$OUTPUT->footerEnd();

?>