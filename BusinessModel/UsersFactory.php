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
    public function countUsers (array $arrayWhere = null)
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
     * @param string $userPassword          Password
     * @param type $userPassword
     * @throws \BusinessModel\Exception
     * @throws Exception
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
     * @param type $userUid
     * @param array $arrayData
     * @throws \BusinessModel\Exception
     * @throws Exception
     * @throws \Exception
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

            if ( isset ($arrayData["USR_DUE_DATE"]) && trim($arrayData["USR_DUE_DATE"]) !== "" )
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

            if (isset($arrayData['dept_id']) && (is_numeric ($arrayData['dept_id']) && $this->validateDeptId ($arrayData['dept_id']) === false) )
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
     * 
     * @param array $arrayData
     * @param \Users $objUser
     * @param type $arrFiles
     * @return type
     * @throws \BusinessModel\Exception
     */
    public function create (array $arrayData, \Users $objUser, $arrFiles = array())
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

                $arrayData['dept_id'] = isset($arrayData['dept_id']) && trim ($arrayData['dept_id']) !== "" ? $arrayData['dept_id'] : 1;

                $password = new \Password();
                $user = new \Users();

                $arrayData["USR_PASSWORD"] = $password->hashPassword ($arrayData["password"]);

                $arrayData["USR_BIRTHDAY"] = (isset ($arrayData["USR_BIRTHDAY"])) ? $arrayData["USR_BIRTHDAY"] : date ("Y-m-d");
                $arrayData["USR_LOGGED_NEXT_TIME"] = (isset ($arrayData["USR_LOGGED_NEXT_TIME"])) ? $arrayData["USR_LOGGED_NEXT_TIME"] : 0;
                $arrayData["USR_CREATE_DATE"] = date ("Y-m-d H:i:s");
                $arrayData["USR_UPDATE_DATE"] = date ("Y-m-d H:i:s");

                //$password->verifyHashPassword ($arrayData['password'], '736b3b43759fa498fb0a3d890ef533d2');

                $userUid = $user->createUser ($objUser, $arrayData, $arrayData["role_id"]);

                if ( isset ($arrFiles['upload']) && !empty ($arrFiles['upload']['name']) )
                {

                    $this->uploadImage ($userUid, $arrFiles);
                }

                //User Properties

                $userProperty = new \UserProperties();

                $aUserProperty = $userProperty->loadOrCreateIfNotExists ($userUid, array("USR_PASSWORD_HISTORY" => serialize (array($password->hashPassword ($arrayData["password"])))));

                $aUserProperty["USR_LOGGED_NEXT_TIME"] = $arrayData["USR_LOGGED_NEXT_TIME"];

                $userProperty->update ($aUserProperty);

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
     * @param string $userUid       Unique id of User
     * @param array  $arrayData     Data
     * @param \Users $objUser
     * @return boolean
     * @throws \BusinessModel\Exception
     * @throws \Exception
     */
    public function update ($userUid, array $arrayData, \Users $objUser)
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
            $countPermission = 0;
            $permission = $this->loadUserRolePermission ($objUser);

            foreach ($permission as $value) {
                if ( preg_match ('/^(?:PM_USERS|PM_EDITPERSONALINFO)$/', $value['perm_name']) )
                {
                    $countPermission = $countPermission + 1;
                    break;
                }
            }

            if ( $countPermission == 0 )
            {
                throw new \Exception ("ID_USER_CAN_NOT_UPDATE");
            }
            
            try {
                $user = new \Users();
                if ( isset ($arrayData['password']) )
                {
                    $password = new \Password();
                    $arrayData['USR_PASSWORD'] = $password->hashPassword ($arrayData['password']);
                }

                $arrayData["USR_UID"] = $userUid;
                $arrayData["USR_LOGGED_NEXT_TIME"] = (isset ($arrayData["USR_LOGGED_NEXT_TIME"])) ? $arrayData["USR_LOGGED_NEXT_TIME"] : 0;
                $arrayData["USR_UPDATE_DATE"] = date ("Y-m-d H:i:s");

                $flagUserLoggedNextTime = false;

                if ( isset ($arrayData["password"]) )
                {
                    if ( $arrayData["password"] != "" )
                    {
                        //require_once 'classes/model/UsersProperties.php';
                        $passwordHistory = array("USR_PASSWORD_HISTORY" => serialize (array($password->hashPassword ($arrayData["password"]))));
                        $userProperty = new \UserProperties();
                        $aUserProperty = $userProperty->loadOrCreateIfNotExists ($userUid, $passwordHistory);

                        if ( isset ($this->aUserInfo["ROLE"][0]["role_name"]) && $this->aUserInfo["ROLE"][0]["role_name"] == "EASYFLOW_ADMIN" )
                        {
                            $arrayData["USR_LAST_UPDATE_DATE"] = date ("Y-m-d H:i:s");
                            $arrayData["USR_LOGGED_NEXT_TIME"] = $arrayData["USR_LOGGED_NEXT_TIME"];
                            $userProperty->update ($arrayData);
                        }

                        $aHistory = unserialize ($aUserProperty->getUsrPasswordHistory ());

                        if ( !is_array ($aHistory) )
                        {
                            $aHistory = array();
                        }

                        if ( !defined ("PPP_PASSWORD_HISTORY") )
                        {
                            define ("PPP_PASSWORD_HISTORY", 1);
                        }

                        if ( PPP_PASSWORD_HISTORY > 0 )
                        {
                            //it's looking a password igual into aHistory array that was send for post in md5 way
                            $c = 0;
                            $sw = 1;
                            while (count ($aHistory) >= 1 && count ($aHistory) > $c && $sw) {
                                if ( strcmp (trim ($aHistory[$c]), trim ($arrayData['password'])) == 0 )
                                {
                                    $sw = 0;
                                }
                                $c++;
                            }
                            if ( $sw == 0 )
                            {
                                $sDescription = "ID_POLICY_ALERT" . ":\n\n";
                                $sDescription = $sDescription . " - " . "PASSWORD_HISTORY" . ": " . PPP_PASSWORD_HISTORY . "\n";
                                $sDescription = $sDescription . "\n" . "ID_PLEASE_CHANGE_PASSWORD_POLICY" . "";
                                throw new \Exception ($sDescription);
                            }

                            //if ( count ($aHistory) >= PPP_PASSWORD_HISTORY )
                            //{
                            //$sLastPassw = array_shift ($aHistory);
                            //}
                            $aHistory[] = $password->hashPassword ($arrayData["password"]);
                        }

                        $arrayData["USR_LAST_UPDATE_DATE"] = date ("Y-m-d H:i:s");
                        $arrayData["USR_LOGGED_NEXT_TIME"] = $arrayData["USR_LOGGED_NEXT_TIME"];
                        $arrayData["USR_PASSWORD_HISTORY"] = serialize ($aHistory);
                        $userProperty->update ($arrayData);
                    }
                    else
                    {
                        $flagUserLoggedNextTime = true;
                    }
                }
                else
                {
                    $flagUserLoggedNextTime = true;
                }

                if ( $flagUserLoggedNextTime )
                {
                    //require_once "classes/model/Users.php";
                    $oUser = new \Users();
                    $aUser = $oUser->load ($userUid);
                    //require_once "classes/model/UsersProperties.php";
                    $oUserProperty = new \UserProperties();
                    $aUserProperty = $oUserProperty->loadOrCreateIfNotExists ($userUid, array("USR_PASSWORD_HISTORY" => serialize (array($aUser["USR_PASSWORD"]))));
                    $aUserProperty["USR_LOGGED_NEXT_TIME"] = $arrayData["USR_LOGGED_NEXT_TIME"];
                    $oUserProperty->update ($aUserProperty);
                }
                
                //Update in rbac
                $arrayData['dept_id'] = trim ($arrayData['dept_id']) !== "" ? $arrayData['dept_id'] : 1;

                if ( isset ($arrayData["role_id"]) )
                {
                    $result = $user->updateUser ($objUser, $arrayData, $arrayData["role_id"]);
                }
                else
                {
                    $result = $user->updateUser ($objUser, $arrayData);
                }
                //Update in workflow
                //Save Calendar assigment
                if ( isset ($arrayData["calendar"]) )
                {
                    //Save Calendar ID for this user
                    $calendar = new \CalendarDefinition();
                    $calendar->assignCalendarTo ($userUid, $arrayData["calendar"], "USER");
                }

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
    array $arrayWhere = null, $sortField = null, $sortDir = null, $start = 0, $limit = 200, $flagRecord = false, $throwException = true
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
            $numRecTotal = $this->countUsers ($arrayWhere);

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
            }

            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . (int) $start;
            }
            
            $records = $this->objMysql->_query ($criteria, $arrWhere);

            foreach ($records as $record) {
                $arrayUser[] = ($flagRecord) ? $record : $this->__getUserCustomRecordFromRecord ($record);
            }

            //Return
            return array(
                "total" => $numRecTotal,
                "total_pages" => (int) ceil ($numRecTotal / $limit),
                "page" => (int) !is_null ($start) ? $start : 0,
                "limit" => (int) !is_null ($limit) ? $limit : 0,
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
            $objUsers->setUsrAddress ($record['USR_ADDRESS']);
            //$objUsers->setUsrBirthday (date ("Y-m-d H:i:s", strtotime ($record['USR_BIRTHDAY'])));
            $objUsers->setUsrPhone ($record['USR_PHONE']);
            //$objUsers->setUsrUpdateDate ($record['USR_UPDATE_DATE']);
            //$objUsers->setUsrCreateDate ($record['USR_CREATE_DATE']);
            $objUsers->setUsrZipCode ($record['USR_ZIP_CODE']);
            $objUsers->setUsrLocation ($record['USR_LOCATION']);
            $objUsers->setUsrCity ($record['USR_CITY']);
            $objUsers->setUsrCountry ($record['USR_COUNTRY']);

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
            
            $criteria .= " limit 1";

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
    public function loadUserRolePermission (\Users $objUser)
    {

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

        $this->aUserInfo['USER_INFO'] = $objUser;
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

            $arrayUserRolePermission = $this->loadUserRolePermission ($objUser);

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
