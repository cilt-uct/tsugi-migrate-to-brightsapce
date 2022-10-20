<?php
require_once "../../config.php";
include '../tool-config.php';
include '../src/simple_html_dom.php';
include '../src/Template.php';

require_once "../dao/MigrateDAO.php";

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

if (isset($_GET["sid"]) || isset($_GET["tid"])) {
    $PDOX = LTIX::getConnection();

    $html = '';
    $migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);
    
    if (isset($_GET["sid"]) && isset($_GET["lid"])) {
        $rows = $migrationDAO->getReportSID($_GET["lid"], $_GET["sid"]);
        if (isset($rows["report"])) {
            $html = $rows['report'];
        }
    } elseif (isset($_GET["tid"])) {
        $rows = $migrationDAO->getReportTID($_GET["tid"]);
        if (isset($rows["report"])) {
            $html = $rows['report'];
        }
    }

    if ($html === '') {
        echo Template::view('../templates/no-report.html', []);
    } else {
        $dom = str_get_html($html);
        $link_tags = $dom->find('link');
        
        foreach ( $link_tags as $link) {
            $new_href = str_replace("https://tsugidev.uct.ac.za","",$link->getAttribute('href'));
            $link->setAttribute('href', preg_replace('/^conversion-report.*/i', '../static/css/'. $new_href, $new_href));
        }

        $body = $dom->find('body', 0);
        $script = $dom->createElement('script');
        $script->setAttribute('src', '../static/js/report.min.js');
        $body->appendChild($script);

        echo htmLawed($dom, array('tidy'=> 1));
    }
} else {
    header("HTTP/1.0 400 Bad Request");
}
