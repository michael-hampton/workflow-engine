<?php

/**
 * AppEvent.php
 *
 * @package workflow.engine.classes.model
 */
//require_once 'classes/model/om/BaseAppEvent.php';
/**
 * Skeleton subclass for representing a row from the 'APP_EVENT' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package workflow.engine.classes.model
 */
class AppEvent extends BaseAppEvent
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function load ($sApplicationUID, $iDelegation)
    {
        try {
            $oAppEvent = $this->retrieveByPK ($sApplicationUID, $iDelegation);

            if ( $oAppEvent === false )
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }

            return $oAppEvent;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function retrieveByPK ($app_uid, $del_index, $evn_uid, $caseId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.APP_EVENT", [], ["APP_UID" => $app_uid, "DEL_INDEX" => $del_index, "EVN_UID" => $evn_uid, "CASE_UID" => $caseId]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objAppEvent = new AppEvent();
        $objAppEvent->setAppEvnActionDate ($result[0]['APP_EVN_ACTION_DATE']);
        $objAppEvent->setAppEvnAttempts ($result[0]['APP_EVN_ATTEMPTS']);
        $objAppEvent->setAppEvnLastExecutionDate ($result[0]['APP_EVN_LAST_EXECUTION_DATE']);
        $objAppEvent->setAppEvnStatus ($result[0]['APP_EVN_STATUS']);
        $objAppEvent->setAppUid ($result[0]['APP_UID']);
        $objAppEvent->setCaseUid ($result[0]['CASE_UID']);
        $objAppEvent->setDelIndex ($result[0]['DEL_INDEX']);
        $objAppEvent->setEvnUid ($result[0]['EVN_UID']);
        $objAppEvent->setId ($result[0]['id']);

        return $objAppEvent;
    }

    public function create ($aData)
    {
        try {
            $oAppEvent = new AppEvent();

            $oAppEvent->loadObject ($aData);

            if ( $oAppEvent->validate () )
            {

                $oAppEvent->save ();

                return true;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oAppEvent->getValidationFailures ();

                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }

                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

    public function update ($aData)
    {
        try {
            $oAppEvent = $this->retrieveByPK ($aData['APP_UID'], $aData['DEL_INDEX']);

            if ( !is_null ($oAppEvent) )
            {
                $oAppEvent->loadArray ($aData);

                if ( $oAppEvent->validate () )
                {

                    $iResult = $oAppEvent->save ();

                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oAppEvent->getValidationFailures ();

                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

    public function remove ($sApplicationUID, $iDelegation, $sEvnUid)
    {

        try {
            $oAppEvent = $this->retrieveByPK ($sApplicationUID, $iDelegation, $sEvnUid);

            if ( !is_null ($oAppEvent) )
            {

                $iResult = $oAppEvent->delete ();

                return $iResult;
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

}
