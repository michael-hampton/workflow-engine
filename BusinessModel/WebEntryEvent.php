<?php

namespace BusinessModel;

class WebEntryEvent
{

    use Validator;

    private $webEntryEventWebEntryUid = "";
    private $webEntryEventWebEntryTaskUid = "";
    private $webEntry;
    private $objMysql;

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the WebEntry-Event
     *
     * @param string $webEntryEventUid Unique id of WebEntry-Event
     *
     * return bool Return true if exists the WebEntry-Event, false otherwise
     */
    public function exists ($webEntryEventUid)
    {
        try {
            $obj = \WebEntryEventPeer::retrieveByPK ($webEntryEventUid);

            return (!is_null ($obj)) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Event of a WebEntry-Event
     *
     * @param string $projectUid                Unique id of Project
     * @param string $eventUid                  Unique id of Event
     * @param string $webEntryEventUidToExclude Unique id of WebEntry-Event to exclude
     *
     * return bool Return true if exists the Event of a WebEntry-Event, false otherwise
     */
    public function existsEvent ($projectUid, $eventUid, $webEntryEventUidToExclude = "")
    {
        try {
            $criteria = new \Criteria ("workflow");

            $sql = "SELECT WEE_UID FROM web_entry WHERE PRJ_UID = ?";
            $arrParameters = array($projectUid);


            if ( $webEntryEventUidToExclude != "" )
            {
                $sql .= " AND WEE_UID != ?";
                $arrParameters[] = $webEntryEventUidToExclude;
            }

            $sql .= " AND EVN_UID = ?";
            $arrParameters[] = $eventUid;


            $result = $this->objMysql->_query ($sql, $arrParameters);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a WebEntry-Event
     *
     * @param string $projectUid                Unique id of Project
     * @param string $webEntryEventTitle        Title
     * @param string $webEntryEventUidToExclude Unique id of WebEntry-Event to exclude
     *
     * return bool Return true if exists the title of a WebEntry-Event, false otherwise
     */
    public function existsTitle ($projectUid, $webEntryEventTitle, $webEntryEventUidToExclude = "")
    {
        try {
            $sql = "SELECT * FROM web_entry WHERE PRJ_UID = ?";
            $arrParameters = array($projectUid);


            if ( $webEntryEventUidToExclude != "" )
            {
                $sql .= " AND WEE_UID != ?";
                $arrParameters[] = $webEntryEventUidToExclude;
            }

            $result = $this->objMysql->_query ($sql, $arrParameters);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the WebEntry-Event
     *
     * @param string $webEntryEventUid      Unique id of WebEntry-Event
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exists the WebEntry-Event
     */
    public function throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid, $fieldNameForException)
    {
        try {
            if ( !$this->exists ($webEntryEventUid) )
            {
                throw new \Exception ("ID_WEB_ENTRY_EVENT_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if is registered the Event
     *
     * @param string $projectUid                Unique id of Project
     * @param string $eventUid                  Unique id of Event
     * @param string $fieldNameForException     Field name for the exception
     * @param string $webEntryEventUidToExclude Unique id of WebEntry-Event to exclude
     *
     * return void Throw exception if is registered the Event
     */
    public function throwExceptionIfEventIsRegistered ($projectUid, $eventUid, $fieldNameForException, $webEntryEventUidToExclude = "")
    {
        try {
            if ( $this->existsEvent ($projectUid, $eventUid, $webEntryEventUidToExclude) )
            {
                throw new \Exception ("ID_WEB_ENTRY_EVENT_ALREADY_REGISTERED");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a WebEntry-Event
     *
     * @param string $projectUid                Unique id of Project
     * @param string $webEntryEventTitle        Title
     * @param string $fieldNameForException     Field name for the exception
     * @param string $webEntryEventUidToExclude Unique id of WebEntry-Event to exclude
     *
     * return void Throw exception if exists the title of a WebEntry-Event
     */
    public function throwExceptionIfExistsTitle ($projectUid, $webEntryEventTitle, $fieldNameForException, $webEntryEventUidToExclude = "")
    {
        try {
            if ( $this->existsTitle ($projectUid, $webEntryEventTitle, $webEntryEventUidToExclude) )
            {
                throw new \Exception ("ID_WEB_ENTRY_EVENT_TITLE_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $webEntryEventUid Unique id of WebEntry-Event
     * @param string $projectUid       Unique id of Project
     * @param array  $arrayData        Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($webEntryEventUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayWebEntryEventData = ($webEntryEventUid == "") ? array() : $this->getWebEntryEvent ($webEntryEventUid, true);
            $flagInsert = ($webEntryEventUid == "") ? true : false;

            $arrayFinalData = array_merge ($arrayWebEntryEventData, $arrayData);


            //Verify data
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $this->throwExceptionIfEventIsRegistered ($projectUid, $arrayData["EVN_UID"], $this->arrayFieldNameForException["eventUid"], $webEntryEventUid);
            }

            if ( isset ($arrayData["EVN_UID"]) )
            {
                $obj = \BpmnEventPeer::retrieveByPK ($arrayData["EVN_UID"]);

                if ( is_null ($obj) )
                {
                    throw new \Exception ("ID_EVENT_NOT_EXIST");
                }

                if ( ($obj->getEvnType () != "START") || ($obj->getEvnType () == "START" && $obj->getEvnMarker () != "EMPTY") )
                {
                    throw new \Exception ("ID_EVENT_NOT_IS_START_EVENT");
                }
            }

            if ( isset ($arrayData["WEE_TITLE"]) )
            {
                $this->throwExceptionIfExistsTitle ($projectUid, $arrayData["WEE_TITLE"], $webEntryEventUid);
            }

            if ( isset ($arrayData["ACT_UID"]) )
            {
                $bpmn = new \ProcessMaker\Project\Bpmn();

                if ( !$bpmn->activityExists ($arrayData["ACT_UID"]) )
                {
                    throw new \Exception ("ID_ACTIVITY_DOES_NOT_EXIST");
                }
            }

            if ( isset ($arrayData["EVN_UID"]) || isset ($arrayData["ACT_UID"]) )
            {
                $criteria = new \Criteria ("workflow");

                $criteria->addSelectColumn (\BpmnFlowPeer::FLO_UID);
                $criteria->add (\BpmnFlowPeer::FLO_ELEMENT_ORIGIN, $arrayFinalData["EVN_UID"], \Criteria::EQUAL);
                $criteria->add (\BpmnFlowPeer::FLO_ELEMENT_ORIGIN_TYPE, "bpmnEvent", \Criteria::EQUAL);
                $criteria->add (\BpmnFlowPeer::FLO_ELEMENT_DEST, $arrayFinalData["ACT_UID"], \Criteria::EQUAL);
                $criteria->add (\BpmnFlowPeer::FLO_ELEMENT_DEST_TYPE, "bpmnActivity", \Criteria::EQUAL);

                $rsCriteria = \BpmnFlowPeer::doSelectRS ($criteria);

                if ( !$rsCriteria->next () )
                {
                    throw new \Exception (\G::LoadTranslation ("ID_WEB_ENTRY_EVENT_FLOW_EVENT_TO_ACTIVITY_DOES_NOT_EXIST"));
                }
            }

            if ( isset ($arrayData["DYN_UID"]) )
            {
                $dynaForm = new \ProcessMaker\BusinessModel\DynaForm();

                $dynaForm->throwExceptionIfNotExistsDynaForm ($arrayData["DYN_UID"], $projectUid, $this->arrayFieldNameForException["dynaFormUid"]);
            }

            if ( isset ($arrayData["USR_UID"]) )
            {
                $process->throwExceptionIfNotExistsUser ($arrayData["USR_UID"], $this->arrayFieldNameForException["userUid"]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create WebEntry
     *
     * @param string $projectUid     Unique id of Project
     * @param string $eventUid       Unique id of Event
     * @param string $activityUid    Unique id of Activity
     * @param string $dynaFormUid    WebEntry, unique id of DynaForm
     * @param string $userUid        WebEntry, unique id of User
     * @param string $title          WebEntry, title
     * @param string $description    WebEntry, description
     * @param string $userUidCreator WebEntry, unique id of creator User
     *
     * return void
     */
    public function createWebEntry ($projectUid, $eventUid, $activityUid, $dynaFormUid, $userUid, $title, $description, $userUidCreator)
    {
        try {
            $bpmn = new \ProcessMaker\Project\Bpmn();

            $arrayEventData = $bpmn->getEvent ($eventUid);

            //Task
            $task = new \Task();

            $prefix = "wee-";

            $this->webEntryEventWebEntryTaskUid = $task->create (
                    array(
                "TAS_UID" => $prefix . substr (\ProcessMaker\Util\Common::generateUID (), (32 - strlen ($prefix)) * -1),
                "PRO_UID" => $projectUid,
                "TAS_TYPE" => "WEBENTRYEVENT",
                "TAS_TITLE" => "WEBENTRYEVENT",
                "TAS_START" => "TRUE",
                "TAS_POSX" => (int) ($arrayEventData["BOU_X"]),
                "TAS_POSY" => (int) ($arrayEventData["BOU_Y"])
                    ), false
            );

            //Task - Step
            $step = new \Step();

            $stepUid = $step->create (array("PRO_UID" => $projectUid, "TAS_UID" => $this->webEntryEventWebEntryTaskUid));
            $result = $step->update (array("STEP_UID" => $stepUid, "STEP_TYPE_OBJ" => "DYNAFORM", "STEP_UID_OBJ" => $dynaFormUid, "STEP_POSITION" => 1, "STEP_MODE" => "EDIT"));

            //Task - User
            $task = new \Tasks();

            $result = $task->assignUser ($this->webEntryEventWebEntryTaskUid, $userUid, 1);

            //Route
            $workflow = \ProcessMaker\Project\Workflow::load ($projectUid);

            $result = $workflow->addRoute ($this->webEntryEventWebEntryTaskUid, $activityUid, "SEQUENTIAL");

            //WebEntry
            $arrayWebEntryData = $this->webEntry->create (
                    $projectUid, $userUidCreator, array(
                "TAS_UID" => $this->webEntryEventWebEntryTaskUid,
                "DYN_UID" => $dynaFormUid,
                "USR_UID" => $userUid,
                "WE_TITLE" => $title,
                "WE_DESCRIPTION" => $description,
                "WE_METHOD" => "WS",
                "WE_INPUT_DOCUMENT_ACCESS" => 1
                    )
            );

            $this->webEntryEventWebEntryUid = $arrayWebEntryData[$this->getFieldNameByFormatFieldName ("WE_UID")];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete WebEntry
     *
     * @param string $webEntryUid     Unique id of WebEntry
     * @param string $webEntryTaskUid WebEntry, unique id of Task
     *
     * return void
     */
    public function deleteWebEntry ($webEntryUid, $webEntryTaskUid)
    {
        try {
            if ( $webEntryTaskUid != "" )
            {
                $obj = \TaskPeer::retrieveByPK ($webEntryTaskUid);

                if ( !is_null ($obj) )
                {
                    $task = new \Tasks();

                    $task->deleteTask ($webEntryTaskUid);
                }
            }

            if ( $webEntryUid != "" )
            {
                $obj = \WebEntryPeer::retrieveByPK ($webEntryUid);

                if ( !is_null ($obj) )
                {
                    $this->webEntry->delete ($webEntryUid);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create WebEntry-Event for a Project
     *
     * @param string $projectUid     Unique id of Project
     * @param string $userUidCreator Unique id of creator User
     * @param array  $arrayData      Data
     *
     * return array Return data of the new WebEntry-Event created
     */
    public function create ($projectUid, $userUidCreator, array $arrayData)
    {
        try {
            //Verify data
            $process = new \ProcessMaker\BusinessModel\Process();
            $validator = new \ProcessMaker\BusinessModel\Validator();

            $validator->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $validator->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Set data
            $arrayData = array_change_key_case ($arrayData, CASE_UPPER);

            unset ($arrayData["WEE_UID"]);
            unset ($arrayData["PRJ_UID"]);
            unset ($arrayData["WEE_WE_UID"]);
            unset ($arrayData["WEE_WE_TAS_UID"]);

            if ( !isset ($arrayData["WEE_DESCRIPTION"]) )
            {
                $arrayData["WEE_DESCRIPTION"] = "";
            }

            if ( !isset ($arrayData["WEE_STATUS"]) )
            {
                $arrayData["WEE_STATUS"] = "ENABLED";
            }

            //Verify data
            $process->throwExceptionIfNotExistsProcess ($projectUid, $this->arrayFieldNameForException["projectUid"]);

            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);

            //Create

            $this->webEntryEventWebEntryUid = "";
            $this->webEntryEventWebEntryTaskUid = "";

            try {
                //WebEntry
                if ( $arrayData["WEE_STATUS"] == "ENABLED" )
                {
                    $this->createWebEntry (
                            $projectUid, $arrayData["EVN_UID"], $arrayData["ACT_UID"], $arrayData["DYN_UID"], $arrayData["USR_UID"], $arrayData["WEE_TITLE"], $arrayData["WEE_DESCRIPTION"], $userUidCreator
                    );
                }

                //WebEntry-Event
                $webEntryEvent = new \WebEntryEvent();

                $webEntryEvent->fromArray ($arrayData, \BasePeer::TYPE_FIELDNAME);

                $webEntryEventUid = \ProcessMaker\Util\Common::generateUID ();

                $webEntryEvent->setWeeUid ($webEntryEventUid);
                $webEntryEvent->setPrjUid ($projectUid);
                $webEntryEvent->setWeeWeUid ($this->webEntryEventWebEntryUid);
                $webEntryEvent->setWeeWeTasUid ($this->webEntryEventWebEntryTaskUid);

                if ( $webEntryEvent->validate () )
                {
                    $cnn->begin ();

                    $result = $webEntryEvent->save ();

                    $cnn->commit ();

                    //Set WEE_TITLE
                    if ( isset ($arrayData["WEE_TITLE"]) )
                    {
                        $result = \Content::addContent ("WEE_TITLE", "", $webEntryEventUid, SYS_LANG, $arrayData["WEE_TITLE"]);
                    }

                    //Set WEE_DESCRIPTION
                    if ( isset ($arrayData["WEE_DESCRIPTION"]) )
                    {
                        $result = \Content::addContent ("WEE_DESCRIPTION", "", $webEntryEventUid, SYS_LANG, $arrayData["WEE_DESCRIPTION"]);
                    }

                    //Return
                    return $this->getWebEntryEvent ($webEntryEventUid);
                }
                else
                {
                    $msg = "";

                    foreach ($webEntryEvent->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure->getMessage ();
                    }

                    throw new \Exception (\G::LoadTranslation ("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "") ? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                $cnn->rollback ();

                $this->deleteWebEntry ($this->webEntryEventWebEntryUid, $this->webEntryEventWebEntryTaskUid);

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update WebEntry-Event
     *
     * @param string $webEntryEventUid Unique id of WebEntry-Event
     * @param string $userUidUpdater   Unique id of updater User
     * @param array  $arrayData        Data
     *
     * return array Return data of the WebEntry-Event updated
     */
    public function update ($webEntryEventUid, $userUidUpdater, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Set data
            $arrayDataBackup = $arrayData;

            unset ($arrayData["WEE_UID"]);
            unset ($arrayData["PRJ_UID"]);
            unset ($arrayData["WEE_WE_UID"]);
            unset ($arrayData["WEE_WE_TAS_UID"]);

            //Set variables
            $arrayWebEntryEventData = $this->getWebEntryEvent ($webEntryEventUid, true);

            $arrayFinalData = array_merge ($arrayWebEntryEventData, $arrayData);

            //Verify data
            $this->throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid, $this->arrayFieldNameForException["webEntryEventUid"]);

            $this->throwExceptionIfDataIsInvalid ($webEntryEventUid, $arrayWebEntryEventData["PRJ_UID"], $arrayData);

            //Update

            $this->webEntryEventWebEntryUid = "";
            $this->webEntryEventWebEntryTaskUid = "";

            try {
                //WebEntry
                $option = "UPDATE";

                if ( isset ($arrayData["WEE_STATUS"]) )
                {
                    if ( $arrayData["WEE_STATUS"] == "ENABLED" )
                    {
                        if ( $arrayWebEntryEventData["WEE_STATUS"] == "DISABLED" )
                        {
                            $option = "INSERT";
                        }
                    }
                    else
                    {
                        if ( $arrayWebEntryEventData["WEE_STATUS"] == "ENABLED" )
                        {
                            $option = "DELETE";
                        }
                    }
                }

                switch ($option) {
                    case "INSERT":
                        $this->createWebEntry (
                                $arrayFinalData["PRJ_UID"], $arrayFinalData["EVN_UID"], $arrayFinalData["ACT_UID"], $arrayFinalData["DYN_UID"], $arrayFinalData["USR_UID"], $arrayFinalData["WEE_TITLE"], $arrayFinalData["WEE_DESCRIPTION"], $userUidUpdater
                        );

                        $arrayData["WEE_WE_UID"] = $this->webEntryEventWebEntryUid;
                        $arrayData["WEE_WE_TAS_UID"] = $this->webEntryEventWebEntryTaskUid;
                        break;
                    case "UPDATE":
                        if ( $arrayWebEntryEventData["WEE_WE_UID"] != "" )
                        {
                            $task = new \Tasks();

                            //Task - Step
                            if ( isset ($arrayData["DYN_UID"]) && $arrayData["DYN_UID"] != $arrayWebEntryEventData["DYN_UID"] )
                            {
                                //Delete
                                $step = new \Step();

                                $criteria = new \Criteria ("workflow");

                                $criteria->add (\StepPeer::TAS_UID, $arrayWebEntryEventData["WEE_WE_TAS_UID"]);

                                $rsCriteria = \StepPeer::doSelectRS ($criteria);
                                $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);

                                while ($rsCriteria->next ()) {
                                    $row = $rsCriteria->getRow ();

                                    $result = $step->remove ($row["STEP_UID"]);
                                }

                                //Add
                                $step = new \Step();

                                $stepUid = $step->create (array("PRO_UID" => $arrayWebEntryEventData["PRJ_UID"], "TAS_UID" => $arrayWebEntryEventData["WEE_WE_TAS_UID"]));
                                $result = $step->update (array("STEP_UID" => $stepUid, "STEP_TYPE_OBJ" => "DYNAFORM", "STEP_UID_OBJ" => $arrayData["DYN_UID"], "STEP_POSITION" => 1, "STEP_MODE" => "EDIT"));
                            }

                            //Task - User
                            if ( isset ($arrayData["USR_UID"]) && $arrayData["USR_UID"] != $arrayWebEntryEventData["USR_UID"] )
                            {
                                //Unassign
                                $taskUser = new \TaskUser();

                                $criteria = new \Criteria ("workflow");

                                $criteria->add (\TaskUserPeer::TAS_UID, $arrayWebEntryEventData["WEE_WE_TAS_UID"]);

                                $rsCriteria = \TaskUserPeer::doSelectRS ($criteria);
                                $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);

                                while ($rsCriteria->next ()) {
                                    $row = $rsCriteria->getRow ();

                                    $result = $taskUser->remove ($row["TAS_UID"], $row["USR_UID"], $row["TU_TYPE"], $row["TU_RELATION"]);
                                }

                                //Assign
                                $result = $task->assignUser ($arrayWebEntryEventData["WEE_WE_TAS_UID"], $arrayData["USR_UID"], 1);
                            }

                            //Route
                            if ( isset ($arrayData["ACT_UID"]) && $arrayData["ACT_UID"] != $arrayWebEntryEventData["ACT_UID"] )
                            {
                                //Delete
                                $result = $task->deleteAllRoutesOfTask ($arrayWebEntryEventData["PRJ_UID"], $arrayWebEntryEventData["WEE_WE_TAS_UID"], true);

                                //Add
                                $workflow = \ProcessMaker\Project\Workflow::load ($arrayWebEntryEventData["PRJ_UID"]);

                                $result = $workflow->addRoute ($arrayWebEntryEventData["WEE_WE_TAS_UID"], $arrayData["ACT_UID"], "SEQUENTIAL");
                            }

                            //WebEntry
                            $arrayDataAux = array();

                            if ( isset ($arrayData["DYN_UID"]) )
                            {
                                $arrayDataAux["DYN_UID"] = $arrayData["DYN_UID"];
                            }

                            if ( isset ($arrayData["USR_UID"]) )
                            {
                                $arrayDataAux["USR_UID"] = $arrayData["USR_UID"];
                            }

                            if ( isset ($arrayData["WEE_TITLE"]) )
                            {
                                $arrayDataAux["WE_TITLE"] = $arrayData["WEE_TITLE"];
                            }

                            if ( isset ($arrayData["WEE_DESCRIPTION"]) )
                            {
                                $arrayDataAux["WE_DESCRIPTION"] = $arrayData["WEE_DESCRIPTION"];
                            }

                            if ( count ($arrayDataAux) > 0 )
                            {
                                $arrayDataAux = $this->webEntry->update ($arrayWebEntryEventData["WEE_WE_UID"], $userUidUpdater, $arrayDataAux);
                            }
                        }
                        break;
                    case "DELETE":
                        $this->deleteWebEntry ($arrayWebEntryEventData["WEE_WE_UID"], $arrayWebEntryEventData["WEE_WE_TAS_UID"]);

                        $arrayData["WEE_WE_UID"] = "";
                        $arrayData["WEE_WE_TAS_UID"] = "";
                        break;
                }

                //WebEntry-Event
                $webEntryEvent = \WebEntryEventPeer::retrieveByPK ($webEntryEventUid);

                $webEntryEvent->fromArray ($arrayData, \BasePeer::TYPE_FIELDNAME);

                if ( $webEntryEvent->validate () )
                {
                    $cnn->begin ();

                    $result = $webEntryEvent->save ();

                    $cnn->commit ();

                    //Set WEE_TITLE
                    if ( isset ($arrayData["WEE_TITLE"]) )
                    {
                        $result = \Content::addContent ("WEE_TITLE", "", $webEntryEventUid, SYS_LANG, $arrayData["WEE_TITLE"]);
                    }

                    //Set WEE_DESCRIPTION
                    if ( isset ($arrayData["WEE_DESCRIPTION"]) )
                    {
                        $result = \Content::addContent ("WEE_DESCRIPTION", "", $webEntryEventUid, SYS_LANG, $arrayData["WEE_DESCRIPTION"]);
                    }

                    //Return
                    $arrayData = $arrayDataBackup;

                    if ( !$this->formatFieldNameInUppercase )
                    {
                        $arrayData = array_change_key_case ($arrayData, CASE_LOWER);
                    }

                    return $arrayData;
                }
                else
                {
                    $msg = "";

                    foreach ($webEntryEvent->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure->getMessage ();
                    }

                    throw new \Exception (\G::LoadTranslation ("ID_REGISTRY_CANNOT_BE_UPDATED") . (($msg != "") ? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                $cnn->rollback ();

                $this->deleteWebEntry ($this->webEntryEventWebEntryUid, $this->webEntryEventWebEntryTaskUid);

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete WebEntry-Event
     *
     * @param string $webEntryEventUid Unique id of WebEntry-Event
     *
     * return void
     */
    public function delete ($webEntryEventUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid, $this->arrayFieldNameForException["webEntryEventUid"]);

            //Set variables
            $arrayWebEntryEventData = $this->getWebEntryEvent ($webEntryEventUid, true);

            //Delete WebEntry
            $this->deleteWebEntry ($arrayWebEntryEventData["WEE_WE_UID"], $arrayWebEntryEventData["WEE_WE_TAS_UID"]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for WebEntry-Event
     *
     * return object
     */
    public function getWebEntryEventCriteria ()
    {
        try {

            $criteria = " SELECT WEE_UID, PRJ_UID, EVN_UID, ACT_UID, DYN_UID, USR_UID, WEE_STATUS, WEE_WE_UID, WEE_WE_TAS_UID, WE_DATA AS WEE_WE_URL FROM web_entry";

            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a WebEntry-Event from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data WebEntry-Event
     */
    public function getWebEntryEventDataFromRecord (array $record)
    {
        try {
            if ( $record["WEE_WE_UID"] . "" != "" )
            {
                $http = (\G::is_https ()) ? "https://" : "http://";
                $url = $http . $_SERVER["HTTP_HOST"] . "/sys" . SYS_SYS . "/" . SYS_LANG . "/" . SYS_SKIN . "/" . $record["PRJ_UID"];

                $record["WEE_WE_URL"] = $url . "/" . $record["WEE_WE_URL"];
            }

            return array(
                $this->getFieldNameByFormatFieldName ("WEE_UID") => $record["WEE_UID"],
                $this->getFieldNameByFormatFieldName ("EVN_UID") => $record["EVN_UID"],
                $this->getFieldNameByFormatFieldName ("ACT_UID") => $record["ACT_UID"],
                $this->getFieldNameByFormatFieldName ("DYN_UID") => $record["DYN_UID"],
                $this->getFieldNameByFormatFieldName ("USR_UID") => $record["USR_UID"],
                $this->getFieldNameByFormatFieldName ("WEE_TITLE") => $record["WEE_TITLE"],
                $this->getFieldNameByFormatFieldName ("WEE_DESCRIPTION") => $record["WEE_DESCRIPTION"] . "",
                $this->getFieldNameByFormatFieldName ("WEE_URL") => $record["WEE_WE_URL"] . "",
                $this->getFieldNameByFormatFieldName ("WEE_STATUS") => $record["WEE_STATUS"]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all WebEntry-Events
     *
     * @param string $projectUid Unique id of Project
     *
     * return array Return an array with all WebEntry-Events
     */
    public function getWebEntryEvents ($projectUid)
    {
        try {
            $arrayWebEntryEvent = array();

            //Get data
            $criteria = $this->getWebEntryEventCriteria ();

            $criteria .= " WHERE PRJ_UID = ?";
            $results = $this->objMysql->_query ($criteria, [$projectUid]);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                return FALSE;
            }


            foreach ($results as $result)

                $arrayWebEntryEvent[] = $this->getWebEntryEventDataFromRecord ($result);
            }

            //Return
            return $arrayWebEntryEvent;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a WebEntry-Event
     *
     * @param string $webEntryEventUid Unique id of WebEntry-Event
     * @param bool   $flagGetRecord    Value that set the getting
     *
     * return array Return an array with data of a WebEntry-Event
     */
    public function getWebEntryEvent ($webEntryEventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid);

            //Get data
            $criteria = $this->getWebEntryEventCriteria ();

            $criteria .= " WHERE WEE_UID = ?";

            $result = $this->objMysql->_query ($criteria, [$webEntryEventUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return FALSE;
            }

            //Return
            return (!$flagGetRecord) ? $this->getWebEntryEventDataFromRecord ($result[0]) : $result[0];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a WebEntry-Event by unique id of Event
     *
     * @param string $projectUid    Unique id of Project
     * @param string $eventUid      Unique id of Event
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a WebEntry-Event by unique id of Event
     */
    public function getWebEntryEventByEvent ($projectUid, $eventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            if ( !$this->existsEvent ($projectUid, $eventUid) )
            {
                throw new \Exception ("ID_WEB_ENTRY_EVENT_DOES_NOT_IS_REGISTERED");
            }

            //Get data
            $criteria = $this->getWebEntryEventCriteria ();

            $criteria .= " WHERE PRJ_UID = ? AND EVN_UID = ?";
            $result = $this->objMysql->_query ($criteria, [$projectUid, $eventUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }

            //Return
            return (!$flagGetRecord) ? $this->getWebEntryEventDataFromRecord ($result[0]) : $result[0];
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
