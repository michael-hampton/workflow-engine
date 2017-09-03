<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TeamFunctions
 *
 * @author michael.hampton
 */
class TeamFunctions
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the assigned users of a group
     *
     * @param string $sGroupUID
     * @return array
     * @throws Exception
     */
    public function getUsersOfGroup ($sGroupUID, $statusUser = 1)
    {
        try {
            $aUsers = array();
            $arrWhere = array();

            $sql = "SELECT * FROM user_management.poms_users WHERE team_id = ? ";
            $arrWhere[] = $sGroupUID;

            if ( $statusUser !== 'ALL' )
            {
                $sql .= " AND status = ?";
                $arrWhere[] = $statusUser;
            }

            $results = $this->objMysql->_query ($sql, $arrWhere);

            foreach ($results as $aRow) {
                $aUsers[] = $aRow;
            }

            return $aUsers;
        } catch (exception $oError) {
            throw ($oError);
        }
    }

}
