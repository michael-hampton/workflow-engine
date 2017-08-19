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

    use Validator;

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

            $objTask->setTasType ("SCRIPT-TASK");

            if ( is_object ($objTask) && $objTask->getTasType () == "SCRIPT-TASK" )
            {
                $sql = "SELECT SCRTAS_OBJ_UID from WORKFLOW.SCRIPT_TASK WHERE ACT_UID = ?";
                $results = $this->objMysql->_query ($sql, [$objTask->getStepId ()]);

                if ( !isset ($results[0]) || empty ($results[0]) )
                {
                    return false;
                }

                foreach ($results as $row) {
                    $scriptTasObjUid = $row["SCRTAS_OBJ_UID"];

                    $trigger = (new StepTrigger())->getDataTrigger ($scriptTasObjUid);

                    if ( !is_null ($trigger) )
                    {
                        $pmScript = new \ScriptFunctions();
                        $pmScript->setFields ($arrayApplicationData);
                        $pmScript->setScript ($trigger['template_name']);

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

    public function retrieveByPk ($pk)
    {
        try {
            $result = $this->objMysql->_select ("workflow.script_task", [], ["SCRTAS_UID" => $pk]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Verify if exists the Script-Task
     *
     * @param string $scriptTaskUid Unique id of Script-Task
     *
     * return bool Return true if exists the Script-Task, false otherwise
     */
    public function exists ($scriptTaskUid)
    {
        try {
            $obj = $this->retrieveByPk ($scriptTaskUid);

            //Return
            return $obj === false ? false : true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the Script-Task
     *
     * @param string $scriptTaskUid         Unique id of Script-Task
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exists the Script-Task
     */
    public function throwExceptionIfNotExistsScriptTask ($scriptTaskUid)
    {
        try {
            if ( !$this->exists ($scriptTaskUid) )
            {
                throw new \Exception ("ID_SCRIPT_TASK_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $scriptTaskUid Unique id of Script-Task
     * @param string $projectUid    Unique id of Project
     * @param array  $arrayData     Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($scriptTaskUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayScriptTaskData = ($scriptTaskUid == "") ? array() : $this->getScriptTask ($scriptTaskUid, true);
            $flagInsert = ($scriptTaskUid == "") ? true : false;

            $arrayFinalData = array_merge ($arrayScriptTaskData, $arrayData);

            //---
            if ( isset ($arrayData["ACT_UID"]) )
            {
                $obj = (new \Task())->retrieveByPk ($arrayData["ACT_UID"]);

                if ( $obj === false )
                {
                    throw new \Exception ("ID_SCRIPT_TASK_DOES_NOT_ACTIVITY");
                }
            }

            if ( isset ($arrayData["SCRTAS_OBJ_UID"]) )
            {
                $obj = (new \Trigger())->retrieveByPK ($arrayData["SCRTAS_OBJ_UID"]);

                if ( is_null ($obj) )
                {
                    throw new \Exception ("ID_SCRIPT_TASK_DOES_NOT_TRIGGER");
                }
            }

            if ( isset ($arrayData["ACT_UID"]) )
            {
                $result = $this->objMysql->_select ("workflow.status_mapping", [], ["TAS_UID" => $arrayData["ACT_UID"], "workflow_id" => $projectUid]);



                if ( !isset ($result[0]) || empty ($result[0]) )
                {
                    throw new \Exception ("ID_SCRIPT_TASK_ACTIVITY_NOT_BELONG_TO_PROJECT");
                }
            }

            if ( isset ($arrayData["SCRTAS_OBJ_UID"]) )
            {
                $result2 = $this->objMysql->_select ("workflow.step_trigger", [], ["id" => $arrayData["SCRTAS_OBJ_UID"], "workflow_id" => $projectUid]);


                if ( !isset ($result2[0]) || empty ($result2[0]) )
                {
                    throw new \Exception ("ID_SCRIPT_TASK_TRIGGER_NOT_BELONG_TO_PROJECT");
                }
            }

            /* $obj = \BpmnActivityPeer::retrieveByPK ($arrayFinalData["ACT_UID"]);

              if ( $obj->getActTaskType () != "SCRIPTTASK" )
              {
              throw new \Exception (\G::LoadTranslation ("ID_SCRIPT_TASK_TYPE_ACTIVITY_NOT_IS_SCRIPTTASK", array($this->arrayFieldNameForException["actUid"], $arrayData["ACT_UID"])));
              } */
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Script-Task for a Project
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Script-Task created
     */
    public function create ($projectUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData);
            $this->throwExceptionIfDataIsEmpty ($arrayData);

            //Set data
            unset ($arrayData["SCRTAS_UID"]);
            unset ($arrayData["PRJ_UID"]);

            //Verify data
            (new Process())->throwExceptionIfNotExistsProcess ($projectUid);

            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);

            //Create
            try {
                $scriptTask = new \ScriptTask();

                $scriptTask->loadObject ($arrayData);

                $scriptTask->setPrjUid ($projectUid);

                if ( $scriptTask->validate () )
                {
                    $scriptTaskUid = $scriptTask->save ();

                    //Return
                    return $this->getScriptTask ($scriptTaskUid);
                }
                else
                {
                    $msg = "";

                    foreach ($scriptTask->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }

                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Script-Task
     *
     * @param string $scriptTaskUid Unique id of Script-Task
     * @param array  $arrayData     Data
     *
     * return array Return data of the Script-Task updated
     */
    public function update ($scriptTaskUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Set data
            $arrayDataBackup = $arrayData;

            unset ($arrayData["SCRTAS_UID"]);
            unset ($arrayData["PRJ_UID"]);

            //Set variables
            $arrayScriptTaskData = $this->getScriptTask ($scriptTaskUid, true);

            //Verify data
            $this->throwExceptionIfNotExistsScriptTask ($scriptTaskUid);

            $this->throwExceptionIfDataIsInvalid ($scriptTaskUid, $arrayScriptTaskData["PRJ_UID"], $arrayData);

            //Update
            try {
                $scriptTask = (new \ScriptTask())->retrieveByPK ($scriptTaskUid);

                $scriptTask->loadObject ($arrayData);

                if ( $scriptTask->validate () )
                {
                    $scriptTask->save ();

                    //Return
                    $arrayData = $arrayDataBackup;

                    return $arrayData;
                }
                else
                {
                    $msg = "";

                    foreach ($scriptTask->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }

                    throw new \Exception ("ID_REGISTRY_CANNOT_BE_UPDATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Script-Task
     *
     * @param string $scriptTaskUid Unique id of Script-Task
     *
     * return void
     */
    public function delete ($scriptTaskUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsScriptTask ($scriptTaskUid);

            //Delete
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Script-Task
     *
     * @param array $arrayCondition Conditions
     *
     * return void
     */
    public function deleteWhere (array $arrayCondition)
    {
        try {
            //Delete
            $sql = "";
            foreach ($arrayCondition as $key => $value) {
                if ( is_array ($value) )
                {
                    $sql .= $key . " " . $value[0] . " " . $value[1];
                }
                else
                {
                    $sql .= $key . " = " . $value;
                }
            }

            //$result = \ScriptTaskPeer::doDelete ($criteria);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Script-Task
     *
     * return object
     */
    public function getScriptTaskCriteria ()
    {
        try {
            $criteria = "SELECT t.title, SCRTAS_UID, PRJ_UID, ACT_UID, SCRTAS_OBJ_TYPE, SCRTAS_OBJ_UID FROM workflow.SCRIPT_TASK st LEFT JOIN workflow.step_trigger t ON t.id = st.SCRTAS_OBJ_UID";

            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Script-Task from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Script-Task
     */
    public function getScriptTaskDataFromRecord (array $record)
    {
        try {

            $objScript = new \ScriptTask();

            $objScript->setActUid ($record["ACT_UID"]);
            $objScript->setPrjUid ($record["PRJ_UID"]);
            $objScript->setScrtasObjType ($record["SCRTAS_OBJ_TYPE"]);
            $objScript->setScrtasObjUid ($record["SCRTAS_OBJ_UID"]);
            $objScript->setScrtasUid ($record["SCRTAS_UID"]);
             $objScript->setTitle ($record["title"]);

            return $objScript;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Script-Task
     *
     * @param string $scriptTaskUid Unique id of Script-Task
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Script-Task
     */
    public function getScriptTask ($scriptTaskUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsScriptTask ($scriptTaskUid);

            //Get data
            $criteria = $this->getScriptTaskCriteria ();

            $criteria .= " WHERE SCRTAS_UID = ?";
            $results = $this->objMysql->_query ($criteria, [$scriptTaskUid]);

            //Return
            return (!$flagGetRecord) ? $this->getScriptTaskDataFromRecord ($results[0]) : $results[0];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Script-Tasks
     *
     * @param string $scriptTaskDefUid Unique id of Project
     *
     * return array Return an array with all Script-Tasks
     */
    public function getScriptTasks ($projectUid)
    {
        try {
            $arrayScriptTask = array();

            //Verify data
            $process = new Process();

            $process->throwExceptionIfNotExistsProcess ($projectUid);

            //Get data
            $criteria = $this->getScriptTaskCriteria ();

            $criteria .= " WHERE PRJ_UID = ?";

            $results = $this->objMysql->_query ($criteria, [$projectUid]);

            foreach ($results as $row) {

                $arrayScriptTask[] = $this->getScriptTaskDataFromRecord ($row);
            }

            //Return
            return $arrayScriptTask;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Script-Task by unique id of Activity
     *
     * @param string $projectUid  Unique id of Project
     * @param string $activityUid Unique id of Event
     *
     * return array Return an array with data of a Script-Task by unique id of Activity
     */
    public function getScriptTaskByActivity ($projectUid, $activityUid)
    {
        try {
            //Verify data
            $process = new Process();

            $process->throwExceptionIfNotExistsProcess ($projectUid);

            $sql = "SELECT ACT_UID FROM workflow.SCRIPT_TASK WHERE PRJ_UID = ? AND ACT_UID = ?";
            $results = $this->objMysql->_query ($sql, [$projectUid, $activityUid]);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                throw new \Exception ("ID_SCRIPT_TASK_ACTIVITY_NOT_BELONG_TO_PROJECT");
            }

            $criteria = $this->getScriptTaskCriteria ();

            $criteria .= " WHERE PRJ_UID = ? AND ACT_UID = ?";
            $results2 = $this->objMysql->_query ($criteria, [$projectUid, $activityUid]);

            //Return
            return isset ($results2[0]) && !empty ($results2[0]) ? $this->getScriptTaskDataFromRecord ($results2[0]) : array();
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
