<?php

/**
 * Description of Gateway
 *
 * @author michael.hampton
 */
class Gateway extends BaseGateway
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function create ($aData)
    {
        try {

            $this->setCondition ($aData['condition']);
            $this->setConditionValue ($aData['conditionValue']);
            $this->setElse ($aData['else']);
            $this->setField ($aData['field']);
            $this->setStep_to ($aData['next_step']);
            $this->setTriggerType ($aData['trigger_type']);
            $this->setWorkflowId ($aData['workflow_id']);

            if ( $this->validate () )
            {
                $iResult = $this->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $this->getArrayValidationErrors ();
                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }
    
     public function update ($fields)
    {
        try {
             $this->setCondition ($aData['condition']);
            $this->setConditionValue ($aData['conditionValue']);
            $this->setElse ($aData['else']);
            $this->setField ($aData['field']);
            $this->setStep_to ($aData['next_step']);
            $this->setTriggerType ($aData['trigger_type']);
            $this->setWorkflowId ($aData['workflow_id']);
            
            if ($this->validate()) {
                $result = $this->save();
                return $result;
            } else {
                throw (new Exception( "Failed Validation in class " . get_class( $this ) . "." ));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function updateStep ($arrTrigger, $arrWorkflowObject, $objMike)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }
        
        $this->elementId = $objMike->getId ();

        $arrField = $this->objMysql->_select ("workflow.fields", array(), array("field_identifier" => trim ($arrTrigger['field_name'])));
        if ( empty ($arrField) )
        {
            throw new Exception ("Field cannot be found");
        }
        $strField = $arrField[0]['field_identifier'];
        $strValue = $objMike->arrElement[$strField];
        $conditionalValue = $arrTrigger['conditionValue'];
        $trueField = $arrTrigger['step_to'];
        $falseField = $arrTrigger['else_step'];
        switch ($arrTrigger['condition_type']) {
            case "=":
                if ( trim ($strValue) == trim ($conditionalValue) )
                {
                    if ( isset ($arrWorkflowObject['elements'][$this->elementId]) )
                    {
                        $arrWorkflowObject['elements'][$this->elementId]['current_step'] = $trueField;
                    }
                }
                else
                {
                    if ( isset ($arrWorkflowObject['elements'][$this->elementId]) )
                    {
                        $arrWorkflowObject['elements'][$this->elementId]['current_step'] = $falseField;
                    }
                }
                break;
        }

        return $arrWorkflowObject;
    }

}
