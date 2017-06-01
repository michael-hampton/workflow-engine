<?php

class ProcessUser extends BaseProcessUser
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $aData
     * @throws type
     */
    public function create ($aData)
    {
        $results = $this->objMysql->_select ("workflow.process_supervisors", array(), array(
            "user_id" => $aData['USR_UID'],
            "workflow_id" => $aData['PRO_UID'],
            "pu_type" => $aData['PU_TYPE']
                )
        );

        if ( !empty ($results) )
        {
            foreach ($results as $result) {
                $this->remove ($result['id']);
            }
        }

        $oProcessUser = new BaseProcessUser();
        $oProcessUser->loadObject ($aData);

        if ( $oProcessUser->validate () )
        {
            $oProcessUser->save();
        }
        else
        {
            $sMessage = '';
            $aValidationFailures = $oProcessUser->getValidationFailures ();
            foreach ($aValidationFailures as $oValidationFailure) {
                $sMessage .= $oValidationFailure . '<br />';
            }
            throw(new Exception ('The registry cannot be created!<br />' . $sMessage));
        }
    }

    /**
     * get process user by id
     * @param type $puId
     * @return \BaseProcessUser
     */
    public function retrieveByPK ($puId)
    {
        $result = $this->objMysql->_select ("workflow.process_supervisors", array(), array("id" => $puId));

        $base = new BaseProcessUser();
        $base->setPu_id ($puId);

        if ( !empty ($result) )
        {
            $base->setUsr_uid ($result[0]['user_id']);
            $base->setPu_type ($result[0]['pu_type']);
            $base->setPro_uid($result[0]['workflow_id']);
        }

        return $base;
    }

    /**
     * Remove the users and teams from a process
     * @param string $sPuUid
     * @return string
     * */
    public function remove ($sPuUid)
    {
        try {
            $oProcessUser = $this->retrieveByPK ($sPuUid);

            if ( !empty ($oProcessUser) )
            {
                $oProcessUser->delete ();
            }
            else
            {
                throw(new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $ex) {
            throw($oError);
        }
    }

}
