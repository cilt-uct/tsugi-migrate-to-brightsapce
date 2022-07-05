<?php
require_once "../../config.php";
include '../tool-config.php';

require_once("../dao/MigrateDAO.php");

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);

$result = ['success' => 0, 'msg' => 'requires POST'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // $result['msg'] = 'POST is mallformed';
    $result['msg'] = $_POST;
    if (isset($_POST['type'])) {

        switch($_POST['type']) {
            case 'init':
                $result['success'] = $migrationDAO->startMigration($LINK->id, $USER->id, $_POST['notification']) ? 1 : 0;
                break;
            case 'updating':    
            case 'starting':
            case 'exporting':
            case 'running':
            case 'importing':
            case 'completed':
                $result['success'] = $migrationDAO->updateMigration($LINK->id, $USER->id, $_POST['notification']) ? 1 : 0;
            case 'error':
                break;                
        }
        $result['msg'] = $result['success'] ? 'Updated' : 'Error Updating';
    }
}

echo json_encode($result);
exit;
