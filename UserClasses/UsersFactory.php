<?php

class UsersFactory
{

    private $objMysql;
    private $userId;
    private $deptId;
    private $permId;
    private $teamId;

    use Validator;

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
    public function create (array $arrayData)
    {
        try {

            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData);
            $this->throwExceptionIfDataIsEmpty ($arrayData);
            $this->throwExceptionIfDataIsInvalid ("", $arrayData);


            $user = new Users();
            //Set data
            $arrayData["USR_CREATE_DATE"] = date ("Y-m-d H:i:s");
            $arrayData["USR_UPDATE_DATE"] = date ("Y-m-d H:i:s");

            $user->loadObject ($arrayData);

            if ( $user->validate () )
            {
                $userUid = $user->save ($arrayData);
            }
            else
            {
                
            }

//                if ( $sRolCode != '' )
//                {
//                    $this->assignRoleToUser ($userUid, $sRolCode);
//                }
            //Return
            return $this->getUser ($userUid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update ($fields)
    {

        try {
            $user = new Users();
            $user->loadObject ($fields);

            if ( $user->validate () )
            {
                $result = $user->save ();
                return $result;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $user->getValidationFailures ();

                foreach ($aValidationFailures as $message) {
                    $sMessage .= $message . '<br />';
                }
                throw (new Exception ('The user cannot be updated!<br />' . $sMessage));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function remove ($UsrUid)
    {
        try {
            $user = new Users();
            $user->setUserId ($UsrUid);
            $result = $user->disableUser ();
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Get criteria for User
     *
     * return object
     */
    public function getUserCriteria ()
    {
        try {
            $sql = "SELECT * FROM user_management.poms_users";

            return $sql;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exist the User in table USERS
     *
     * @param string $userUid               Unique id of Email Server
     *
     * return void Throw exception if does not exist the User in table USERS
     */
    public function throwExceptionIfNotExistsUser ($userUid)
    {
        try {
            $obj = $this->getUser ($userUid);
            if ( !isset ($obj[0]) || empty ($obj[0]) )
            {
                throw new Exception ("ID_USER_DOES_NOT_EXIST");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a User
     *
     * @param string $userUid       Unique id of User
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a User
     */
    public function getUser ($userUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsUser ($userUid);
            //Get data
            //SQL
            $criteria = $this->getUserCriteria ();

            $criteria .= " WHERE usrid = ?";
            $arrWhere = array($userUid);

            $result = $this->objMysql->_query ($criteria, $arrWhere);

            //Return
            return (!$flagGetRecord) ? $this->__getUserCustomRecordFromRecord ($result) : $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Name of a User
     *
     * @param string $userName         Name
     * @param string $userUidToExclude Unique id of User to exclude
     *
     * return bool Return true if exists the Name of a User, false otherwise
     */
    public function existsName ($userName, $userUidToExclude = "")
    {
        try {
            $arrWhere = array();
            $criteria = $this->getUserCriteria ();

            $criteria .= " WHERE 1=1";

            if ( $userUidToExclude != "" )
            {
                $criteria .= " AND usrid != ?";
                $arrWhere[] = $userUidToExclude;
            }

            $criteria .= " AND username = ?";
            $arrWhere[] = $userName;

            //QUERY
            $result = $this->objMysql->_query ($criteria, $arrWhere);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Name of a User
     *
     * @param string $userName              Name
     * @param string $userUidToExclude      Unique id of User to exclude
     *
     * return void Throw exception if exists the title of a User
     */
    public function throwExceptionIfExistsName ($userName, $userUidToExclude = "")
    {
        try {
            if ( $this->existsName ($userName, $userUidToExclude) )
            {
                throw new Exception ("ID_USER_NAME_ALREADY_EXISTS");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify password
     *
     * @param string $userPassword          Password
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if password is invalid
     */
    public function throwExceptionIfPasswordIsInvalid ($userPassword)
    {
        try {
            $result = $this->testPassword ($userPassword);
            if ( !$result )
            {
                throw new Exception ("Invalid Password given");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * to test Password
     *
     * @access public
     * @param string $sPassword
     * @return array
     */
    public function testPassword ($sPassword = '')
    {
        if ( !preg_match ("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{8,}$/", $this->password) )
        {
            return false;
        }

        return true;
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $userUid   Unique id of User
     * @param array  $arrayData Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($userUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayUserData = ($userUid == "") ? array() : $this->getUser ($userUid, true);
            $flagInsert = ($userUid == "") ? true : false;

            $arrayFinalData = array_merge ($arrayUserData, $arrayData);


            //Verify data
            if ( isset ($arrayData["username"]) )
            {

                $this->throwExceptionIfExistsName ($arrayData["username"], $userUid);
            }

            if ( isset ($arrayData["user_email"]) )
            {
                if ( !filter_var ($arrayData["user_email"], FILTER_VALIDATE_EMAIL) )
                {
                    throw new Exception ("ID_INCORRECT_EMAIL");
                }
            }

            if ( isset ($arrayData["USR_NEW_PASS"]) )
            {
                $this->throwExceptionIfPasswordIsInvalid ($arrayData["USR_NEW_PASS"], $this->arrayFieldNameForException["usrNewPass"]);
            }


            if ( is_numeric ($arrayData['dept_id']) && $this->validateDeptId ($arrayData['dept_id']) === false )
            {
                throw new Exception ("Incorrect department given");
            }

            if ( isset ($this->teamId) && is_numeric ($this->team_id) && $this->validateTeamId ($this->team_id) )
            {
                throw new Exception ("Invalid team given");
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
    public function saveDepartment ($dep_data, $create = true)
    {
        $this->isArray ($dep_data, '$dep_data');
        $this->isNotEmpty ($dep_data, '$dep_data');
        $this->isBoolean ($create, '$create');

        if ( $create )
        {
            unset ($dep_data["DEP_UID"]);
        }

        $oDepartment = new Departments();

        if ( isset ($dep_data['DEP_UID']) && $dep_data['DEP_UID'] != '' )
        {
            $this->depUid ($dep_data['DEP_UID']);
        }

        if ( isset ($dep_data['department_manager']) && $dep_data['department_manager'] != '' )
        {
            $this->validateUserId ($dep_data['department_manager']);
        }
        if ( isset ($dep_data['status']) )
        {
            $this->depStatus ($dep_data['status']);
        }

        $oDepartment->loadObject ($dep_data);

        if ( $create )
        {
            if ( isset ($dep_data['department']) )
            {
                $oDepartment->throwExceptionIfExistsTitle ($dep_data["department"]);
            }
            else
            {
                throw (new Exception ("Title is missing"));
            }
        }

        $dep_uid = $oDepartment->save ();
        //$response = $this->getDepartment ($dep_uid);
        return $dep_uid;
    }

    /**
     * Delete department
     * @var string $dep_uid. Uid for department
     *
     * @access public
     *
     * @return array
     */
    public function deleteDepartment ($dep_uid)
    {
        $dep_uid = $this->validateDeptId ($dep_uid);
        $oDepartment = new Departments();
        $oDepartment->setId($dep_uid);
        $countUsers = $oDepartment->countUsersInDepartment ($dep_uid);
        if ( $countUsers != 0 )
        {
            throw (new Exception ("ID_CANT_DELETE_DEPARTMENT_HAS_USERS"));
        }

        $oDepartment->delete ();
    }

}
