<?php

class ProcessSupervisor
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get Supervisors
     *
     * @param string $processUid
     * @param string $option
     * @param array  $arrayFilterData
     * @param int    $start
     * @param int    $limit
     * @param string $type
     *
     * @return array
     */
    public function getProcessSupervisors ($processUid, $option, $arrayFilterData = null, $start = null, $limit = null, $type = null)
    {

        try {

            $arraySupervisor = array();

            $numRecTotal = 0;
            $startbk = $start;
            $limitbk = $limit;

            //Verify data
            $process = new Process();


            //Set variables
            $filterName = "filter";

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) )
            {
                $arrayAux = array(
                    "" => "filter",
                    "LEFT" => "lfilter",
                    "RIGHT" => "rfilter"
                );

                $filterName = $arrayAux[(isset ($arrayFilterData["filterOption"])) ? $arrayFilterData["filterOption"] : ""];
            }

            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                //Return
                return array(
                    "total" => $numRecTotal,
                    "start" => (int) ((!is_null ($startbk)) ? $startbk : 0),
                    "limit" => (int) ((!is_null ($limitbk)) ? $limitbk : 0),
                    $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                    "data" => $arraySupervisor
                );
            }

            //Verify data
            $process->throwExceptionIfNotExistsProcess ($processUid);

            //Set variables
            $numRecTotalGroup = 0;
            $numRecTotalUser = 0;

            $option = strtoupper ($option);

            switch ($option) {
                case "ASSIGNED":
                    break;
                case "AVAILIABLE":
                    $arrayGroupUid = array();
                    $arrayUserUid = array();

                    $results = $this->objMysql->_query ("SELECT u.usrid, t.team_id FROM user_management.poms_users u
                                                        INNER JOIN user_management.user_roles ur ON ur.userId = u.usrid
                                                        INNER JOIN user_management.roles r ON r.role_id = ur.roleId
                                                        INNER JOIN user_management.teams t ON t.team_id = u.team_id
                                                        WHERE r.role_name = 'PROCESS_SUPERVISOR'", []);

                    foreach ($results as $row) {
                        $arrayUserUid[] = $row["usrid"];
                        $arrayGroupUid[] = $row["team_id"];
                    }

                    //$arrayRbacSystemData = array("SYS_UID" => "PROCESSMAKER", "SYS_CODE" => "00000000000000000000000000000002");
                    break;
            }

            //Groups
            //Query
            if ( empty ($type) || $type == "teams" )
            {

                $teamSql = "select t.team_name, t.team_id ";

                switch ($option) {
                    case "ASSIGNED":

                        $teamSql .= ", ps.id AS PU_UID ";

                        $teamJoin = "
					INNER JOIN workflow.process_supervisors ps ON ps.user_id = u.team_id 
					WHERE t.status = 1 AND ps.workflow_id = ? AND ps.pu_type = 'GROUP_SUPERVISOR'";




                        break;
                    case "AVAILIABLE":

                        $teamJoin = "
                            INNER JOIN user_management.user_roles ur ON ur.userId = u.usrid
                            INNER JOIN user_management.roles r ON r.role_id = ur.roleId
                            WHERE r.role_name = 'PROCESS_SUPERVISOR' 
                                AND t.status = 1 
                                AND u.status = 1
                                AND t.team_id NOT IN(
                                    select t.team_id from user_management.poms_users u 
                                    INNER JOIN user_management.teams t ON t.team_id = u.team_id
                                    INNER JOIN workflow.process_supervisors ps ON ps.user_id = u.team_id 
                                    WHERE t.status = 1 
                                        AND ps.workflow_id = ? 
                                        AND ps.pu_type = 'GROUP_SUPERVISOR')";



                        break;
                }

                $teamSql .= "FROM user_management.poms_users u 
                            INNER JOIN user_management.teams t ON t.team_id = u.team_id";
                $fullTeamSql = $teamSql . $teamJoin;

                if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
                {
                    $fullTeamSql .= " AND t.team_name LIKE '%" . $arrayFilterData['filter'] . "%'";
                }

                //Number records total
                $countSql = "SELECT COUNT(*) AS NUM_REC FROM user_management.teams t ";
                $countSql .= $teamJoin;

                $countResult = $this->objMysql->_query ($countSql, [$processUid]);
                $row = $countResult[0];

                $numRecTotalGroup = (int) ($row["NUM_REC"]);
                $numRecTotal = $numRecTotal + $numRecTotalGroup;
            }

            //Users
            //Query
            if ( empty ($type) || $type == "users" )
            {
                $userSql = "SELECT u.usrid, u.username, u.firstName, u.lastName, u.user_email  ";

                switch ($option) {
                    case "ASSIGNED":

                        $userSql .= ", ps.id AS PU_UID";

                        $userJoin = " INNER JOIN workflow.process_supervisors ps ON ps.user_id = u.usrid 
			WHERE ps.workflow_id = ? AND u.status = 1
			AND ps.pu_type = 'SUPERVISOR'";


                        break;
                    case "AVAILIABLE":

                        $userJoin = " INNER JOIN user_management.user_roles ur ON ur.userId = u.usrid
                            INNER JOIN user_management.roles r ON r.role_id = ur.roleId
                            WHERE r.role_name = 'PROCESS_SUPERVISOR' 
                                AND u.status = 1
                                AND u.usrid NOT IN(
                                    SELECT DISTINCT ps.user_id 
                                    FROM workflow.process_supervisors ps 
                                    WHERE ps.workflow_id = ? 
                                        AND ps.pu_type = 'SUPERVISOR')";

                        break;
                }

                $userSql .= " FROM user_management.poms_users u";
                $fullUserSql = $userSql . $userJoin;

                if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
                {
                    $fullUserSql .= " AND u.username LIKE '%" . $arrayFilterData["filter"] . "%'";
                }

                $countSql = "SELECT COUNT(*) AS NUM_REC FROM user_management.poms_users u ";
                $countSql .= $userJoin;

                $countResult = $this->objMysql->_query ($countSql, [$processUid]);
                $row = $countResult[0];

                $numRecTotalUser = (int) ($row["NUM_REC"]);
                $numRecTotal = $numRecTotal + $numRecTotalUser;
            }

            //Groups
            //Query
            if ( empty ($type) || $type == "teams" )
            {
                $fullTeamSql .= " GROUP BY t.team_id";

                $fullTeamSql .= " ORDER BY t.team_name ASC";

                if ( !is_null ($limit) )
                {
                    $fullTeamSql .= " LIMIT " . ((int) ($limit));
                }

                if ( !is_null ($start) )
                {
                    $fullTeamSql .= " OFFSET " . ((int) ($start));
                }
                $results = $this->objMysql->_query ($fullTeamSql, [$processUid]);

                $numRecGroup = 0;

                foreach ($results as $row) {

                    switch ($option) {
                        case "ASSIGNED":
                            $arraySupervisor[] = array(
                                "pu_uid" => $row["PU_UID"],
                                "pu_type" => "GROUP_SUPERVISOR",
                                "grp_uid" => $row["team_id"],
                                "grp_name" => $row["team_name"],
                                "obj_type" => "teams"
                            );
                            break;
                        case "AVAILIABLE":
                            $arraySupervisor[] = array(
                                "grp_uid" => $row["team_id"],
                                "grp_name" => $row["team_name"],
                                "obj_type" => "teams"
                            );
                            break;
                    }

                    $numRecGroup++;
                }
            }

            //Users
            //Query
            if ( empty ($type) || $type == "users" )
            {
                $flagUser = true;

                if ( $numRecTotalGroup > 0 )
                {
                    if ( $numRecGroup > 0 )
                    {
                        if ( !is_null ($limit) )
                        {
                            if ( $numRecGroup < (int) ($limit) )
                            {
                                $start = 0;
                                $limit = $limit - $numRecGroup;
                            }
                            else
                            {
                                $flagUser = false;
                            }
                        }
                        else
                        {
                            $start = 0;
                        }
                    }
                    else
                    {
                        $start = (int) ($start) - $numRecTotalGroup;
                    }
                }

                if ( $flagUser )
                {
                    //Users
                    //Query

                    $fullUserSql .= " ORDER BY u.firstName ASC";

                    if ( !is_null ($limit) )
                    {
                        $fullUserSql .= " LIMIT " . ((int) ($limit));
                    }

                    if ( !is_null ($start) )
                    {
                        $fullUserSql .= " OFFSET " . ((int) ($start));
                    }

                    $results = $this->objMysql->_query ($fullUserSql, [$processUid]);

                    foreach ($results as $row) {
                        switch ($option) {
                            case "ASSIGNED":
                                $arraySupervisor[] = array(
                                    "pu_uid" => $row["PU_UID"],
                                    "pu_type" => "SUPERVISOR",
                                    "usr_uid" => $row["usrid"],
                                    "usr_firstname" => $row["firstName"],
                                    "usr_lastname" => $row["lastName"],
                                    "usr_username" => $row["username"],
                                    "usr_email" => $row["user_email"],
                                    "obj_type" => "users"
                                );
                                break;
                            case "AVAILIABLE":
                                $arraySupervisor[] = array(
                                    "usr_uid" => $row["usrid"],
                                    "usr_firstname" => $row["firstName"],
                                    "usr_lastname" => $row["lastName"],
                                    "usr_username" => $row["username"],
                                    "usr_email" => $row["user_email"],
                                    "obj_type" => "users"
                                );
                                break;
                        }
                    }
                }
            }

            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($startbk)) ? $startbk : 0),
                "limit" => (int) ((!is_null ($limitbk)) ? $limitbk : 0),
                $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                "data" => $arraySupervisor
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return a spefic supervisor
     * @param string $sProcessUID
     * @param string $sPuUID
     *
     * @return object
     *
     * @access public
     */
    public function getProcessSupervisor ($sProcessUID = '', $sPuUID = '')
    {
        try {

            $sql = "select t.team_name from users u 
			INNER JOIN teams t ON t.team_id = u.team_id
			INNER JOIN process_supervisors ps ON ps.user_id = u.team_id 
			WHERE t.status = 1 AND ps.workflow_id = ? 
			AND ps.pu_type = 'GROUP_SUPERVISOR'
			AND t.id = ?";


            while ($aRow = $oDataset->getRow ()) {
                $aResp = array('pu_uid' => $aRow['PU_UID'],
                    'pu_type' => "GROUP_SUPERVISOR",
                    'grp_uid' => $aRow['USR_UID'],
                    'grp_name' => $aRow['GRP_TITLE']);
                $oDataset->next ();
            }


            // Users
            $sql = "SELECT u.usrid, u.username, u.firstName, u.lastName, u.user_email FROM users u
			INNER JOIN process_supervisors ps ON ps.user_id = u.usrid 
			WHERE ps.workflow_id = ? AND u.status = 1
			AND ps.pu_type = 'SUPERVISOR'
			AND U.usrid = ?";


            while ($aRow = $oDataset->getRow ()) {
                $aResp = array('pu_uid' => $aRow['PU_UID'],
                    'pu_type' => "SUPERVISOR",
                    'usr_uid' => $aRow['USR_UID'],
                    'usr_firstname' => $aRow['USR_FIRSTNAME'],
                    'usr_lastname' => $aRow['USR_LASTNAME'],
                    'usr_username' => $aRow['USR_USERNAME'],
                    'usr_email' => $aRow['USR_EMAIL']);
                $oDataset->next ();
            }
            return $aResp;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Assign a supervisor of a process
     *
     * @param string $sProcessUID
     * @param string $sUsrUID
     * @param string $sTypeUID
     * @access public
     */
    public function addProcessSupervisor ($sProcessUID, $sUsrUID, $sTypeUID)
    {
        $objUsers = new UsersFactory ($sUsrUID, null, null, $sUsrUID);
        $objTeams = new Team();
        $oTypeAssigneeT = $objTeams->getGroups ();
        $oTypeAssigneeU = $objUsers->getUsers ();

        if ( empty ($oTypeAssigneeT) && empty ($oTypeAssigneeU) )
        {
            throw new Exception ("ID_USER_DOES_NOT_CORRESPOND_TYPE");
        }

        if ( empty ($oTypeAssigneeT) && !empty ($oTypeAssigneeU) )
        {
            if ( "SUPERVISOR" != $sTypeUID )
            {
                throw new Exception ("ID_USER_DOES_NOT_CORRESPOND_TYPE");
            }
        }
        if ( !empty ($oTypeAssigneeT) && empty ($oTypeAssigneeU) )
        {
            if ( "GROUP_SUPERVISOR" != $sTypeUID )
            {
                throw new Exception ("ID_USER_DOES_NOT_CORRESPOND_TYPE");
            }
        }

        // validate group id

        $sPuUIDT = array();
        $oProcessUser = new ProcessUser();

        if ( $sTypeUID == "GROUP_SUPERVISOR" )
        {
            $query = "select t.team_id AS PU_UID from user_management.poms_users u 
			INNER JOIN user_management.teams t ON t.team_id = u.team_id
			INNER JOIN workflow.process_supervisors ps ON ps.user_id = u.team_id 
			WHERE t.status = 1 AND ps.workflow_id = ?
			AND ps.pu_type = 'GROUP_SUPERVISOR'
			AND t.team_id = ?";
        }
        else
        {
            $query = "select u.usrid AS PU_UID from user_management.poms_users u 
			INNER JOIN workflow.process_supervisors ps ON ps.user_id = u.usrid 
			WHERE u.status = 1 AND ps.workflow_id = ?
			AND ps.pu_type = 'SUPERVISOR'
			AND u.usrid = ?";
        }

        $results = $this->objMysql->_query ($query, [$sProcessUID, $sUsrUID]);

        if ( isset ($results[0]) && !empty ($results[0]) )
        {
            $sPuUIDT = $results[0]['PU_UID'];
        }

        if ( empty ($sPuUIDT) )
        {
            $oProcessUser->create (array(
                'PRO_UID' => $sProcessUID,
                'USR_UID' => $sUsrUID,
                'PU_TYPE' => $sTypeUID));
        }
        else
        {
            //throw new Exception ("ID_RELATION_EXIST");
        }
    }

    /**
     * Remove a supervisor
     *
     * @param string $sProcessUID
     * @param string $sPuUID
     * @access public
     */
    public function removeProcessSupervisor ($sProcessUID, $sPuUID)
    {
        try {
            $processUser = new ProcessUser();
            $oProcessUser = $processUser->retrieveByPK ($sPuUID);
            if ( !empty ($oProcessUser) )
            {
                $iResult = $oProcessUser->delete ();
                return $iResult;
            }
            else
            {
                throw new Exception ("ID_ROW_DOES_NOT_EXIST");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate if the user is supervisor of the process
     *
     * @param string $projectUid Unique id of process
     * @param string $userUid    Unique id of User
     *
     * @return bool Return
     */
    public function isUserProcessSupervisor ($workflowId, Users $objUser)
    {
        try {
            $result = $this->objMysql->_query ("SELECT * FROM workflow.`process_supervisors` 
                                             WHERE `user_id` = ? 
                                             AND `workflow_id` = ? 
                                             AND `pu_type` = 'SUPERVISOR'", [$objUser->getUserId (), $workflowId]);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
            //Return
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
