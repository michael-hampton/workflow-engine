<?php
namespace ProcessMaker\BusinessModel;

class EmailEvent
{
    use Validator;
    
    private $objMysql;

    /**
     * Get the Email-Event data
     * @var string $evn_uid. uid for activity
     * @var string $pro_uid. uid for process
     * return array
     */
    public function getEmailEventData($stepId)
    {
        try {
            //Get data
            $criteria = $this->getEmailEventCriteria();
            
            $criteria .= " WHERE triggering_status = ?";
            
            $row = $this->objMysql->_query($criteria, [$stepId]);

            return $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the Email-Event data
     * @var string $emailEventUid. uid for email event
     * @var string $pro_uid. uid for process
     * return array
     */
    public function getEmailEventDataByUid($stepId, $emailEventUid)
    {
        try {
            //Get data
            $criteria = $this->getEmailEventCriteria();
            
            $criteria .= " WHERE id = ? AND triggering_status = ?";
            
            $row = $this->objMysql->_query($criteria, [$emailEventUid, $stepId]);
           
            return $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Save Data for Email-Event
     * @var string $prj_uid. Uid for Process
     * @var string $arrayData. Data for Trigger
     *
     * return array
     */
    public function save($stepId, $arrayData = array())
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            //Verify data
            // verify step

            //Create

            try {
                $emailEvent = new \EmailEvent();

                $emailEvent->loadObject($arrayData);

                $emailEvent->setPrjUid($prj_uid);

                $emailEventUid = $emailEvent->save();

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

            //Set variables
            $arrayEmailEventData = $this->getEmailEvent($emailEventUid);

            //Verify data
            $this->verifyIfEmailEventExists($emailEventUid);

            //Update

            try {
                $emailEvent = $this->retrieveByPK($emailEventUid);
                $emailEvent->loadObject($arrayData);

                $result = $emailEvent->save();

                return $result;

            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function retrieveByPK($pk)
    {
        $result = $this->objMysql->_select("task_manager.auto_notifications", [], ["id" => $pk]);
        
        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }
    }

    /**
     * Delete Email-Event
     *
     * @param string $emailEventUid Unique id of Email-Event
     *
     * return void
     */
    public function delete($pro_uid, $emailEventUid, $passValidation = true)
    {
        try {
            //Verify data
            if ($passValidation) {
                $this->verifyIfEmailEventExists($emailEventUid);

            }
            //Delete Email event


        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Email-Event by event uid
     *
     * @param string $emailEventUid Unique id of Email-Event
     *
     * return void
     */
    public function deleteByEvent($stepId)
    {
        try {
            //Verify data
            if (!$this->existsEvent($prj_uid, $evn_uid)) {
                throw new \Exception(\G::LoadTranslation("ID_EMAIL_EVENT_DEFINITION_DOES_NOT_EXIST"));
            }
            $arrayData = $this->existsEvent($prj_uid, $evn_uid);
            $this->delete($prj_uid, $arrayData[0]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Email-Event
     *
     * @param string $emailEventUid Unique id of Email-Event
     * @param bool   $flagGetRecord             Value that set the getting
     *
     * return array Return an array with data of a Email-Event
     */
    public function getEmailEvent($emailEventUid)
    {
        try {
            $result = $this->objMysql->_select("task_manager.auto_notifications", [], ["id" => $emailEventUid]);
            
            if(!isset($result[0]) || empty($result[0])) {
                return false;
            }

            //Return
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Email-Event
     *
     * @param string $emailEventUid Unique id of Email-Event
     *
     * return bool Return true if exists the Email-Event, false otherwise
     */
    public function exists($emailEventUid)
    {
        try {
            $obj = $this->retrieveByPK($emailEventUid);

            return (!is_null($obj))? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Email-Event
     *
     * return object
     */
    public function getEmailEventCriteria()
    {
        try {
         $criteria = "SELECT * FROM task_manager.auto_notifications ";

            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function verifyIfEmailEventExists($emailEventUid)
    {
        if (!$this->exists($emailEventUid)) {
            throw new \Exception("ID_EMAIL_EVENT_DEFINITION_DOES_NOT_EXIST");
        }
    }

    /**
     * Verify if exists the Event of a Message-Event-Definition
     *
     * @param string $projectUid                         Unique id of Project
     * @param string $eventUid                           Unique id of Event
     *
     * return bool Return true if exists the Event of a Message-Event-Definition, false otherwise
     */
    public function existsEvent($projectUid, $eventUid)
    {
        try {
          
             //Get data
            $criteria = $this->getEmailEventCriteria();
            
            $criteria .= " WHERE triggering_status = ?";
            
            $row = $this->objMysql->_query($criteria, [$stepId]);
            
            if(!isset($row[0]) || empty($row[0])){
                return false;
            }
            
            return $row;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
