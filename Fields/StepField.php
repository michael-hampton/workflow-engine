<?php

class StepField
{

    private $fieldId;
    private $stepId;
    private $isDisabled;
    private $orderId;
    private $objMysql;
    
    public function __construct ($stepId, $fieldId = null)
    {
        $this->fieldId = $fieldId;
        $this->stepId = $stepId;
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getFieldId ()
    {
        return $this->fieldId;
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function getIsDisabled ()
    {
        return $this->isDisabled;
    }

    public function getOrderId ()
    {
        return $this->orderId;
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
     * @param type $stepId
     */
    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    /**
     * 
     * @param type $isDisabled
     */
    public function setIsDisabled ($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * 
     * @param type $orderId
     */
    public function setOrderId ($orderId)
    {
        $this->orderId = $orderId;
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->stepId) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->fieldId) === "" )
        {
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_insert (
                "workflow.step_fields", array(
            "field_id" => $this->fieldId,
            "step_id" => $this->stepId,
            "is_disabled" => $this->isDisabled,
            "order_id" => $this->orderId
                )
        );
    }

    public function delete ()
    {
        if ( $this->validate () === false )
        {
            return false;
        }

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_delete ("workflow.step_fields", array("step_id" => $this->stepId));
    }

}
