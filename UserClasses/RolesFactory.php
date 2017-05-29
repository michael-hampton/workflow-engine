<?php

class RolesFactory
{

    use Validator;

    private $objMysql;

    public function __construct ($permId = null)
    {
        $this->objMysql = new Mysql2();
        $this->permId = $permId;
    }

    public function getRoles ($pageLimit = 10, $page = 0, $strOrderBy = "role_name", $strOrderDir = "ASC")
    {

        $arrRoles = $this->objMysql->_select ("user_management.roles", array(), array(), array($strOrderBy => $strOrderDir), (int) $pageLimit, (int) $page);

        $arrAllRoles = [];

        foreach ($arrRoles as $key => $arrRole) {
            $objRoles = new Roles();
            $objRoles->loadObject ($arrRole);
            $arrAllRoles[$key] = $objRoles;
        }

        return $arrAllRoles;
    }

    public function getPermissions ()
    {
        $arrWhere = array();

        if ( $this->permId !== null )
        {
            $arrWhere['perm_id'] = $this->permId;
        }

        $arrPermissions = $this->objMysql->_select ("user_management.permissions", array(), $arrWhere);

        $arrAllPermissions = array();

        foreach ($arrPermissions as $key => $arrPermission) {

            $objPermissions = new Roles();
            $objPermissions->loadObject ($arrPermission);
            $arrAllPermissions[$key] = $objPermissions;
        }

        return $arrAllPermissions;
    }

    public function getPermissionsWithRoles ()
    {
        $arrPermissions = $this->objMysql->_query ("SELECT * FROM user_management.`role_perms` rp
                                                    INNER JOIN user_management.permissions p ON p.id = rp.`perm_id`
                                                    INNER JOIN user_management.roles r ON r.role_id = rp.`role_id");

        $arrAllPermissions = array();

        foreach ($arrPermissions as $key => $arrPermission) {

            $objPermissions = new Roles();
            $objPermissions->loadObject ($arrPermission);
            $arrAllPermissions[$key] = $objPermissions;
        }

        return $arrAllPermissions;
    }

    public function getAllRoles ()
    {
        $roles = $this->objMysql->_select ("user_management.roles", array(), array());

        $arrAllRoles = [];

        foreach ($roles as $key => $role) {
            $objPermissions = new Roles();
            $objPermissions->loadObject ($role);
            $arrAllRoles[$key] = $objPermissions;
        }

        return $arrAllRoles;
    }

    /**
     * Verify if not role exists
     *
     * @param string $roleUid               Unique id of Role
     *
     * return void Throw exception if doesnt exist
     */
    public function throwExceptionIfNotExistsRole ($roleId)
    {
        $result = $this->objMysql->_select ("user_management.roles", array(), array("role_id" => $roleId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        throw new Exception ("Role doesnt exist");
    }

    public function throwExceptionIfNotExistsUser ($userId)
    {
        $result = $this->objMysql->_select ("user_management.poms_users", array(), array("usrid" => $userId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        throw new Exception ("User doesnt exist");
    }

    /* Assign User to Role
     *
     * @param string $roleUid   Unique id of Role
     * @param array  $arrayData Data
     *
     * return array Return data of the User assigned to Role
     */

    public function create ($roleUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsEmpty ($arrayData);
            //Set data
            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid, $this->arrayFieldNameForException["roleUid"]);
            $this->throwExceptionIfNotExistsUser ($arrayData["USR_UID"]);
            $this->throwExceptionIfItsAssignedUserToRole ($roleUid);

            //Create

            $objUswers = new Users();
            $objUswers->setUserId ($arrayData["USR_UID"]);
            $objUswers->setRoleId ($roleUid);
            $objUswers->assignUserToRole ();
            //Return
            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if not it's assigned the User to Role
     *
     * @param string $roleUid               Unique id of Role
     * @param string $userUid               Unique id of User
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if not it's assigned the User to Role
     */
    public function throwExceptionIfNotItsAssignedUserToRole ($roleUid, $userUid)
    {
        try {
            $result = $this->objMysql->_select ("user_management.user_roles", array(), array("roleId" => $roleUid, "userId" => $userUid));

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                throw new Exception ("ID_ROLE_USER_IS_NOT_ASSIGNED");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Unassign User of the Role
     *
     * @param string $roleUid Unique id of Role
     * @param string $userUid Unique id of User
     *
     * return void
     */
    public function delete ($roleUid, $userUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid);
            $this->throwExceptionIfNotExistsUser ($userUid);
            $this->throwExceptionIfNotItsAssignedUserToRole ($roleUid, $userUid);

            //Delete
            $objUsers = new Users();
            $objUsers->setUserId ($userUid);
            $objUsers->setRoleId ($roleUid);
            $objUsers->removeRoleFromUser ();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Users of a Role
     *
     * @param string $roleUid         Unique id of Role
     * @param string $option          Option (USERS, AVAILABLE-USERS)
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Users of a Role
     */
    public function getUsers ($roleUid, $option, array $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayUser = array();
            $numRecTotal = 0;
            //Verify data and Set variables
            $flagFilter = !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter']);
            $role = new \ProcessMaker\BusinessModel\Role();
            $this->throwExceptionIfNotExistsRole ($roleUid);

            //Set variables
            $filterName = 'filter';
            if ( $flagFilter )
            {
                $filterName = $arrayAux[
                        (isset ($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : ''
                ];
            }
            //Get data
            if ( !is_null ($limit) && (string) ($limit) == '0' )
            {
                return [
                    'total' => $numRecTotal,
                    'start' => (int) ((!is_null ($start)) ? $start : 0),
                    'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                    $filterName => ($flagFilter) ? $arrayFilterData['filter'] : '',
                    'data' => $arrayUser
                ];
            }

            $criteria = " SELECT usrid, username, firstName, lastName, status FROM user_management.poms_users u";
            $criteria .= " LEFT JOIN user_management.user_roles ur ON ur.userId = u.usrid";
            $criteria .= " LEFT JOIN user_management.roles r ON r.role_id = ur.roleId";

            $criteria .= " WHERE u.status = 1";

            $arrWhere = [];


            //Query

            switch ($option) {
                case "USERS":
                    $criteria .= " AND r.role_id = ?";
                    $arrWhere[] = $roleUid;
                    break;
                case "AVAILABLE-USERS":
                    $criteria .= " AND r.role_id != ?";
                    $arrWhere[] = $roleUid;
                    break;
            }
            if ( $flagFilter && trim ($arrayFilterData['filter']) != '' )
            {


                $query .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
                $arrWhere[] = "%" . $arrayFilterData['filter'] . "%";
                $arrWhere[] = "%" . $arrayFilterData['filter'] . "%";
            }

            $result1 = $this->objMysql->_query ($criteria, $arrWhere);

            //Number records total
            $numRecTotal = count ($result1);
            //Query


            if ( !is_null ($sortField) && trim ($sortField) != '' )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "role_name ASC";
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
            return [
                'total' => $numRecTotal,
                'start' => (int) ((!is_null ($start)) ? $start : 0),
                'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                $filterName => ($flagFilter) ? $arrayFilterData['filter'] : '',
                'data' => $arrayUser
            ];
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
    public function getUserDataFromRecord (array $record)
    {
        try {

            $objUsers = new Users();
            $objUsers->setUserId ($record['usrid']);
            $objUsers->setUsername ($record['username']);
            $objUsers->setFirstName ($record['firstName']);
            $objUsers->setLastName ($record['lastName']);
            $objUsers->setStatus ($record['status']);

            return $objUsers;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
