<?php
class BaseMessageEvent
{
    use Validator;

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
