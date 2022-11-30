<?php

// To allow this to be called directly or from admin/upgrade.php
if ( !isset($PDOX) ) {
    require_once "../config.php";
    $CURRENT_FILE = __FILE__;
    require $CFG->dirroot."/admin/migrate-setup.php";
}

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    "drop table if exists {$CFG->dbprefix}migration",
    "drop table if exists {$CFG->dbprefix}migration_site",
    "drop table if exists {$CFG->dbprefix}migration_site_property"
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
array(  
    "{$CFG->dbprefix}migration",
    "CREATE TABLE `{$CFG->dbprefix}migration` (
        `link_id` int NOT NULL DEFAULT '0',
        `user_id` int NOT NULL DEFAULT '0',

        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_by` int NOT NULL DEFAULT '0',
        `is_admin` tinyint(1) NOT NULL DEFAULT '0',

        UNIQUE KEY `link_id` (`link_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
),  
array( "{$CFG->dbprefix}migration_site",
"CREATE TABLE `{$CFG->dbprefix}migration_site` (
    `link_id` int NOT NULL,
    `site_id` varchar(99) NOT NULL,
    `transfer_site_id` varchar(255) NOT NULL,
    `imported_site_id` int NOT NULL DEFAULT '0',
    `started_at` datetime DEFAULT NULL,
    `started_by` int NOT NULL DEFAULT '0',
    `uploaded_at` datetime DEFAULT NULL,
    `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified_by` int NOT NULL DEFAULT '0',        
    `provider` mediumtext,
    `term` int,
    `dept` VARCHAR(25),
    `active` tinyint(1) DEFAULT '0',
    `is_paused` tinyint(1) DEFAULT '0',
    `state` enum('init','starting','exporting','running','importing','updating','completed','error','paused','admin') DEFAULT NULL,
    `title` VARCHAR(99),
    `workflow` mediumtext,
    `notification` mediumtext,
    `report_url` VARCHAR(255),
    `files` mediumtext,
    `test_conversion` tinyint(1) NOT NULL DEFAULT '0',
    `target_title` VARCHAR(255),
    `target_course` VARCHAR(255),
    `target_term` int,
    `target_dept` VARCHAR(25),
    `target_site_id` int NOT NULL DEFAULT '0',
    `target_site_created` tinyint(1) DEFAULT '0',
    `create_course_offering` tinyint(1) DEFAULT '0',

    PRIMARY KEY (`site_id`,`link_id`),
    KEY `idx_started_by` (`started_by`),
    KEY `idx_modified_by` (`modified_by`),

    CONSTRAINT `{$CFG->dbprefix}migration_link_ibfk` 
        FOREIGN KEY (`link_id`) 
        REFERENCES `{$CFG->dbprefix}migration` (`link_id`) 
        ON DELETE CASCADE ON UPDATE NO ACTION
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;"
),
array( "{$CFG->dbprefix}migration_site_property",
"CREATE TABLE `{$CFG->dbprefix}migration_site_property` (
  `site_id` varchar(99) NOT NULL,
  `key` varchar(45) NOT NULL,
  `found` tinyint(1) DEFAULT '0',
  `detail` mediumtext,
  PRIMARY KEY (`site_id`,`key`),
  CONSTRAINT `migration_property_link_fk` FOREIGN KEY (`site_id`) REFERENCES `migration_site` (`site_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
)
);

// Database upgrade
$DATABASE_UPGRADE = function($oldversion) {
    global $CFG, $PDOX;

    // This is a place to make sure added fields are present
    // if you add a field to a table, put it in here and it will be auto-added
    $add_some_fields = array(
        array('migration', 'is_admin', 'TINYINT(1) NOT NULL DEFAULT 0'),

        array('migration_site', 'title', 'VARCHAR(99)'),
        array('migration_site', 'state', "enum('init','starting','exporting','running','uploading','importing','updating','completed','error','paused','admin')"),
        array('migration_site', 'transfer_site_id', 'varchar(255)'),
        array('migration_site', 'imported_site_id', 'int NOT NULL DEFAULT 0'),
        array('migration_site', 'files', 'mediumtext'),
        array('migration_site', 'term', 'int'),
        array('migration_site', 'dept', 'VARCHAR(25)'),
        array('migration_site', 'test_conversion', 'tinyint(1) NOT NULL DEFAULT 0'),
        array('migration_site', 'uploaded_at', 'datetime DEFAULT NULL'),
        array('migration_site', 'report_url', 'varchar(255)'),
        array('migration_site', 'is_paused', 'tinyint(1) DEFAULT 0'),
        array('migration_site', 'target_title', 'VARCHAR(255)'),
        array('migration_site', 'target_course', 'VARCHAR(255)'),
        array('migration_site', 'target_term', 'int'),
        array('migration_site', 'target_dept', 'VARCHAR(25)'),
        array('migration_site', 'create_course_offering', 'tinyint(1) DEFAULT 0'),
        array('migration_site', 'target_site_id', 'int NOT NULL DEFAULT 0'),
        array('migration_site', 'target_site_created', 'tinyint(1) DEFAULT 0'),

        // drop report
        array('migration_site', 'report', 'DROP')
    );

    foreach ( $add_some_fields as $add_field ) {
        if (count($add_field) != 3 ) {
            echo("Badly formatted add_field");
            var_dump($add_field);
            continue;
        }
        $table = $add_field[0];
        $column = $add_field[1];
        $type = $add_field[2];
        $sql = false;
        if ( $PDOX->columnExists($column, $CFG->dbprefix.$table ) ) {
            if ( $type == 'DROP' ) {
                $sql= "ALTER TABLE {$CFG->dbprefix}$table DROP COLUMN $column";
            } else {
                // continue;
                $sql= "ALTER TABLE {$CFG->dbprefix}$table MODIFY $column $type";
            }
        } else {
            if ( $type == 'DROP' ) continue;
            $sql= "ALTER TABLE {$CFG->dbprefix}$table ADD $column $type";
        }
        echo("Upgrading: ".$sql."<br/>\n");
        error_log("Upgrading: ".$sql);
        $q = $PDOX->queryReturnError($sql);
    }

    return 202210211000;
}; // Don't forget the semicolon on anonymous functions :)

// Do the actual migration if we are not in admin/upgrade.php
if ( isset($CURRENT_FILE) ) {
    include $CFG->dirroot."/admin/migrate-run.php";
}


