<?php

class StepField
{

    private $fieldId;
    private $stepId;
    private $isDisabled;
    private $orderId;
    private $objMysql;
    private $arrayValidation;

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

    public function getArrayValidation ()
    {
        return $this->arrayValidation;
    }

    public function setArrayValidation ($arrayValidation)
    {
        $this->arrayValidation = $arrayValidation;
    }

    public function checkRequiredFields ($fields)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $mandatoryFields = [26, 27, 29];

        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_from" => $this->stepId));

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return FALSE;
        }

        $firstStep = $result[0]['first_step'];

        if ( $firstStep === 1 )
        {
            foreach ($mandatoryFields as $mandatoryField) {
                if ( !in_array ($mandatoryField, $fields) )
                {   
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Saves multiple fields to a step
     *
     * @param array $aData Fields with field ids
     * @return string
     */
    public function create ($arrFields)
    {
        try {
            // check all mandatory fields are present
            if ( !$this->checkRequiredFields ($arrFields) )
            {
                throw new Exception ("There are some required fields missing");
            }

            foreach ($arrFields as $key => $fieldId) {
                $this->setFieldId ($fieldId);
                $this->setOrderId ($key);
                $this->setIsDisabled (0);

                if ( $this->validate () )
                {
                    $this->save ();
                    unset ($this->fieldId);
                    unset ($this->orderId);
                    unset ($this->isDisabled);
                }
                else
                {
                    $msg = '';
                    foreach ($this->getArrayValidation () as $strMessage) {
                        $msg .= $strMessage . "<br/>";
                    }
                    throw (new Exception ('The row cannot be created! ' . $msg));
                }
            }
        } catch (Exception $ex) {
            throw ($ex);
        }
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->stepId) === "" )
        {
            $this->arrayValidation[] = "Step Id is missing";
            $errorCount++;
        }

        if ( trim ($this->fieldId) === "" )
        {
            $this->arrayValidation[] = "Field id is missing";
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
