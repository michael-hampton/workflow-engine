<?php

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
class Role extends BaseRole
{

    private $objMysql;

    public function __construct ($roleId = null)
    {
        parent::__construct ($roleId);
        $this->objMysql = new Mysql2();
    }

    public function createRole ($aData)
    {
        try {
            $sRolCode = $aData['role_code'];
            //$sRolSystem = $aData['ROL_SYSTEM'];
            $status = $aData['status'];

            $result = $this->objMysql->_select ("user_management.roles", [], ["role_code" => $sRolCode]);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return $result[0];
            }

            $this->setStatus ($status);
            $this->setRoleCode ($sRolCode);

            if ( !isset ($aData['role_name']) )
            {
                $aData['role_name'] = '';
            }
            $rol_name = $aData['role_name'];
            unset ($aData['role_name']);

            $this->setRoleName ($rol_name);


            if ( $this->validate () )
            {
                $result = $this->save ();
                return $result;
            }
            else
            {
                $e = new Exception ("Failed Validation in class " . get_class ($this) . ".");
                $e->aValidationFailures = $this->getValidationFailures ();
                throw ($e);
            }
            return $result;
        } catch (exception $e) {
            throw ($e);
        }
    }

    public function updateRole ($fields)
    {
        try {
            $this->setRoleId ($fields['role_id']);

            $this->loadObject ($fields);
            if ( $this->validate () )
            {
                $result = $this->save ();
                return $result;
            }
            else
            {
                throw (new Exception ("Failed Validation in class " . get_class ($this) . "."));
            }
        } catch (exception $e) {
            throw ($e);
        }
    }

    public function deleteUserRole ($ROL_UID, $USR_UID)
    {
        $objUser = new Users();
        $objUser->removeRoleFromUser ($USR_UID, $ROL_UID);
    }

    public function assignUserToRole ($aData)
    {
        $objUser = new Users();
        $objUser->assignRoleToUser ($aData['USR_UID'], $aData['ROL_UID']);
    }

    public function removeRole ($ROL_UID)
    {
        try {
            $this->setRoleId ($ROL_UID);
            $this->setStatus(0);
            $result = $this->disableRole ();

            return $result;
        } catch (exception $e) {
            throw ($e);
        }
    }

    private function numUsersWithRole ($ROL_UID)
    {
        $result = $this->objMysql->_select ("user_management.user_roles", [], ["roleId" => $ROL_UID]);

        return count ($result);
    }
    
     public function assignPermissionRole(Role $objRole, Permission $objPermission) {
        $o = new RolePermissions();
        
        $o->setPerUid($objPermission->getPermId ());
        $o->setRolUid($objRole->getRoleId ());
        
        $o->save();
      
    }
    
     public function deletePermissionRole(Role $objRole, Permission $objPermission) {
        $o = new RolePermissions();
        $o->setPerUid($objPermission->getPermId ());
        $o->setRolUid($objRole->getRoleId ());
        $o->delete();
    }

}
