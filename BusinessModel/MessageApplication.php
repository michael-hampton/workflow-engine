<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageApplication
 *
 * @author michael.hampton
 */
class MessageApplication
{

    private $frontEnd = false;

    /**
     * Verify if exists the Message-Application
     *
     * @param string $messageApplicationUid Unique id of Message-Application
     *
     * return bool Return true if exists the Message-Application, false otherwise
     */
    public function exists ($messageApplicationUid)
    {
        try {
            $obj = \MessageApplicationPeer::retrieveByPK ($messageApplicationUid);
            return (!is_null ($obj)) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Message-Application for the Case
     *
     * @param string $applicationUid       Unique id of Case
     * @param string $projectUid           Unique id of Project
     * @param string $eventUidThrow        Unique id of Event (throw)
     * @param array  $arrayApplicationData Case data
     *
     * return bool Return true if been created, false otherwise
     */
    public function create ($workflowId, $applicationUid, $projectUid, $eventUidThrow, array $arrayApplicationData)
    {
        try {
            $flagCreate = true;
            //Set data
            //Message-Event-Relation - Get unique id of Event (catch)
            $messageEventRelation = new MessageEventRelation();
            $arrayMessageEventRelationData = $messageEventRelation->getMessageEventRelationWhere (
                    array(
                "PRJ_UID" => $workflowId,
                "EVN_UID_THROW" => $eventUidThrow
                    ), true
            );

            if ( !is_null ($arrayMessageEventRelationData) )
            {
                $eventUidCatch = $arrayMessageEventRelationData["EVN_UID_CATCH"];
            }
            else
            {
                $flagCreate = false;
            }
            //Message-Application - Get data ($eventUidThrow)
            $messageEventDefinition = new MessageEventDefinition();
            if ( $messageEventDefinition->existsEvent ($projectUid, $eventUidThrow) )
            {
                $arrayMessageEventDefinitionData = $messageEventDefinition->getMessageEventDefinitionByEvent ($projectUid, $eventUidThrow, true);


                $arrayMessageApplicationVariables = unserialize ($arrayMessageEventDefinitionData[0]["MSGT_VARIABLES"]);
                $arrData = (new \Elements ($projectUid, $applicationUid))->arrElement;

                if ( !empty ($arrayMessageApplicationVariables) )
                {
                    foreach ($arrayMessageApplicationVariables['MSGT_VARIABLES'] as $key => $arrVariable) {

                        if ( isset ($arrData[$arrVariable['FIELD']]) )
                        {

                            $arrayMessageApplicationVariables['MSGT_VARIABLES'][$key]['VALUE'] = $arrData[$arrVariable['FIELD']];
                        }
                    }
                }
            }
            else
            {
                $flagCreate = false;
            }

            if ( !$flagCreate )
            {
                //Return
                return false;
            }
            
            $messageApplicationCorrelation =  $arrayMessageEventDefinitionData[0]["MSGED_CORRELATION"];
            
            //Create
            try {
                $messageApplication = new \MessageApplications();
                $messageApplication->setAppUid ($applicationUid);
                $messageApplication->setPrjUid ($projectUid);
                $messageApplication->setEvnUidThrow ($eventUidThrow);
                $messageApplication->setEvnUidCatch ($eventUidCatch);
                $messageApplication->setMsgappVariables (serialize ($arrayMessageApplicationVariables));
                $messageApplication->setMsgappCorrelation ($messageApplicationCorrelation);
                $messageApplication->setMsgappThrowDate ("now");
                
                
                if ( $messageApplication->validate () )
                {
                    $result = $messageApplication->save ();
                    //Return
                    return true;
                }
                else
                {
                    $msg = "";
                    foreach ($messageApplication->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "") ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
