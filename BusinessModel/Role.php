<?php

namespace BusinessModel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Role
 *
 * @author michael.hampton
 */
class Role
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the code of a Role
     *
     * @param string $roleCode       Code
     * @param string $roleUidExclude Unique id of Role to exclude
     *
     * return bool Return true if exists the code of a Role, false otherwise
     */
    public function existsCode ($roleCode, $roleUidExclude = "")
    {
        try {
            //SQL
            $arrWhere = [];
            $criteria = "SELECT * FROM user_management.roles WHERE role_code = ?";
            $arrWhere[] = $roleCode;

            if ( $roleUidExclude != "" )
            {
                $criteria .= " AND role_id != ?";
                $arrWhere[] = $roleUidExclude;
            }

            $result = $this->objMysql->_query ($criteria, $arrWhere);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the name of a Role
     *
     * @param string $roleName       Name
     * @param string $roleUidExclude Unique id of Role to exclude
     *
     * return bool Return true if exists the name of a Role, false otherwise
     */
    public function existsName ($roleName, $roleUidExclude = "")
    {
        try {

            //SQL
            $arrWhere = [];
            $criteria = "SELECT * FROM user_management.roles WHERE role_name = ?";
            $arrWhere[] = $roleName;

            if ( $roleUidExclude != "" )
            {
                $criteria .= " AND role_id != ?";
                $arrWhere[] = $roleUidExclude;
            }

            $result = $this->objMysql->_query ($criteria, $arrWhere);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return TRUE;
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the code of a Role
     *
     * @param string $roleCode              Code
     * @param string $roleUidExclude        Unique id of Role to exclude
     *
     * return void Throw exception if exists the code of a Role
     */
    public function throwExceptionIfExistsCode ($roleCode, $roleUidExclude = "")
    {
        try {
            if ( $this->existsCode ($roleCode, $roleUidExclude) )
            {
                throw new \Exception ("ROLE CODE ALREADY EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the name of a Role
     *
     * @param string $roleName              Name
     * @param string $roleUidExclude        Unique id of Role to exclude
     *
     * return void Throw exception if exists the name of a Role
     */
    public function throwExceptionIfExistsName ($roleName, $roleUidExclude = "")
    {
        try {
            if ( $this->existsName ($roleName, $roleUidExclude) )
            {
                throw new Exception ("ID_ROLE_NAME_ALREADY_EXISTS");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $roleUid   Unique id of Role
     * @param array  $arrayData Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($roleUid, array $arrayData)
    {
        try {

            //Set variables
            //Verify data
            if ( isset ($arrayData["role_code"]) && !preg_match ("/^\w+$/", $arrayData["role_code"]) )
            {
                throw new Exception ("ID_ROLE_FIELD_CANNOT_CONTAIN_SPECIAL_CHARACTERS");
            }
            if ( isset ($arrayData["role_code"]) )
            {
                $this->throwExceptionIfExistsCode ($arrayData["role_code"], $roleUid);
            }
            if ( isset ($arrayData["role_name"]) )
            {
                $this->throwExceptionIfExistsName ($arrayData["role_name"], $roleUid);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Role
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new Role created
     */
    public function create (array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            //Verify data
            $this->throwExceptionIfDataIsInvalid ("", $arrayData);

            //Create
            $role = new \Role();
            $arrayData["status"] = (isset ($arrayData["status"])) ? (($arrayData["status"] == "ACTIVE") ? 1 : 0) : 1;
            $arrayData["ROL_CREATE_DATE"] = date ("Y-M-d H:i:s");
            $roleUid = $role->createRole ($arrayData);
            //Return
            return $this->getRole ($roleUid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     Roles
     */
    public function retrieveByPK ($pk)
    {

        $v = $this->objMysql->_select ("user_management.roles", [], ["role_id" => $pk]);

        return !empty ($v) > 0 ? $v[0] : null;
    }

    /**
     * Verify if does not exist the Role in table ROLES
     *
     * @param string $roleUid               Unique id of Role
     *
     * return void Throw exception if does not exist the Role in table ROLES
     */
    public function throwExceptionIfNotExistsRole ($roleUid)
    {
        try {
            $obj = $this->retrieveByPK ($roleUid);
            if ( is_null ($obj) )
            {
                throw new Exception ("ID_ROLE_DOES_NOT_EXIST");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Role
     *
     * return object
     */
    public function getRoleCriteria ()
    {
        try {
            $criteria = "SELECT role_id, role_name, role_code, status, (SELECT COUNT(*) FROM user_management.user_roles WHERE roleId = role_id) COUNT FROM user_management.roles WHERE 1=1";

            return $criteria;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Role
     *
     * @param string $roleUid       Unique id of Role
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Role
     */
    public function getRole ($roleUid, $flagGetRecord = false)
    {
        try {
            $arrWhere = [];
            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid);
            //Set variables
            //Get data
            //SQL
            $criteria = $this->getRoleCriteria ();

            if ( !$flagGetRecord )
            {
                //$criteria->addAsColumn ("ROL_TOTAL_USERS", "(SELECT COUNT(" . \UsersRolesPeer::ROL_UID . ") FROM " . \UsersRolesPeer::TABLE_NAME . " WHERE " . \UsersRolesPeer::ROL_UID . " = " . \RolesPeer::ROL_UID . ")");
            }

            $criteria .= " AND role_id = ?";
            $arrWhere[] = $roleUid;

            $row = $this->objMysql->_query ($criteria, $arrWhere);

            //Return
            return (!$flagGetRecord) ? $this->getRoleDataFromRecord ($row[0]) : $row[0];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Role from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Role
     */
    public function getRoleDataFromRecord (array $record)
    {
        try {
            $objRoles = new \Role();
            $objRoles->setRoleId ($record['role_id']);
            $objRoles->setRoleCode ($record['role_code']);
            $objRoles->setRoleName ($record['role_name']);
            $objRoles->setStatus ($record['status']);

            return $objRoles;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get all Roles
     *
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Roles
     */
    public function getRoles (array $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayRole = array();
            $arrWhere = [];
            //Verify data
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayRole;
            }
            //Set variables
            //SQL
            $criteria = $this->getRoleCriteria ();

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {
                $whereSql .= " AND role_code LIKE ?";
                $arrWhere[] = "%" . $arrayFilterData['filter'] . "%";
            }
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "role_code";
            }
            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $whereSql .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $whereSql .= " ORDER BY " . $sortField . " ASC";
            }

            $countSql = "SELECT COUNT(*) as count FROM user_management.roles WHERE 1=1" . $whereSql;
            

            if ( !is_null ($limit) )
            {
                $whereSql .= " LIMIT " . (int) ($limit);
            }
            if ( !is_null ($start) )
            {
                $whereSql .= " OFFSET " . (int) ($start);
            }

            //COUNT RESULTS
            $countResult = $this->objMysql->_query ($countSql, $arrWhere);
            
            // FULL RESULTS
            $criteria .= $whereSql;
            $results = $this->objMysql->_query ($criteria, $arrWhere);

            foreach ($results as $row) {

                $arrayRole[] = $this->getRoleDataFromRecord ($row);
            }

            $numRecTotal = $countResult[0]['count'];

            //Return
            return array(
                "total" => $numRecTotal,
                "total_pages" => (int) ceil ($numRecTotal / $limit),
                "page" => (int) !is_null ($start) ? $start : 0,
                "limit" => (int) !is_null ($limit) ? $limit : 0,
                "data" => $arrayRole
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Role
     *
     * @param string $roleUid   Unique id of Role
     * @param array  $arrayData Data
     *
     * return array Return data of the Role updated
     */
    public function update ($roleUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid);

            $this->throwExceptionIfDataIsInvalid ($roleUid, $arrayData);
            //Update
            $role = new \Role();
            $arrayData["role_id"] = $roleUid;
            $arrayData["ROL_UPDATE_DATE"] = date ("Y-M-d H:i:s");

            if ( isset ($arrayData["status"]) )
            {
                $arrayData["status"] = ($arrayData["status"] == "ACTIVE") ? 1 : 0;
            }

            $role->updateRole ($arrayData);


            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Role
     *
     * @param string $roleUid Unique id of Role
     *
     * return void
     */
    public function delete ($roleUid)
    {
        try {
            $role = new \Role();
            //Verify data
            $this->throwExceptionIfNotExistsRole ($roleUid);
            if ( $role->numUsersWithRole ($roleUid) > 0 )
            {
                throw new Exception ("ID_ROLES_CAN_NOT_DELETE");
            }
            //Delete
            $role->removeRole ($roleUid);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
