<?php

namespace BusinessModel;

class MessageEventDefinition
{

    use Validator;

    /**
     * Constructor of the class
     *
     * return void
     */
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the Message-Event-Definition
     *
     * @param string $messageEventDefinitionUid Unique id of Message-Event-Definition
     *
     * return bool Return true if exists the Message-Event-Definition, false otherwise
     */
    public function exists ($messageEventDefinitionUid)
    {
        try {
            $obj = $this->objMysql->_select ("workflow.message_definition", [], ["id" => $messageEventDefinitionUid]);
            return (isset ($obj[0]) && !empty ($obj[0])) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Event of a Message-Event-Definition
     *
     * @param string $projectUid                         Unique id of Project
     * @param string $eventUid                           Unique id of Event
     * @param string $messageEventDefinitionUidToExclude Unique id of Message-Event-Definition to exclude
     *
     * return bool Return true if exists the Event of a Message-Event-Definition, false otherwise
     */
    public function existsEvent ($projectUid, $eventUid, $messageEventDefinitionUidToExclude = "")
    {
        try {

            $arrParameters = [];

            $sql = "SELECT * FROM workflow.message_definition md INNER JOIN workflow.status_mapping e ON e.id = md.EVT_UID WHERE md.EVT_UID = ?";
            $arrParameters[] = $eventUid;
            if ( $messageEventDefinitionUidToExclude != "" )
            {
                $sql .= " AND md.id != ?";
                $arrParameters[] = $messageEventDefinitionUidToExclude;
            }

            $result = $this->objMysql->_query ($sql, $arrParameters);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the Message-Event-Definition
     *
     * @param string $messageEventDefinitionUid Unique id of Message-Event-Definition
     * @param string $fieldNameForException     Field name for the exception
     *
     * return void Throw exception if does not exists the Message-Event-Definition
     */
    public function throwExceptionIfNotExistsMessageEventDefinition ($messageEventDefinitionUid)
    {
        try {
            if ( !$this->exists ($messageEventDefinitionUid) )
            {
                throw new \Exception ("ID_MESSAGE_EVENT_DEFINITION_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if is registered the Event
     *
     * @param string $projectUid                         Unique id of Project
     * @param string $eventUid                           Unique id of Event
     * @param string $fieldNameForException              Field name for the exception
     * @param string $messageEventDefinitionUidToExclude Unique id of Message-Event-Definition to exclude
     *
     * return void Throw exception if is registered the Event
     */
    public function throwExceptionIfEventIsRegistered ($projectUid, $eventUid, $messageEventDefinitionUidToExclude = "")
    {
        try {
            if ( $this->existsEvent ($projectUid, $eventUid, $messageEventDefinitionUidToExclude) )
            {
                throw new \Exception ("ID_MESSAGE_EVENT_DEFINITION_ALREADY_REGISTERED");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $messageEventDefinitionUid Unique id of Message-Event-Definition
     * @param string $projectUid                Unique id of Project
     * @param array  $arrayData                 Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($messageEventDefinitionUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayMessageEventDefinitionData = ($messageEventDefinitionUid == "") ? array() : $this->getMessageEventDefinition ($messageEventDefinitionUid, true);
            $flagInsert = ($messageEventDefinitionUid == "") ? true : false;
            $arrayFinalData = array_merge ($arrayMessageEventDefinitionData, $arrayData);
            //Verify data - Field definition
            //$process = new \ProcessMaker\BusinessModel\Process();
            $messageType = new \BusinessModel\MessageType();
            //$process->throwExceptionIfDataNotMetFieldDefinition($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            //Verify data
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $this->throwExceptionIfEventIsRegistered ($projectUid, $arrayData["EVN_UID"], $messageEventDefinitionUid);
            }
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $eventType = "START";

                $arrayEventType = array("START", "END", "INTERMEDIATE");
                $arrayEventMarker = array("sendNotification", "receiveNotification");
                $bpmnEvent = (new \BusinessModel\Event())->getEvent ($arrayData["EVN_UID"]);

                if ( !isset ($bpmnEvent[0]) || empty ($bpmnEvent[0]) )
                {
                    throw new \Exception ("ID_EVENT_NOT_EXIST");
                }

               

                if ( $arrayData['messageType'] == "send" )
                {
                    $eventMarker = "sendNotification";
                }
                else
                {
                    $eventMarker = "receiveNotification";
                }


                if ( !in_array ($eventType, $arrayEventType) || !in_array ($eventMarker, $arrayEventMarker) )
                {
                    throw new \Exception ("ID_EVENT_NOT_IS_MESSAGE_EVENT");
                }
            }
            if ( isset ($arrayData["MSGT_UID"]) && $arrayData["MSGT_UID"] . "" != "" )
            {
                $messageType->throwExceptionIfNotExistsMessageType ($arrayData["MSGT_UID"]);
            }

            $flagCheckData = false;
            $flagCheckData = (isset ($arrayData["MSGT_UID"]) && $arrayData["MSGT_UID"] . "" != "") ? true : $flagCheckData;
            $flagCheckData = (isset ($arrayData["MSGT_VARIABLES"])) ? true : $flagCheckData;

            if ( isset ($arrayFinalData["MSGT_UID"]) && $arrayFinalData["MSGT_UID"] . "" != "" && $flagCheckData )
            {
                $arrayMessageTypeData = $messageType->getMessageType ($arrayFinalData["MSGT_UID"], true);

                $newVariables = [];
                $count = 0;

                foreach ($arrayFinalData['MSGT_VARIABLES']['MSGTV_NAME'] as $key => $value) {
                    $newVariables['MSGT_VARIABLES'][$key]['MSGTV_NAME'] = $value;
                    $newVariables['MSGT_VARIABLES'][$key]['FIELD'] = $arrayFinalData['MSGT_VARIABLES']['FIELD'][$key];
                    $count++;
                }
                $checkVariables = [];
                $count2 = 0;

                foreach ($arrayMessageTypeData[0]["MSGT_VARIABLES"] as $value) {
                    $checkVariables['MSGT_VARIABLES'][$count2]['MSGTV_NAME'] = $value["MSGTV_NAME"];
                    $checkVariables['MSGT_VARIABLES'][$count2]['FIELD'] = $value["MSGTV_DEFAULT_VALUE"];
                    $count2++;
                }

                unset ($arrayFinalData['MSGT_VARIABLES']);
                $arrayFinalData['MSGT_VARIABLES'] = $newVariables;

                if ( $count2 != $count || count (array_diff_key ($checkVariables, $arrayFinalData["MSGT_VARIABLES"])) > 0 )
                {
                    throw new \Exception ("ID_MESSAGE_EVENT_DEFINITION_VARIABLES_DO_NOT_MEET_DEFINITION");
                }

                return $arrayFinalData;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Message-Event-Definition
     *
     * @param string $messageEventDefinitionUid Unique id of Message-Event-Definition
     * @param array  $arrayData                 Data
     *
     * return array Return data of the Message-Event-Definition updated
     */
    public function update ($messageEventDefinitionUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            $arrayDataBackup = $arrayData;
            unset ($arrayData["MSGED_UID"]);
            unset ($arrayData["workflow_id"]);
            //Set variables
            $arrayMessageEventDefinitionData = $this->getMessageEventDefinition ($messageEventDefinitionUid, true);

            //Verify data
            $this->throwExceptionIfNotExistsMessageEventDefinition ($messageEventDefinitionUid);
            $arrayData = $this->throwExceptionIfDataIsInvalid ($messageEventDefinitionUid, $arrayMessageEventDefinitionData[0]["workflow_id"], $arrayData);

            //Update
            try {
                $messageEventDefinition = new \MessageDefinition();
                if ( isset ($arrayData["MSGT_UID"]) && $arrayData["MSGT_UID"] . "" == "" )
                {
                    $arrayData["MSGED_VARIABLES"] = array();
                }

                $messageEventDefinition->setMsgedUid ($messageEventDefinitionUid);
                $messageEventDefinition->setPrjUid ($arrayMessageEventDefinitionData[0]["workflow_id"]);
                $messageEventDefinition->setMsgedUsrUid ($_SESSION['user']['usrid']); //admin
                $messageEventDefinition->setEvnUid ($arrayData['EVN_UID']);
                $messageEventDefinition->setMsgedCorrelation ($arrayData['MSGED_CORRELATION']);
                $messageEventDefinition->setMsgtUid ($arrayData['MSGT_UID']);
                $messageEventDefinition->setMsgedVariables ($arrayData["MSGT_VARIABLES"]);
                $messageEventDefinition->setMessageType ($arrayData['messageType']);
                $messageEventDefinition->setMsgedCorrelation ($arrayData['MSGED_CORRELATION']);

                if ( $messageEventDefinition->validate () )
                {
                    $messageEventDefinition->save ();
                    //Return
                    $arrayData = $arrayDataBackup;

                    return $arrayData;
                }
                else
                {
                    $msg = "";
                    foreach ($messageEventDefinition->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
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
     * Create Message-Event-Definition for a Project
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Message-Event-Definition created
     */
    public function create ($projectUid, array $arrayData, $flagValidateArrayData = true)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            unset ($arrayData["MSGED_UID"]);
            unset ($arrayData["PRJ_UID"]);
            //Verify data
            //$process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            if ( $flagValidateArrayData )
            {
                $arrayData = $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);
            }
            //Create
            try {
                $messageEventDefinition = new \MessageDefinition();
                if ( !isset ($arrayData["MSGT_UID"]) || $arrayData["MSGT_UID"] . "" == "" )
                {
                    $arrayData["MSGT_UID"] = "";
                    $arrayData["MSGED_VARIABLES"] = array();
                }
                if ( !isset ($arrayData["MSGED_VARIABLES"]) )
                {
                    $arrayData["MSGED_VARIABLES"] = array();
                }

                if ( isset ($arrayData["MSGT_VARIABLES"]) )
                {
                    $arrayData["MSGT_VARIABLES"] = serialize ($arrayData["MSGT_VARIABLES"]);
                }

                //$messageEventDefinition->setMsgedUid ($messageEventDefinitionUid);
                $messageEventDefinition->setPrjUid ($projectUid);
                $messageEventDefinition->setMessageType ($arrayData['messageType']);
                $messageEventDefinition->setMsgedUsrUid ($_SESSION['user']['usrid']); //admin
                $messageEventDefinition->setEvnUid ($arrayData['EVN_UID']);
                $messageEventDefinition->setMsgedCorrelation ($arrayData['MSGED_CORRELATION']);
                $messageEventDefinition->setMsgtUid ($arrayData['MSGT_UID']);
                $messageEventDefinition->setMsgedVariables ($arrayData["MSGT_VARIABLES"]);
                $messageEventDefinition->setEvnUid ($arrayData['EVN_UID']);
                $messageEventDefinition->setMsgedCorrelation ($arrayData['MSGED_CORRELATION']);

                if ( $messageEventDefinition->validate () )
                {

                    $messageEventDefinitionUid = $messageEventDefinition->save ();
                    //Return
                    return $this->getMessageEventDefinition ($messageEventDefinitionUid);
                }
                else
                {
                    $msg = "";
                    foreach ($messageEventDefinition->getValidationFailures () as $message) {
                        $msg = $msg . ($msg != "" ? "\n" : "") . $message;
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
     * Set Message-Event-Definition-Variables by Message-Type for a record
     *
     * @param array $record Record
     *
     * return array Return the record
     */
    public function setMessageEventDefinitionVariablesForRecordByMessageType (array $record)
    {
        try {
            $record["MSGT_VARIABLES"] = ($record[0]["MSGT_VARIABLES"] . "" != "") ? unserialize ($record[0]["MSGT_VARIABLES"]) : array();

            return $record;
            //Return
            return $record;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Message-Event-Definitions
     *
     * @param string $projectUid Unique id of Project
     *
     * return array Return an array with all Message-Event-Definitions
     */
    public function getMessageEventDefinitions ($projectUid)
    {
        try {
            $arrayMessageEventDefinition = array();
            //Verify data
            $process = new \ProcessMaker\BusinessModel\Process();
            $process->throwExceptionIfNotExistsProcess ($projectUid, $this->arrayFieldNameForException["projectUid"]);
            //Get data
            $results = $this->objMysql->_query ("SELECT * FROM workflow.message_definition WHERE workflow_id = ?", [$projectUid]);

            foreach ($results as $result) {
                $row = $this->setMessageEventDefinitionVariablesForRecordByMessageType ($result);
                $arrayMessageEventDefinition[] = $this->getMessageEventDefinitionDataFromRecord ($row);
            }
            //Return
            return $arrayMessageEventDefinition;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Message-Event-Definition
     *
     * @param string $messageEventDefinitionUid Unique id of Message-Event-Definition
     * @param bool   $flagGetRecord             Value that set the getting
     *
     * return array Return an array with data of a Message-Event-Definition
     */
    public function getMessageEventDefinition ($messageEventDefinitionUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsMessageEventDefinition ($messageEventDefinitionUid);
            //Get data
            $result = $this->objMysql->_select ("workflow.message_definition", [], ["id" => $messageEventDefinitionUid]);

            $row = $this->setMessageEventDefinitionVariablesForRecordByMessageType ($result);
            //Return
            return $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Message-Event-Definition by unique id of Event
     *
     * @param string $projectUid    Unique id of Project
     * @param string $eventUid      Unique id of Event
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Message-Event-Definition by unique id of Event
     */
    public function getMessageEventDefinitionByEvent ($projectUid, $eventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            //if ( !$this->existsEvent ($projectUid, $eventUid) )
            //{
            //throw new Exception ("ID_MESSAGE_EVENT_DEFINITION_DOES_NOT_IS_REGISTERED");
            //}
            //Get data
            $results = $this->objMysql->_select ("workflow.message_definition", [], ["EVT_UID" => $eventUid]);

            //Return
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
