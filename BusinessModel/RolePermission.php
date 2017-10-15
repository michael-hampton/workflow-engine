<?php

namespace BusinessModel;

class RolePermission
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
        
    }

    public function getConnection ()
    {
        $this->objMysql = new \Mysql2();
    }

    private function retrieveByPK ($roleId, $permId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

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
                throw new Exception ("ID_ROLE_PERMISSION_IS_ALREADY_ASSIGNED");
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
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("user_management.roles", array(), array("role_id" => $roleId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        throw new Exception ("Role doesnt exist");
    }

    public function throwExceptionIfNotExistsPermission ($permId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("user_management.permissions", array(), array("id" => $permId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        throw new Exception ("Permission doesnt exist");
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
                throw new Exception ("ID_ROLE_PERMISSION_IS_NOT_ASSIGNED");
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
    public function create (\Role $objRole, \Permission $objPermission)
    {
        try {
            //Set data
            $role = new \Role();
            $this->throwExceptionIfNotExistsRole ($objRole->getRoleId ());

            $this->throwExceptionIfNotExistsPermission ($objPermission->getPermId ());
            $this->throwExceptionIfItsAssignedPermissionToRole ($objRole->getRoleId (), $objPermission->getPermId ());

            //Create
            $role = new \Role();
            $role->assignPermissionRole ($objRole, $objPermission);

            return true;
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
    public function delete (\Role $objRole, \Permission $objPermission)
    {
        try {
            //Verify data

            $role = new \Role();
            $this->throwExceptionIfNotExistsRole ($objRole->getRoleId ());
            $this->throwExceptionIfNotExistsPermission ($objPermission->getPermId ());
            $this->throwExceptionIfNotItsAssignedPermissionToRole ($objRole->getRoleId (), $objPermission->getPermId ());


            $role->deletePermissionRole ($objRole, $objPermission);
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

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            $criteria = "SELECT p.id, p.perm_name, rp.role_id, r.role_name FROM user_management.permissions p";
            $criteriaWhere = " WHERE 1=1";
            $arrWhere = array();

            if ( $roleUid != "" )
            {
                $criteria .= " LEFT JOIN user_management.role_perms rp ON rp.perm_id = p.id";
                $criteria .= " LEFT JOIN user_management.roles r ON r.role_id = rp.role_id";
            }

            if ( !is_null ($arrayPermissionUidExclude) && is_array ($arrayPermissionUidExclude) )
            {
                $criteriaWhere .= " AND p.id NOT IN (?)";
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
            $objRole = new \RolePermissions();
            $objRole->setPerUid ($record['id']);
            $objRole->setPermissionName ($record['perm_name']);
            $objRole->setRolUid ($record['role_id']);

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
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $arrayPermission = array();
            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid);

            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayPermission;
            }
            //Set variables
            //SQL
            switch ($option) {
                case "PERMISSIONS":
                    //Criteria
                    $criteria = $this->getPermissionCriteria ($roleUid);
                    $arrWhere = $criteria['where'];
                    $criteria = $criteria['sql'];

                    $criteria .= " AND rp.role_id = ?";
                    $arrWhere[] = $roleUid;


                    break;
                case "AVAILABLE-PERMISSIONS":
                    //Get Uids
                  
                    $criteria = $this->getPermissionCriteria ($roleUid);
                    $arrWhere = $criteria['where'];
                    $criteria = $criteria['sql'];

                    $criteria .= " AND p.id not in (
                                    SELECT perm_id 
                                    FROM user_management.role_perms 
                                    WHERE role_id = ?)";
                    $arrWhere[] = $roleUid;


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
            else
            {
                $criteria .= " LIMIT 100";
            }
            
            $results = $this->objMysql->_query ($criteria, $arrWhere);
            
            if($results === false) {
                return false;
            }
            
            if (isset($results[0]) && !empty ($results[0]) )
            {
                foreach ($results as $row) {
                    $arrayPermission[] = $this->getPermissionDataFromRecord ($row);
                }
            }
            
            //Return
            return $arrayPermission;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
