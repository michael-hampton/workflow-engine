<?php

class UsersFactory
{

    private $objMysql;
    private $userId;
    private $deptId;
    private $permId;
    private $teamId;

    /**
     * 
     * @param type $userId
     * @param type $deptId
     * @param type $permId
     */
    public function __construct ($userId = null, $deptId = null, $permId = null, $teamId = null)
    {
        $this->objMysql = new Mysql2();
        $this->userId = $userId;
        $this->deptId = $deptId;
        $this->permId = $permId;
        $this->teamId = $teamId;
    }

    /**
     * 
     * @param type $searchText
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return type
     */
    public function countUsers ($searchText = null, $pageLimit = 10, $page = 0, $strOrderBy = "u.username", $strOrderDir = "ASC")
    {
        $arrWhere = array();

        $query = "SELECT
                    u.*,
                    r.role_name ,
                    d.department
                    FROM user_management.poms_users u
                    LEFT JOIN user_management.user_roles ur ON ur.`userId` = u.usrid
                    LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                    LEFT join user_management.departments d ON d.id - u.dept_id
                    WHERE 1=1";

        if ( !empty ($searchText) && $searchText !== null )
        {
            $query .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
            $arrWhere[] = "%" . $searchText . "%";
            $arrWhere[] = "%" . $searchText . "%";
        }

        if ( $this->userId !== null )
        {
            $query .= " AND u.usrid = ?";
            $arrWhere[] = $this->userId;
        }


        $query .= " GROUP BY u.usrid";
        $query .= " ORDER BY " . $strOrderBy . " " . $strOrderDir;

        $arrUsers = $this->objMysql->_query ($query, $arrWhere);

        return count ($arrUsers);
    }

    /**
     * 
     * @param type $searchText
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return \Users
     */
    public function getUsers ($searchText = null, $pageLimit = 10, $page = 0, $strOrderBy = "u.username", $strOrderDir = "ASC")
    {
        $totalRows = $this->countUsers ($searchText, $pageLimit, $page, $strOrderBy, $strOrderDir);

        $arrWhere = array();

        $query = "SELECT
                    u.*,
                    r.role_name ,
                    d.department
                    FROM user_management.poms_users u
                    LEFT JOIN user_management.user_roles ur ON ur.`userId` = u.usrid
                    LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                    LEFT join user_management.departments d ON d.id - u.dept_id
                    WHERE 1=1";

        if ( !empty ($searchText) && $searchText !== null )
        {
            $query .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
            $arrWhere[] = "%" . $searchText . "%";
            $arrWhere[] = "%" . $searchText . "%";
        }

        if ( $this->userId !== null )
        {
            $query .= " AND u.usrid = ?";
            $arrWhere[] = $this->userId;
        }

        $query .= " GROUP BY u.usrid";

        $query .= " ORDER BY " . $strOrderBy . " " . $strOrderDir;

        ///////////////////////////////////////////////////////////////////////////////////////////////
        //
        //      Pagination
        //

        
        //all rows
        $_SESSION["pagination"]["total_counter"] = $totalRows;

        $current_page = $page;
        $startwith = $pageLimit * $page;
        $total_pages = $totalRows / $pageLimit;
        $_SESSION["pagination"]["current_page"] = $current_page;

        // calculating displaying pages
        $_SESSION["pagination"]["total_pages"] = (int) ($totalRows / $pageLimit);
        if ( fmod ($totalRows, $pageLimit) > 0 )
            $_SESSION["pagination"]["total_pages"] ++;

        $query .= " LIMIT " . $page . ", " . $pageLimit;

        $arrUsers = $this->objMysql->_query ($query, $arrWhere);

        $arrAllUsers = array();


        if ( !empty ($arrUsers) )
        {
            foreach ($arrUsers as $key => $arrUser) {
                $objUser = new Users ($arrUser['usrid']);
                $objUser->loadObject ($arrUser);
                $arrAllUsers[$key] = $objUser;
            }

            return $arrAllUsers;
        }

        return [];
    }

    /**
     * 
     * @return \Departments
     */
    public function getDepartments ()
    {
        $arrWhere = array();

        if ( $this->deptId !== null )
        {
            $arrWhere['id'] = $this->deptId;
        }

        $arrDepartments = $this->objMysql->_select ("user_management.departments", array(), $arrWhere);

        $arrAllDepartments = array();

        if ( !empty ($arrDepartments) )
        {
            foreach ($arrDepartments as $key => $arrDepartment) {

                $objDepartments = new Departments();
                $objDepartments->loadObject ($arrDepartment);
                $arrAllDepartments[$key] = $objDepartments;
            }

            return $arrAllDepartments;
        }

        return [];
    }

    /**
     * 
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return \Teams
     */
    public function getTeams ($pageLimit = 10, $page = 0, $strOrderBy = "team_name", $strOrderDir = "ASC", $teamName = null)
    {
        $arrWhere = array();

        $sql = "SELECT * FROM user_management.teams WHERE 1=1";

        if ( $this->teamId !== null )
        {
            $sql .= " AND team_id = ?";
            $arrWhere[] = $this->teamId;
        }

        if ( $teamName !== null )
        {
            $sql .= " AND team_name LIKE ?";
            $arrWhere[] = "%" . $teamName . "%";
        }

        $sql .= " ORDER BY " . $strOrderBy . " " . $strOrderDir;

        ///////////////////////////////////////////////////////////////////////////////////////////////
        //
        //      Pagination
        //


//        $current_page = $page;
//        $startwith = $pageLimit * $page;
//        $total_pages = $totalRows / $pageLimit;
//        $_SESSION["pagination"]["current_page"] = $current_page;
//
//        // calculating displaying pages
//        $_SESSION["pagination"]["total_pages"] = (int) ($totalRows / $pageLimit);
//        if ( fmod ($totalRows, $pageLimit) > 0 )
//            $_SESSION["pagination"]["total_pages"] ++;

        $sql .= " LIMIT " . $page . ", " . $pageLimit;

        $arrTeams = $this->objMysql->_query ($sql, $arrWhere);

        $arrAllTeams = array();

        if ( !empty ($arrTeams) )
        {
            foreach ($arrTeams as $key => $arrTeam) {

                $objTeams = new Teams();
                $objTeams->loadObject ($arrTeam);
                $arrAllTeams[$key] = $objTeams;
            }

            return $arrAllTeams;
        }

        return [];
    }
    
      /**
     * Create User
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new User created
     */
    public function create(array $arrayData)
    {
        try {

            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData);
            $this->throwExceptionIfDataIsEmpty($arrayData);

            //Set data
   

            /*----------------------------------********---------------------------------*/

            $this->throwExceptionIfDataIsInvalid("", $arrayData);

            //Create

            try {
                $user = new Users();

                $arrayData["USR_CREATE_DATE"]      = date("Y-m-d H:i:s");
                $arrayData["USR_UPDATE_DATE"]      = date("Y-m-d H:i:s");

                
                //
                //$arrayData["USR_STATUS"] = $userStatus;

		$user->loadObject($arrayData);

		 if ($this->validate()) {

			 $userUid = $user->save($arrayData);
		} else {

		}

               

		 if ($sRolCode != '') {
            		$this->assignRoleToUser( $userUid, $sRolCode );
        	}


                //Return
                return $this->getUser($userUid);
            } catch (\Exception $e) {
                $cnn->rollback();

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

	public function update ($fields)
    {
        
        try {
           $user = new Users();
		$user->loadObject($fields);

            if ($this->validate()) {
                $result = $this->save();
                return $result;
            } else {
                $con->rollback();
                throw (new Exception(G::LoadTranslation("ID_FAILED_VALIDATION_IN_CLASS1", SYS_LANG, array("CLASS" => get_class($this)))));
            }
        } catch (Exception $e) {
            $con->rollback();
            throw ($e);
        }
    }

    public function remove ($UsrUid)
    {
        try {
            $this->setUsrUid( $UsrUid );
            $result = $this->delete();
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }


 /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $userUid   Unique id of User
     * @param array  $arrayData Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid($userUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayUserData = ($userUid == "")? array() : $this->getUser($userUid, true);
            $flagInsert = ($userUid == "")? true : false;

            $arrayFinalData = array_merge($arrayUserData, $arrayData);


            //Verify data
            if (isset($arrayData["USR_USERNAME"])) {
                $this->throwExceptionIfExistsName($arrayData["USR_USERNAME"], $this->arrayFieldNameForException["usrUsername"], $userUid);
            }

            if (isset($arrayData["USR_EMAIL"])) {
                if (!filter_var($arrayData["USR_EMAIL"], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception($this->arrayFieldNameForException["usrEmail"] . ": " . \G::LoadTranslation("ID_INCORRECT_EMAIL"));
                }
            }

            if (isset($arrayData["USR_NEW_PASS"])) {
                $this->throwExceptionIfPasswordIsInvalid($arrayData["USR_NEW_PASS"], $this->arrayFieldNameForException["usrNewPass"]);
            }

           

            if (isset($arrayData["USR_ROLE"])) {
                
            }

           

            if (isset($arrayData["DEP_UID"]) && $arrayData["DEP_UID"] != "") {
                $department = new \Department();

                if (!$department->existsDepartment($arrayData["DEP_UID"])) {
                    throw new Exception("ID_DEPARTMENT_NOT_EXIST");
                }
            }

        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Save Department
     * @var string $dep_data. Data for Process
     * @var string $create. Flag for create or update
     *
     * @access public
     *
     * @return array
     */
    public function saveDepartment($dep_data, $create = true)
    {
        $this->isArray($dep_data, '$dep_data');
        $this->isNotEmpty($dep_data, '$dep_data');
        $this->isBoolean($create, '$create');

        if ($create) {
            unset($dep_data["DEP_UID"]);
        }

        $oDepartment = new Department();

        if (isset($dep_data['DEP_UID']) && $dep_data['DEP_UID'] != '') {
            $this->depUid($dep_data['DEP_UID']);
        }
        
        if (isset($dep_data['DEP_MANAGER']) && $dep_data['DEP_MANAGER'] != '') {
            $this->usrUid($dep_data['DEP_MANAGER'], 'dep_manager');
        }
        if (isset($dep_data['DEP_STATUS'])) {
            $this->depStatus($dep_data['DEP_STATUS']);
        }

	$oDepartment->loadObject($dep_data);

        if ($create) {
            if (isset($dep_data['DEP_TITLE'])) {
                $this->throwExceptionIfExistsTitle($dep_data["DEP_TITLE"], strtolower("DEP_TITLE"));
            } else {
                throw (new Exception("Title is missing"));
            }
	}

            $dep_uid = $oDepartment->save();
            $response = $this->getDepartment($dep_uid);
            return $response;
    }

    /**
     * Delete department
     * @var string $dep_uid. Uid for department
     *
     * @access public
     *
     * @return array
     */
    public function deleteDepartment($dep_uid)
    {
        $dep_uid = $this->depUid($dep_uid);
        $oDepartment = new Department();
        $countUsers = $oDepartment->cantUsersInDepartment($dep_uid);
        if ($countUsers != 0) {
            throw (new Exception("ID_CANT_DELETE_DEPARTMENT_HAS_USERS"));
        }
        
        $oDepartment->delete($dep_uid);
    }


}
