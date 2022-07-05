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
        link_id         INTEGER NOT NULL DEFAULT 0,
        user_id         INTEGER NOT NULL DEFAULT 0,
        notification    MEDIUMTEXT NULL,
        created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by      INTEGER NOT NULL DEFAULT 0,
        started_at      DATETIME,
        started_by      INTEGER NOT NULL DEFAULT 0,
        modified_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        modified_by     INTEGER NOT NULL DEFAULT 0,
        site_id         varchar(255) NOT NULL DEFAULT '',
        provider        MEDIUMTEXT,
        active          TINYINT(1) NOT NULL DEFAULT 0,
        state           ENUM('init', 'starting', 'exporting', 'running', 'importing', 'completed', 'error') NOT NULL DEFAULT 'init',
        workflow        MEDIUMTEXT NULL,

        UNIQUE(link_id)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);

