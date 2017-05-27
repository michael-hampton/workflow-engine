<?php

class BaseGateway
{

    private $workflowId;

    /**
     * The value for the step_to field.
     * @var        int
     */
    private $step_to;
    private $field;
    private $condition;
    private $conditionValue;
    private $else;
    private $objMysql;
    private $arrayValidationErrors;

    /**
     * The value for the tas_uid field.
     * @var        int
     */
    private $taskId;
    private $triggerType;

    public function __construct ($taskId)
    {
        $this->taskId = $taskId;
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $arrTrigger = array(
            "moveTo" => array(
                "field" => $this->getField (),
                "conditionValue" => $this->getConditionValue (),
                "trigger_type" => $this->getTriggerType (),
                "step_to" => $this->getStep_to (),
                "else" => $this->else,
                "condition" => $this->condition,
                "workflow_id" => $this->getWorkflowId ()
            )
        );

        $this->objMysql->_update (
                "workflow.status_mapping", array(
            "step_trigger" => json_encode ($arrTrigger)
                ), array(
            "id" => $this->taskId
                )
        );
    }

    /**
     * Get the [workflowId] column value.
     * 
     * @return     int
     */
    public function getWorkflowId ()
    {
        return $this->workflowId;
    }

    /**
     * Get the [step_to] column value.
     * 
     * @return     int
     */
    public function getStep_to ()
    {
        return $this->step_to;
    }

    public function getTriggerType ()
    {
        return $this->triggerType;
    }

    public function setTriggerType ($triggerType)
    {
        $this->triggerType = $triggerType;
    }

    public function getField ()
    {
        return $this->field;
    }

    public function getCondition ()
    {
        return $this->condition;
    }

    public function getConditionValue ()
    {
        return $this->conditionValue;
    }

    public function getElse ()
    {
        return $this->else;
    }

    public function setWorkflowId ($workflowId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $workflowId !== null && !is_int ($workflowId) && is_numeric ($workflowId) )
        {
            $workflowId = (int) $workflowId;
        }
        if ( $this->workflowId !== $workflowId || $workflowId === 0 )
        {
            $this->workflowId = $workflowId;
        }
    }

    public function setStep_to ($step_to)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $step_to !== null && !is_int ($step_to) && is_numeric ($step_to) )
        {
            $step_to = (int) $step_to;
        }
        if ( $this->step_to !== $step_to || $step_to === 0 )
        {
            $this->step_to = $step_to;
        }
    }

    public function setField ($field)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $field !== null && !is_string ($field) )
        {
            $field = (string) $field;
        }
        if ( $this->field !== $field || $field === '' )
        {
            $this->field = $field;
        }
    }

    public function setCondition ($condition)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $condition !== null && !is_string ($condition) )
        {
            $condition = (string) $condition;
        }
        if ( $this->condition !== $condition || $condition === '' )
        {
            $this->condition = $condition;
        }
    }

    public function setConditionValue ($conditionValue)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $conditionValue !== null && !is_string ($conditionValue) )
        {
            $conditionValue = (string) $conditionValue;
        }
        if ( $this->conditionValue !== $conditionValue || $conditionValue === '' )
        {
            $this->conditionValue = $conditionValue;
        }
    }

    public function setElse ($else)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $else !== null && !is_string ($else) )
        {
            $else = (string) $else;
        }
        if ( $this->else !== $else || $else === '' )
        {
            $this->else = $else;
        }
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

        if ( trim ($this->step_to) === "" )
        {
            $this->arrayValidationErrors[] = "STEP ID IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->taskId) === "" )
        {
            $this->arrayValidationErrors[] = "ID IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->triggerType) === "" )
        {
            $this->arrayValidationErrors[] = "TYPE IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->else) === "" )
        {
            $this->arrayValidationErrors[] = "ELSE IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->field) === "" )
        {
            $this->arrayValidationErrors[] = "FIELD IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->condition) === "" )
        {
            $this->arrayValidationErrors[] = "CONDITION IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->conditionValue) === "" )
        {
            echo $this->conditionValue;
            die;
            $this->arrayValidationErrors[] = "CONDITION VALUE IS MISSING";
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function getArrayValidationErrors ()
    {
        return $this->arrayValidationErrors;
    }

    public function setArrayValidationErrors ($arrayValidationErrors)
    {
        $this->arrayValidationErrors = $arrayValidationErrors;
    }

}
