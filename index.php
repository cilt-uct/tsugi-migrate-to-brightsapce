<?php
require_once('../config.php');
include 'tool-config.php';

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$launch = LTIX::requireData();

$menu = false; // We are not using a menu
if ( $USER->instructor ) {
    header( 'Location: '.addSession('instructor-home.php') ) ;
} else {
    header( 'Location: '.addSession('student-home.php') ) ;
}
