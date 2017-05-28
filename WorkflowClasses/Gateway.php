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

}
