<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RolePermissions
 *
 * @author michael.hampton
 */
class RolePermissions extends BaseRolePermission
{

    private $objMysql;
    public $permission_name;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param array $aData
     * @return boolean
     */
    function create ($aData)
    {
        try {
            $result = $this->objMysql->_select ("user_management.role_perms", [], ["perm_id" => $aData['PER_UID'], "role_id" => $aData['ROL_UID']]);


            if ( is_array ($result) && !empty ($result) )
            {
                return true;
            }

            $this->setPerUid ($aData['PER_UID']);
            $this->setRolUid ($aData['ROL_UID']);

            if ( $this->validate () )
            {
                $this->save ();
            }

            return true;
        } catch (Exception $oError) {
            throw($oError);
        }
    }

    /**
     * 
     * @param string $name
     */
    public function setPermissionName ($name)
    {
        if ( $this->getPerUid () == '' )
        {
            throw (new Exception ("Error in setPerTitle, the PER_UID can't be blank"));
        }
        if ( $name !== null && !is_string ($name) )
        {
            $name = (string) $name;
        }

        if ( $this->permission_name !== $name || $name === '' )
        {
            $this->permission_name = $name;
        }
    }

    /**
     * @return string
     */
    public function getPermissionName ()
    {
        if ( $this->getPerUid () == '' )
        {
            throw (new Exception ("Error in getPerName, the PER_UID can't be blank"));
        }
        return $this->permission_name;
    }

}
