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
     * Assign a user or group to an activity
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $sTaskUID {@min 32} {@max 32}
     * @param string $sAssigneeUID {@min 32} {@max 32}
     * @param string $assType {@choice user,group}
     *
     * return array
     *
     * @access public
     */
    
public function addTaskAssignee($sProcessUID, $sTaskUID, $sAssigneeUID, $assType)
    {
        try {
            $this->proUid($sProcessUID);
            $this->validateActUid($sTaskUID);
            $iType = 1;
            $iRelation = '';
            
            $sql =_"SELECT TU_RELATION, USR_UID, TAS_UID, TU_TYPE FROM TASK_USER WHERE TAS_UID = ? AND USR_UID = ?";
            $arrParameters = array();
          

            $results= $this->objMysql->_query($sql, $arrParameters);
            foreach($results as $aRow){
                $iRelation = $aRow['TU_RELATION'];
            }
            $oTaskUser = $this->retrieveByPK( $sTaskUID, $sAssigneeUID, $iType, $iRelation );
            if (! is_null( $oTaskUser )) {
                throw new \Exception("ID_ALREADY_ASSIGNED");
            } else {
                $oTypeAssigneeG = \GroupwfPeer::retrieveByPK( $sAssigneeUID );
                $oTypeAssigneeU = \UsersPeer::retrieveByPK( $sAssigneeUID );
                if (is_null( $oTypeAssigneeG ) && is_null( $oTypeAssigneeU ) ) {
                    throw new \Exception("ID_DOES_NOT_CORRESPOND");
                }
                if (is_null( $oTypeAssigneeG ) && ! is_null( $oTypeAssigneeU) ) {
                    $type = "user";
                    if ( $type != $assType ) {
                        throw new \Exception(\G::LoadTranslation("ID_DOES_NOT_CORRESPOND", array($sAssigneeUID, $assType)));
                    }
                }
                if (! is_null( $oTypeAssigneeG ) && is_null( $oTypeAssigneeU ) ) {
                    $type = "group";
                    if ( $type != $assType ) {
                        throw new \Exception(\G::LoadTranslation("ID_DOES_NOT_CORRESPOND", array($sAssigneeUID, $assType)));
                    }
                }
                $oTaskUser = new \TaskUser();
                if ( $assType == "user" ) {
                    $oTaskUser->create(array('TAS_UID' => $sTaskUID,
                                             'USR_UID' => $sAssigneeUID,
                                             'TU_TYPE' => $iType,
                                             'TU_RELATION' => 1));
                } else {
                    $oTaskUser->create(array('TAS_UID' => $sTaskUID,
                                             'USR_UID' => $sAssigneeUID,
                                             'TU_TYPE' => $iType,
                                             'TU_RELATION' => 2));
                }
            }
        } catch ( \Exception $e ) {
            throw $e;
        }
    }

    /**
     * Return an assignee list of an activity
     *
     * @param string $processUid
     * @param string $taskUid
     * @param string $option
     * @param int    $taskUserType
     * @param array  $arrayFilterData
     * @param int    $start
     * @param int    $limit
     * @param string $type
     *
     * return array
     */
    public function getTaskAssignees ($processUid, $taskUid, $option, $taskUserType, $arrayFilterData = null, $start = null, $limit = null, $type = null)
    {
        try {
            $arrayAssignee = array();
            $numRecTotal = 0;
            $startbk = $start;
            $limitbk = $limit;
            //Verify data
            //Set variables
            $filterName = "filter";
            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) )
            {
                $arrayAux = array(
                    "" => "filter",
                    "LEFT" => "lfilter",
                    "RIGHT" => "rfilter"
                );
                $filterName = $arrayAux[(isset ($arrayFilterData["filterOption"])) ? $arrayFilterData["filterOption"] : ""];
            }
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                //Return
                return array(
                    "total" => $numRecTotal,
                    "start" => (int) ((!is_null ($startbk)) ? $startbk : 0),
                    "limit" => (int) ((!is_null ($limitbk)) ? $limitbk : 0),
                    $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                    "data" => $arrayAssignee
                );
            }
            //Verify data
            (new Process())->throwExceptionIfNotExistsProcess ($processUid);
            $this->throwExceptionIfNotExistsTask ($processUid, $taskUid, "act_uid");
            //Set variables
            $numRecTotalGroup = 0;
            $numRecTotalUser = 0;
            switch ($option) {
                case "ASSIGNEE":
                    break;
                case "AVAILABLE":
                    $task = new \Tasks();
                    $arrayGroupUid = array();
                    foreach ($task->getGroupsOfTask ($taskUid, $taskUserType) as $value) {
                        $arrayGroupUid[] = $value['GRP_UID'];
                    }
                    $arrayUserUid = array();
                    foreach ($task->getUsersOfTask ($taskUid, $taskUserType) as $value) {
                        $arrayUserUid[] = $value['USR_UID'];
                    }
                    break;
            }

            //Groups
            //Query
            if ( empty ($type) || $type == "group" )
            {
                $select = "SELECT permission, team_id, t.team_name  ";
                $sql = "FROM workflow.step_permission p
                        LEFT JOIN user_management.poms_users u ON u.team_id = p.permission AND p.permission_type = 'team'
                         LEFT JOIN user_management.teams t ON t.team_id = u.team_id
                         WHERE 1=1
   
                ";

                $sqlWhere = "";


                switch ($option) {
                    case "ASSIGNEE":

                        break;
                    case "AVAILABLE":
                        $sql .= " AND t.team_id NOT IN (" . implode (",", $arrayGroupUid) . ")";
                        break;
                }
                $sql .= " AND p.step_id = ? AND p.access_level = ?";
                $arrParameters = array($taskUid, $taskUserType);

                if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
                {
                    $arraySearch = array(
                        "" => "%" . $arrayFilterData["filter"] . "%",
                        "LEFT" => $arrayFilterData["filter"] . "%",
                        "RIGHT" => "%" . $arrayFilterData["filter"]
                    );
                    $search = isset ($arrayFilterData["filterOption"]) ? $arrayFilterData["filterOption"] : "";
                    $sql .= " AND t.team_name LIKE '%" . $search . "%'";
                }

                $sql .= " AND t.status = 1";

                $selectCount = "COUNT(team_id) AS NUM_REC";

                /* switch ($option) {
                  case "ASSIGNEE":
                  $rsCriteriaCount = \TaskUserPeer::doSelectRS ($criteriaCount);
                  break;
                  case "AVAILABLE":
                  $rsCriteriaCount = \GroupwfPeer::doSelectRS ($criteriaCount);
                  break;
                  } */

                $count = $selectCount . $sql;
                $countResult = $this->objMsql->_query ($count, $arrParameters);

                $numRecTotalGroup = (int) ($countResult[0]["NUM_REC"]);
                $numRecTotal = $numRecTotal + $numRecTotalGroup;
            }
            //Users
            //Query
            if ( empty ($type) || $type == "user" )
            {
                $sqlSelect = "SELECT u.usrid, u.username, u.firstName, u.lastName FROM workflow.step_permission";
                $sql = "LEFT JOIN user_management.poms_users u ON u.usrid = p.permission AND p.permission_type = 'user' ";

                switch ($option) {
                    case "ASSIGNEE":
                        $sql .= "   
                                    WHERE step_id = ? AND access_level = ?
                        ";

                        $arrParameters = array($taskUid, $taskUserType);

                        break;
                    case "AVAILABLE":
                        $sql .= " AND u.usrid NOT IN (" . implode (",", $arrayUserUid) . ")";
                        break;
                }
                if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
                {
                    $search = isset ($arrayFilterData["filterOption"]) ? $arrayFilterData["filterOption"] : "";

                    $sql .= " AND (u.username LIKE '%" . $search . "%' OR u.firstName LIKE '%" . $search . "%' OR lastName LIKE '%" . $search . "%')";
                }

                $sql .= "AND u.status = 1";
                //Number records total
                $sqlCount = "SELECT COUNT(u,usrid) AS NUM_REC ";
                $sqlCount .= $sql;


                /* switch ($option) {
                  case "ASSIGNEE":
                  $rsCriteriaCount = \TaskUserPeer::doSelectRS ($criteriaCount);
                  break;
                  case "AVAILABLE":
                  $rsCriteriaCount = \UsersPeer::doSelectRS ($criteriaCount);
                  break;
                  } */

                $countResult = $this->objMsql->_query ($sql, $arrParameters);

                $numRecTotalUser = (int) ($countResult[0]["NUM_REC"]);
                $numRecTotal = $numRecTotal + $numRecTotalUser;
            }
            //Groups
            //Query
            if ( empty ($type) || $type == "group" )
            {
                $sql .= " ORDER BY t.team_name ASC";

                if ( !is_null ($limit) )
                {
                   $sql .= " LIMIT ". (int) $limit;
                }

                if ( !is_null ($start) )
                {
                    $sql .= " OFFSET ". (int) $start;
                }

                /*switch ($option) {
                    case "ASSIGNEE":
                        $rsCriteriaGroup = \TaskUserPeer::doSelectRS ($criteriaGroup);
                        break;
                    case "AVAILABLE":
                        $rsCriteriaGroup = \GroupwfPeer::doSelectRS ($criteriaGroup);
                        break;
                }*/
                
                $results = $this->objMsql->_query($sql, $arrParameters);
     
                $numRecGroup = 0;
                foreach($results as $row){
                    
                    
                    $sql2 = "SELECT COUNT(team_id) AS NUM_REM FROM user_management.teams WHERE team_id = ".$row["team_id"];
                    
                    
                    $result2 = $this->objMysql->_query($sql2);
                    
                    $row2 = $result2[0];
                    $row["GRP_TITLE"] = $row["GRP_TITLE"] . " (" . $row2["NUM_MEM"] . " " . \G::LoadTranslation (((int) ($row2["NUM_MEM"]) == 1) ? "ID_USER" : "ID_USERS") . ")";
                    $arrayAssignee[] = $this->getTaskAssigneeDataFromRecord (
                            array(
                        $row["team_id"],
                        $row["team_name"],
                        "",
                        $row["team_name"],
                        "group"
                            ), $taskUserType
                    );
                    $numRecGroup++;
                }
            }
            //Users
            //Query
            if ( empty ($type) || $type == "user" )
            {
                $flagUser = true;
                if ( $numRecTotalGroup > 0 )
                {
                    if ( $numRecGroup > 0 )
                    {
                        if ( !is_null ($limit) )
                        {
                            if ( $numRecGroup < (int) ($limit) )
                            {
                                $start = 0;
                                $limit = $limit - $numRecGroup;
                            }
                            else
                            {
                                $flagUser = false;
                            }
                        }
                        else
                        {
                            $start = 0;
                        }
                    }
                    else
                    {
                        $start = (int) ($start) - $numRecTotalGroup;
                    }
                }
                if ( $flagUser )
                {
                    //Users
                    //Query
                    $sql .= " ORDER BY u.firstName ASC";
                    
                    if ( !is_null ($limit) )
                    {
                        $sql .= " LIMIT " . (int) $limit;
                    }
                    
                    if ( !is_null ($start) )
                    {
                        $sql .= " OFFSET " . (int) $start;
                    }
                    
                    /*switch ($option) {
                        case "ASSIGNEE":
                            $rsCriteriaUser = \TaskUserPeer::doSelectRS ($criteriaUser);
                            break;
                        case "AVAILABLE":
                            $rsCriteriaUser = \UsersPeer::doSelectRS ($criteriaUser);
                            break;
                    }*/
                    $results = $this->objMysql->_query($sql, $arrParameters);
                    foreach($results as $row){
                        
                        $arrayAssignee[] = $this->getTaskAssigneeDataFromRecord (
                                array(
                            $row["usrid"],
                            $row["firstName"],
                            $row["lastName"],
                            $row["username"],
                            "user"
                                ), $taskUserType
                        );
                    }
                }
            }
            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($startbk)) ? $startbk : 0),
                "limit" => (int) ((!is_null ($limitbk)) ? $limitbk : 0),
                $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                "data" => $arrayAssignee
            );
        } catch (\Exception $e) {
            throw $e;
        }
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
            if ( isset ($arrayProperty["TAS_SEND_LAST_EMAIL"]) && $arrayProperty["TAS_SEND_LAST_EMAIL"] == "TRUE" )
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
        $this->throwExceptionIfNotExistsTask (null, $act_uid);
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
    public function throwExceptionIfNotExistsTask ($processUid = null, $taskUid)
    {
        try {

            $sql = "SELECT TAS_UID FROM workflow.task WHERE TAS_UID = ?";
            $arrParameters = array($taskUid);


            if ( $processUid != null )
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
