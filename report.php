<?php
require_once('../config.php');
include 'tool-config.php';
include 'src/simple_html_dom.php';
include 'src/Template.php';

require_once "dao/MigrateDAO.php";

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

if (isset($_GET["sid"])) {
    $PDOX = LTIX::getConnection();

    $migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
    $list = $migrationDAO->getReport($_GET["sid"]);

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
                              'imported_site_id' => $row['imported_site_id'],
                              'body' => $html->find('body')[0] ]);
    }

    Template::view('templates/report-body.html', array('links' => $reports));
    Template::view('templates/report-footer.html');
} else {
    header("HTTP/1.0 400 Bad Request");
}
