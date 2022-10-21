<?php
namespace Migration\DAO;

class MigrateDAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function getMigration($link_id, $user_id, $site_id, $provider, $is_admin, $title) {

        $arr = array(':linkId' => $link_id, ':siteId' => $site_id, ':title' => $title);
        $this->PDOX->queryDie("Update {$this->p}migration_site set title = :title where link_id = :linkId and site_id = :siteId;", $arr);

        $arr = array(':linkId' => $link_id, ':siteId' => $site_id);
        $query = "SELECT 
            `site`.notification as `notification`,
            `migration`.created_at, `site`.started_at, `site`.modified_at,
            ifnull(`user`.displayname,'') as displayname, ifnull(`user`.email,'') as email,
            ifnull(`site`.report_url,'') as report_url,
            `site`.state, `site`.`active`, `site`.workflow, `migration`.is_admin, 
            `site`.imported_site_id,
            `site`.transfer_site_id,
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

        $query = "SELECT `site`.site_id, `site`.title, `site`.state, `site`.imported_site_id, `site`.modified_at,
            ifnull(`site`.report_url,'') as report_url, 
            `site`.test_conversion
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

    function startMigration($link_id, $user_id, $site_id, $notifications, $dept, $term, $provider, $is_test) {
        $now = date("Y-m-d H:i:s");

        $user_details = $this->PDOX->rowDie("SELECT user_id, displayname,email FROM {$this->p}lti_user WHERE user_id = :userid;",
                                            array(':userid' => $user_id));

        $user_name = $user_details['displayname'];
        $user_email = $user_details['email'];

        $workflow = ["$now,000 INFO Migration for site $site_id started by $user_name ($user_email)","$now,001 INFO Scheduled Export..."];

        $query = "REPLACE INTO {$this->p}migration_site
                    (site_id, link_id, modified_at, modified_by, started_at, started_by, uploaded_at,
                    active, state, workflow, notification, term, provider, dept, report_url, files, 
                    imported_site_id, transfer_site_id, test_conversion)
                VALUES
                (:siteId, :linkId, NOW(), :userId, NOW(), :userId, NULL, 
                1, 'starting', :workflow, :notifications, :term, :provider, :dept, NULL, NULL,
                0, NULL, :is_test);";


        $arr = array(':linkId' => $link_id, ':siteId' => $site_id, ':userId' => $user_id, 
                        ':term' => $term, ':provider' => $provider, ':dept' => $dept, ':is_test' => $is_test ? 1 : 0,
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

    function addSitesMigration($link_id, $user_id, $sites, $term, $is_test) {

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
                $output = $this->startMigration($link_id, $user_id, $site, $notifications, '', $term, '[]', $is_test) ? 1 : 0;
                array_push($result, $output);
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
        $query = "SELECT workflow, 
                        ifnull(report_url,'') as report_url,
                        imported_site_id, transfer_site_id
                        FROM {$this->p}migration_site where link_id = :linkId and site_id = :siteId;";
        $rows = $this->PDOX->rowDie($query, array(':siteId' => $site_id, ':linkId' => $link_id));

        return ($rows == 0 ? [] : $rows);
    }    

    #### SUPER ADMIN
    function getAdminSiteIDs() {
        $query = "SELECT `A`.link_id, `B`.site_id, ifnull(`B`.title, 'No Title') as title, `A`.created_at
            FROM {$this->p}migration `A`
            inner join {$this->p}migration_site `B` on `B`.link_id = `A`.link_id and `B`.state = 'admin' 
            where `A`.is_admin = 1 order by title, `A`.created_at asc;";

        $arr = array();
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getAdminSiteStatus() {
        $query = "SELECT `B`.link_id, `B`.state, count(*) as n
            FROM {$this->p}migration `A`
            inner join {$this->p}migration_site `B` on `B`.link_id = `A`.link_id and `B`.state <> 'admin' 
            where `A`.is_admin = 1
            group by `B`.link_id, `B`.state;";

        $arr = array();
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getSingleSiteStatus() {
        $query = "SELECT `B`.state, count(*) as n
            FROM {$this->p}migration `A`
            inner join {$this->p}migration_site `B` on `B`.link_id = `A`.link_id and `B`.state <> 'admin' 
            where `A`.is_admin = 0
            group by `B`.state;";

        $arr = array();
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getAllReports($id) {
        $query = "SELECT `site`.title, ifnull(`site`.report_url,'') as report_url, `site`.started_at, `site`.modified_at, `site`.state, 
                        `site`.link_id, `site`.site_id, `site`.imported_site_id, `site`.transfer_site_id,
                        IF(`site`.transfer_site_id = :id, 1, 0)  as `is_found`
                    FROM {$this->p}migration_site `site` 
                    WHERE (`site`.site_id = :id or `site`.transfer_site_id = :id)
                    order by `site`.modified_at desc";
           
        return $this->PDOX->allRowsDie($query, array(':id' => $id));
    }

    function getReportTID($tid) {
        $query = "SELECT `site`.report_url FROM {$this->p}migration_site `site` 
                    WHERE (`site`.transfer_site_id = :tid) 
                    limit 1";
           
        return $this->PDOX->rowDie($query, array(':tid' => $tid));
    }

    function getReportSID($lid, $sid) {
        $query = "SELECT `site`.report_url FROM {$this->p}migration_site `site`  
                    WHERE `site`.link_id = :linkId and `site`.site_id = :siteId
                    limit 1";
           
        return $this->PDOX->rowDie($query, array(':linkId' => $lid, ':siteId' => $sid));
    }

}