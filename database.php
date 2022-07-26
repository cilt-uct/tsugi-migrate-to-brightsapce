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
    `started_at` datetime DEFAULT NULL,
    `started_by` int NOT NULL DEFAULT '0',
    `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified_by` int NOT NULL DEFAULT '0',        
    `provider` mediumtext,
    `active` tinyint(1) DEFAULT '0',
    `state` enum('init','starting','exporting','running','importing','completed','error') NOT NULL DEFAULT 'init',
    `title` VARCHAR(99),
    `workflow` mediumtext,
    `notification` mediumtext,

    PRIMARY KEY (`site_id`,`link_id`),
    KEY `idx_started_by` (`started_by`),
    KEY `idx_modified_by` (`modified_by`),

    CONSTRAINT `{$CFG->dbprefix}migration_link_ibfk` 
        FOREIGN KEY (`link_id`) 
        REFERENCES `{$CFG->dbprefix}migration` (`link_id`) 
        ON DELETE CASCADE ON UPDATE NO ACTION
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;"
)
);

// Database upgrade
$DATABASE_UPGRADE = function($oldversion) {
    global $CFG, $PDOX;

    $sql= "UPDATE {$CFG->dbprefix}tdiscus_comment SET parent_id = 0 WHERE parent_id IS NULL";
    echo("Upgrading: ".$sql."<br/>\n");
    error_log("Upgrading: ".$sql);
    $q = $PDOX->queryReturnError($sql);

    // This is a place to make sure added fields are present
    // if you add a field to a table, put it in here and it will be auto-added
    $add_some_fields = array(
        array('migration', 'is_admin', 'TINYINT(1) NOT NULL DEFAULT 0'),

        array('migration_site', 'title', 'VARCHAR(99)'),
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


    return 202012101330;

}; // Don't forget the semicolon on anonymous functions :)

// Do the actual migration if we are not in admin/upgrade.php
if ( isset($CURRENT_FILE) ) {
    include $CFG->dirroot."/admin/migrate-run.php";
}
