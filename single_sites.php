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

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix, $tool);

$result = ['success' => 0, 'msg' => 'requires POST'];
$state = "all";
$page = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['page'])) {
        $page = $_POST["page"];
    } else {
        $page = 1;
    }

    if (isset($_POST['state'])) {
        $state = $_POST['state'];
    } else {
        $state = "all";
    }
}

$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;
$single_sites_by_state = $migrationDAO->getSingleSitesByState($LINK->id, $state, $offset, $records_per_page);

$page_result = $migrationDAO->getAllSingleSitesByState($LINK->id, $state);
$total_records = count($page_result);
$total_pages = ceil($total_records/$records_per_page);
$previous_page = $page - 1;
$next_page = $page + 1;

$menu = false; // We are not using a menu

$context = [
    'single_sites_by_state' => $single_sites_by_state,
    'page_result' => $page_result,
    'page' => $page,
    'state' => $state,
    'total_records' => $total_records,
    'total_pages' => $total_pages,
    'previous_page' => $previous_page,
    'next_page' => $next_page,
    'jira_url' => $tool['jira_url']
];

echo Template::view('templates/singlesites-body.html', $context);

$OUTPUT->footerStart();

echo  Template::view('templates/singlesites-footer.html', $context);

$OUTPUT->footerEnd();
?>