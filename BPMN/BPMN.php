<?php

class BPMN
{

    private $objFlow;
    private $objTask;
    private $objMysql;

    public function __construct ()
    {
        $this->objTask = new Task();
        $this->objFlow = new Flow();
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $from
     * @param type $to
     * @param type $workflowId
     * @param type $orderId
     * @param type $condition
     * @param type $loc
     */
    public function setStart ($from, $to, $workflowId, $orderId, $condition = '', $loc)
    {
        $this->objFlow->setCondition ($condition);
        $this->objFlow->setStepFrom ($from);
        $this->objFlow->setStepTo ($to);
        $this->objFlow->setWorkflowId ($workflowId);
        $this->objFlow->setOrderId ($orderId);
        $this->objFlow->setIsActive (1);
        $this->objFlow->setFirstStep (1);
        $this->objFlow->setLoc ($loc);
        
        $this->objFlow->save();
    }

    /**
     * 
     * @param type $from
     * @param type $workflowId
     * @param type $orderId
     * @param type $condition
     * @param type $loc
     */
    public function setEnd ($from, $workflowId, $orderId, $condition = '', $loc)
    {
        $this->objFlow->setCondition ($condition);
        $this->objFlow->setStepFrom ($from);
        $this->objFlow->setStepTo (0);
        $this->objFlow->setWorkflowId ($workflowId);
        $this->objFlow->setOrderId ($orderId);
        $this->objFlow->setIsActive (1);
        $this->objFlow->setFirstStep (0);
        $this->objFlow->setLoc ($loc);
        
        $this->objFlow->save();
    }

    /**
     * 
     * @param type $from
     * @param type $to
     * @param type $workflowId
     * @param type $orderId
     * @param type $condition
     * @param type $loc
     */
    public function saveFlow ($from, $to, $workflowId, $orderId, $condition = '', $loc)
    {
        $this->objFlow->setCondition ($condition);
        $this->objFlow->setStepFrom ($from);
        $this->objFlow->setStepTo ($to);
        $this->objFlow->setWorkflowId ($workflowId);
        $this->objFlow->setOrderId ($orderId);
        $this->objFlow->setIsActive (1);
        $this->objFlow->setFirstStep (0);
        $this->objFlow->setLoc ($loc);
        
        $this->objFlow->save();
    }

    /**
     * 
     * @param type $stepName
     */
    public function createTask ($stepName)
    {
        $this->objTask->setStepName ($stepName);
        $this->objTask->saveNewStep ();
    }

    public function addParticipant ()
    {
        
    }

    public function removeParticipant ()
    {
        
    }

    public function addGateway ()
    {
        
    }

    public function removeGateway ()
    {
        
    }

    /**
     * 
     * @param type $stepFrom
     * @param type $workflow
     * @return \Trigger
     */
    public function getFlow ($stepFrom, $workflow)
    {
        $check = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_from" => $stepFrom, "workflow_id" => $workflow));

        if ( !empty ($check) )
        {
            $objTrigger = new Trigger();
            $objTrigger->setTrigger ($check[0]['step_trigger']);

            return $objTrigger;
        }
    }

    /**
     * 
     * @param type $workflowId
     * @return \Flow
     */
    public function getAllTasks ($workflowId)
    {
        $arrStepMapping = $this->objMysql->_query ("SELECT sm.*, s.step_name FROM workflow.status_mapping sm
                                                    INNER JOIN workflow.task s ON s.step_id = sm.step_from
                                                    WHERE workflow_id = ?
                                                    ORDER By sm.order_id", [$workflowId]);
        $arrAllMaps = [];

        foreach ($arrStepMapping as $key => $mapping) {

            //$objFlow =
            $arrAllMaps[$key] = new Flow ($mapping['id'], $workflowId);
            $arrAllMaps[$key]->setCondition ($mapping['step_condition']);
            $arrAllMaps[$key]->setFirstStep ($mapping['first_step']);
            $arrAllMaps[$key]->setIsActive ($mapping['is_active']);
            $arrAllMaps[$key]->setLoc ($mapping['loc']);
            $arrAllMaps[$key]->setOrderId ($mapping['order_id']);
            $arrAllMaps[$key]->setStepFrom ($mapping['step_from']);
            $arrAllMaps[$key]->setStepTo ($mapping['step_to']);
            $arrAllMaps[$key]->setWorkflowId ($mapping['workflow_id']);
            $arrAllMaps[$key]->setStepName ($mapping['step_name']);
        }

        return $arrAllMaps;
    }

}
