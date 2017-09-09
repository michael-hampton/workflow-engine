<?php

namespace BusinessModel;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RoleUser
 *
 * @author michael.hampton
 */
class RoleUser
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Retrieve object using using composite pkey values.
     * @param string $usr_uid
      @param string $rol_uid

     * @param      Connection $con
     * @return     UsersRoles
     */
    public function retrieveByPK ($usr_uid, $rol_uid)
    {

        $v = $this->objMysql->_select ("user_management.user_roles", [], ["roleId" => $rol_uid, "userId" => $usr_uid]);

        return !empty ($v) ? $v[0] : null;
    }

    /**
     * Verify if it's assigned the User to Role
     *
     * @param string $roleUid               Unique id of Role
     * @param string $userUid               Unique id of User
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if it's assigned the User to Role
     */
    public function throwExceptionIfItsAssignedUserToRole ($roleUid, $userUid)
    {
        try {
            $obj = $this->retrieveByPK ($userUid, $roleUid);
            if ( !is_null ($obj) )
            {
                throw new Exception ("ID_ROLE_USER_IS_ALREADY_ASSIGNED");
            }
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
            $obj = $this->retrieveByPK ($userUid, $roleUid);
            if ( is_null ($obj) )
            {
                throw new Exception ("ID_ROLE_USER_IS_NOT_ASSIGNED");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Assign User to Role
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
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            //Verify data
            $role = new \BusinessModel\Role();
            $role->throwExceptionIfNotExistsRole ($roleUid);
            $this->validateUserId ($arrayData["USR_UID"]);
            $this->throwExceptionIfItsAssignedUserToRole ($roleUid, $arrayData["USR_UID"]);

            //Create
            $role = new \Roles();
            $arrayData = array_merge (array("ROL_UID" => $roleUid), $arrayData);
            $role->assignUserToRole ($arrayData);
            //Return

            return $arrayData;
        } catch (\Exception $e) {
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
            $role = new \BusinessModel\Role();
            $role->throwExceptionIfNotExistsRole ($roleUid);
            $this->validateUserId ($userUid);
            $this->throwExceptionIfNotItsAssignedUserToRole ($roleUid, $userUid);


            //Delete
            $role = new \Roles();
            $role->deleteUserRole ($roleUid, $userUid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRolesForUser (\Users $objUser)
    {
        $results = $this->objMysql->_query ("SELECT * FROM user_management.user_roles ur
                                    INNER JOIN user_management.roles r ON r.role_id = ur.roleId
                                    WHERE ur.userId = ?
                                    GROUP BY r.role_id", [$objUser->getUserId ()]);

        return $results;
    }

    function getAllPermissions (\Role $objRole)
    {
        try {
            $results = $this->objMysql->_query ("SELECT p.* FROM user_management.role_perms rp
                                                    INNER JOIN user_management.permissions p ON p.perm_id = rp.perm_id
                                                    WHERE rp.role_id = ?", [$objRole->getRoleId ()]);
            return $results;
        } catch (Exception $oError) {
            throw($oError);
        }
    }

}
