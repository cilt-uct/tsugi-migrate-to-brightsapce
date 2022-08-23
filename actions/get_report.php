<?php
require_once "../../config.php";
include '../tool-config.php';
include '../src/Template.php';

require_once("../dao/MigrateDAO.php");

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
        
$context = [
    'styles'     => [ 
        '/tsugi-static/bootstrap-3.4.1/css/bootstrap.min.css',
        '/tsugi-static/js/jquery-ui-1.11.4/jquery-ui.min.css',
        '/tsugi-static/fontawesome-free-5.8.2-web/css/all.css',
        '/tsugi-static/fontawesome-free-5.8.2-web/css/v4-shims.css',
        '/tsugi-static/css/tsugi2.css',
        addSession('static/css/app.min.css'), ],
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $site_id = $LAUNCH->ltiRawParameter('context_id','none');

    if (isset($_GET['site'])) {
        $site_id = $_GET['site'];
    }

    $result = $migrationDAO->getWorkflowAndReport($LINK->id, $site_id);
    // $result = $migrationDAO->getWorkflowAndReport(2, '12e53e1a-c037-48d0-a2a7-e82240862d88');
    echo $result['report'] != '' ? $result['report'] : Template::view('../templates/no-report.html', $context);
}
