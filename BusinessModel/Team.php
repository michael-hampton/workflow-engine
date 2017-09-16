<?php

namespace BusinessModel;

class Team
{

    /**
     * Constructor of the class
     *
     * return void
     */
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
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
    public function existsTitle ($groupTitle, $teamId = null)
    {
        try {

            $arrWhere = array();
            $sql = "SELECT * FROM user_management.teams WHERE team_name = ?";
            $arrWhere[] = $groupTitle;

            if ( $teamId !== null )
            {
                $sql .= " AND team_id != ?";
                $arrWhere[] = $teamId;
            }

            $result = $this->objMysql->_query ($sql, $arrWhere);

            if ( isset ($result[0]['team_name']) && !empty ($result[0]['team_name']) )
            {
                return true;
            }

            return false;
        } catch (Exception $e) {
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
            if ( !$this->groupExists ($groupUid) )
            {
                throw new \Exception ("GROUP DOES NOT EXIST");
            }
        } catch (\Exception $e) {
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
    public function throwExceptionIfExistsTitle ($groupTitle, $teamId = null)
    {
        try {
            if ( $this->existsTitle ($groupTitle, $teamId) )
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
            //Verify data
            $this->throwExceptionIfExistsTitle ($arrayData["team_name"]);
            //Create
            $group = new \Team();
            $groupUid = $group->create ($arrayData);
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
            //Verify data
            $this->throwExceptionIfNotExistsGroup ($groupUid);
            if ( isset ($arrayData["team_name"]) )
            {
                $this->throwExceptionIfExistsTitle ($arrayData["team_name"], $groupUid);
            }
            //Update
            $group = new \Team();
            $arrayData["team_id"] = $groupUid;
            $group->update ($arrayData);
            //Return
            unset ($arrayData["GRP_UID"]);

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
    public function delete (\Team $objTeam)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsGroup ($objTeam->getId ());
            $arrayTotalTasksByGroup = $this->getTotalTasksByGroup ($objTeam->getId ());
            if ( isset ($arrayTotalTasksByGroup[$objTeam->getId ()]) && $arrayTotalTasksByGroup[$objTeam->getId ()] > 0 )
            {
                throw new Exception ("ID_GROUP_CANNOT_DELETE_WHILE_ASSIGNED_TO_TASK");
            }

            //Delete
            $group = new \Team();
            $group->remove ($objTeam);


            $objPermissions = new \ObjectPermissions (null);

            try {
                $objPermissions->deleteAll ("team", $objTeam->getId ());
            } catch (Exception $ex) {
                
            }
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

            $sql = "SELECT COUNT(u.username) AS NUM_REC, t.team_id FROM user_management.poms_users u
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

            $arrElements = array_keys ($workflowData['elements']);

            if ( !empty ($arrElements) )
            {
                foreach ($arrElements as $elementId) {

                    foreach ($auditData['elements'][$elementId]['steps'] as $audit) {
                        $arrUsers[] = $audit['claimed'];
                    }
                }
            }


            $arrGroups = array();

            if ( !empty ($arrUsers) )
            {
                foreach ($arrUsers as $strUser) {
                    $objUsersFactory = new \BusinessModel\UsersFactory();
                    $arrUser = $objUsersFactory->getUsers (array("filter" => "user", "filterOption" => $strUser));

                    $teamId = $arrUser['data'][0]->getTeam_id ();

                    $arrGroups[$teamId] = isset ($arrGroups[$teamId]) ? $arrGroups[$teamId] ++ : 1;
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
        $sql = "SELECT team_id, team_name, dept_id, status FROM user_management.teams";

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
    public function getGroups ($arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayGroup = array();
            $arrWhere = array();

            //Set variables
            $filterName = "filter";

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
                $criteria .= " WHERE team_name LIKE ?";
                $arrWhere[] = "%" . $arrayFilterData['filter'] . "%";

                $filterName = (isset ($arrayFilterData["filterOption"])) ? $arrayFilterData["filterOption"] : "";
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
            
            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
            }
            
            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
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
                "total_pages" => (int) ceil ($numRecTotal / $limit),
                "page" => (int) !is_null ($start) ? $start : 0,
                "limit" => (int) !is_null ($limit) ? $limit : 0,
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

            $objTeam = new \Team();
            $objTeam->setTeamName ($record['team_name']);
            $objTeam->setId ($record['team_id']);
            $objTeam->setDeptId ($record['dept_id']);
            $objTeam->setStatus ($record['status']);

            return $objTeam;
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

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }


            $result[0]["GRP_USERS"] = (isset ($arrayTotalUsersByGroup[$groupUid])) ? $arrayTotalUsersByGroup[$groupUid] : 0;
            $result[0]["GRP_TASKS"] = (isset ($arrayTotalTasksByGroup[$groupUid])) ? $arrayTotalTasksByGroup[$groupUid] : 0;

            //Return
            $arrResult = $this->getGroupDataFromRecord ($result[0]);

            return $arrResult;
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
            $sqlWhere = ' WHERE 1=1 ';

            $sql = "SELECT usrid, username, firstName, lastName, user_email, u.status, u.team_id "
                    . "FROM user_management.poms_users u";

            $flag = !is_null ($arrayWhere) && is_array ($arrayWhere);
            $flagCondition = $flag && array_key_exists ('condition', $arrayWhere);
            $flagFilter = $flag && array_key_exists ('filter', $arrayWhere);

            if ( $groupUid != "" )
            {
                $sql .= " LEFT JOIN user_management.teams t ON u.team_id = t.team_id";
                $sqlWhere .= " AND t.team_id = ?";
                $arrWhere[] = $groupUid;
            }
            if ( $flagCondition && !empty ($arrayWhere['condition']) )
            {
                
            }
            else
            {
                $sqlWhere .= " AND u.status = 1";
            }
            if ( !is_null ($arrayUserUidExclude) && is_array ($arrayUserUidExclude) )
            {
                $sqlWhere .= " AND u.usrid NOT IN (?)";
                $arrWhere[] = implode (",", $arrayUserUidExclude);
            }
            if ( $flagFilter && trim ($arrayWhere['filter']) != '' )
            {
                $sqlWhere .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
                $arrWhere[] = "%" . $arrayWhere['filter'] . "%";
                $arrWhere[] = "%" . $arrayWhere['filter'] . "%";
            }

            $sql = $sql . $sqlWhere;

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

            $objUsers = new \Users();
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
                    //$flagPermission = true;
                    //Criteria for Supervisor
                    $criteria = $this->getUserCriteria ($groupUid, $arrayFilterData);
                    break;
                case "USERS":
                    //Criteria
                    $criteria = $this->getUserCriteria ($groupUid, $arrayFilterData);
                    $arrWhere = $criteria['where'];
                    $criteria = $criteria['sql'];

                    break;
                case "AVAILABLE-USERS":
                    //Get Uids
                    $arrayUid = array();
                    $criteria = $this->getUserCriteria (null, $arrayFilterData);
                    $criteria = $criteria['sql'];

                    $criteria .= " AND u.team_id = null";
                    $results = $this->objMysql->_query ($criteria);

                    foreach ($results as $row) {
                        if ( (int) $row['team_id'] === 0 )
                        {
                            $arrayUid[] = $row["usrid"];
                        }
                    }

                    foreach ($arrayUid as $userId) {
                        $objUser = new \BusinessModel\UsersFactory();
                        $arrayUser[] = $objUser->getUser ($userId);
                    }

                    return $arrayUser;

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

            $results = $this->objMysql->_query ($criteria, $arrWhere);


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
