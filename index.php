<?php
require_once('../config.php');
include 'tool-config_dist.php';

require_once "dao/MigrateDAO.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Migration\DAO\MigrateDAO;

$LAUNCH = LTIX::requireData();

$is_admin = $LAUNCH->ltiRawParameter('custom_admin', false);
$is_super_admin = $LAUNCH->ltiRawParameter('custom_superadmin', false);
$is_dev = $LAUNCH->ltiRawParameter('custom_dev', false);
// custom_admin=true

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
$course_providers  = $LAUNCH->ltiRawParameter('lis_course_section_sourcedid','none');
$context_id = $LAUNCH->ltiRawParameter('context_id','none');
$context_title = $LAUNCH->ltiRawParameter('context_title','No Title');
$provider = "none";

if ($is_dev == FALSE) {
    $is_dev = in_array($site_id, $tool['dev']);
}

if ($course_providers != $context_id) {
    // So we might have some providers to show
    $list = explode('+', $course_providers);

    if (count($list) == 1) {
        $provider = $list[0];
    } else {
        $provider = $list;
    }
}

# So the tool is not active yet - so display the coming soon page
if ( !($is_admin || $is_super_admin || $is_dev) ) {
    if ($tool['active'] == FALSE) {
        header( 'Location: '.addSession('coming-soon.php') ) ;
        exit;
    }
}

if ( $USER->instructor ) {

    if ($is_super_admin) {
        header( 'Location: '.addSession('superadmin-home.php') ) ;
    } else {
        $migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix, $tool);
        $current_migration = $migrationDAO->getMigration($LINK->id, $USER->id, $site_id, $provider, $is_admin, $context_title);

        if (($is_admin == 'true') || ($current_migration['is_admin'] === 1)) {
            header( 'Location: '.addSession('admin-home.php') ) ;
        } else {
            header( 'Location: '.addSession('instructor-home.php') ) ;
        }
    }
} else {
    header( 'Location: '.addSession('student-home.php') ) ;
}
