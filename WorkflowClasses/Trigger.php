<?php

class Trigger
{

    private $objMysql;
    private $workflowId;
    private $triggerType;
    private $stepTo;
    private $workflowTo;
    private $id;
    private $arrTrigger = array();
    private $arrayValidationErrors = array();

    /**
     *
     * @param type $id
     */
    public function __construct ($id = null)
    {
        $this->objMysql = new Mysql2();

        if ( $id !== null )
        {
            $this->id = $id;
        }
    }

    public function loadObject ()
    {
        if ( $_POST['step']['conditionValue'] == "" || $_POST['step']['condition'] == "" )
        {
            $_POST['step']['trigger_type'] = "step";
        }
        else
        {
            $_POST['step']['trigger_type'] = "gateway";
        }

        $strTrigger = json_encode (array("moveTo" => $_POST['step']));

        $step = $this->_model->_select ("workflow.status_mapping", array(), array("id" => $_POST['stepId']));
        $step = $step[0]['step_from'];
        $this->_model->_update ("workflow.status_mapping", array("step_trigger" => $strTrigger), array("step_from" => $step));
    }

    function getWorkflowId ()
    {
        return $this->workflowId;
    }

    function getTriggerType ()
    {
        return $this->triggerType;
    }

    function getStepTo ()
    {
        return $this->stepTo;
    }

    function getWorkflowTo ()
    {
        return $this->workflowTo;
    }

    /**
     *
     * @param type $workflowId
     */
    function setWorkflowId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     *
     * @param type $triggerType
     */
    function setTriggerType ($triggerType)
    {
        $this->triggerType = $triggerType;
    }

    /**
     *
     * @param type $stepTo
     */
    function setStepTo ($stepTo)
    {
        $this->stepTo = $stepTo;
    }

    /**
     *
     * @param type $workflowTo
     */
    function setWorkflowTo ($workflowTo)
    {
        $this->workflowTo = $workflowTo;
    }

    /**
     *
     * @param type $trigger
     */
    public function setTrigger ($trigger)
    {
        $this->arrTrigger = $trigger;
    }

    public function getTrigger ()
    {
        return $this->arrTrigger;
    }

    /**
     *
     * @return type
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     *
     * @param type $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getArrayValidationErrors ()
    {
        return $this->arrayValidationErrors;
    }

    public function setArrayValidationErrors ($arrayValidationErrors)
    {
        $this->arrayValidationErrors = $arrayValidationErrors;
    }

    public function save ()
    {
        
        $arrTrigger = array(
            "moveTo" => array(
                "workflow_id" => $this->getWorkflowId (),
                "workflow_to" => $this->getWorkflowTo (),
                "trigger_type" => $this->getTriggerType (),
                "step_to" => $this->getStepTo ()
            )
        );

        $this->objMysql->_update (
                "workflow.status_mapping", array(
            "step_trigger" => json_encode ($arrTrigger)
                ), array(
            "id" => $this->id
                )
        );

        return true;
    }

    /**
     *
     * @param type $id
     * @return boolean
     */
    public function delete ($id)
    {
        if ( trim ($id) == "" || !is_numeric ($id) )
        {
            throw new Exception ("Invalid id given");
        }

        $this->objMysql->_update ("workflow.status_mapping", array("step_trigger" => ""), array("id" => $id));

        return true;
    }

    /**
     *
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;

        if ( $this->workflowId == "" )
        {
            $this->arrayValidationErrors[] = "WORKFLOW ID IS MISSING";
            $errorCount++;
        }

        if ( $this->stepTo == "" )
        {
            $this->arrayValidationErrors[] = "STEP ID IS MISSING";
            $errorCount++;
        }

        if ( $this->id == "" )
        {
            $this->arrayValidationErrors[] = "ID IS MISSING";
            $errorCount++;
        }

        if ( $this->triggerType == "" )
        {
            $this->arrayValidationErrors[] = "TYPE IS MISSING";
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

}
