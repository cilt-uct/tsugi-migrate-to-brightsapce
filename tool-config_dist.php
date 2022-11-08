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
    ['ACC','Faculty of Commerce','College of Accounting'],
    ['DOC','Faculty of Commerce','Dean\'s Office: Commerce'],
    ['FTX','Faculty of Commerce','Dept. of Finance & Tax'],
    ['INF','Faculty of Commerce','Dept. of Information Systems'],
    ['COM','Faculty of Commerce','Faculty of Commerce'],
    ['ECO','Faculty of Commerce','School of Economics'],
    ['BUS','Faculty of Commerce','School of Management Studies'],
    ['APG','Faculty of Engineering & Built Environment','APG: School of Architec, Planning & Geomatic'],
    ['CON','Faculty of Engineering & Built Environment','CEM: Dept. of Construction Econ & Managemnt'],
    ['CHE','Faculty of Engineering & Built Environment','CHE: Dept. of Chemical Engineering'],
    ['CIV','Faculty of Engineering & Built Environment','CIV: Dept. of Civil Engineering'],
    ['CPD','Faculty of Engineering & Built Environment','EBE: Contin. Professional Developmnt Unit'],
    ['EEE','Faculty of Engineering & Built Environment','EEE: Dept. of Electrical Engineering'],
    ['EMU','Faculty of Engineering & Built Environment','EMU: Electron Microscope Unit'],
    ['ERI','Faculty of Engineering & Built Environment','Energy Research Centre'],
    ['EBE','Faculty of Engineering & Built Environment','Faculty of Engineering&Built Environment'],
    ['MEC','Faculty of Engineering & Built Environment','MEC: Dept. of Mechanical Engineering'],
    ['END','Faculty of Engineering & Built Environment','Professional Communication Studies'],
    ['AAE','Faculty of Health Sciences','ANAES: Dept. of Anaesthesia'],
    ['MED','Faculty of Health Sciences','Faculty of Health Sciences'],
    ['DOM','Faculty of Health Sciences','FHS: Dean\'s Office: Health Sciences'],
    ['AHS','Faculty of Health Sciences','HRS: Dept. of Health & Rehab Sciences'],
    ['HSE','Faculty of Health Sciences','HSE: Dept. of Health Sciences Education'],
    ['HUB','Faculty of Health Sciences','HUB: Dept. of Human Biology'],
    ['IBS','Faculty of Health Sciences','IBMS: Dept. of Integrtve Biomed Sciences'],
    ['MDN','Faculty of Health Sciences','MED: Dept. of Medicine'],
    ['OBS','Faculty of Health Sciences','OBG: Dept. of Obstetrics & Gynaecology'],
    ['PED','Faculty of Health Sciences','PAED: Children\'s Institute of UCT'],
    ['PTY','Faculty of Health Sciences','PATH: Dept. of Pathology'],
    ['PPH','Faculty of Health Sciences','PHFM: Dept. of Public Health & Fam Med.'],
    ['PRY','Faculty of Health Sciences','PRY: Dept. of Psychiatry & Mental Health'],
    ['RAY','Faculty of Health Sciences','RAD: Dept. of Radiation Medicine'],
    ['CHM','Faculty of Health Sciences','SUR: Dept. of Surgery'],
    ['AGI','Faculty of Humanities','African Gender Institute'],
    ['ALL','Faculty of Humanities','African Languages & Literature'],
    ['CAS','Faculty of Humanities','African Studies'],
    ['SAN','Faculty of Humanities','Anthropology (ANS)'],
    ['FAM','Faculty of Humanities','Centre for Film & Media Studies'],
    ['CLA','Faculty of Humanities','Classical Studies'],
    ['MUZ','Faculty of Humanities','College of Music'],
    ['REL','Faculty of Humanities','Dept. for the Study of Religions'],
    ['DRM','Faculty of Humanities','Dept. of Drama'],
    ['ELL','Faculty of Humanities','Dept. of English Language & Literature'],
    ['HST','Faculty of Humanities','Dept. of Historical Studies'],
    ['LIS','Faculty of Humanities','Dept. of Knowledge & Info Stewardship'],
    ['PHI','Faculty of Humanities','Dept. of Philosophy'],
    ['POL','Faculty of Humanities','Dept. of Political Studies'],
    ['PSY','Faculty of Humanities','Dept. of Psychology'],
    ['SWK','Faculty of Humanities','Dept. of Social Development'],
    ['SOC','Faculty of Humanities','Dept. of Sociology'],
    ['HUM','Faculty of Humanities','Faculty of Humanities'],
    ['HEB','Faculty of Humanities','Hebrew Language & Literature'],
    ['FIN','Faculty of Humanities','Michaelis School of Fine Art'],
    ['AXL','Faculty of Humanities','School of African&GenderStuds, Anth&Ling'],
    ['EDN','Faculty of Humanities','School of Education'],
    ['SLL','Faculty of Humanities','School of Languages & Literatures'],
    ['TDP','Faculty of Humanities','Theatre,Dance&Performance Studies(CTDPS)'],
    ['DOL','Faculty of Law','Dean\'s Office: Law'],
    ['CML','Faculty of Law','Dept. of Commercial Law'],
    ['PVL','Faculty of Law','Dept. of Private Law'],
    ['RDL','Faculty of Law','Dept. of Private Law'],
    ['PBL','Faculty of Law','Dept. of Public Law'],
    ['LAW','Faculty of Law','Faculty of Law'],
    ['AGE','Faculty of Science','AGE: Dept. of Archaeology'],
    ['AST','Faculty of Science','AST: Dept. of Astronomy'],
    ['BIO','Faculty of Science','BIO: Dept. of Biological Sciences'],
    ['CEM','Faculty of Science','CEM: Dept. of Chemistry'],
    ['CSC','Faculty of Science','CSC: Dept. of Computer Science'],
    ['DOH','Faculty of Science','Dean\'s Office: Humanities'],
    ['EGS','Faculty of Science','EGS:Dept of Environ & Geographic Science'],
    ['SCI','Faculty of Science','Faculty of Science'],
    ['DSC','Faculty of Science','FSC: Dean\'s Office: Science'],
    ['GEO','Faculty of Science','GEO: Dept. of Geological Sciences'],
    ['MAM','Faculty of Science','MAM: Dept of Mathematics & Applied Maths'],
    ['MCB','Faculty of Science','MCB: Dept. of Molecular & Cell Biology'],
    ['PHY','Faculty of Science','PHY: Dept. of Physics'],
    ['SEA','Faculty of Science','SEA: Dept. of Oceanography'],
    ['STA','Faculty of Science','STA: Dept. of Statistical Sciences'],
    ['GSB','Graduate School of Business (GSB)','Graduate School of Business (GSB)'],
    ['GPP','Graduate School of Business (GSB)','The Nelson Mandela School of Public Gov'],
];

