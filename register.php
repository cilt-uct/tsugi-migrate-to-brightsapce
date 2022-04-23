<?php

$REGISTER_LTI2 = array(
    "name" => "Migrate to Brightspace"
    ,"FontAwesome" => "fa-rocket"
    ,"short_name" => "Migrate to Brightspace"
    ,"description" => "Export an  Vula site to archive, process and then import into Brightspace."
    ,"messages" => array("launch") // By default, accept launch messages..
    ,"privacy_level" => "public" // anonymous, name_only, public
    ,"license" => "Apache"
    ,"languages" => array(
        "English",
    )
    ,"source_url" => "https://github.com/cilt-uct/tsugi-migrate-to-brightspace"
    // For now Tsugi tools delegate this to /lti/store
    ,"placements" => array(
        /*
        "course_navigation", "homework_submission",
        "course_home_submission", "editor_button",
        "link_selection", "migration_selection", "resource_selection",
        "tool_configuration", "user_navigation"
        */
    )
    ,"screen_shots" => array(
        /* no screenshots */
    )
);
