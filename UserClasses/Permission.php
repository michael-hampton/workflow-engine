<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Permission
 *
 * @author michael.hampton
 */
class Permission extends BasePermission
{

    private $objMysql;

    public function __construct ($permId = null)
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();

        if ( $permId !== null )
        {
            $this->permId = $permId;
        }
    }

    public function checkName ($permName)
    {
        $result = $this->objMysql->_select ("user_management.permissions", [], ["perm_name" => $permName]);

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            throw new Exception ("Name already exists");
        }
    }

    public function create ($aData)
    {
        $this->checkName ($aData['perm_name']);

        $this->loadObject ($aData);

        if ( $this->validate () )
        {
            $this->save ($aData);
        }
        else
        {
            $msg = '';

            foreach ($this->getValidationFailures () as $message) {
                $msg .= $message . "</br>";
            }

            throw new Exception ("Permission could not be saved " . $msg);
        }
    }

}
