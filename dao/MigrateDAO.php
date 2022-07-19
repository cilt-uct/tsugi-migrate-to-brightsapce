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
        $query = "SELECT 
            `migration`.notification, 
            `migration`.created_at, `migration`.started_at, `migration`.modified_at,
            `user`.displayname, `user`.email,
            `migration`.state, `migration`.`active`, `migration`.workflow, `migration`.is_admin
            FROM {$this->p}migration `migration`
            left join {$this->p}lti_user `user` on `user`.user_id = `migration`.started_by
            WHERE link_id = :linkId limit 1;";
        $arr = array(':linkId' => $link_id);
        $rows = $this->PDOX->rowDie($query, $arr);

        if ($rows == 0) {
            if ($this->createEmpty($link_id, $user_id, $site_id, $provider, $is_admin)) {
                return $this->getMigration($link_id, $user_id, $site_id, $provider, $is_admin);
            }
        }
        
        return $rows;
    }

    function createEmpty($link_id, $user_id, $site_id, $provider, $is_admin) {
        $query = "INSERT INTO {$this->p}migration 
               (link_id, user_id, created_at, created_by, modified_at, modified_by, provider, site_id, is_admin) 
                VALUES (:linkId, :userId, NOW(), :userId, NOW(), :userId, :provider, :siteid, :isAdmin);";
        try {
            $this->PDOX->queryDie($query, 
                    array(':linkId' => $link_id, ':userId' => $user_id, ':provider' => $provider, ':siteid' => $site_id, ':isAdmin' => ($is_admin ? 1 : 0)));
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    function getMigrationsPerLink($link_id) {

        $query = "SELECT `site`.site_id,`site`.title, `site`.state 
            FROM {$this->p}migration `main`
            left join {$this->p}migration_site `site` on `site`.link_id = `main`.link_id
            where `main`.link_id = :linkId;";

        $arr = array(':linkId' => $link_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function startMigration($link_id, $user_id, $site_id, $notifications) {
        $now = date("Y-m-d H:i:s");
        $workflow = ["$now,000 - [INFO] - Started Migration ($site_id)","$now,001 - [INFO] - Scheduled Export..."];

        $query = "UPDATE {$this->p}migration 
                SET modified_at = NOW(), modified_by = :userId, started_at = NOW(), started_by = :userId, 
                    workflow = :workflow, active = 1, state='starting', notification = :notifications
                 WHERE link_id = :linkId;";

        $arr = array(':linkId' => $link_id, ':userId' => $user_id, ':notifications' => $notifications, ':workflow' => json_encode($workflow));
        return $this->PDOX->queryDie($query, $arr);
    }

    function updateMigration($link_id, $user_id, $notifications) {
        $query = "UPDATE {$this->p}migration 
                SET modified_at = NOW(), modified_by = :userId, notification = :notifications
                 WHERE link_id = :linkId;";
        $arr = array(':linkId' => $link_id, ':userId' => $user_id, ':notifications' => $notifications);
        return $this->PDOX->queryDie($query, $arr);
    }

    function addSitesMigration($link_id, $user_id, $sites) {
        $query = "UPDATE {$this->p}migration 
                SET modified_at = NOW(), modified_by = :userId, started_at = NOW(), started_by = :userId, 
                    active = 0, state='running'
                 WHERE link_id = :linkId;";

        $arr = array(':linkId' => $link_id, ':userId' => $user_id);
        $this->PDOX->queryDie($query, $arr);

        $result = [];
        foreach ($sites as $site) {
            $now = date("Y-m-d H:i:s");
            $workflow = ["$now,000 - [INFO] - Started Migration ($site)","$now,001 - [INFO] - Scheduled Export..."];

            $query = "REPLACE INTO {$this->p}migration_site 
               (site_id, link_id, user_id, started_at, started_by, active, state, workflow) 
                VALUES (:siteId, :linkId, :userId, NOW(), :userId, 1, 'starting', :workflow);";
            
            $arr = array(':siteId' => $site, ':linkId' => $link_id, ':userId' => $user_id, ':workflow' => json_encode($workflow));

            try {
                $this->PDOX->queryDie($query, $arr);
                array_push($result, 1);
            } catch (PDOException $e) {
                array_push($result, 0);
            }
        }

        return $result;
    }
    
    function getWorkflow($link_id, $site_id) {
        $query = "SELECT workflow FROM {$this->p}migration_site where link_id = :linkId and site_id = :siteId;";
        $rows = $this->PDOX->rowDie($query, array(':siteId' => $site_id, ':linkId' => $link_id));

        return ($rows == 0 ? [] : $rows);
    }
    
}