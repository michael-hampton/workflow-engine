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

    public function getCountries ($pk = null)
    {
        $arrWhere = [];

        if ( $pk !== null )
        {
            $arrWhere['IC_UID'] = $pk;
        }

        $results = $this->objMysql->_select ("workflow.iso_country", [], $arrWhere, ["IC_NAME" => "ASC"]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        return $results;
    }

    public function retrieveLocationByPk ($country, $location)
    {
        $results = $this->objMysql->_select ("workflow.iso_location", [], ["IC_UID" => $country, "IL_UID" => $location]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        return $results;
    }

    public function retrieveSubdivisionByPk ($country, $city)
    {
        $results = $this->objMysql->_select ("workflow.iso_subdivision", [], ["IC_UID" => $country, "IS_UID" => $city]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        return $results;
    }

    public function getLocations ($filter = '')
    {
        $arrWhere = [];

        if ( $filter !== "" )
        {
            $arrWhere['IS_UID'] = $filter;
        }

        $results = $this->objMysql->_select ("workflow.iso_location", [], $arrWhere, ["IL_NAME" => "ASC"]);

        return $results;
    }

    public function getSubDivisions ($filter = '')
    {
        $arrWhere = [];

        if ( $filter !== "" )
        {
            $arrWhere['IC_UID'] = $filter;
        }

        $results = $this->objMysql->_select ("workflow.iso_subdivision", [], $arrWhere, ["IS_NAME" => "ASC"]);

        return $results;
    }

    //put your code here
}
