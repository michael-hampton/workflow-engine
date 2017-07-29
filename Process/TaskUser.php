<?php

class TaskUser extends BaseTaskUser
{
    private $objMysql;
    
    public function __construct ()
    {
        parent::__construct();
        $this->objMysql = new Mysql2();
    }

    /**
     * Retrieve object using using composite pkey values.
     * @param string $tas_uid
     * @param string $usr_uid
     * @param int $tu_type
     * @param int $tu_relation
     * @param      Connection $con
     * @return     TaskUser
     */
    public function retrieveByPK ($tas_uid, $usr_uid, $tu_type, $tu_relation)
    {
        $results = $this->objMysql->_select("workflow.TASK_USER", [], ["TAS_UID" => $tas_uid, "USR_UID" => $usr_uid, "TU_TYPE" => $tu_type, "TU_RELATION" => $tu_relation]);

       if(!isset($results[0]) || empty($results[0])) {
           return false;
       }
       
       $TaskUser = new TaskUser();
       $TaskUser->setTasUid($results[0]['TAS_UID']);
       $TaskUser->setTuType($tu_type);
       $TaskUser->setTuRelation($tu_relation);
       $TaskUser->setUsrUid($usr_uid);
       
       return $TaskUser;
    }

    /**
     * Create the application document registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function create ($aData)
    {
        try {
            $taskUser = $this->retrieveByPK ($aData['TAS_UID'], $aData['USR_UID'], $aData['TU_TYPE'], $aData['TU_RELATION']);

            if ( is_object ($taskUser) )
            {
                return - 1;
            }

            $oTaskUser = new TaskUser();
            $oTaskUser->loadObject ($aData);
            
            if ( $oTaskUser->validate () )
            {
                $iResult = $oTaskUser->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oTaskUser->getValidationFailures ();
                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure - oValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Remove the application document registry
     *
     * @param string $sTasUid
     * @param string $sUserUid
     * @return string
     *
     */
    public function remove ($sTasUid, $sUserUid, $iType, $iRelation)
    {
        try {
            $oTaskUser = $this->retrieveByPK ($sTasUid, $sUserUid, $iType, $iRelation);
            if ( !is_null ($oTaskUser) )
            {
                $oTaskUser->delete ();
                return true;
            }
            else
            {
                throw (new Exception ('This row does not exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function TaskUserExists ($sTasUid, $sUserUid, $iType, $iRelation)
    {
        try {
            $oTaskUser = $this->retrieveByPk ($sTasUid, $sUserUid, $iType, $iRelation);
            if ( is_object ($oTaskUser) && get_class ($oTaskUser) == 'TaskUser' )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function getCountAllTaksByGroups ()
    {
        $sql = "SELECT COUNT(*) AS CNT, USR_UID AS GRP_UID FROM workflow.TASK_USER 
        WHERE TU_TYPE = 1 AND TU_RELATION = 2
        GROUP BY USR_UID";
        
        $results = $this->objMysql->_query($sql);
        $aRows = array();
        
        foreach($results as $row){
            $aRows[$row['GRP_UID']] = $row['CNT'];
        }
        return $aRows;
    }

    //erik: new functions
    public function getUsersTask ($TAS_UID, $TU_TYPE = 1)
    {
        require_once 'classes/model/Users.php';
        $groupsTask = array();
        $usersTask = array();
        //getting task's users
        
        $sql = "SELECT firstName, lastName, USR_UID, username, TAS_UID, TU_TYPE, TU_RELATION
        FROM user_management.poms_users u
        LEFT JOIN workflow.TASK_USER tu ON tu.USR_UID = u.usrid
        WHERE TAS_UID = ? AND TU_TYPE = ? AND TU_RELATION = 1";
        
        $arrParameters = array($TAS_UID, $TU_TYPE);
       
        $usersTask = $this->objMysql->_query($sql);
        
        //getting task's groups
        
        $criteria->addAsColumn ('GRP_TITLE', 'CONTENT.CON_VALUE');
        $criteria->addSelectColumn (TaskUserPeer::TAS_UID);
        $criteria->addSelectColumn (TaskUserPeer::USR_UID);
        $criteria->addSelectColumn (TaskUserPeer::TU_TYPE);
        $criteria->addSelectColumn (TaskUserPeer::TU_RELATION);
        $aConditions[] = array(TaskUserPeer::USR_UID, 'CONTENT.CON_ID');
        $aConditions[] = array('CONTENT.CON_CATEGORY', $delimiter . 'GRP_TITLE' . $delimiter);
        $aConditions[] = array('CONTENT.CON_LANG', $delimiter . SYS_LANG . $delimiter);
        $criteria->addJoinMC ($aConditions, Criteria::LEFT_JOIN);
        $criteria->add (TaskUserPeer::TAS_UID, $TAS_UID);
        $criteria->add (TaskUserPeer::TU_TYPE, $TU_TYPE);
        $criteria->add (TaskUserPeer::TU_RELATION, 2);
        $dataset = TaskUserPeer::doSelectRS ($criteria);
        $dataset = TaskUserPeer::doSelectRS ($criteria);
        $dataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
        while ($dataset->next ()) {
            $usersTask[] = $dataset->getRow ();
        }
        $result = new stdClass();
        $result->data = $usersTask;
        $result->totalCount = sizeof ($usersTask);
        return $result;
    }

}
