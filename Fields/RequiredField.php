<?php

class RequiredField
{

    private $stepId;
    private $fieldId;
    private $objMysql;
    private $workflowId;

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
