<?php
namespace Migration\DAO;

class MigrateDAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function getMigration($link_id, $user_id, $site_id, $provider) {
        $query = "SELECT 
            `migration`.notification, 
            `migration`.created_at, `migration`.started_at, `migration`.modified_at,
            `user`.displayname, `user`.email,
            `migration`.state, `migration`.`active`, `migration`.workflow
            FROM {$this->p}migration `migration`
            left join {$this->p}lti_user `user` on `user`.user_id = `migration`.started_by
            WHERE link_id = :linkId limit 1;";
        $arr = array(':linkId' => $link_id);
        $rows = $this->PDOX->rowDie($query, $arr);

        if ($rows == 0) {
            if ($this->createEmpty($link_id, $user_id, $site_id, $provider)) {
                return $this->getMigration($link_id, $user_id, $site_id, $provider);
            }
        }
        
        return $rows;
    }

    function createEmpty($link_id, $user_id, $site_id, $provider) {
        $query = "INSERT INTO {$this->p}migration 
               (link_id, user_id, created_at, created_by, modified_at, modified_by, provider, site_id) 
                VALUES (:linkId, :userId, NOW(), :userId, NOW(), :userId, :provider, :siteid);";
        try {
            $this->PDOX->queryDie($query, 
                    array(':linkId' => $link_id, ':userId' => $user_id, ':provider' => $provider, ':siteid' => $site_id));
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    function startMigration($link_id, $user_id, $notifications) {
        $now = date("Y-m-d H:i:s");
        $workflow = ["$now - [INFO] - Started Migration","$now - [INFO] - Scheduled Export..."];

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
}