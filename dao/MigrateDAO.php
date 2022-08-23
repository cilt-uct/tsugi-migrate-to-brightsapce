<?php
namespace Migration\DAO;

class MigrateDAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function getMigration($link_id, $user_id, $site_id, $provider, $is_admin) {

        $arr = array(':linkId' => $link_id, ':siteId' => $site_id);
        $query = "SELECT 
            `site`.notification as `notification`,
            `migration`.created_at, `site`.started_at, `site`.modified_at,
            ifnull(`user`.displayname,'') as displayname, ifnull(`user`.email,'') as email,
            if(`site`.report is not null and LENGTH(`site`.report) > 1, 1, 0) as report,
            `site`.state, `site`.`active`, `site`.workflow, `migration`.is_admin,
            ifnull(`site`.`provider`, '') as `provider`, 
            ifnull(`site`.`term`, 0) as `term`, 
            ifnull(`site`.`dept`, '') as `dept`
            FROM {$this->p}migration `migration`
            left join {$this->p}migration_site `site` on `site`.link_id = `migration`.link_id
            left join {$this->p}lti_user `user` on `user`.user_id = `site`.started_by
            WHERE `migration`.link_id = :linkId and `site`.site_id = :siteId and `site`.state is not null limit 1;";

        if ($is_admin) {
            $query = "SELECT `migration`.created_at, `migration`.is_admin, `site`.state,
                            '' as `email`, '' as `displayname`, `site`.notification as `notification`,
                            ifnull(`site`.`provider`, '') as `provider`, 
                            ifnull(`site`.`term`, 0) as `term`, 
                            ifnull(`site`.`dept`, '') as `dept`
                FROM {$this->p}migration `migration`
                left join {$this->p}migration_site `site` on `site`.link_id = `migration`.link_id
                WHERE `migration`.link_id = :linkId and `site`.site_id = :siteId limit 1;";
            // unset($arr[':siteId']);
        }
        
        $rows = $this->PDOX->rowDie($query, $arr);

        if (gettype($rows) == "boolean") {
            if ($this->createEmpty($link_id, $user_id, $site_id, $provider, $is_admin)) {
                return $this->getMigration($link_id, $user_id, $site_id, $provider, $is_admin);
            }
        }
        
        return $rows;
    }

    function createEmpty($link_id, $user_id, $site_id, $provider, $is_admin) {
        $this->PDOX->queryDie("REPLACE INTO {$this->p}migration 
                    (link_id, user_id, created_at, created_by, is_admin) 
                    VALUES (:linkId, :userId, NOW(), :userId, :isAdmin)", 
                array(':linkId' => $link_id, ':userId' => $user_id, ':isAdmin' => $is_admin ? b'1' : b'0'));

        $this->PDOX->queryDie("REPLACE INTO {$this->p}migration_site 
                (link_id, site_id, modified_at, modified_by, provider, state) 
                VALUES (:linkId, :siteId, NOW(), :userId, :provider, :state)", 
            array(':linkId' => $link_id, ':siteId' => $site_id, ':userId' => $user_id, ':provider' => $is_admin ? b'1' : b'0', 
                    ':state' => $is_admin ? 'admin' : 'init' ));

        return true;
    }

    function getMigrationsPerLinkStats($link_id) {

        $query = "SELECT `site`.`state`, count(*) as n 
            FROM {$this->p}migration_site `site`
            where `site`.link_id = :linkId
            group by `state`
            having `site`.state <> 'admin';";

        $arr = array(':linkId' => $link_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getMigrationsPerLink($link_id) {

        $query = "SELECT `site`.site_id, `site`.title, `site`.state, if(`site`.report is not null and LENGTH(`site`.report) > 1, 1, 0) as report
            FROM {$this->p}migration_site `site`
            where `site`.link_id = :linkId
            having `site`.state <> 'admin';";

        $arr = array(':linkId' => $link_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function setAdmin($link_id, $user_id, $site_id) {

        $this->PDOX->queryDie("UPDATE {$this->p}migration SET `is_admin` = 1 " .
                                "WHERE `link_id` = :linkId;", 
                                array(':linkId' => $link_id));
                                
        $this->PDOX->queryDie("UPDATE {$this->p}migration_site SET `state` = 'admin' " .
                                "WHERE `link_id` = :linkId and `site_id` = :siteId;", 
                                array(':linkId' => $link_id, ':siteId' => $site_id));
    }

    function removeSite($link_id, $user_id, $site_id) {
                                
        return $this->PDOX->queryDie("DELETE FROM {$this->p}migration_site " .
                                "WHERE `link_id` = :linkId and `site_id` = :siteId;", 
                                array(':linkId' => $link_id, ':siteId' => $site_id));
    }    

    function startMigration($link_id, $user_id, $site_id, $notifications, $dept, $term, $provider) {
        $now = date("Y-m-d H:i:s");
        $workflow = ["$now,000 - [INFO] - Started Migration ($site_id)","$now,001 - [INFO] - Scheduled Export..."];

        $query = "UPDATE {$this->p}migration_site
                SET modified_at = NOW(), modified_by = :userId, started_at = NOW(), started_by = :userId, 
                    workflow = :workflow, active = 1, state='starting', notification = :notifications,
                    term =  :term, provider = :provider, dept = :dept, report = NULL, files = NULL
                WHERE `link_id` = :linkId and `site_id` = :siteId;";

        $arr = array(':linkId' => $link_id, ':siteId' => $site_id, ':userId' => $user_id, 
                        ':term' => $term, ':provider' => $provider, ':dept' => $dept,
                        ':notifications' => $notifications, ':workflow' => json_encode($workflow));
        return $this->PDOX->queryDie($query, $arr);
    }

    function updateMigration($link_id, $user_id, $notifications, $term) {
        $is_admin = FALSE; // Update all records at the same time

        // $is_admin = $this->PDOX->rowDie("SELECT is_admin FROM {$this->p}migration where link_id = :linkId limit 1;",
        //                                     array(':linkId' => $link_id));

        // if (gettype($is_admin) == "boolean") {
        //     $is_admin = FALSE;
        // } else {
        //     $is_admin = $is_admin['is_admin'] === 1;
        // }

        $query = "UPDATE {$this->p}migration_site
                SET modified_at = NOW(), modified_by = :userId, notification = :notifications, term = :term
                WHERE link_id = :linkId " . ($is_admin ? " and state = 'admin' " : "") .";";
        
        $arr = array(':linkId' => $link_id, ':userId' => $user_id, ':notifications' => $notifications, ':term' => $term);
        return $this->PDOX->queryDie($query, $arr);
    }

    function addSitesMigration($link_id, $user_id, $sites, $term) {

        $notifications = $this->PDOX->rowDie("SELECT notification FROM {$this->p}migration_site where state = 'admin' and link_id = :linkId limit 1;", 
                                            array(':linkId' => $link_id));

        if (gettype($notifications) == "boolean") {
            $notifications = '';
        } else {
            $notifications = $notifications['notification'];
        }

        $result = [];
        foreach ($sites as $site) {

            if (strlen($site) > 3) {
                $now = date("Y-m-d H:i:s");
                $workflow = ["$now,000 - [INFO] - Started Migration ($site)","$now,001 - [INFO] - Scheduled Export..."];

                $query = "REPLACE INTO {$this->p}migration_site 
                (site_id, link_id, started_at, started_by, modified_at, modified_by, active, state, workflow, notification, term) 
                    VALUES (:siteId, :linkId, NOW(), :userId, NOW(), :userId, 1, 'starting', :workflow, :notification, :term);";
                
                $arr = array(':siteId' => $site, ':linkId' => $link_id, ':userId' => $user_id, ':term' => $term, 
                                ':workflow' => json_encode($workflow), ':notification' => $notifications);

                try {
                    $this->PDOX->queryDie($query, $arr);
                    array_push($result, 1);
                } catch (PDOException $e) {
                    array_push($result, 0);
                }
            }
        }

        return $result;
    }
    
    function getWorkflow($link_id, $site_id) {
        $query = "SELECT workflow FROM {$this->p}migration_site where link_id = :linkId and site_id = :siteId;";
        $rows = $this->PDOX->rowDie($query, array(':siteId' => $site_id, ':linkId' => $link_id));

        return ($rows == 0 ? [] : $rows);
    }
    
    function getWorkflowAndReport($link_id, $site_id) {
        $query = "SELECT workflow, report FROM {$this->p}migration_site where link_id = :linkId and site_id = :siteId;";
        $rows = $this->PDOX->rowDie($query, array(':siteId' => $site_id, ':linkId' => $link_id));

        return ($rows == 0 ? [] : $rows);
    }    
}