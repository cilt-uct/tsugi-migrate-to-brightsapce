<?php
require_once('../config.php');
include 'tool-config.php';

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$LAUNCH = LTIX::requireData();

$is_admin = $LAUNCH->ltiRawParameter('custom_admin', false);
// custom_admin=true

$menu = false; // We are not using a menu
if ( $USER->instructor ) {
    if ($is_admin === true) {
        header( 'Location: '.addSession('admin-home.php') ) ;
    } else {
        header( 'Location: '.addSession('instructor-home.php') ) ;
    }
} else {
    header( 'Location: '.addSession('student-home.php') ) ;
}
