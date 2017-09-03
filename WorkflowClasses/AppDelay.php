<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppDelay
 *
 * @author michael.hampton
 */
class AppDelay extends BaseAppDelay
{

    private $objMysql;

    public function __construct ($id = null)
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();
    }

    /**
     * Create the application delay registry
     * @param array $aData
     * @return string
     * */
    public function create ($aData)
    {
        try {

            $oAppDelay = new AppDelay();
            $oAppDelay->loadObject ($aData);

            if ( $oAppDelay->validate () )
            {
                $id = $oAppDelay->save ();

                return $id;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oAppDelay->getValidationFailures ();

                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }

                throw(new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

    public function retrieveByPk ($pk)
    {
        $result = $this->objMysql->_select ("workflow.APP_DELAY", [], ["APP_DELAY_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $appDelay = new AppDelay();
        $appDelay->setAppAutomaticDisabledDate ($result[0]['APP_AUTOMATIC_DISABLED_DATE']);
        $appDelay->setAppDelIndex ($result[0]['APP_DEL_INDEX']);
        $appDelay->setAppDelayUid ($result[0]['APP_DELAY_UID']);
        $appDelay->setAppDelegationUser ($result[0]['APP_DELEGATION_USER']);
        $appDelay->setAppDisableActionDate ($result[0]['APP_DISABLE_ACTION_DATE']);
        $appDelay->setAppDisableActionUser ($result[0]['APP_DISABLE_ACTION_USER']);
        $appDelay->setAppEnableActionDate ($result[0]['APP_ENABLE_ACTION_DATE']);
        $appDelay->setAppEnableActionUser ($result[0]['APP_ENABLE_ACTION_USER']);
        $appDelay->setAppNextTask ($result[0]['APP_NEXT_TASK']);
        $appDelay->setAppStatus ($result[0]['APP_STATUS']);
        $appDelay->setAppThreadIndex ($result[0]['APP_THREAD_INDEX']);
        $appDelay->setAppType ($result[0]['APP_TYPE']);
        $appDelay->setAppUid ($result[0]['APP_UID']);
        $appDelay->setProUid ($result[0]['PRO_UID']);

        return $appDelay;
    }

    /**
     * Update the application delay registry
     * @param array $aData
     * @return string
     * */
    public function update ($aData)
    {
        try {
            $oAppDelay = $this->retrieveByPK ($aData['APP_DELAY_UID']);

            if ( $oAppDelay !== false )
            {

                $oAppDelay->loadObject ($aData);

                if ( $oAppDelay->validate () )
                {
                    $iResult = $oAppDelay->save ();
                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oAppDelay->getValidationFailures ();

                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw(new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw(new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

    public function isPaused ($appUid, $delIndex)
    {

        $result = "SELECT * FROM APP_DELAY WHERE APP_UID = ? AND (APP_DISABLE_ACTION_USER = 0 or ISNULL(APP_DISABLE_ACTION_USER))";
        $arrParameters = [$appUid];

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
