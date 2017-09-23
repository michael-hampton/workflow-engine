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
    public function saveEvents ($dataEvent, $create = true)
    {
        
        $taskId = $this->validateProUid ($dataEvent['TAS_UID']);
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
     * @return string
     */
    public function validateProUid ($taskId)
    {
        $taskId = trim ($taskId);
        if ( $taskId == '' )
        {
            throw (new \Exception ("TASK ID DOESNT EXIST"));
        }
        $objTask = new \Task();
        if ( $objTask->retrieveByPk ($taskId) === false )
        {
            throw new Exception ("TASK ID DOES NOT EXIST");
        }
        return $taskId;
    }

    public function getEvent ($taskId)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", ['step_condition'], ['id' => $taskId]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
    }

}
