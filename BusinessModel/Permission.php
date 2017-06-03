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
class Permission
{
    
    private $objMysql;
    
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getAllPermissions()
    {
        $results = $this->objMysql->_select("user_management.permissions");
        
        return $results;
    }
}
