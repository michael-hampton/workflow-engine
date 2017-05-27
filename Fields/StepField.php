<?php

class StepField
{

    private $fieldId;
    private $stepId;
    private $isDisabled;
    private $orderId;

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

        if ( trim ($this->orderId) === "" )
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
        $this->objMysql->_insert (
                "workflow.step_fields", array(
            "field_id" => $this->fieldId,
            "step_id" => $this->stepId,
            "is_disabled" => $this->isDisabled,
            "order_id" => $this->orderId
                )
        );
    }

}
