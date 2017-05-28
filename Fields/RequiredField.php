<?php

class RequiredField
{

    private $stepId;
    private $fieldId;
    private $objMysql;
    private $workflowId;
    private $arrayValidation;

    public function __construct ($stepId, $fieldId, $workflowId)
    {
        $this->stepId = $stepId;
        $this->fieldId = $fieldId;
        $this->workflowId = $workflowId;
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }
    
    public function getStepId ()
    {
        return $this->stepId;
    }

    public function getFieldId ()
    {
        return $this->fieldId;
    }

    public function getWorkflowId ()
    {
        return $this->workflowId;
    }

    public function getArrayValidation ()
    {
        return $this->arrayValidation;
    }

    /**
     * 
     * @param type $stepId
     */
    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    /**
     * 
     * @param type $fieldId
     */
    public function setFieldId ($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    /**
     * 
     * @param type $workflowId
     */
    public function setWorkflowId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     * 
     * @param type $arrayValidation
     */
    public function setArrayValidation ($arrayValidation)
    {
        $this->arrayValidation = $arrayValidation;
    }

    
    /**
     * 
     * @param type $fieldId
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_insert ("workflow.required_fields", array(
            "step_id" => $this->stepId,
            "field_id" => $this->fieldId,
            "workflow_id" => $this->workflowId));
    }

    public function checkRequiredField ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.required_fields", array(), array("workflow_id" => $this->workflowId, "step_id" => $this->stepId, "field_id" => $this->fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }

        return [];
    }

    public function validate ()
    {
        $errorCounter = 0;

        if ( trim ($this->workflowId) === "" )
        {
            $this->arrayValidation[] = "Workflow Id is missing";
            $errorCounter++;
        }

        if ( trim ($this->fieldId) == "" )
        {
            $this->arrayValidation[] = "Field Id is missing";
            $errorCounter++;
        }

        if ( trim ($this->stepId) == "" )
        {
            $this->arrayValidation[] = "Step Id is missing";
            $errorCounter++;
        }

        if ( $errorCounter === false )
        {
            return false;
        }

        return true;
    }

    public function delete ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( !empty ($this->checkRequiredField ($this->fieldId)) )
        {
            throw new Exception ('row doesnt exist.');
        }

        $this->objMysql->_delete ("workflow.required_fields", array("step_id" => $this->stepId, "workflow_id" => $this->workflowId, "field_id" => $this->fieldId));
    }

    public function removeAllRequiredFieldsFromStep ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_delete ("workflow.required_fields", array("step_id" => $this->stepId));
    }

}
