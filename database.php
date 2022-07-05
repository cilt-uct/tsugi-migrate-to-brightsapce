<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    "drop table if exists {$CFG->dbprefix}migration"
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
array(  
    "{$CFG->dbprefix}migration",
    "create table {$CFG->dbprefix}migration (
        link_id         INTEGER NOT NULL,
        user_id         INTEGER NOT NULL,
        notification    MEDIUMTEXT NOT NULL DEFAULT '',
        created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by      INTEGER NOT NULL,
        started_at      DATETIME,
        started_by      INTEGER NOT NULL,
        modified_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        modified_by     INTEGER NOT NULL,
        site_id         varchar(255) NOT NULL DEFAULT '',
        provider        MEDIUMTEXT NOT NULL DEFAULT '',
        active          TINYINT(1) NOT NULL DEFAULT 0,
        state           ENUM('init', 'starting', 'exporting', 'running', 'importing', 'completed', 'error') NOT NULL DEFAULT 'init',
        workflow        MEDIUMTEXT NULL,

        UNIQUE(link_id)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);

