<?php
class Event
{
    private $taskId;
    private $event;
    private $objMysql;
    private $pro_uid;
    private $ValidationFailures;

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param mixed $taskId
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getProUid()
    {
        return $this->pro_uid;
    }

    /**
     * @param mixed $pro_uid
     */
    public function setProUid($pro_uid)
    {
        $this->pro_uid = $pro_uid;
    }

    /**
     * @return mixed
     */
    public function getValidationFailures()
    {
        return $this->ValidationFailures;
    }

    /**
     * @param mixed $ValidationFailures
     */
    public function setValidationFailures($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }


    public function validate()
    {
        $errorCount = 0;

        if(trim($this->event) === "") {
            $errorCount++;
        }

        if(trim($this->taskId) === "") {
            $errorCount++;
        }

        if($errorCount > 0) {
            return false;
        }

        return true;
    }

    public function getEvents()
    {
        $result = $this->objMysql->_select("workflow.status_mapping", ["condition"], ["id" => $this->taskId]);

        if(isset($result[0]) && !empty($result[0])) {
            $arrCondition = json_decode($result[0]['condition']);

            return $arrCondition;
        }

        return [];

    }

    public function save()
    {
        $arrCondition = [];

        $this->objMysql->_update("workflow.status_mapping", array("condition" => $arrCondition), array("id" => $this->taskId));
    }

    public function validateTaskId($taskId)
    {
         $result = $this->objMysql->_select("workflow.status_mapping", [], ["id" => $taskId]);

         if(isset($result[0]) && !empty($result[0])) {
	 	return true;
	 }

	 return false;
    }

	 /**
     * Save Event Post Put
     *
     * @param string $evn_uid
     *
     * @access public
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return array
     */
    public function saveEvents($pro_uid, $dataEvent, $create = false)
    {
        $pro_uid = $this->validateProUid($pro_uid);
        //if (!$create) {
            //$dataEvent['evn_uid'] = $this->validateEvnUid($dataEvent['evn_uid']);
        //}

        if ( ($pro_uid == '') || (count($dataEvent) == 0) ) {
            return false;
        }


            if (empty($dataEvent['TAS_UID'])) {
                throw (new \Exception("ID_FIELD_REQUIRED"));
            }
            $this->validateTasUid($dataEvent['TAS_UID']);

        //$this->validateTriUid($dataEvent['TRI_UID']);
        
	if ( $create && (isset($dataEvent['EVN_UID'])) ) {
            unset($dataEvent['EVN_UID']);
        }

        $dataEvent['PRO_UID'] = $pro_uid;
        $oEvent = new \Event();

        if ($create) {
            $uidNewEvent = $oEvent->create( $dataEvent );
            $dataEvent = $this->getEvents($pro_uid, '', $uidNewEvent);
            return $dataEvent;
        } else {
            $oEvent->update( $dataEvent );
            $uidNewEvent = $dataEvent['EVN_UID'];
        }
    }

}



class MessageEvent
{
    private $elementId;
    private $projectId;
    private $status;
    private $dateSent;
    private $msg_type_id;
    private $objMysql;
    private $id;
    private $ValidationFailures;

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * @return mixed
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param mixed $elementId
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param mixed $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getDateSent()
    {
        return $this->dateSent;
    }

    /**
     * @param mixed $dateSent
     */
    public function setDateSent($dateSent)
    {
        $this->dateSent = $dateSent;
    }

    /**
     * @return mixed
     */
    public function getValidationFailures()
    {
        return $this->ValidationFailures;
    }

    /**
     * @param mixed $ValidationFailures
     */
    public function setValidationFailures($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }



    /**
     * @return mixed
     */
    public function getMsgTypeId()
    {
        return $this->msg_type_id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $msg_type_id
     */
    public function setMsgTypeId($msg_type_id)
    {
        $this->msg_type_id = $msg_type_id;
    }

    public function validate()
    {
        $errorCount = 0;

        if(trim($this->msg_type_id) === "") {
            $errorCount++;
        }

        if(trim($this->status) === "") {
            $errorCount++;
        }

        if(trim($this->projectId) === "") {
            $errorCount++;
        }

        if(trim($this->elementId) === "") {
            $errorCount++;
        }


        if($errorCount > 0) {
            return false;
        }

        return true;

    }

    public function save()
    {
        $this->objMysql->_insert("workflow.event_messages", ["msg_type_id" => $this->msg_type_id, "project_id" => $this->projectId, "element_id" => $this->elementId, "status" => $this->status, "date_sent" => $this->dateSent]);

    }

    public function update()
    {
        $this->objMysql->_update("workflow.event_messages", ["msg_type_id" => $this->msg_type_id, "project_id" => $this->projectId, "element_id" => $this->elementId, "status" => $this->status, "date_sent" => $this->dateSent], ["id" => $this->id]);

    }

    public function exists($is)
    {
         $result = $this->objMysql->_select("workflow.event_messages", array(), array("id" => $id));
    }

    public function hasMessage($projectId, $msgType)
    {
        $result = $this->objMysql->_select("workflow.event_messages", array(), array("project_id" => $projectId, "msg_type_id" => $msgType));

        if(isset($result[0]) && !empty($result[0])) {
            return $result[0];
        }
    }

    public function load($arrData)
    {
        $this->msg_type_id = $arrData['msg_type'];
        $this->dateSent = $arrData['dateSent'];
        $this->elementId = $arrData['elementId'];
        $this->projectId = $arrData['projectId'];
        $this->status = $arrData['status'];
    }

    public function checkEvent($taskId)
    {
        $objEvent = new Event();
        $objEvent->setTaskId($taskId);
        $arrConditions = $objEvent->getEvents();

        if(isset($arrConditions['send_message'])) {
            return $arrConditions['msg_type_id'];
        }
    }

    public function receive($projectId, $msgType)
    {
        $message = $this->hasMessage($projectId, $msgType);

        if(!empty($message)) {
            $this->setDateSent(date("Y-m-d H:i:s"));
            $this->setStatus(1);
            $this->setProjectId($projectId);
            $this->setMsgTypeId($msgType);

            if($this->validate()) {
                $this->save();
            }
        }

        return true;
    }

    /**
     * Save Data for Email-Event
     * @var string $prj_uid. Uid for Process
     * @var string $arrayData. Data for Trigger
     *
     * return array
     */
    public function save($prj_uid = '', $arrayData = array())
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");


            //Verify data
            $process->throwExceptionIfNotExistsProcess($prj_uid, "projectUid");

            //Create

            try {
                $emailEvent = new \EmailEvent();


                $emailEvent->setEmailEventUid($emailEventUid);
                $emailEvent->setPrjUid($prj_uid);

                $result = $emailEvent->save();

                return $this->getEmailEvent($emailEventUid);
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Email-Event
     *
     * @param string $emailEventUid Unique id of Email-Event
     * @param array  $arrayData Data
     *
     * return array Return data of the Email-Event updated
     */
    public function update($emailEventUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            //Set data
            //Set variables
            $arrayEmailEventData = $this->getEmailEvent($emailEventUid);

            //Verify data
            $this->verifyIfEmailEventExists($emailEventUid);

            //Update

            try {

                $result = $emailEvent->save();

                return true;

            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}

class MessageType
{
    private $id;
    private $title;
    private $description;
    private $variables;
    private $objMysql;
    private $blUpdate;
    private $ValidationFailures;

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getValidationFailures()
    {
        return $this->ValidationFailures;
    }

    /**
     * @param mixed $ValidationFailures
     */
    public function setValidationFailures($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }



    /**
     * @return mixed
     */
    public function getBlUpdate()
    {
        return $this->blUpdate;
    }

    /**
     * @param mixed $blUpdate
     */
    public function setBlUpdate($blUpdate)
    {
        $this->blUpdate = $blUpdate;
    }

    /**
     * @return mixed
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param mixed $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    public function validate()
    {
        $errorCount = 0;

        if($this->title === "") {
            $errorCount++;
        }

        if($errorCount > 0) {
            return false;
        }

        return true;

    }

    public function titleExists($title)
    {
        $result = $this->objMysql->_select("workflow.message_type", [], ["title" => $title]);

        if(isset($result[0]) && !empty($result[0])) {
            return true;
        }

        return false;
    }

    public function getMessageType($id)
    {
        $result = $this->objMysql->_select("workflow.message_type", [], ["id" => $id]);

        if(isset($result[0]) && !empty($result[0])) {
           return $result[0];
        }

        return false;
    }

    public function throwExceptionIfMessageTypeNotExist($id)
    {
        $result = $this->getMessageType($id);

        if(!$result) {
            // throw exception
        }

        return true;
    }

    public function getMessageTypes()
    {
        $results = $this->objMysql->_select("workflow.message_type");

        return $results;
    }

    public function save()
    {
        if($this->blUpdate === true) {
            $this->objMysql->_update("workflow.message_type", array("title" => $this->title, "description" => $this->description, "variables" => $this->variables, array($this->id)));
        } else {
            $this->objMysql->_insert("workflow.message_type", array("title" => $this->title, "description" => $this->description, "variables" => $this->variables));
        }
    }

    public function delete()
    {
        $this->objMysql->_delete("workflow.message_type", ["id" => $this->id]);
    }

    /**
     * Create Message-Type
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Message-Type created
     */
    public function create($projectUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            $process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);

            $this->throwExceptionIfDataIsInvalid("", $projectUid, $arrayData);

            //Create

            try {
                $messageType = new \MessageType();

                $messageType->fromArray($arrayData, \BasePeer::TYPE_FIELDNAME);


                $messageType->setPrjUid($projectUid);

                if ($messageType->validate()) {

                    $result = $messageType->save();

                    /*if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                        $variable = new \ProcessMaker\BusinessModel\MessageType\Variable();
                        $variable->setFormatFieldNameInUppercase($this->formatFieldNameInUppercase);

                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;

                            $arrayResult = $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }*/

                    //Return
                    return $this->getMessageType($messageTypeUid);
                } else {
                    $msg = "";

                    foreach ($messageType->getValidationFailures() as $validationFailure) {
                        $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure->getMessage();
                    }

                    throw new \Exception("ID_RECORD_CANNOT_BE_CREATED" . (($msg != "")? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

	 public function update($messageTypeUid, $arrayData)
    {
        try {
            //Verify data
           

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            //Set data

            //Set variables
            $arrayMessageTypeData = $this->getMessageType($messageTypeUid, true);

            //Verify data
            $this->throwExceptionIfNotExistsMessageType($messageTypeUid, $this->arrayFieldNameForException["messageTypeUid"]);

            $this->throwExceptionIfDataIsInvalid($messageTypeUid, $arrayMessageTypeData["PRJ_UID"], $arrayData);

            //Update

            try {
                $messageType = \MessageTypePeer::retrieveByPK($messageTypeUid);
                $messageType->fromArray($arrayData, \BasePeer::TYPE_FIELDNAME);

                if ($messageType->validate()) {

                    $result = $messageType->save();


                    /*if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                        $variable = new \ProcessMaker\BusinessModel\MessageType\Variable();
                        $variable->setFormatFieldNameInUppercase($this->formatFieldNameInUppercase);

                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;

                            $arrayResult = $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }*/


                    return true
                } else {
                    $msg = "";

                    foreach ($messageType->getValidationFailures() as $validationFailure) {
                        $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure->getMessage();
                    }

                    throw new \Exception("ID_REGISTRY_CANNOT_BE_UPDATED" . (($msg != "")? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                $cnn->rollback();

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $messageTypeUid Unique id of Message-Type
     * @param string $projectUid     Unique id of Project
     * @param array  $arrayData      Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid($messageTypeUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayMessageTypeData = ($messageTypeUid == "")? array() : $this->getMessageType($messageTypeUid, true);
            $flagInsert           = ($messageTypeUid == "")? true : false;

            $arrayFinalData = array_merge($arrayMessageTypeData, $arrayData);

            //Verify data - Field definition

            //Verify data
            if (isset($arrayData["MSGT_NAME"])) {
                $this->throwExceptionIfExistsName($projectUid, $arrayData["MSGT_NAME"], $this->arrayFieldNameForException["messageTypeName"], $messageTypeUid);
            }

            if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                $this->throwExceptionCheckIfThereIsRepeatedVariableName($arrayData["MSGT_VARIABLES"]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

 public function throwExceptionCheckIfThereIsRepeatedVariableName(array $arrayDataVariables)
    {
        try {
            $i = 0;
            $arrayDataVarAux = $arrayDataVariables;

            while ($i <= count($arrayDataVariables) - 1) {
                if (array_key_exists("MSGTV_NAME", $arrayDataVariables[$i])) {
                    $msgtvNameAux = $arrayDataVariables[$i]["MSGTV_NAME"];
                    $counter = 0;

                    foreach ($arrayDataVarAux as $key => $value) {
                        if ($value["MSGTV_NAME"] == $msgtvNameAux) {
                            $counter = $counter + 1;
                        }
                    }

                    if ($counter > 1) {
                        throw new \Exception("ID_MESSAGE_TYPE_NAME_VARIABLE_EXISTS");
                    }
                }

                $i = $i + 1;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    
}
