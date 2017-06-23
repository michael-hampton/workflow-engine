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
    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

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

            $messageApplicationCorrelation = $arrayMessageEventDefinitionData[0]["MSGED_CORRELATION"];

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

    /**
     * Get all Message-Applications
     *
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Message-Applications
     */
    public function getMessageApplications ($arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {

            $this->getConnection ();

            $arrayMessageApplication = array();
            //Verify data
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayMessageApplication;
            }

            $arrayEventType = array("START", "INTERMEDIATE");
            $arrayEventMarker = array("MESSAGECATCH");

            //SQL
            $select = "SELECT ma.*, md.*, sm.step_condition, sm.order_id";
            $criteria = " FROM workflow.message_application ma 
                   INNER JOIN workflow.status_mapping sm ON sm.id = ma.EVN_UID_CATCH
                    INNER JOIN workflow.message_definition md ON md.EVT_UID = sm.id
                   WHERE 1=1
                    ";

            $arrParameters = [];

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["messageApplicationStatus"]) && trim ($arrayFilterData["messageApplicationStatus"]) != "" )
            {
                $criteria .= " AND MSGAPP_STATUS = ?";
                $arrParameters[] = $arrayFilterData["messageApplicationStatus"];
            }

            $criteria .= " GROUP BY  ma.`APP_UID`, ma.`PRJ_UID`";

            $fullQuery = $select . $criteria;

            $criteriaCount = "SELECT COUNT(*) AS NUM_REC " . $criteria;
            $countResult = $this->objMysql->_query ($criteriaCount, $arrParameters);

            $numRecTotal = $countResult[0]["NUM_REC"];
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = strtoupper ($sortField);
                if ( in_array ($sortField, array("MSGAPP_THROW_DATE", "MSGAPP_CATCH_DATE", "MSGAPP_STATUS")) )
                {
                    $sortField = $sortField;
                }
                else
                {
                    $sortField = 'MSGAPP_THROW_DATE';
                }
            }
            else
            {
                $sortField = 'MSGAPP_THROW_DATE';
            }
            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $criteria .= " ORDER BY " . $sortField . " ASC";
            }

            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
            }
            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
            }

            $results = $this->objMysql->_query ($fullQuery, $arrParameters);

            foreach ($results as $row) {
                $row["MSGAPP_VARIABLES"] = unserialize ($row["MSGAPP_VARIABLES"]);
                $row["MSGED_VARIABLES"] = unserialize ($row["MSGT_VARIABLES"]);
                $arrayMessageApplication[] = $row;
            }

            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($start)) ? $start : 0),
                "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                "filter" => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["messageApplicationStatus"])) ? $arrayFilterData["messageApplicationStatus"] : "",
                "data" => $arrayMessageApplication
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Catch Message-Events for the Cases
     *
     * @param bool $frontEnd Flag to represent progress bar
     *
     * @throws \Exception
     * @return void
     */
    public function catchMessageEvent ($frontEnd = false)
    {
        try {
            $case = new \Cases();

            //Get data
            $totalMessageEvent = 0;
            $counterStartMessageEvent = 0;
            $counterIntermediateCatchMessageEvent = 0;
            $counter = 0;
            $start = 0;
            $limit = 1000;
            $flagFirstTime = false;

            do {
                $flagNextRecords = false;
                $arrayMessageApplicationUnread = $this->getMessageApplications (array("messageApplicationStatus" => "UNREAD"), null, null, $start, $limit);

                if ( !$flagFirstTime )
                {
                    $totalMessageEvent = $arrayMessageApplicationUnread["total"];
                    $flagFirstTime = true;
                }
  
                foreach ($arrayMessageApplicationUnread["data"] as $value) {
                    $start++;
                    if ( $counter + 1 > $totalMessageEvent )
                    {
                        $flagNextRecords = false;
                        break;
                    }
                    $arrayMessageApplicationData = $value;
                    $processUid = $arrayMessageApplicationData["PRJ_UID"];
                    $taskUid = $arrayMessageApplicationData["EVN_UID_CATCH"];
                    $messageApplicationUid = $arrayMessageApplicationData["MSGAPP_UID"];
                    $messageApplicationCorrelation = $arrayMessageApplicationData["MSGAPP_CORRRELATION"];
//                    $messageEventDefinitionUserUid = $arrayMessageApplicationData["MSGED_USR_UID"];
                    $messageEventDefinitionCorrelation = $arrayMessageApplicationData["MSGED_CORRELATION"];
                    $arrayVariable = $this->mergeVariables ($arrayMessageApplicationData["MSGED_VARIABLES"], $arrayMessageApplicationData["MSGAPP_VARIABLES"]);
                    $flagCatched = false;

                    $eventCondition = json_decode ($arrayMessageApplicationData['step_condition'], true);

                    if ( isset ($eventCondition['receiveNotification']) )
                    {
                        $eventCondition["evn_type"] = "START";

                        switch ($eventCondition["evn_type"]) {
                            case "START":
                                if ( $messageEventDefinitionCorrelation == $messageApplicationCorrelation )
                                {

                                    $variables = [];

                                    foreach ($arrayMessageApplicationData['MSGAPP_VARIABLES']['MSGT_VARIABLES'] as $variable) {
                                        $variables[$variable['FIELD']] = $variable['VALUE'];
                                    }

                                    $variables['name'] = "NAME";
                                    $variables['description'] = "DESCRIPTION";

                                    //Start and derivate new Case
                                    $arrCase = $case->addCase ($arrayMessageApplicationData['workflow_id'], $_SESSION['user']['usrid'], array("form" => $variables));

                                    echo '<pre>';
                                    print_r($arrCase);
                                    
                                    $workflowData = $this->objMysql->_select ("workflow.workflow_data", [], ["object_id" => $arrCase['project_id']]);
                                    $workflowData = json_decode ($workflowData[0]['workflow_data'], true);
                                    
                                    if ( isset ($workflowData['elements'][$arrCase['case_id']]) )
                                    {
                                        $workflowData['elements'][$arrCase['case_id']]['current_step'] = $arrayMessageApplicationData['EVN_UID_CATCH'];
                                        $this->objMysql->_update("workflow.workflow_data", ["workflow_data" => json_encode($workflowData)], ["object_id" => $arrCase['project_id']]);
                                        die;
                                    }
                                }
                                break;

                            case "INTERMEDIATE":

                                break;
                        }
                        
                         $counter++;
                    }
                }
            }
            while ($flagNextRecords);
        } catch (Exception $ex) {
            
        }
    }

    /**
     * Merge and get variables
     *
     * @param array $arrayVariableName  Variables
     * @param array $arrayVariableValue Values
     *
     * return array Return an array
     */
    public function mergeVariables (array $arrayVariableName, array $arrayVariableValue)
    {
        try {

            $arrayVariable = array();
            foreach ($arrayVariableName['MSGT_VARIABLES'] as $key => $value) {
                foreach ($arrayVariableValue['MSGT_VARIABLES'] as $key2 => $value2) {
                    if ( $value2['MSGTV_NAME'] == $value['MSGTV_NAME'] )
                    {
                        $arrayVariable[$value['FIELD']] = $value2['VALUE'];
                    }
                }
            }

            //Return
            return $arrayVariable;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
