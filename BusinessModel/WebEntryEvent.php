<?php

namespace BusinessModel;

class WebEntryEvent
{

    use Validator;

    private $webEntryEventWebEntryUid = "";
    private $webEntryEventWebEntryTaskUid = "";
    private $webEntryMethod = "WS";
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
        $this->webEntry = new WebEntry();
    }

    public function retrieveByPK ($webEntryEventUid)
    {
        $result = $this->objMysql->_select ("workflow.WEB_ENTRY_EVENT", [], ["WEE_UID" => $webEntryEventUid]);
        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }
        return $result;
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
            $obj = $this->retrieveByPK ($webEntryEventUid);
            return $obj !== false ? true : false;
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
            $sql = "SELECT WEE_UID FROM workflow.WEB_ENTRY_EVENT WHERE PRJ_UID = ?";
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
            $sql = "SELECT * FROM WEB_ENTRY_EVENT WHERE PRJ_UID = ? AND WEE_TITLE = ?";
            $arrParameters = array($projectUid, $webEntryEventTitle);
            
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
    public function throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid)
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
    public function throwExceptionIfEventIsRegistered ($projectUid, $eventUid, $webEntryEventUidToExclude = "")
    {
        try {
            if ( $this->existsEvent ($projectUid, $eventUid, $webEntryEventUidToExclude) )
            {
               // throw new \Exception ("ID_WEB_ENTRY_EVENT_ALREADY_REGISTERED");
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
    public function throwExceptionIfExistsTitle ($projectUid, $webEntryEventTitle, $webEntryEventUidToExclude = "")
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
            //Verify data
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $this->throwExceptionIfEventIsRegistered ($projectUid, $arrayData["EVN_UID"], $webEntryEventUid);
            }
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $obj = (new \Flow())->retrieveByPk ($arrayData['EVN_UID']);
                if ( $obj === FALSE )
                {
                    throw new \Exception ("ID_EVENT_NOT_EXIST");
                }
                if ( ((int) $obj->getFirstStep () != 1) || ((int) $obj->getFirstStep () !== 1 && trim ($obj->getStepTo ()) === "") )
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

            if ( isset ($arrayData["DYN_UID"]) )
            {
                $dynaForm = new \WorkflowStep();
                if ( $dynaForm->stepExists ($arrayData['DYN_UID']) === false )
                {
                    throw new Exception ("Step doesnt exist");
                }
            }
            if ( isset ($arrayData["USR_UID"]) )
            {
                (new \BusinessModel\UsersFactory())->throwExceptionIfNotExistsUser ($arrayData['USR_UID']);
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
    public function createWebEntry ($projectUid, $eventUid, $dynaFormUid, $userUid, $title, $description, \Users $objUser)
    {
        
        try {
            //Task
            //Task - User
            $task = new StepPermission (new \Task ($dynaFormUid));
            $permissions = array(
                "selectedPermissions" => array(
                    0 => array(
                        "objectType" => "user",
                        "id" => $objUser->getUserId (),
                        "permissionType" => "master"
                    )
                )
            );
            
            $task->saveProcessPermission ($permissions);
            $this->webEntryEventWebEntryTaskUid = $eventUid;
            
            //WebEntry
            $arrayWebEntryData = $this->webEntry->create (
                    $projectUid, $objUser->getUserId (), array(
                "TAS_UID" => $this->webEntryEventWebEntryTaskUid,
                "DYN_UID" => $dynaFormUid,
                "USR_UID" => $userUid,
                "WE_TITLE" => $title,
                "WE_DESCRIPTION" => $description,
                "WE_METHOD" => $this->webEntryMethod,
                "WE_INPUT_DOCUMENT_ACCESS" => 1
                    )
            );
            
            $this->webEntryEventWebEntryUid = $arrayWebEntryData->getWeUid ();
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
    public function deleteWebEntry ($webEntryUid)
    {
        try {

            if ( $webEntryUid != "" )
            {
                $obj = $this->webEntry->retrieveByPK ($webEntryUid);
                if ( $obj !== false )
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
    public function create ($projectUid, \Users $objUser, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
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

            if(isset($arrayData['WEB_ENTRY_METHOD'])) {
                $this->webEntryMethod = $arrayData['WEB_ENTRY_METHOD'];
            }

            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($projectUid);
            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);
            //Create
            $this->webEntryEventWebEntryUid = "";
            $this->webEntryEventWebEntryTaskUid = "";
            try {
                //WebEntry
                if ( $arrayData["WEE_STATUS"] == "ENABLED" )
                {
                    $this->createWebEntry (
                            $projectUid, $arrayData["EVN_UID"], $arrayData["DYN_UID"], $arrayData["USR_UID"], $arrayData["WEE_TITLE"], $arrayData["WEE_DESCRIPTION"], $objUser
                    );
                }
                //WebEntry-Event
                $webEntryEvent = new \WebEntryEvent();
                $webEntryEvent->loadObject ($arrayData);
                $webEntryEvent->setPrjUid ($projectUid);
                $webEntryEvent->setWeeWeUid ($this->webEntryEventWebEntryUid);
                $webEntryEvent->setWeeWeTasUid ($this->webEntryEventWebEntryTaskUid);
                if ( $webEntryEvent->validate () )
                {
                    $webEntryEventUid = $webEntryEvent->save ();
                    //Return
                    return $this->getWebEntryEvent ($webEntryEventUid);
                }
                else
                {
                    $msg = "";
                    foreach ($webEntryEvent->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                //$this->deleteWebEntry ($this->webEntryEventWebEntryUid, $this->webEntryEventWebEntryTaskUid);
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
    public function update ($webEntryEventUid, \Users $objUser, array $arrayData)
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
            $this->throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid);
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
                                $arrayFinalData["PRJ_UID"], $arrayFinalData["EVN_UID"], $arrayFinalData["ACT_UID"], $arrayFinalData["DYN_UID"], $arrayFinalData["USR_UID"], $arrayFinalData["WEE_TITLE"], $arrayFinalData["WEE_DESCRIPTION"], $objUser
                        );
                        $arrayData["WEE_WE_UID"] = $this->webEntryEventWebEntryUid;
                        $arrayData["WEE_WE_TAS_UID"] = $this->webEntryEventWebEntryTaskUid;
                        break;
                    case "UPDATE":
                        if ( $arrayWebEntryEventData["WEE_WE_UID"] != "" )
                        {
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

                            if ( isset ($arrayData['EVN_UID']) )
                            {
                                $arrayDataAux['EVN_UID'] = $arrayData['EVN_UID'];
                            }

                            if ( isset ($arrayData['TAS_UID']) )
                            {
                                $arrayDataAux['TAS_UID'] = $arrayData['TAS_UID'];
                            }

                            if ( isset ($arrayData['DYN_UID']) )
                            {
                                $arrayDataAux['DYN_UID'] = $arrayData['DYN_UID'];
                            }

                            if ( isset ($arrayData['PRO_UID']) )
                            {
                                $arrayDataAux['PRO_UID'] = $arrayData['PRO_UID'];
                            }

                            if ( count ($arrayDataAux) > 0 )
                            {
                                $arrayDataAux = $this->webEntry->update ($arrayWebEntryEventData["WEE_WE_UID"], $objUser->getUserId (), $arrayDataAux);
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

                $objWebEntry = new \WebEntryEvent();
                $objWebEntry->setWeeUid ($webEntryEventUid);
                $objWebEntry->setWeeWeUid($arrayWebEntryEventData["WEE_WE_UID"]);

                $objWebEntry->loadObject ($arrayData);
                if ( $objWebEntry->validate () )
                {
                    $objWebEntry->save ();

                    //Return
                    $arrayData = $arrayDataBackup;
                    return $arrayData;
                }
                else
                {
                    $msg = "";
                    foreach ($objWebEntry->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_REGISTRY_CANNOT_BE_UPDATED " . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                //$this->deleteWebEntry ($this->webEntryEventWebEntryUid, $this->webEntryEventWebEntryTaskUid);
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
            $this->throwExceptionIfNotExistsWebEntryEvent ($webEntryEventUid);
            //Set variables
            $arrayWebEntryEventData = $this->getWebEntryEvent ($webEntryEventUid, true);
            //Delete WebEntry
            $this->deleteWebEntry ($arrayWebEntryEventData["WEE_WE_UID"], $arrayWebEntryEventData["WEE_WE_TAS_UID"]);
            //Delete WebEntry-Event

            $objWebEntryEvent = new \WebEntryEvent();
            $objWebEntryEvent->setWeeUid ($webEntryEventUid);
            $objWebEntryEvent->delete ();
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
            $criteria = " SELECT `WEE_UID`,
                    `WEE_TITLE`, 
                    `WEE_DESCRIPTION`, 
                    `PRJ_UID`, 
                    `EVN_UID`, 
                    `ACT_UID`, 
                    `DYN_UID`, 
                    `USR_UID`, 
                    `WEE_STATUS`, 
                    `WEE_WE_UID`, 
                    `WEE_WE_TAS_UID` 
                    FROM workflow.`WEB_ENTRY_EVENT`";
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
                $url = WEB_ENTRY_DIR . $record["PRJ_UID"];
                
                $record["WEE_WE_URL"] = $url . "/" . str_replace(" ", "_", $record["WEE_TITLE"]) . ".php";
            }
            $objWebEntryEvent = new \WebEntryEvent();
            $objWebEntryEvent->setWeeUid ($record["WEE_UID"]);
            $objWebEntryEvent->setEvnUid ($record["EVN_UID"]);
            $objWebEntryEvent->setActUid ($record["ACT_UID"]);
            $objWebEntryEvent->setDynUid ($record["DYN_UID"]);
            $objWebEntryEvent->setUsrUid ($record["USR_UID"]);
            $objWebEntryEvent->setWeeTitle ($record["WEE_TITLE"]);
            $objWebEntryEvent->setWeeDescription ($record["WEE_DESCRIPTION"] . "");
            $objWebEntryEvent->setWeeStatus ($record["WEE_STATUS"]);
            $objWebEntryEvent->setWeeUrl ($record["WEE_WE_URL"] . "");
            return $objWebEntryEvent;
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
            foreach ($results as $result) {
                $arrayWebEntryEvent[] = $this->getWebEntryEventDataFromRecord ($result);
            }
            //Return
            return $arrayWebEntryEvent;
        } catch (Exception $ex) {
            throw $ex;
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
