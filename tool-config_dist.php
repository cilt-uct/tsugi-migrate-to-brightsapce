<?php
// Configuration file - copy from tool-config_dist.php to tool-config.php
// and then edit. 

if ((basename(__FILE__, '.php') != 'tool-config') && (file_exists('tool-config.php'))) {
    include 'tool-config.php';
    return;
}

# The configuration file - stores the paths to the scripts
$tool = array();
$tool['debug'] = FALSE;
$tool['active'] = TRUE; # if false will show coming soon page

$tool['brightspace_url'] = 'https://amathuba.uct.ac.za/d2l/home/';
$tool['brightspace_log_url'] = 'https://amathuba.uct.ac.za/d2l/le/conversion/import/';
$tool['vula_url'] = 'https://vula.uct.ac.za/portal/site/';

# these sites are used for development - so ignore coming soon page
$tool['dev'] = [];

$departments = [
    ['other','University - wide Community or Activity'],
    ['COM','Faculty of Commerce'],
    ['EBE','Faculty of Engineering & Built Environment'],
    ['FHS','Faculty of Health Sciences'],
    ['HUM','Faculty of Humanities'],
    ['LAW','Faculty of Law'],
    ['SCI','Faculty of Science'],
    ['GSB','Graduate School of Business (GSB)'],
    ['CHED','Centre for Higher Education Development']
];

