<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Teams
 *
 * @author michael.hampton
 */
class Team extends BaseTeam
{

    private $objMysql;

    public function __construct ($deptId = null, $teamId = null)
    {
        $this->objMysql = new Mysql2();
        parent::__construct ($deptId, $teamId);
    }

    /**
     * Creates the Group
     *
     * @param array $aData $oData is not necessary
     * @return void
     */
    public function create ($aData)
    {
        //$oData is not necessary
        try {
            if ( isset ($aData['GRP_UID']) )
            {
                $this->setGrpUid ($aData['GRP_UID']);
            }

            if ( isset ($aData['team_name']) )
            {
                $this->setTeamName ($aData['team_name']);
            }
            else
            {
                throw new Exception ("Team name is empty");
            }

            if ( isset ($aData['dept_id']) )
            {
                $this->setDeptId ($aData['dept_id']);
            }
            else
            {
                
            }

            if ( isset ($aData['status']) )
            {
                $this->setStatus ($aData['status']);
            }
            else
            {
                $this->setStatus (1);
            }


            if ( $this->validate () )
            {
                $res = $this->save ();
                return $this->getId ();
            }
            else
            {
                $msg = '';
                foreach ($this->getValidationFailures () as $message) {
                    $msg .= $message . "<br/>";
                }
                throw (new Exception ('The row cannot be created! ' . $msg));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function retrieveByPk ($groupUid)
    {
        $result = $this->objMysql->_select ("user_management.teams", array(), array("team_id" => $groupUid));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $this->loadObjectFromArray ($result[0]);
        }

        throw new Exception ("Record could not be found");
    }

    public function loadObjectFromArray ($record)
    {
        $objTeam = new self();
        $objTeam->setDeptId ($record['dept_id']);
        $objTeam->setId ($record['team_id']);
        $objTeam->setTeamName ($record['team_name']);
        $objTeam->setStatus ($record['status']);

        return $objTeam;
    }

    /**
     * Update the Group row
     *
     * @param array $aData
     * @return variant
     *
     */
    public function update ($aData)
    {
        try {
            $oPro = $this->retrieveByPk ($aData['team_id']);

            if ( is_object ($oPro) && get_class ($oPro) == 'Teams' )
            {
                $oPro->loadObject ($aData);
                if ( $oPro->validate () )
                {
                    $res = $oPro->save ();
                    return $res;
                }
                else
                {
                    $msg = '';
                    foreach ($this->getValidationFailures () as $message) {
                        $msg .= $message . "<br/>";
                    }
                    throw (new Exception ('The row cannot be created! ' . $msg));
                }
            }
            else
            {
                $con->rollback ();
                throw (new Exception ("The row '" . $aData['GRP_UID'] . "' in table Group doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function addUserToGroup (Team $objTeam, Users $objUser)
    {
        if ( trim ($objTeam->getId ()) === "" )
        {
            return false;
        }

        if ( trim ($objUser->getUserId ()) === "" )
        {
            return false;
        }

        $this->removeUserOfGroup ($objUser->getUserId ());
        $this->addUserOfGroup ($objTeam->getId (), $objUser->getUserId ());
    }

    public function removeUsersFromGroup ($userUid, $groupUid = null)
    {
        if ( $groupUid === null )
        {
            $this->removeUserOfGroup ($userUid);
        }
    }

    /**
     * Remove the Prolication document registry
     *
     * @param array $aData or string $ProUid
     * @return string
     *
     */
    public function remove ($groupUid)
    {
        try {
            $oPro = $this->retrieveByPk ($groupUid);
            if ( is_object ($oPro) && get_class ($oPro) === "Teams" )
            {
                return $oPro->deleteTeam ();
            }
            else
            {
                throw (new Exception ("The row '$ProUid' in table Group doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
