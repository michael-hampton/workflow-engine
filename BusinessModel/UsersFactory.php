<?php

namespace BusinessModel;

class UsersFactory
{

    private $objMysql;
    private $userId;
    private $deptId;
    private $permId;
    private $teamId;
    private $aUserInfo;

    use Validator;

    /**
     * 
     * @param type $userId
     * @param type $deptId
     * @param type $permId
     */
    public function __construct ($userId = null, $deptId = null, $permId = null, $teamId = null)
    {
        $this->objMysql = new \Mysql2();
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
    public function countUsers (array $arrayWhere = null, $flagRecord = true, $throwException = true)
    {
        $arrWhere = array();
        $flag = !is_null ($arrayWhere) && is_array ($arrayWhere);
        $flagCondition = $flag && isset ($arrayWhere['condition']);
        $flagFilter = $flag && isset ($arrayWhere['filter']);

        $criteria = $this->getUserCriteria ();

        if ( $flagCondition && !empty ($arrayWhere['condition']) )
        {
            foreach ($arrayWhere['condition'] as $value) {
                $value2 = !isset ($value[2]) ? " = " : $value[2];
                $criteria .= " AND " . $value[0] . $value2 . " ?";
                $arrWhere[] = $value[1];
            }
        }
        else
        {
            $criteria .= " AND u.status = 1";
        }
        if ( $flagFilter && trim ($arrayWhere['filter']) != '' )
        {
            $search = (isset ($arrayWhere['filterOption'])) ? $arrayWhere['filterOption'] : '';

            $criteria .= " AND (u.username LIKE ? OR u.firstName LIKE ? OR lastName LIKE ?)";
            $arrWhere[] = "%" . $search . "%";
            $arrWhere[] = "%" . $search . "%";
            $arrWhere[] = "%" . $search . "%";
        }

        $criteria .= " GROUP BY u.usrid";

        $arrUsers = $this->objMysql->_query ($criteria, $arrWhere);

        return count ($arrUsers);
    }

    /**
     * Get criteria for User
     *
     * return object
     */
    public function getUserCriteria ()
    {
        try {
            $sql = "SELECT
                    u.*,
                    r.role_name ,
                    d.department
                    FROM user_management.poms_users u
                    LEFT JOIN user_management.user_roles ur ON ur.`userId` = u.usrid
                    LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                    LEFT join user_management.departments d ON d.id - u.dept_id
                    WHERE 1=1";

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

            if ( !is_object ($obj) || empty ($obj) )
            {
                throw new Exception ("ID_USER_DOES_NOT_EXIST");
            }
        } catch (Exception $e) {
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

            if ( $userUidToExclude != "" )
            {
                $criteria .= " AND usrid != ?";
                $arrWhere[] = $userUidToExclude;
            }

            $criteria .= " AND username = ?";

            $criteria .= " GROUP BY u.usrid";
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
                throw new \Exception ("Username already exists");
            }
        } catch (\Exception $e) {
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
    public function testPassword ()
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
            //$arrayUserData = ($userUid == "") ? array() : $this->getUser ($userUid, true);
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
                $this->throwExceptionIfPasswordIsInvalid ($arrayData["USR_NEW_PASS"]);
            }

            if ( isset ($arrayData["replacedBy"]) && $arrayData["replacedBy"] != "" )
            {
                $obj = $this->getUser ($arrayData['replacedBy']);

                if ( !is_object ($obj) || !get_class ($obj) === "Users" )
                {
                    throw new \Exception ("ID_USER_DOES_NOT_EXIST " . $arrayData["USR_REPLACED_BY"]);
                }
            }

            if ( isset ($arrayData["USR_DUE_DATE"]) )
            {

                $arrayUserDueDate = explode ("-", $arrayData["USR_DUE_DATE"]);

                if ( ctype_digit ($arrayUserDueDate[0]) )
                {
                    if ( !checkdate ($arrayUserDueDate[1], $arrayUserDueDate[2], $arrayUserDueDate[0]) )
                    {
                        throw new \Exception ("ID_MSG_ERROR_DUE_DATE");
                    }
                }
                else
                {
                    throw new \Exception ("ID_MSG_ERROR_DUE_DATE");
                }
            }

            $iso = new \Iso();

            if ( isset ($arrayData["country"]) && $arrayData["country"] != "" )
            {
                $obj = $iso->getCountries ($arrayData['country']);

                if ( $obj === false )
                {
                    throw new \Exception ("ID_INVALID_VALUE_FOR COUNTRY");
                }
            }

            if ( isset ($arrayData["state"]) && $arrayData["state"] != "" )
            {
                if ( !isset ($arrayData["country"]) || $arrayData["country"] == "" )
                {
                    throw new \Exception ("ID_INVALID_VALUE_FOR COUNTRY");
                }

                $obj = $iso->retrieveSubdivisionByPk ($arrayData["country"], $arrayData["state"]);

                if ( $obj === false )
                {
                    throw new \Exception ("ID_INVALID_VALUE_FOR CITY");
                }
            }
            if ( isset ($arrayData["location"]) && $arrayData["location"] != "" )
            {
                if ( !isset ($arrayData["country"]) || $arrayData["country"] == "" )
                {
                    throw new \Exception ("ID_INVALID_VALUE_FOR COUNTRY");
                }
                $obj = $iso->retrieveLocationByPk ($arrayData["country"], $arrayData["location"]);

                if ( $obj === false )
                {
                    throw new \Exception ("ID_INVALID_VALUE_FOR TOWN");
                }
            }

            if ( isset ($arrayData["calendar"]) && $arrayData["calendar"] != "" )
            {
                $obj = (new \CalendarDefinition())->retrieveByPk ($arrayData["calendar"]);

                if ( !is_object ($obj) || get_class ($obj) !== "CalendarDefinition" )
                {
                    throw new Exception ("ID_CALENDAR_DOES_NOT_EXIST");
                }
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
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            /* ----------------------------------********--------------------------------- */
            $this->throwExceptionIfDataIsInvalid ("", $arrayData);

            //Create
            try {

                $arrayData['dept_id'] = trim ($arrayData['dept_id']) !== "" ? $arrayData['dept_id'] : 1;

                $password = new \Password();
                $user = new \Users();

                $arrayData["USR_PASSWORD"] = $password->hashPassword ($arrayData["password"]);

                $arrayData["USR_BIRTHDAY"] = (isset ($arrayData["USR_BIRTHDAY"])) ? $arrayData["USR_BIRTHDAY"] : date ("Y-m-d");
                $arrayData["USR_LOGGED_NEXT_TIME"] = (isset ($arrayData["USR_LOGGED_NEXT_TIME"])) ? $arrayData["USR_LOGGED_NEXT_TIME"] : 0;
                $arrayData["USR_CREATE_DATE"] = date ("Y-m-d H:i:s");
                $arrayData["USR_UPDATE_DATE"] = date ("Y-m-d H:i:s");

                //$password->verifyHashPassword ($arrayData['password'], '736b3b43759fa498fb0a3d890ef533d2');

                $userUid = $user->createUser ($arrayData, $arrayData["role_id"]);

                if ( isset ($_FILES['upload']) && !empty ($_FILES['upload']['name']) )
                {

                    $this->uploadImage ($userUid, $_FILES);
                }

                //Save Calendar assigment
                if ( isset ($arrayData["calendar"]) )
                {
                    //Save Calendar ID for this user
                    $calendar = new \CalendarDefinition();
                    $calendar->assignCalendarTo ($userUid, $arrayData["calendar"], "USER");
                }

                //Create in workflow
                //Return
                return $this->getUser ($userUid);
            } catch (Exception $e) {
                throw $e;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update User
     *
     * @param string $userUid       Unique id of User
     * @param array  $arrayData     Data
     * @param string $userUidLogged Unique id of User logged
     *
     * return array Return data of the User updated
     */
    public function update ($userUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data


            /* ----------------------------------********--------------------------------- */
            //Verify data
            $this->throwExceptionIfNotExistsUser ($userUid);
            $this->throwExceptionIfDataIsInvalid ($userUid, $arrayData);
            //Permission Admin
            //$countPermission = 0;
            //$permission = $this->loadUserRolePermission ("PROCESSMAKER", $userUidLogged);
            //foreach ($permission as $key => $value) {
            //if ( preg_match ('/^(?:PM_USERS|PM_EDITPERSONALINFO)$/', $value['PER_CODE']) )
            //{
            //$countPermission = $countPermission + 1;
            //break;
            //}
            //}
            //if ( $countPermission == 0 )
            //{
            //throw new \Exception (\G::LoadTranslation ("ID_USER_CAN_NOT_UPDATE", array($userUidLogged)));
            //}
            //Update

            try {
                $user = new \Users();
                if ( isset ($arrayData['password']) )
                {
                    $password = new \Password();
                    $arrayData['USR_PASSWORD'] = $password->hashPassword ($arrayData['password']);
                }

                $arrayData["USR_UID"] = $userUid;
                $arrayData["USR_UPDATE_DATE"] = date ("Y-m-d H:i:s");

                //Update in rbac
                $arrayData['dept_id'] = trim ($arrayData['dept_id']) !== "" ? $arrayData['dept_id'] : 1;

                if ( isset ($arrayData["role_id"]) )
                {
                    $result = $user->updateUser ($arrayData, $arrayData["role_id"]);
                }
                else
                {
                    $result = $user->updateUser ($arrayData);
                }
                //Update in workflow
                //Save Calendar assigment

                return true;
            } catch (Exception $e) {
                throw $e;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Users
     *
     * @param array  $arrayWhere     Where (Condition and filters)
     * @param string $sortField      Field name to sort
     * @param string $sortDir        Direction of sorting (ASC, DESC)
     * @param int    $start          Start
     * @param int    $limit          Limit
     * @param bool   $flagRecord     Flag that set the "getting" of record
     * @param bool   $throwException Flag to throw the exception (This only if the parameters are invalid)
     *                               (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array Return an array with all Users, ThrowTheException/FALSE otherwise
     */
    public function getUsers (
    array $arrayWhere = null, $sortField = null, $sortDir = null, $start = null, $limit = null, $flagRecord = false, $throwException = true
    )
    {
        try {
            $arrayUser = array();
            $numRecTotal = 0;
            //Verify data and Set variables
            $flag = !is_null ($arrayWhere) && is_array ($arrayWhere);
            $flagCondition = $flag && isset ($arrayWhere['condition']);
            $flagFilter = $flag && isset ($arrayWhere['filter']);
            $result = true;

            if ( $result !== true )
            {
                if ( $throwException )
                {
                    throw new Exception ($result);
                }
                else
                {
                    return false;
                }
            }
            //Set variables
            $filterName = "filter";
            if ( $flagFilter )
            {
                $filterName = (isset ($arrayWhere['filterOption'])) ? $arrayWhere['filterOption'] : '';
            }
            //Get data
            if ( !is_null ($limit) && (string) ($limit) == '0' )
            {
                //Return
                return array(
                    "total" => $numRecTotal,
                    "start" => (int) ((!is_null ($start)) ? $start : 0),
                    "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                    $filterName => ($flagFilter) ? $arrayWhere['filter'] : '',
                    "data" => $arrayUser
                );
            }
            //Query
            $arrWhere = array();
            $criteria = $this->getUserCriteria ();
            if ( $flagCondition && !empty ($arrayWhere['condition']) )
            {
                foreach ($arrayWhere['condition'] as $value) {
                    $value2 = !isset ($value[2]) ? " = " : $value[2];
                    $criteria .= " AND " . $value[0] . $value2 . " ?";
                    $arrWhere[] = $value[1];
                }
            }
            else
            {
                $criteria .= " AND u.status = 1";
            }

            if ( $flagFilter && trim ($arrayWhere['filter']) != '' )
            {
                $search = isset ($arrayWhere['filterOption']) ? $arrayWhere['filterOption'] : '';

                $criteria .= " AND (u.username LIKE ? OR u.firstName LIKE ? OR lastName LIKE ?)";
                $arrWhere[] = "%" . $search . "%";
                $arrWhere[] = "%" . $search . "%";
                $arrWhere[] = "%" . $search . "%";
            }
            //Number records total
            $numRecTotal = $this->countUsers ($arrayWhere, $flagRecord, $throwException);
            //Query

            $criteria .= " GROUP BY u.usrid ";

            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "u.username";
            }

            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $criteria .= " ORDER BY " . $sortField . " ASC";
            }

            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
                // calculating displaying pages
                $_SESSION["pagination"]["total_pages"] = (int) ceil (($numRecTotal / $limit));
            }

            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
                $current_page = $start;
                $_SESSION["pagination"]["current_page"] = $current_page;
            }

            $_SESSION["pagination"]["total_counter"] = $numRecTotal;

            $records = $this->objMysql->_query ($criteria, $arrWhere);

            foreach ($records as $record) {
                $arrayUser[] = ($flagRecord) ? $record : $this->__getUserCustomRecordFromRecord ($record);
            }

            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($start)) ? $start : 0),
                "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                $filterName => ($flagFilter) ? $arrayWhere['filter'] : '',
                "data" => $arrayUser
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get custom record
     *
     * @param array $record Record
     *
     * @return array Return an array with custom record
     */
    private function __getUserCustomRecordFromRecord (array $record)
    {
        try {
            $objUsers = new \Users();

            //Get photo
            $pathPhotoUser = PATH_IMAGES_ENVIRONMENT_USERS . $record["username"] . ".jpg";
            if ( !file_exists ($pathPhotoUser) )
            {
                $pathPhotoUser = "/FormBuilder/public/img/users/nouser.jpg";
            }

            $objUsers->setUserId ($record['usrid']);
            $objUsers->setFirstName ($record['firstName']);
            $objUsers->setLastName ($record['lastName']);
            $objUsers->setDept_id ($record['dept_id']);
            $objUsers->setImg_src ($pathPhotoUser);
            $objUsers->setStatus ($record['status']);
            $objUsers->setPassword ($record['password']);
            $objUsers->setUsername ($record['username']);
            $objUsers->setUser_email ($record['user_email']);
            $objUsers->setTeam_id ($record['team_id']);
            $objUsers->setUserReplaces ($record['user_replaces']);

            return $objUsers;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Upload image User
     *
     * @param string $userUid Unique id of User
     *
     */
    public function uploadImage ($userUid, $arrFiles)
    {
        try {

            //Verify data
            $this->throwExceptionIfNotExistsUser ($userUid);
            if ( !$arrFiles )
            {
                throw new Exception ("ID_UPLOAD_ERR_NO_FILE");
            }

            if ( !isset ($arrFiles["upload"]) )
            {
                throw new Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            if ( $arrFiles['upload']['error'] != 1 )
            {
                if ( $arrFiles['upload']['tmp_name'] != '' )
                {
                    $objFile = new \BusinessModel\FileUpload();

                    $aAux = explode ('.', $arrFiles['upload']['name']);
                    $objFile->uploadFile ($arrFiles['upload']['tmp_name'], PATH_IMAGES_ENVIRONMENT_USERS, $userUid . '.' . $aAux[1]);
                    $objFile->resizeImage (PATH_IMAGES_ENVIRONMENT_USERS . $userUid . '.' . $aAux[1], 96, 96, PATH_IMAGES_ENVIRONMENT_USERS . $userUid . '.gif');
                }
            }
            else
            {
                throw new Exception ('Error uploading file' . ' ' . $_FILES['USR_PHOTO']['error']);
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
            //Get data
            //SQL

            $criteria = $this->getUserCriteria ();

            $criteria .= " AND u.usrid = ?";

            $result = $this->objMysql->_query ($criteria, [$userUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                throw new \Exception ("Failed to find user");
            }

            //Return
            return (!$flagGetRecord) ? $this->__getUserCustomRecordFromRecord ($result[0]) : $result[0];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**

     * Gets the roles and permission for one RBAC_user

     * gets the Role and their permissions for one User

     * @access public

     * @param string $sUser the user

     * @return $this->aUserInfo[ $sSystem ]

     */
    public function loadUserRolePermission ($sUser)
    {
        $objUser = (new UsersFactory())->getUser ($sUser);
        $objUserRole = new \BusinessModel\RoleUser();
        $fieldsRoles = $objUserRole->getRolesForUser ($objUser);

        $fieldsPermissions = [];

        foreach ($fieldsRoles as $fieldsRole) {
            $fieldsPermissions[] = $objUserRole->getAllPermissions (new \Role ($fieldsRole['role_id']));
        }

        $permissions = [];

        foreach ($fieldsPermissions as $fieldsPermission) {
            foreach ($fieldsPermission as $field) {
                $permissions[] = $field;
            }
        }

        $this->aUserInfo['USER_INFO'] = $this->getUser ($sUser);
        $this->aUserInfo['ROLE'] = $fieldsRoles;

        $this->aUserInfo['PERMISSIONS'] = $fieldsPermissions;

        return $permissions;
    }

    public function checkPermission (\BaseUser $objUser, $permissionCode)
    {
        try {

            $flagPermission = false;

            if ( trim ($objUser->getUserId ()) === "" )
            {
                throw new Exception ("User could not be found");
            }

            $userUid = $objUser->getUserId ();

            $arrayUserRolePermission = $this->loadUserRolePermission ($userUid);

            foreach ($arrayUserRolePermission as $value) {

                if ( trim (strtolower ($value["perm_name"])) == trim (strtolower ($permissionCode)) )
                {

                    $flagPermission = true;

                    break;
                }
            }

            return $flagPermission;
        } catch (\Exception $e) {

            throw $e;
        }
    }

}
