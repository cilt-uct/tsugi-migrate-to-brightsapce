<?php
require_once "../config.php";
include "tool-config_dist.php";
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

$debug = $tool['debug'] == true || $LAUNCH->ltiRawParameter('custom_debug', false) == true;

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
$current_site_id = $LAUNCH->ltiRawParameter('context_id','none');

$migrationDAO->setAdmin($LINK->id, $USER->id, $current_site_id);

$current_migration = $migrationDAO->getMigration($LINK->id, $USER->id, $site_id, $provider, true, $context_title);
$sites = $migrationDAO->getMigrationsPerLink($LINK->id);
$site_stats = array();
$site_stats_raw = $migrationDAO->getMigrationsPerLinkStats($LINK->id);
foreach ($site_stats_raw as $v) {
    $site_stats[$v['state']] = $v['n'];
}
$stats = array('all' => count($sites), 'init' => 0,'starting' => 0,'exporting' => 0,'running' => 0,'importing' => 0,'updating' => 0,'completed' => 0,'error' => 0);

$menu = false; // We are not using a menu

$context = [
    'instructor' => $USER->instructor, 
    'styles'     => [ addSession('static/css/app.min.css'), ],
    'scripts'    => [ addSession('static/js/jquery.email.multiple.js'), ],
    'debug'      => $debug,
    'custom_debug' => $LAUNCH->ltiRawParameter('custom_debug', false),
    'tool_debug' => $tool['debug'],
    'title'      => $CONTEXT->title,
    'current_email' => $USER->email,
    'email'      => $current_migration['state'] == 'init' ? $USER->email : $current_migration['email'],
    'name'       => $current_migration['state'] == 'init' ? $USER->displayname : $current_migration['displayname'],
    'notifications' => $current_migration['notification'],
    'state'      => $current_migration['state'],
    'site_stats' => array_merge($stats, $site_stats),
    'sites'      => $sites,
    'reload_url' => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('index.php')) ),
    'submit'     => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'fetch_workflow' => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('actions/process.php')) ),
    'fetch_report'   => str_replace("\\","/",$CFG->getCurrentFileUrl('report.php')),

    'years'         => range(date("Y"), date("Y")+3),
    'current_term'  => $current_migration['term'] < 2000 ? date("Y") : $current_migration['term'],

    'provider'   => $provider,
    
    'brightspace_url' => $tool['brightspace_url'],
    'brightspace_log_url' => $tool['brightspace_log_url'],
    'vula_url' => $tool['vula_url'],
    'jira_url' => $tool['jira_url']
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