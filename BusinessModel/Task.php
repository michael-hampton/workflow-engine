<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of Task
 *
 * @author michael.hampton
 */
class Task
{

    private $objMsql;

    public function __construct ()
    {
        $this->objMsql = new \Mysql2();
    }

    /**
     * Update properties of an Task
     * @var string $prj_uid. Uid for Workflow
     * @var string $act_uid. Uid for Task
     * @var array $arrayProperty. Data for properties of Activity
     *
     * return object
     */
    public function updateProperties ($prj_uid, $act_uid, $arrayProperty)
    {
        //Copy of processmaker/workflow/engine/methods/tasks/tasks_Ajax.php //case "saveTaskData":
        try {

            $prj_uid = $this->validateProUid ($prj_uid);
            $act_uid = $this->validateActUid ($act_uid);
            $arrayProperty["TAS_UID"] = $act_uid;
            $arrayProperty["PRO_UID"] = $prj_uid;
            $task = new \Task();
            $aTaskInfo = $task->retrieveByPk ($arrayProperty["TAS_UID"]);

            $arrayResult = array();

            if ( isset ($arrayProperty["TAS_SELFSERVICE_TIMEOUT"]) && $arrayProperty["TAS_SELFSERVICE_TIMEOUT"] == "1" )
            {
                if ( !is_numeric ($arrayProperty["TAS_SELFSERVICE_TIME"]) || $arrayProperty["TAS_SELFSERVICE_TIME"] == '' )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_selfservice_time'"));
                }
            }

            foreach ($arrayProperty as $k => $v) {
                $arrayProperty[$k] = str_replace ("@amp@", "&", $v);
            }

            if ( isset ($arrayProperty["TAS_SEND_LAST_EMAIL"]) )
            {
                $arrayProperty["TAS_SEND_LAST_EMAIL"] = ($arrayProperty["TAS_SEND_LAST_EMAIL"] == "TRUE") ? "TRUE" : "FALSE";
            }
            else
            {
                if ( isset ($arrayProperty["SEND_EMAIL"]) )
                {
                    $arrayProperty["TAS_SEND_LAST_EMAIL"] = ($arrayProperty["SEND_EMAIL"] == "TRUE") ? "TRUE" : "FALSE";
                }
                else
                {
                    //$arrayProperty["TAS_SEND_LAST_EMAIL"] = trim ($aTaskInfo->getTasSendLastEmail ()) !== "" ? $arrayProperty["TAS_SEND_LAST_EMAIL"] : "FALSE";
                }
            }

            if ( isset ($arrayProperty["TAS_RECEIVE_LAST_EMAIL"]) )
            {
                $arrayProperty["TAS_RECEIVE_LAST_EMAIL"] = $arrayProperty["TAS_RECEIVE_LAST_EMAIL"] === "TRUE" ? "TRUE" : "FALSE";
            }

            $conditions = $aTaskInfo->getCondition ();

            if ( isset ($conditions['autoAssign']) )
            {
                $assignType = "AUTO_ASIGN";
            }
            elseif ( isset ($conditions['claimStep']) )
            {
                $assignType = "SELF_SERVICE";
            }
            elseif ( isset ($conditions['doAllocation']) )
            {
                $assignType = "MANUAL";
            }


            //Validating TAS_ASSIGN_VARIABLE value
            if ( !isset ($arrayProperty["TAS_ASSIGN_TYPE"]) )
            {

                if ( trim ($assignType) === "" )
                {
                    $arrayProperty["TAS_ASSIGN_TYPE"] = "BALANCED";
                }
                else
                {
                    $arrayProperty["TAS_ASSIGN_TYPE"] = $assignType;
                }
            }

            switch ($arrayProperty["TAS_ASSIGN_TYPE"]) {
                case 'BALANCED':
                case 'MANUAL':
                case 'REPORT_TO':
                    $this->unsetVar ($arrayProperty, "TAS_ASSIGN_VARIABLE");
                    $this->unsetVar ($arrayProperty, "TAS_GROUP_VARIABLE");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIMEOUT");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME_UNIT");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TRIGGER_UID");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_EXECUTION");
                    break;
                case 'EVALUATE':
                    if ( empty ($arrayProperty["TAS_ASSIGN_VARIABLE"]) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_assign_variable'"));
                    }
                    $this->unsetVar ($arrayProperty, "TAS_GROUP_VARIABLE");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIMEOUT");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME_UNIT");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TRIGGER_UID");
                    $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_EXECUTION");
                    break;
                case 'SELF_SERVICE':
                case 'SELF_SERVICE_EVALUATE':
                    if ( $arrayProperty["TAS_ASSIGN_TYPE"] == "SELF_SERVICE_EVALUATE" )
                    {
                        if ( empty ($arrayProperty["TAS_GROUP_VARIABLE"]) )
                        {
                            throw (new \Exception ("Invalid value specified for 'tas_group_variable'"));
                        }
                    }
                    else
                    {
                        $arrayProperty["TAS_GROUP_VARIABLE"] = '';
                    }
                    $arrayProperty["TAS_ASSIGN_TYPE"] = "SELF_SERVICE";
                    if ( !($arrayProperty["TAS_SELFSERVICE_TIMEOUT"] == 0 || $arrayProperty["TAS_SELFSERVICE_TIMEOUT"] == 1) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_selfservice_timeout'"));
                    }
                    if ( $arrayProperty["TAS_SELFSERVICE_TIMEOUT"] == "1" )
                    {
                        if ( empty ($arrayProperty["TAS_SELFSERVICE_TIME"]) )
                        {
                            throw (new \Exception ("Invalid value specified for 'tas_assign_variable'"));
                        }
                        if ( empty ($arrayProperty["TAS_SELFSERVICE_TIME_UNIT"]) )
                        {
                            throw (new \Exception ("Invalid value specified for 'tas_selfservice_time_unit'"));
                        }
                        if ( empty ($arrayProperty["TAS_SELFSERVICE_TRIGGER_UID"]) )
                        {
                            throw (new \Exception ("Invalid value specified for 'tas_selfservice_trigger_uid'"));
                        }
                        if ( empty ($arrayProperty["TAS_SELFSERVICE_EXECUTION"]) )
                        {
                            throw (new \Exception ("Invalid value specified for 'tas_selfservice_execution'"));
                        }
                    }
                    else
                    {
                        $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME");
                        $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TIME_UNIT");
                        $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_TRIGGER_UID");
                        $this->unsetVar ($arrayProperty, "TAS_SELFSERVICE_EXECUTION");
                    }
                    break;
                case "MULTIPLE_INSTANCE_VALUE_BASED":
                    if ( trim ($arrayProperty["TAS_ASSIGN_VARIABLE"]) == "" )
                    {
                        throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
                    }
                    break;
            }

            $arrayProperty["TAS_TRANSFER_FLY"] = isset ($arrayProperty["TAS_TIMEUNIT"]) && trim ($arrayProperty["TAS_TIMEUNIT"]) !== "" ? "FALSE" : "TRUE";

            //Validating TAS_TRANSFER_FLY value
            if ( $arrayProperty["TAS_TRANSFER_FLY"] == "FALSE" )
            {
                if ( !isset ($arrayProperty["TAS_DURATION"]) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_duration'"));
                }
                $valuesTimeUnit = array('DAYS', 'HOURS', 'MINUTES');
                if ( (!isset ($arrayProperty["TAS_TIMEUNIT"])) ||
                        (!in_array ($arrayProperty["TAS_TIMEUNIT"], $valuesTimeUnit)) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_timeunit'"));
                }
                $valuesTypeDay = array('1', '2', '');
                if ( (!isset ($arrayProperty["TAS_TYPE_DAY"])) ||
                        (!in_array ($arrayProperty["TAS_TYPE_DAY"], $valuesTypeDay)) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_type_day'"));
                }
                if ( !isset ($arrayProperty["TAS_CALENDAR"]) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_calendar'"));
                }
            }
            else
            {
                $this->unsetVar ($arrayProperty, "TAS_DURATION");
                $this->unsetVar ($arrayProperty, "TAS_TIMEUNIT");
                $this->unsetVar ($arrayProperty, "TAS_TYPE_DAY");
                $this->unsetVar ($arrayProperty, "TAS_CALENDAR");
            }
            if (isset($arrayProperty["TAS_SEND_LAST_EMAIL"]) && $arrayProperty["TAS_SEND_LAST_EMAIL"] == "TRUE" )
            {
                if ( empty ($arrayProperty["TAS_DEF_SUBJECT_MESSAGE"]) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_def_subject_message'"));
                }
                $valuesDefMessageType = array('template', 'text');
                if ( (!isset ($arrayProperty["TAS_DEF_MESSAGE_TYPE"])) ||
                        (!in_array ($arrayProperty["TAS_DEF_MESSAGE_TYPE"], $valuesDefMessageType)) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_def_message_type'"));
                }
                if ( $arrayProperty["TAS_DEF_MESSAGE_TYPE"] == 'template' )
                {
                    if ( empty ($arrayProperty["TAS_DEF_MESSAGE_TEMPLATE"]) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_def_message_template'"));
                    }
                    $this->unsetVar ($arrayProperty, "TAS_DEF_MESSAGE");
                }
                else
                {
                    if ( empty ($arrayProperty["TAS_DEF_MESSAGE"]) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_def_message'"));
                    }
                    $this->unsetVar ($arrayProperty, "TAS_DEF_MESSAGE_TEMPLATE");
                }
                //Additional configuration
                if ( isset ($arrayProperty["TAS_DEF_MESSAGE_TYPE"]) )
                {
                    
                    $oConf = new \Configurations();
                    if ( !isset ($arrayProperty["TAS_DEF_MESSAGE_TEMPLATE"]) )
                    {
                        $arrayProperty["TAS_DEF_MESSAGE_TEMPLATE"] = "alert_message.html";
                    }
                  //  $oConf->aConfig = array("TAS_DEF_MESSAGE_TYPE" => $arrayProperty["TAS_DEF_MESSAGE_TYPE"], "TAS_DEF_MESSAGE_TEMPLATE" => $arrayProperty["TAS_DEF_MESSAGE_TEMPLATE"]);
                  //  $oConf->saveConfig ("TAS_EXTRA_PROPERTIES", $arrayProperty["TAS_UID"], "", "");
                }
            }
            else
            {
                $this->unsetVar ($arrayProperty, "TAS_DEF_SUBJECT_MESSAGE");
                $this->unsetVar ($arrayProperty, "TAS_DEF_MESSAGE_TYPE");
                $this->unsetVar ($arrayProperty, "TAS_DEF_MESSAGE");
                $this->unsetVar ($arrayProperty, "TAS_DEF_MESSAGE_TEMPLATE");
            }
            if ( isset ($arrayProperty["TAS_RECEIVE_LAST_EMAIL"]) && $arrayProperty["TAS_RECEIVE_LAST_EMAIL"] == "TRUE" )
            {
                if ( empty ($arrayProperty["TAS_RECEIVE_SUBJECT_MESSAGE"]) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_receive_subject_message'"));
                }
                if ( !isset ($arrayProperty["TAS_RECEIVE_MESSAGE_TYPE"]) )
                {
                    $arrayProperty["TAS_RECEIVE_MESSAGE_TYPE"] = "text";
                }
                $valuesDefMessageType = array('text', 'template');
                if ( !in_array ($arrayProperty["TAS_RECEIVE_MESSAGE_TYPE"], $valuesDefMessageType) )
                {
                    throw (new \Exception ("Invalid value specified for 'tas_receive_message_type'"));
                }
                if ( !isset ($arrayProperty["TAS_RECEIVE_MESSAGE_TEMPLATE"]) )
                {
                    $arrayProperty["TAS_RECEIVE_MESSAGE_TEMPLATE"] = "alert_message.html";
                }
                if ( $arrayProperty["TAS_RECEIVE_MESSAGE_TYPE"] == 'template' )
                {
                    if ( empty ($arrayProperty["TAS_RECEIVE_MESSAGE_TEMPLATE"]) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_receive_message_template'"));
                    }
                    $this->unsetVar ($arrayProperty, "TAS_RECEIVE_MESSAGE");
                }
                else
                {
                    if ( empty ($arrayProperty["TAS_RECEIVE_MESSAGE"]) )
                    {
                        throw (new \Exception ("Invalid value specified for 'tas_receive_message'"));
                    }
                    $this->unsetVar ($arrayProperty, "TAS_RECEIVE_MESSAGE_TEMPLATE");
                }
            }
            else
            {
                $this->unsetVar ($arrayProperty, "TAS_RECEIVE_SERVER_UID");
                $this->unsetVar ($arrayProperty, "TAS_RECEIVE_SUBJECT_MESSAGE");
                $this->unsetVar ($arrayProperty, "TAS_RECEIVE_MESSAGE");
                $this->unsetVar ($arrayProperty, "TAS_RECEIVE_MESSAGE_TYPE");
                $this->unsetVar ($arrayProperty, "TAS_RECEIVE_MESSAGE_TEMPLATE");
            }
            
            $result = $task->updateTaskProperties ($arrayProperty);
          /*  if ( isset ($arrayProperty['CONSOLIDATE_DATA']) && !empty ($arrayProperty['CONSOLIDATE_DATA']) )
            {
                if ( !empty ($arrayProperty['CONSOLIDATE_DATA']['consolidated_dynaform']) )
                {
                   
                    
                    $dataConso = array(
                        'con_status' => $arrayProperty['CONSOLIDATE_DATA']['consolidated_enable'],
                        'tas_uid' => $arrayProperty['TAS_UID'],
                        'dyn_uid' => $arrayProperty['CONSOLIDATE_DATA']['consolidated_dynaform'],
                        'pro_uid' => $arrayProperty['PRO_UID'],
                        'rep_uid' => $arrayProperty['CONSOLIDATE_DATA']['consolidated_report_table'],
                        'table_name' => $arrayProperty['CONSOLIDATE_DATA']['consolidated_table'],
                        'title' => $arrayProperty['CONSOLIDATE_DATA']['consolidated_title']
                    );
                    
                }
            }*/
            $arrayResult["status"] = "OK";
            if ( $result == 3 )
            {
                $arrayResult["status"] = "CRONCL";
            }
            return $arrayResult;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Unset variable for array
     * @var array $array. Array base
     * @var string $variable. name for variable
     *
     *
     * @return string
     */
    public function unsetVar (&$array, $variable)
    {
        if ( isset ($array[$variable]) )
        {
            unset ($array[$variable]);
        }
    }

    /**
     * Delete Activity
     * @var string $prj_uid. Uid for Process
     * @var string $act_uid. Uid for Activity
     *
     *
     * return object
     */
    public function deleteTask ($prj_uid, $act_uid)
    {
        try {
            $prj_uid = $this->validateProUid ($prj_uid);
            $act_uid = $this->validateActUid ($act_uid);
            G::LoadClass ('tasks');
            $tasks = new \Tasks();
            $tasks->deleteTask ($act_uid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate Process Uid
     * @var string $pro_uid. Uid for process
     *
     *
     * @return string
     */
    public function validateProUid ($pro_uid)
    {
        $pro_uid = trim ($pro_uid);
        if ( $pro_uid == '' )
        {
            throw (new \Exception ("The project with prj_uid: '', does not exist."));
        }
        $oProcess = new Process();
        if ( !($oProcess->processExists ($pro_uid)) )
        {
            throw (new \Exception ("The project with prj_uid: '$pro_uid', does not exist."));
        }
        return $pro_uid;
    }

    /**
     * Validate Task Uid
     * @var string $act_uid. Uid for task
     *
     *
     * @return string
     */
    public function validateActUid ($act_uid)
    {
        $act_uid = trim ($act_uid);
        if ( $act_uid == '' )
        {
            throw (new \Exception ("The activity with act_uid: '', does not exist."));
        }
        $oTask = new \Flow();
        $oTask->throwExceptionIfNotExistsTask ($act_uid);
        return $act_uid;
    }

    /**
     * Verify if doesn't exists the Task
     *
     * @param string $processUid            Unique id of Process
     * @param string $taskUid               Unique id of Task
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if doesn't exists the Task
     */
    public function throwExceptionIfNotExistsTask ($processUid, $taskUid)
    {
        try {
          
            $sql = "SELECT TAS_UID FROM workflow.task WHERE TAS_UID = ?";
            $arrParameters = array($taskUid);


            if ( $processUid != "" )
            {
                $sql .= " AND PRO_UID = ?";
                $arrParameters[] = $processUid;
            }

            $result = $this->objMsql->_query ($sql, $arrParameters);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                throw new \Exception ("ID_ACTIVITY_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
