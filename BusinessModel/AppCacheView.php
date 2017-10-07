<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of AppCacheView
 *
 * @author michael.hampton
 */
class AppCacheView extends BaseAppCacheView
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new \Mysql2();
    }

    public function searchArchive ($arrFilters = array(), $start = 10, $page = 0)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $sql = "SELECT * FROM workflow.APP_CACHE_VIEW WHERE 1=1";

        $arrWhere = [];

        if ( isset ($arrFilters['case_number']) && trim ($arrFilters['case_number']) !== "" )
        {
            $sql .= " AND APP_NUMBER = ?";
            $arrWhere[] = $arrFilters['case_number'];
        }

        if ( isset ($arrFilters['case_title']) && trim ($arrFilters['case_title']) !== "" )
        {
            $sql .= " AND APP_TITLE = ?";
            $arrWhere[] = $arrFilters['case_title'];
        }

        if ( isset ($arrFilters['workflow']) && trim ($arrFilters['workflow']) !== "" )
        {
            $sql .= " AND PRO_UID = ?";
            $arrWhere[] = $arrFilters['workflow'];
        }

        if ( isset ($arrFilters['user']) && trim ($arrFilters['user']) !== "" )
        {
            $sql .= " AND USR_UID = ?";
            $arrWhere[] = $arrFilters['user'];
        }

        $results = $this->objMysql->_query ($sql, $arrWhere);

        return $results;
    }

}
