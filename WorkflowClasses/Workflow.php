<?php

class Workflow
{

    private $intWorkflowId;
    private $objMike;
    private $intCollectionId;
    private $objMysql;

    public function __construct ($workflowId = null, $objMike = null)
    {
        $this->intWorkflowId = $workflowId;
        $this->objMike = $objMike;
        $this->objMysql = new Mysql2();

        if ( $objMike !== null )
        {
            $this->getWorkflowObject ();
        }
    }

    public function getNextStep ()
    {
        if ( $this->objMike !== null )
        {
            $workflowCollection = $this->getWorkflowObject ();

            return new WorkflowStep ($workflowCollection['current_step']);
        }
        else
        {
            $result = $this->objMysql->_select ("workflow.status_mapping", array("id"), array("workflow_id" => $this->intWorkflowId, "first_step" => 1));

            if ( !empty ($result) )
            {
                $intStartingWorkflowStepId = $result[0]['id'];
            }
            else
            {
                return false;
            }

            return new WorkflowStep ($intStartingWorkflowStepId);
        }
    }

    public function getWorkflowObject ()
    {
        $parentId = $this->objMike->getId ();
        $id = $this->objMike->getId ();


        if ( method_exists ($this->objMike, "getParentId") && $this->objMike->getParentId () != "" )
        {
            $parentId = $this->objMike->getSource_id ();
        }

        $result = $this->objMysql->_select ("workflow.workflow_data", array("workflow_data"), array("object_id" => $parentId));

        if ( !isset ($result[0]) )
        {
            //return false;
        }

        $workflowData = json_decode ($result[0]['workflow_data'], true);

        if ( is_numeric ($id) )
        {
            if ( isset ($workflowData['elements'][$id]) )
            {
                $workflowData = $workflowData['elements'][$id];
            }
        }


        if ( empty ($workflowData) )
        {
            return FALSE;
        }

        $this->intWorkflowId = $workflowData['workflow_id'];
        $this->intCollectionId = $workflowData['request_id'];

        return $workflowData;
    }

    public function getStepsForWorkflow ()
    {
        $arrResult = $this->objMysql->_query ("SELECT s.*, m.step_condition, m.first_step, m.step_from, m.step_to, m.id FROM workflow.status_mapping m
                                                INNER JOIN workflow.steps s ON s.step_id = m.step_from
                                                WHERE m.workflow_id = ?
                                                ORDER BY m.order_id ASC", [0 => $this->intWorkflowId]);

        $arrSteps = array();

        foreach ($arrResult as $result) {
            $arrSteps[$result['id']] = $result;
        }

        return $arrSteps;
    }

    public function getWorkflowId ()
    {
        return $this->intWorkflowId;
    }

    public function getPreviousStatus ()
    {
        $arrResult = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => $this->status, "workflow_id" => $this->workflow));
        if ( !empty ($arrResult) )
        {
            return $arrResult;
        }
        else
        {
            $arrResult2 = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => $this->status));
        }
    }
    
    public function deleteWorkflow()
    {
        $arrResult = $this->objMysql->_select ("workflow.status_mapping", array("workflow_id"), array("workflow_id" => $this->intWorkflowId));

        if ( empty ($arrResult) )
        {
            $this->objMysql->_delete ("workflow.status_mapping", array("workflow_id" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflows", array("workflow_id" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflow_mapping", array("workflow.from" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflow_mapping", array("workflow_to" => $this->intWorkflowId));
        }
    }
}
