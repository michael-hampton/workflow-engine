<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of ScriptTask
 *
 * @author michael.hampton
 */
class ScriptTask
{

    private $objMysql;
    
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Execute Script
     *
     * @param string $activityUid          Unique id of Event
     * @param array  $arrayApplicationData Case data
     *
     * return array
     */
    public function execScriptByActivityUid (\Task $objTask, array $arrayApplicationData)
    {
        try {

            $objTask->setTasType("SCRIPT-TASK");
            
            if ( is_object ($objTask) && $objTask->getTasType () == "SCRIPT-TASK" )
            {
                $sql = "SELECT SCRTAS_OBJ_UID from WORKFLOW.SCRIPT_TASK WHERE ACT_UID = ?";
                $results = $this->objMysql->_query($sql, [$objTask->getStepId()]);
                
                if(!isset($results[0]) || empty($results[0])) {
                    return false;
                }

                foreach ($results as $row)
                {
                    $scriptTasObjUid = $row["SCRTAS_OBJ_UID"];

                    $trigger = (new StepTrigger())->getDataTrigger($scriptTasObjUid);

                    if ( !is_null ($trigger) )
                    {
                        $pmScript = new \ScriptFunctions();
                        $pmScript->setFields ($arrayApplicationData);
                        $pmScript->setScript (array($trigger['template_name']));
                                 
                        $result = $pmScript->execute ();
                        
                        if ( isset ($pmScript->aFields["__ERROR__"]) )
                        {
                            \G::log ("Case Uid: " . $arrayApplicationData["APP_UID"] . ", Error: " . $pmScript->aFields["__ERROR__"], PATH_DATA . "log/ScriptTask.log");
                        }

                        $arrayApplicationData["APP_DATA"] = $pmScript->aFields;

                        //$case = new \Cases();

                        //$result = $case->updateCase ($arrayApplicationData["APP_UID"], $arrayApplicationData);
                    }
                }
            }

            //Return
            return $arrayApplicationData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
