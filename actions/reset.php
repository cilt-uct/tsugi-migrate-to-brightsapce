<?php
require_once "../../config.php";
include "../tool-config_dist.php";
require_once("../dao/MigrateDAO.php");

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$site_id = $LAUNCH->ltiRawParameter('context_id','none');

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix, $tool);

$result = ['success' => 0, 'msg' => 'requires POST'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result['msg'] = $_POST;
    if (isset($_POST['type'])) {
        $result['success'] = $migrationDAO->resetMigration($LINK->id, $USER->id, $_POST['type']) ? 1 : 0;

    }
}

echo json_encode($result);
exit;
