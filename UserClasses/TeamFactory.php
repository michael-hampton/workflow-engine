<?php

class TeamFactory
{

    /**
     * Constructor of the class
     *
     * return void
     */
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**

      /**
     * Verify if exists the title of a Group
     *
     * @param string $groupTitle      Title
     * @param string $groupUidExclude Unique id of Group to exclude
     *
     * return bool Return true if exists the title of a Group, false otherwise
     */
    public function existsTitle ($groupTitle, $groupUidExclude = "")
    {
        try {
            $result = $this->objMysql->_select ("user_management.teams", array(), array("team_name" => $name));

            if ( isset ($result[0]['team_name']) && !empty ($result[0]['team_name']) )
            {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function groupExists ($groupUid)
    {
        $result = $this->objMysql->_select ("user_management.teams", array(), array("team_id" => $groupUid));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return false;
    }

    /**
     * Verify if doesn't exists the Group in table GROUP
     *
     * @param string $groupUid              Unique id of Group
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if doesn't exists the Group in table GROUP
     */
    public function throwExceptionIfNotExistsGroup ($groupUid)
    {
        try {
            if ( !$$this->groupExists ($groupUid) )
            {
                throw new Exception ("GROUP DOES NOT EXIST");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a Group
     *
     * @param string $groupTitle            Title
     * @param string $fieldNameForException Field name for the exception
     * @param string $groupUidExclude       Unique id of Group to exclude
     *
     * return void Throw exception if exists the title of a Group
     */
    public function throwExceptionIfExistsTitle ($groupTitle, $fieldNameForException, $groupUidExclude = "")
    {
        try {
            if ( $this->existsTitle ($groupTitle) )
            {
                throw new Exception ("GROUP TITLE ALREADY EXISTS");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Group
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new Group created
     */
    public function create ($arrayData)
    {
        try {
            $this->throwExceptionIfExistsTitle ($arrayData["team_name"]);
            //Create
            $group = new Teams();
            $group->loadObject ($arrayData);
            $groupUid = $group->save ();
            //Return
            $arrayData = array_merge (array("GRP_UID" => $groupUid), $arrayData);

            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Group
     *
     * @param string $groupUid  Unique id of Group
     * @param array  $arrayData Data
     *
     * return array Return data of the Group updated
     */
    public function update ($groupUid, $arrayData)
    {
        try {
            $this->throwExceptionIfNotExistsGroup ($groupUid);
            if ( isset ($arrayData["team_name"]) )
            {
                $this->throwExceptionIfExistsTitle ($arrayData["team_name"]);
            }
            //Update
            $group = new Teams();
            $group->setId ($groupUid);
            $group->loadObject ($arrayData);
            $result = $group->save ();
            //Return
            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Group
     *
     * @param string $groupUid Unique id of Group
     *
     * return void
     */
    public function delete ($groupUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsGroup ($groupUid);
            $arrayTotalTasksByGroup = $this->getTotalTasksByGroup ($groupUid);
            if ( isset ($arrayTotalTasksByGroup[$groupUid]) && $arrayTotalTasksByGroup[$groupUid] > 0 )
            {
                throw new Exception ("ID GROUP CANNOT DELETE WHILE ASSIGNED TO TASK");
            }

            $objPermissions = new Permissions (null);
            $objPermissions->deleteAll ("team", $groupUid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of total Users by Group
     *
     * @param string $groupUid Unique id of Group
     *
     * return array Return an array with data of total Users by Group
     */
    public function getTotalUsersByGroup ($groupUid = "")
    {
        try {
            $arrayData = array();
            $arrParameters = array();
            //Verif data
            if ( $groupUid != "" )
            {
                $this->throwExceptionIfNotExistsGroup ($groupUid);
            }

            $sql = "SELECT COUNT(u.username) AS NUM_REC, t.team_id 
                    INNER JOIN teams t ON t.team_id = u.usrid
                    WHERE u.status = 1";


            if ( $groupUid != "" )
            {
                $sql .= " AND t.team_id = ?";
                $arrParameters[] = $groupUid;
            }

            $sql .= " GROUP BY t.team_id";

            $results = $this->objMysql->_query ($sql, $arrParameters);

            foreach ($results as $row) {
                $arrayData[$row["team_id"]] = (int) ($row["NUM_REC"]);
            }
            //Return
            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of total Tasks by Group
     *
     * @param string $groupUid Unique id of Group
     *
     * return array Return an array with data of total Tasks by Group
     */
    public function getTotalTasksByGroup ($groupUid = "")
    {
        try {
            $arrayData = array();
            //Verif data
            if ( $groupUid != "" )
            {
                $this->throwExceptionIfNotExistsGroup ($groupUid);
            }
            //Get data
            $workflowObject = $this->objMysql->_select ("workflow.workflow_data", array(), array());

            $workflowData = json_decode ($workflowObject[0]['workflow_data'], true);
            $auditData = json_decode ($workflowObject[0]['audit_data'], true);

            $arrUsers = array();

            if ( isset ($workflowData['elements']) && !empty ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {

                    foreach ($auditData['elements'][$elementId]['steps'] as $audit) {
                        $arrUsers[] = $audit['claimed'];
                    }
                }
            }

            $arrGroups = array();

            if ( !empty ($arrUsers) )
            {
                foreach ($arrUsers as $strUser) {
                    $objUsersFactory = new UsersFactory();
                    $arrUser = $objUsersFactory->getUsers ($strUser);
                    $teamId = $arrUser[0]->getTeam_id ();
                    $arrGroups[$teamId] ++;
                }
            }
            //Return
            return $arrGroups;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getGroupCriteria ()
    {
        $sql = "SELECT team_id, team_name, status FROM user_management.teams";

        return $sql;
    }

    public function getTotalGroups ()
    {
        $sql = $this->getGroupCriteria ();
        $results = $this->objMysql->_query ($sql);

        if ( !empty ($results) )
        {
            return count ($results);
        }

        return 0;
    }

    /**
     * Get all Groups
     *
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Groups
     */
    public function getGroups ($teamName = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayGroup = array();
            $numRecTotal = 0;
            $arrWhere = array();
            //Verify data
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                //Return
                return array(
                    "total" => $this->getTotalGroups (),
                    "start" => (int) ((!is_null ($start)) ? $start : 0),
                    "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                    $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                    "data" => $arrayGroup
                );
            }
            //Set variables
            $arrayTotalUsersByGroup = $this->getTotalUsersByGroup ();
            $arrayTotalTasksByGroup = $this->getTotalTasksByGroup ();
            //Query
            $criteria = $this->getGroupCriteria ();
            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {
                $criteria .= " AND team_name LIKE ?";
                $arrWhere[] = "%" . $arrayFilterData['filter'] . "%";
            }
            //Number records total

            $numRecTotal = (int) $this->getTotalGroups ();
            //Query
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "team_name";
            }

            $criteria .= " ORDER BY " . $sortField;

            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " DESC";
            }
            else
            {
                $criteria .= " ASC";
            }
            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
            }
            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
            }

            $results = $this->objMysql->_query ($criteria, $arrWhere);

            foreach ($results as $row) {
                $row["GRP_USERS"] = (isset ($arrayTotalUsersByGroup[$row["team_id"]])) ? $arrayTotalUsersByGroup[$row["team_id"]] : 0;
                $row["GRP_TASKS"] = (isset ($arrayTotalTasksByGroup[$row["team_id"]])) ? $arrayTotalTasksByGroup[$row["team_id"]] : 0;
                $arrayGroup[] = $this->getGroupDataFromRecord ($row);
            }
            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($start)) ? $start : 0),
                "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                "data" => $arrayGroup
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Group from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Group
     */
    public function getGroupDataFromRecord ($record)
    {
        try {

            $objTeams = new Teams();
            $objTeams->setTeamName ($record['team_name']);
            $objTeams->setId ($record['team_id']);
            $objTeams->setDeptId ($record['dept_id']);
            $objTeams->setStatus ($record['status']);

            return $objTeams;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Group
     *
     * @param string $groupUid Unique id of Group
     *
     * return array Return an array with data of a Group
     */
    public function getGroup ($groupUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsGroup ($groupUid);
            //Get data
            $arrayTotalUsersByGroup = $this->getTotalUsersByGroup ($groupUid);
            $arrayTotalTasksByGroup = $this->getTotalTasksByGroup ($groupUid);
            //SQL
            $result = $this->objMysql->_select ("user_management.teams", array(), array("team_id" => $groupUid));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                $result[0]["GRP_USERS"] = (isset ($arrayTotalUsersByGroup[$groupUid])) ? $arrayTotalUsersByGroup[$groupUid] : 0;
                $result[0]["GRP_TASKS"] = (isset ($arrayTotalTasksByGroup[$groupUid])) ? $arrayTotalTasksByGroup[$groupUid] : 0;
            }


            //Return
            return $this->getGroupDataFromRecord ($result[0]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for User
     *
     * @param string $groupUid            Unique id of Group
     * @param array  $arrayFilterData     Data of the filters
     * @param array  $arrayUserUidExclude Unique id of Users to exclude
     *
     * return object
     */
    public function getUserCriteria ($groupUid, array $arrayWhere = null, $arrayUserUidExclude = null)
    {
        try {
            $arrWhere = array();
            $sql = "SELECT usrid, username, firstName, lastName, user_email, status "
                    . "FROM user_management.poms_users"
                    . " WHERE 1=1";

            $flag = !is_null ($arrayWhere) && is_array ($arrayWhere);
            $flagCondition = $flag && array_key_exists ('condition', $arrayWhere);
            $flagFilter = $flag && array_key_exists ('filter', $arrayWhere);

            if ( $groupUid != "" )
            {
                $sql .= " LEFT JOIN user_management.teams ON u.team_id = t.team_id";
                $sql .= " AND t.team_id = ?";
                $arrWhere[] = $groupUid;
            }
            if ( $flagCondition && !empty ($arrayWhere['condition']) )
            {
                
            }
            else
            {
                $sql .= " AND u.status = 1";
            }
            if ( !is_null ($arrayUserUidExclude) && is_array ($arrayUserUidExclude) )
            {
                $sql .= " AND u.usrid NOT IN (?)";
                $arrWhere[] = implode (",", $arrayUserUidExclude);
            }
            if ( $flagFilter && trim ($arrayWhere['filter']) != '' )
            {
                $sql .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
                $arrWhere[] = "%" . $arrayWhere['filter'] . "%";
                $arrWhere[] = "%" . $arrayWhere['filter'] . "%";
            }
            return array("sql" => $sql, "where" => $arrWhere);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a User from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data User
     */
    public function getUserDataFromRecord ($record)
    {

        try {

            $objUsers = new Users();
            $objUsers->setUserId ($record['usrid']);
            $objUsers->setUsername ($record['username']);
            $objUsers->setFirstName ($record['firstName']);
            $objUsers->setLastName ($record['lastName']);
            $objUsers->setUser_email ($record['user_email']);
            $objUsers->setStatus ($record['status']);

            return $objUsers;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Users of a Group
     *
     * @param string $option          Option (USERS, AVAILABLE-USERS)
     * @param string $groupUid        Unique id of Group
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Users of a Group
     */
    public function getUsers ($option, $groupUid, $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayUser = array();
            //Verify data

            $this->throwExceptionIfNotExistsGroup ($groupUid);

            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayUser;
            }
            //SQL
            switch ($option) {
                case "SUPERVISOR":
                    $flagPermission = true;
                    //Criteria for Supervisor
                    $criteria = $this->getUserCriteria ($groupUid, $arrayFilterData);
                    break;
                case "USERS":
                    //Criteria
                    $criteria = $this->getUserCriteria ($groupUid, $arrayFilterData);
                    $criteria = $criteria['sql'];
                    $arrWhere = $criteria['where'];
                    break;
                case "AVAILABLE-USERS":
                    //Get Uids
                    $arrayUid = array();
                    $criteria = $this->getUserCriteria ($groupUid);
                    $rsCriteria = \UsersPeer::doSelectRS ($criteria);
                    $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);
                    while ($rsCriteria->next ()) {
                        $row = $rsCriteria->getRow ();
                        $arrayUid[] = $row["USR_UID"];
                    }
                    //Criteria
                    $criteria = $this->getUserCriteria ("", $arrayFilterData, $arrayUid);
                    break;
            }
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "u.username";
            }

            $criteria .= " ORDER BY " . $sortField;

            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " DESC";
            }
            else
            {
                $criteria .= " ASC";
            }
            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
            }
            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
            }

            $results = $this->objMysql->_query ($criteria);


            foreach ($results as $row) {
                $arrayUser[] = $this->getUserDataFromRecord ($row);
            }
            //Return
            return $arrayUser;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
