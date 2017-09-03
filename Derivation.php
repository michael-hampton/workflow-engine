<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Derivation
 *
 * @author michael.hampton
 */
class Derivation
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /* get an array of users, and returns the same arrays with User's fullname and other fields

     *

     * @param   Array   $aUsers      the task uidUser

     * @return  Array   $aUsersData  an array with with User's fullname

     */

    public function getUsersFullNameFromArray ($aUsers)
    {
        $oUser = new BusinessModel\UsersFactory();

        $aUsersData = array();

        if ( is_array ($aUsers) )
        
            foreach ($aUsers as $val) {
                
                $arrUser = $oUser->getUser($val);

                $auxFields['USR_UID'] = $arrUser->getUserId();

                $auxFields['USR_USERNAME'] = $arrUser->getUsername();

                $auxFields['USR_FIRSTNAME'] = $arrUser->getFirstName();

                $auxFields['USR_LASTNAME'] = $arrUser->getLastName();

                $auxFields['USR_FULLNAME'] = $arrUser->getLastName() . ($arrUser->getLastName() != '' ? ', ' : '') . $arrUser->getFirstName();

                $auxFields['USR_EMAIL'] = $arrUser->getUser_email();

                $auxFields['USR_STATUS'] = $arrUser->getStatus();

                $auxFields['DEP_UID'] = $arrUser->getDepartment();

                $auxFields['USR_HIDDEN_FIELD'] = '';

                $aUsersData[] = $auxFields;
            }
        }
        else
        {

        }

        return $aUsersData;
    }

    /* get all users, from any task, if the task have Groups, the function expand the group

     *

     * @param   string  $sTasUid  the task uidUser

     * @param   bool    $flagIncludeAdHocUsers

     * @return  Array   $users an array with userID order by USR_UID

     */

    public function getAllUsersFromAnyTask ($sTasUid, $flagIncludeAdHocUsers = false)
    {
        $users = array();

        $arrWhere = array();

        $sql = "SELECT USR_UID, TU_RELATION FROM workflow.task_user WHERE TAS_UID = ?";
        $arrWhere[] = $sTasUid;


        if ( $flagIncludeAdHocUsers )
        {
            $sql .= " AND (TU_TYPE = 1 OR TU_TYPE = 2)";
        }
        else
        {

            $sql .= " AND TU_TYPE = 1";
        }

        $results = $this->objMysql->_query ($sql, $arrWhere);

        foreach ($results as $row) {

            if ( $row['TU_RELATION'] == '2' )
            {
                $sql2 = "SELECT * FROM user_management.teams t
                        INNER JOIN user_management.poms_users u ON u.team_id = t.team_id
                         WHERE t.status = 1 AND t.team_id = ? AND u.status != 0";
                $arrParameters = array($row['USR_UID']);

                $results2 = $this->objMysql->_query ($sql2, $arrParameters);


                foreach ($results2 as $rowGrp) {

                    $users[$rowGrp['usrid']] = $rowGrp['usrid'];
                }
            }
            else
            {

                //filter to users that is in vacation or has an inactive estatus, and others

                $oUser = (new Users())->retrieveByPk ($row['USR_UID']);

                if ( $oUser !== false )
                {
                    $users[$row['USR_UID']] = $row['USR_UID'];
                }
            }
        }

        //to do: different types of sort

        sort ($users);
        
        return $users;
    }

}
