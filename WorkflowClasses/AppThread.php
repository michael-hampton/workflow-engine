<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppThread
 *
 * @author michael.hampton
 */
class AppThread extends BaseAppThread
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function createAppThread (WorkflowStep $objWorkflowStep, $objElement, Users $objUser, Task $objTask)
    {
        if ( strlen ($objWorkflowStep->getWorkflowId ()) == 0 )
        {
            throw (new Exception ('Column "PRO_UID" cannot be null.'));
        }

        if ( method_exists ($objElement, "getSource_id") && strlen ($objElement->getSource_id ()) == 0 )
        {
            throw (new Exception ('Column "APP_UID" cannot be null.'));
        }
        elseif ( method_exists ($objElement, "getId") && strlen ($objElement->getId ()) == 0 )
        {
            throw (new Exception ('Column "APP_UID" cannot be null.'));
        }

        if ( strlen ($objWorkflowStep->getCurrentTask ()) == 0 )
        {
            throw (new Exception ('Column "TAS_UID" cannot be null.'));
        }
        if ( trim ($objUser->getUserId ()) === "" )
        {
            throw (new Exception ('Column "USR_UID" cannot be null.'));
        }

        $objectId = method_exists ($objElement, "getSource_id") ? $objElement->getSource_id () : $objElement->getId ();

        $sql = "SELECT MAX(`APP_THREAD_INDEX`) AS max FROM workflow.workflow_data WHERE object_id = ?";
        $arrParameters = array($objectId);
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $iAppThreadIndex = isset ($results[0]) && !empty ($results[0]) ? (int) $results[0]['max'] + 1 : 0;
        $this->setAppUid (method_exists ($objElement, "getSource_id") ? $objElement->getSource_id () : $objElement->getId ());
        $this->setAppNumber ($objElement->getId ());
        $this->setAppThreadIndex ($iAppThreadIndex);
        $this->setCollectionId ($objWorkflowStep->getCollectionId ());
        $this->setProUid ($objWorkflowStep->getWorkflowId ());
        $this->setStatus ('OPEN');
        $this->setTasUid ($objTask->getStepId ());
        $this->setHasEvent ($objWorkflowStep->getHasEvent ());
        
        if ( $this->validate () )
        {
            try {
                $this->save ();
            } catch (Exception $e) {
                throw ( $e );
            }
        }
        else
        {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $this->getValidationFailures ();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage ();
            }
            throw ( new Exception ('Failed Data validation. ' . $msg) );
        }
        return $iAppThreadIndex;
    }

    public function update ($aData)
    {
        try {
            $oApp = $this->retrieveByPK ($aData['APP_UID'], $aData['APP_THREAD_INDEX']);

            if ( is_object ($oApp) && get_class ($oApp) == 'AppThread' )
            {
                $oApp->loadObject ($aData);

                if ( $oApp->validate () )
                {
                    $oApp->save ();
                }
                else
                {
                    $msg = '';
                    foreach ($this->getValidationFailures () as $objValidationFailure) {
                        $msg .= $objValidationFailure . "<br/>";
                    }
                    throw ( new Exception ('The AppThread row cannot be created! ' . $msg) );
                }
            }
            else
            {
                throw(new Exception ("This AppThread row doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

}
