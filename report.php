<?php
require_once('../config.php');
include 'tool-config.php';
include 'src/simple_html_dom.php';
include 'src/Template.php';

require_once "dao/MigrateDAO.php";

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

if (isset($_GET["tid"])) {
    header( 'Location: '. str_replace("\\","/", $CFG->getCurrentFileUrl('actions/get_report.php')) . "?tid=". $_GET["tid"]);
} elseif (isset($_GET["sid"])) {
    $PDOX = LTIX::getConnection();

    $migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
    $list = $migrationDAO->getAllReports($_GET["sid"]);

    if (count($list) == 1) {
        header( 'Location: '. str_replace("\\","/", $CFG->getCurrentFileUrl('actions/get_report.php')) . "?sid=". $list[0]['site_id'] ."&lid=". $list[0]['link_id']);
    } else {    
        $reports = array();
        foreach ($list as $i => $row) {
            $html = str_get_html($row['report']);
            $started = date_create($row['started_at']);
            $modified = date_create($row['modified_at']);

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
                                    'url' => str_replace("\\","/", $CFG->getCurrentFileUrl('actions/get_report.php')) .
                                                (strlen($row['transfer_site_id']) > 0 ? "?tid=". $row['transfer_site_id'] : "?sid=". $row['site_id'] ."&lid=". $row['link_id'])
                                ]);
        }

        Template::view('templates/report-body.html', array('links' => $reports));
        Template::view('templates/report-footer.html');
    }
} else {
    header("HTTP/1.0 400 Bad Request");
}
