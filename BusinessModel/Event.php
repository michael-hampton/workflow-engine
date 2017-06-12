<?php

class Event
{

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
        $oProcess = new \WorkflowStep();
        if ( !($oProcess->taskExists ($taskId)) )
        {
            throw (new \Exception ("TASK ID DOESNT EXIST"));
        }
        return $taskId;
    }

}
