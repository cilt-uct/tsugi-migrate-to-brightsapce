<?php
require_once "../../config.php";
include '../tool-config.php';

require_once("../dao/MigrateDAO.php");

use \Tsugi\Core\LTIX;
use \Migration\DAO\MigrateDAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$site_id = $LAUNCH->ltiRawParameter('context_id','none');

$migrationDAO = new MigrateDAO($PDOX, $CFG->dbprefix);


$result = ['success' => 0, 'msg' => 'requires POST'];

$records_per_page = 10;  
$output = '';

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
} else  {  
    $page = 1;  
}  


$offset = ($page - 1) * $records_per_page;
$result = $migrationDAO->getSingleSitesByState($LINK->id, $state, $offset, $records_per_page);

$output .= "<table class='table flex-wrap' id='singlesites_tbl'><tbody>";  

foreach($result as $row) {
    $output .= '<tr>  
                <td><span>'.$row["started_at"].'</span></td> ';
                
    if($row['state'] == "completed") {
        $output .= ' <td colspan="2"><span class="label alert-success"><strong>State: </strong>'.$row["state"].'</span></td>';
    } else if($row['state'] == "init" || $row['state'] == "starting" || $row['state'] == "running" || $row['state'] == "importing" || $row['state'] == "exporting") {
        $output .= ' <td colspan="2"><span class="label alert-info"><strong>State: </strong>'.$row["state"].'</span></td>';
    } else if($row['state'] == "updating") {
        $output .= ' <td colspan="2"><span class="label alert-info"><strong>State: </strong>'.$row["state"].'</span></td>';
    } else if($row['state'] == "error") {
        $output .= ' <td colspan="2"><span class="label alert-danger"><strong>State: </strong>'.$row["state"].'</span></td>';
    }
    
    $output .= '<td colspan="2"><span>'.$row["title"].'</span></td>
    <td colspan="2"><a href="https://vula.uct.ac.za/portal/site/'.$row["site_id"].'" target="_blank" class="img">
        <img src="./static/img/vula.svg" class="img-fluid"/>
        </a>
    </td>';

    if($row["imported_site_id"] > 0) {
        $output .= '<td colspan="2"><a href="https://amathuba.uct.ac.za/d2l/home/'.$row["imported_site_id"].'" target="_blank" class="img">
                        <img src="./static/img/amathuba_woodmark.svg" class="img-fluid"/>
                        </a>
                    </td>';
    } else {
        $output .= '<td colspan="2">&nbsp;</td>';
    }

    if($row["report_url"] != '' || $row["report_url"] != NULL) {
        $output .= '<td colspan="2"><i class="fas fa-file-alt fa-2x text-primary show_report" data-toggle="modal" rel='.$row["report_url"].' data-target="#reportModal" id='.$row["site_id"].'></i></td>';
    } else {
        $output .= '<td colspan="2">&nbsp;</td>';
    }
    $output .= '</tr>';  
}
 
$output .= '</tbody></table>'; 

$page_result = $migrationDAO->getAllSingleSitesByState($LINK->id, $state);
$total_records = count($page_result);
$total_pages = ceil($total_records/$records_per_page);  
$previous_page = $page - 1;
$next_page = $page + 1;
$adjacents = "2";
$second_last = $total_pages - 1;

$output .='<div class="container text-center"><div class="btn-group flex-wrap" role="group">';

if($page > 1) {
    $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link'  data-state='$state' id='1'>
    <i class='fa fa-angle-double-left'></i></button>";

    $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link'  data-state='$state' id='$previous_page'>
    <i class='fa fa-angle-left'></i></button>";
}

if ($total_pages <= 10){ 
    for($i=1; $i<=$total_pages; $i++) {  
        if ($i == $page) {  
            $output .= "<button type='button' class='btn btn-primary btn-xs pagination_link' disabled  data-state='$state'><span>$i</span></button>";
        } else {
            $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='$i'>$i</button>";
        }
    }  
} elseif ($total_pages > 6){
    if($page <= 4) {			
        for ($j = 1; $j < 8; $j++){		 
            if ($j == $page) {	
                $output .= "<button type='button' class='btn btn-primary btn-xs pagination_link'  data-state='$state' id='$j'><span>$j</span></button>";
            }else{
                $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link'  data-state='$state' id='$j'><span>$j<span></button>";
            }
        }
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' disabled>...</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id=".$second_last.">".$second_last."</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id=".$total_pages.">".$total_pages."</button>";

    } elseif($page > 4 && $page < $total_pages - 4) {		 
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='1'>1</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='2'>2</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' disabled>...</button>";
        for ($k = $page - $adjacents; $k <= $page + $adjacents; $k++) {		
            if ($k == $page) {
                $output .= "<button type='button' class='btn btn-primary btn-xs pagination_link' data-state='$state'>".$k."</button>";	
            }else{
                $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state'  id='$k'>".$k."</button>";
            }                  
        }
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' disabled>...</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id=".$second_last.">".$second_last."</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id=".$total_pages.">".$total_pages."</button>";
    } else {
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='1'>1</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='2'>2</button>";
        $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' disabled>...</button>";
        for ($q = $total_pages - 6; $q <= $total_pages; $q++) {
            if ($q == $page) {
                $output .= "<button type='button' class='btn btn-primary btn-xs pagination_link' data-state='$state'>".$q."</button>";	
            }else{
                $output .= "<button type='button' class='btn  btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id='$q'>".$q."</button>";
            }                   
        }
    }
}


if($page < $total_pages) {
    $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link' data-state='$state' id=".$next_page.">
    <i class='fa fa-angle-right'></i></button>";

    $output .= "<button type='button' class='btn btn-default btn-outline-primary btn-xs pagination_link text-info' data-state='$state' id=".$total_pages.">
    <i class='fa fa-angle-double-right'></i></button>";
}
$output .= '</div></div><br/><br/>'; 
echo $output;
exit;