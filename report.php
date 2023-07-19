<?php
require_once('../config.php');
include 'tool-config_dist.php';
include 'src/simple_html_dom.php';
include 'src/Template.php';

require_once "dao/MigrateDAO.php";

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

if (isset($_GET["sid"]) || isset($_GET["tid"])) {
    $PDOX = LTIX::getConnection();

    $migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix, $tool);
    $list = $migrationDAO->getAllReports(isset($_GET["tid"]) ? $_GET["tid"] : $_GET["sid"]);

    if (count($list) == 0) {
        echo Template::view('templates/no-report.html');
        exit;
    }

    if (count($list) == 1) {
        if (strlen($list[0]['report_url']) > 0) {
            header( 'Location: '. $list[0]['report_url']);
            exit;
        } else {
            echo Template::view('templates/no-report.html');
            exit;
        }
    }

    $reports = array();
    foreach ($list as $i => $row) {
        $started = date_create($row['started_at']);
        $modified = date_create($row['modified_at']);

        if (strlen($row['report_url']) > 0) {
            array_push($reports, ['id' => $i,
                                    'title' => $row['title'],
                                    'started_raw' => $row['started_at'],
                                    'modified_raw' => $row['modified_at'],
                                    'started' => date_format($started,"D, j M"),
                                    'modified' => date_format($modified,"D, j M"),
                                    'state' => $row['state'],
                                    'active' => $row['is_found'],
                                    'imported_site_id' => $row['imported_site_id'],
                                    'transfer_site_id' => $row['transfer_site_id'],
                                    'url' => $row['report_url']
                                ]);
        }
    }

    if (count($reports) == 0) {
        echo Template::view('templates/no-report.html');
        exit;
    }
    Template::view('templates/report-body.html', array('links' => $reports));
    Template::view('templates/report-footer.html');
} else {
    header("HTTP/1.0 400 Bad Request");
}
