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
abstract class BaseRolePermission implements Persistent
{

    public $perUid;
    public $rolUid;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getPerUid ()
    {
        return $this->perUid;
    }

    public function getRolUid ()
    {
        return $this->rolUid;
    }

    public function setPerUid ($perUid)
    {
        $this->perUid = $perUid;
    }

    public function setRolUid ($rolUid)
    {
        $this->rolUid = $rolUid;
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_insert ("user_management.role_perms", array("perm_id" => $this->perUid, "role_id" => $this->rolUid));
    }

    public function delete ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_delete ("user_management.role_perms", array("perm_id" => $this->perUid, "role_id" => $this->rolUid));
    }
    
    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {
            if ( isset ($this->arrayFieldDefinition[$formField]) )
            {
                $mutator = $this->arrayFieldDefinition[$formField]['mutator'];
                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrayFieldDefinition[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }
    }
    
    public function validate ()
    {
        $errorCount = 0;
        
        if(trim($this->perUid) === "") {
            $errorCount++;
        }
        
        if(trim($this->rolUid) === "") {
            $errorCount++;
        }
        
        if($errorCount > 0) {
            return false;
        }
        
        return true;
    }

}
