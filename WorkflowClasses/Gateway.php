<?php

/**
 * Description of Gateway
 *
 * @author michael.hampton
 */
class Gateway extends BaseGateway
{

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
    
    public function updateStep($arrTrigger, $arrWorkflowObject)
    {
          $arrField = $this->objMysql->_select ("workflow.fields", array(), array("field_identifier" => trim ($arrTrigger['moveTo']['field'])));
                    if ( empty ($arrField) )
                    {
                        throw new Exception ("Field cannot be found");
                    }
                    $strField = $arrField[0]['field_identifier'];
                    $strValue = $objMike->arrElement[$strField];
                    $conditionalValue = $arrTrigger['moveTo']['conditionValue'];
                    $trueField = $arrTrigger['moveTo']['step_to'];
                    $falseField = $arrTrigger['moveTo']['else'];
                    switch ($arrTrigger['moveTo']['condition']) {
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
    
    }

}
