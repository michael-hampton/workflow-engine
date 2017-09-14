<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Iso
 *
 * @author michael.hampton
 */
class Iso
{
    private $objMysql;
    
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }
    
    public function getCountries()
    {
        $results = $this->objMysql->_select("workflow.iso_country", [], [], ["IC_NAME" => "ASC"]);
        
        return $results;
    }
    
    public function getLocations()
    {
        $results = $this->objMysql->_select("workflow.iso_location", [], [], ["IL_NAME" => "ASC"]);
        
        return $results;
    }
    
    public function getSubDivisions()
    {
        $results = $this->objMysql->_select("workflow.iso_subdivision", [], [], ["IS_NAME" => "ASC"]);
        
        return $results;
    }

    //put your code here
}
