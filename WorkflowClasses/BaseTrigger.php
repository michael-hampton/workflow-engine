<?php

abstract class BaseTrigger
{

    private $objMysql;
    private $workflowId;
    private $triggerType;
    private $stepTo;
    private $workflowTo;
    private $id;
    private $arrTrigger = array();
    private $arrayValidationErrors = array();
    private $title;
    private $description;
    private $triggerId;
    private $New;
    private $eventType;
    private $template;

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

    public function getTitle ()
    {
        return $this->title;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function setTitle ($title)
    {
        $this->title = $title;
    }

    public function setDescription ($description)
    {
        $this->description = $description;
    }

    public function getTriggerId ()
    {
        return $this->triggerId;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setTriggerId ($triggerId)
    {
        $this->triggerId = $triggerId;
    }

    public function getNew ()
    {
        return $this->New;
    }

    public function setNew ($New)
    {
        $this->New = $New;
    }

    public function getArrayValidationErrors ()
    {
        return $this->arrayValidationErrors;
    }

    public function setArrayValidationErrors ($arrayValidationErrors)
    {
        $this->arrayValidationErrors = $arrayValidationErrors;
    }
    
    public function getEventType ()
    {
        return $this->eventType;
    }

    public function setEventType ($eventType)
    {
        $this->eventType = $eventType;
    }

    public function save ()
    {
        if ( $this->New === true )
        {
            $id = $this->objMysql->_insert ("workflow.step_trigger", array(
                "workflow_id" => $this->workflowId,
                "workflow_to" => $this->workflowTo,
                "trigger_type" => $this->triggerType,
                "step_to" => $this->stepTo,
                "step_id" => $this->id,
                "title" => $this->title,
                "description" => $this->description,
                "event_type" => $this->eventType,
                "template_name" => $this->template
            ));
            
             $this->triggerId = $id;
        }
        else
        {
            $this->objMysql->_update ("workflow.step_trigger", array(
                "workflow_id" => $this->workflowId,
                "workflow_to" => $this->workflowTo,
                "trigger_type" => $this->triggerType,
                "step_to" => $this->stepTo,
                "step_id" => $this->id,
                "title" => $this->title,
                "description" => $this->description,
                "event_type" => $this->eventType,
                "template_name" => $this->template
                    ), array(
                "id" => $this->triggerId
            ));
        }


       


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

        $this->objMysql->_delete ("workflow.step_trigger", array("id" => $id));

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

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @return     Triggers
     */
    public function retrieveByPK ($pk)
    {
        $v = $this->objMysql->_query ("SELECT workflow_id, workflow_from, workflow_to, trigger_type, step_to, step_id, title, description, event_type, template_name, TO_BASE64(`template_name`) AS code FROM workflow.step_trigger WHERE id = ?", [$pk]);
        
        return !empty ($v) > 0 ? $v[0] : null;
    }

}
