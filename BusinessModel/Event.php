<?php

namespace BusinessModel;

class Event
{
    private $objMysql;


    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Save Event Post Put
     *
     * @param string $evn_uid
     *
     * @access public
     * @return array
     */
    public function saveEvents ($taskId, $dataEvent, $create = true)
    {

        $taskId = $this->validateProUid ($taskId);
        //if (!$create) {
        //$dataEvent['evn_uid'] = $this->validateEvnUid($dataEvent['evn_uid']);
        //}

        if ( ($taskId == '') || (count ($dataEvent) == 0) )
        {
            return false;
        }

        $dataEvent['task_id'] = $taskId;
        $oEvent = new \EventModel();

        if ( $create )
        {
            $uidNewEvent = $oEvent->create ($dataEvent);
            //$dataEvent = $this->getEvents ($pro_uid, '', $uidNewEvent);
            return true;
        }
        else
        {
            $oEvent->update ($dataEvent);
            $uidNewEvent = $dataEvent['EVN_UID'];
        }
    }

    /**
     * Validate Process Uid
     * @var string $pro_uid. Uid for process
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return string
     */
    public function validateProUid ($taskId)
    {
        $taskId = trim ($taskId);
        if ( $taskId == '' )
        {
            throw (new \Exception ("TASK ID DOESNT EXIST"));
        }
        $oProcess = new \Flow();
        $oProcess->throwExceptionIfNotExistsTask($taskId);
        return $taskId;
    }
    
    public function getEvent($taskId)
    {
        $result = $this->objMysql->_select("workflow.status_mapping", ['step_condition'], ['id' => $taskId]);
        
        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }
        
       return $result;
    }

}
