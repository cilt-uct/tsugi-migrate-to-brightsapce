<?php
require_once('../config.php');
include 'src/Template.php';

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$menu = false; // We are not using a menu

// Start of the output
$OUTPUT->header();

$context = [
    'styles'     => [ addSession('static/css/app.min.css'), ],
];

Template::view('templates/header.html', $context);

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);
?>
<div class="bgnew"></div>
<?php
$OUTPUT->splashPage(
    "<img src='static/img/vula.svg' alt='Vula'/><i class='fas fa-arrow-right'></i><img src='static/img/amathuba_woodmark.svg' alt='Amathuba'/>",
    __("<h2>Migrate to Amathuba - Coming Soon!<h2>")
);

$OUTPUT->footerStart();

$OUTPUT->footerEnd();