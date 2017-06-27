<?php

namespace BusinessModel;

class Permission
{

    /**
     * Constructor of the class
     *
     * return void
     */
    private $objMysql;

    use Validator;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    private function retrieveByPK ($roleId, $permId)
    {

        $result = $this->objMysql->_select ("user_management.role_perms", array(), array("role_id" => $roleId, "perm_id" => $permId));

        return $result;
    }

    /**
     * Verify if it's assigned the Permission to Role
     *
     * @param string $roleUid               Unique id of Role
     * @param string $permissionUid         Unique id of Permission
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if it's assigned the Permission to Role
     */
    public function throwExceptionIfItsAssignedPermissionToRole ($roleUid, $permissionUid)
    {
        try {
            $obj = $this->retrieveByPK ($roleUid, $permissionUid);
            if ( !empty ($obj) )
            {
                throw new \Exception ("ID_ROLE_PERMISSION_IS_ALREADY_ASSIGNED");
            }
        } catch (Exception $e) {
            throw $e;
        }
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

        throw new \Exception ("Role doesnt exist");
    }

    public function throwExceptionIfNotExistsPermission ($permId)
    {
        $result = $this->objMysql->_select ("user_management.permissions", array(), array("perm_id" => $permId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        throw new \Exception ("Permission doesnt exist");
    }

    /**
     * Verify if not it's assigned the Permission to Role
     *
     * @param string $roleUid               Unique id of Role
     * @param string $permissionUid         Unique id of Permission
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if not it's assigned the Permission to Role
     */
    public function throwExceptionIfNotItsAssignedPermissionToRole ($roleUid, $permissionUid)
    {
        try {
            $obj = $this->retrieveByPK ($roleUid, $permissionUid);
            if ( empty ($obj) )
            {
                throw new \Exception ("ID_ROLE_PERMISSION_IS_NOT_ASSIGNED");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Assign Permission to Role
     *
     * @param string $roleUid   Unique id of Role
     * @param array  $arrayData Data
     *
     * return array Return data of the Permission assigned to Role
     */
    public function create ($roleUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsEmpty ($arrayData);

            //Set data
            //Verify data
            $role = new Role();
            $this->throwExceptionIfNotExistsRole ($roleUid);

            if ( isset ($arrayData['perm_id']) )
            {
                $this->throwExceptionIfNotExistsPermission ($arrayData["perm_id"]);
                $this->throwExceptionIfItsAssignedPermissionToRole ($roleUid, $arrayData["perm_id"]);
            }


            //Create

            $role->setRoleId ($roleUid);
            $role->setPermId ($arrayData['perm_id']);
            $role->addRolePerms ();

            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Unassign Permission of the Role
     *
     * @param string $roleUid       Unique id of Role
     * @param string $permissionUid Unique id of Permission
     *
     * return void
     */
    public function delete ($roleUid, $permissionUid)
    {
        try {
            //Verify data

            $role = new Role();
            $this->throwExceptionIfNotExistsRole ($roleUid);
            $this->throwExceptionIfNotExistsPermission ($permissionUid);
            $this->throwExceptionIfNotItsAssignedPermissionToRole ($roleUid, $permissionUid);


            $role->setRoleId ($roleUid);
            $role->setPermId ($permissionUid);
            $role->deleteRolePerms ();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Permission
     *
     * @param string $roleUid                   Unique id of Role
     * @param array  $arrayPermissionUidExclude Unique id of Permissions to exclude
     *
     * return object
     */
    public function getPermissionCriteria ($roleUid, array $arrayPermissionUidExclude = null)
    {
        try {

            $criteria = "SELECT p.perm_id, p.perm_name, rp.role_id, r.role_name FROM user_management.permissions p";
            $criteriaWhere = " WHERE 1=1";
            $arrWhere = array();

            if ( $roleUid != "" )
            {
                $criteria .= " LEFT JOIN user_management.role_perms rp ON rp.perm_id = p.perm_id";
                $criteria .= " LEFT JOIN user_management.roles r ON r.role_id = rp.role_id";
                $criteriaWhere .= " AND rp.role_id = ?";
                $arrWhere[] = $roleUid;
            }

            if ( !is_null ($arrayPermissionUidExclude) && is_array ($arrayPermissionUidExclude) )
            {
                $criteriaWhere .= " AND perm_id NOT IN (?)";
                $arrWhere[] = implode (",", $arrayPermissionUidExclude);
            }

            $criteria = $criteria . $criteriaWhere;

            return array("sql" => $criteria, "where" => $arrWhere);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Permission from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Permission
     */
    public function getPermissionDataFromRecord (array $record)
    {
        try {
            $objRole = new Role();
            $objRole->setPermId ($record['perm_id']);
            $objRole->setPermName ($record['perm_name']);
            $objRole->setRoleId ($record['role_id']);
            $objRole->setRoleName ($record['role_name']);

            return $objRole;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Permissions of a Role
     *
     * @param string $roleUid         Unique id of Role
     * @param string $option          Option (PERMISSIONS, AVAILABLE-PERMISSIONS)
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Permissions of a Role
     */
    public function getPermissions ($roleUid, $option, array $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayPermission = array();
            //Verify data
            $role = new Role();
            $this->throwExceptionIfNotExistsRole ($roleUid);

            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayPermission;
            }
            //Set variables
            $rolePermission = new RolePermission();
            //SQL
            switch ($option) {
                case "PERMISSIONS":
                    //Criteria
                    $criteria = $this->getPermissionCriteria ($roleUid);
                    $arrWhere = $criteria['where'];
                    $criteria = $criteria['sql'];


                    break;
                case "AVAILABLE-PERMISSIONS":
                    //Get Uids
                    $arrayUid = array();
                    $criteria = $this->getPermissionCriteria ($roleUid);
                    $rsCriteria = \PermissionsPeer::doSelectRS ($criteria);
                    $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);
                    while ($rsCriteria->next ()) {
                        $row = $rsCriteria->getRow ();
                        $arrayUid[] = $row["PER_UID"];
                    }
                    //Criteria
                    $criteria = $this->getPermissionCriteria ("", $arrayUid);
                    break;
            }
            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {
                $criteria .= " AND p.perm_name LIKE '%" . $arrayFilterData["filter"] . "%'";
            }

            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "p.perm_name";
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
                $arrayPermission[] = $this->getPermissionDataFromRecord ($row);
            }

            //Return
            return $arrayPermission;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
