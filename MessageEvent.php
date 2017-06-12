<?php
class MessageEvent
{
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
