<?php

class StepTrigger extends Trigger
{

    public $arrWorkflowObject = array();
    private $objMysql;
    private $elementId;
    public $blMove = false;
    private $nextStep;

    /**
     * 
     * @param type $nextStep
     */
    public function __construct ($nextStep = null)
    {
        parent::__construct ();

        $this->objMysql = new Mysql2();

        $this->nextStep = $nextStep;
    }

    /**
     * 
     * @return boolean
     */
    public function getTriggers ()
    {
        $arrTriggers = $this->objMysql->_select ("workflow.status_mapping", array("step_trigger"), array("id" => $this->_workflowStepId));

        if ( $this->nextStep !== null )
        {
            $nextTrigger = $this->objMysql->_select ("workflow.status_mapping", array("step_trigger"), array("id" => $this->nextStep));
            
            if ( isset ($nextTrigger[0]['step_trigger']) )
            {
                $stepTrigger = json_decode ($nextTrigger[0]['step_trigger'], true);

                if ( isset ($stepTrigger['moveTo']) && $stepTrigger['moveTo']['trigger_type'] == "gateway" )
                {
                    $arrTriggers = $nextTrigger;
                }
            }
        }

        if ( !empty ($arrTriggers) )
        {

            $arrTrigger = json_decode ($arrTriggers[0]['step_trigger'], true);

            if ( !isset ($arrTrigger['moveTo']) || !isset ($arrTrigger['moveTo']['trigger_type']) )
            {
                return false;
            }

            switch ($arrTrigger['moveTo']['trigger_type']) {
                case "step":
                    return array(
                        "step_to" => $arrTrigger['moveTo']['step_to'],
                        "workflow_from" => $arrTrigger['moveTo']['workflow_id'],
                        "trigger_type" => "step"
                    );
                    break;

                case "workflow":
                    return array(
                        "workflow_to" => $arrTrigger['moveTo']['workflow_to'],
                        "workflow_from" => $arrTrigger['moveTo']['workflow_id'],
                        "trigger_type" => "workflow"
                    );
                    break;
                default :
                    return $arrTrigger;
                    break;
            }
        }

        return false;
    }

    /**
     * 
     * @return boolean
     */
    private function getWorkflowData ()
    {

        if ( is_numeric ($this->parentId) )
        {
            $result = $this->objMysql->_select ("workflow.workflow_data", array("workflow_data", "audit_data", "id"), array("object_id" => $this->parentId));

            if ( empty ($result) )
            {
                return false;
            }

            $this->objectId = $result[0]['id'];

            return $result;
        }
        else
        {
            //$result = $objMysql->_select ("task_manager.projects", array("workflow_data", "audit_data"), array("id" => $this->id));
        }
    }

    /**
     * 
     * @param type $objMike
     * @return boolean
     */
    public function checkTriggers ($objMike)
    {
        $this->elementId = $objMike->getId ();

        $arrWorkflow = array();

        if ( method_exists ($objMike, "getParentId") )
        {
            $this->parentId = $objMike->getParentId ();
        }
        else
        {
            $this->parentId = $objMike->getId ();
        }

        $arrWorkflowData = $this->getWorkflowData ();
        $this->arrWorkflowObject = json_decode ($arrWorkflowData[0]['workflow_data'], true);

        if ( isset ($this->arrWorkflowObject['elements'][$this->elementId]) )
        {
            $this->_workflowStepId = $this->arrWorkflowObject['elements'][$this->elementId]['current_step'];
        }
        else
        {
            $this->blMove = true;
            $this->_workflowStepId = $this->arrWorkflowObject['current_step'];
        }

        $arrTrigger = $this->getTriggers ();

        $blHasTrigger = false;
        $strTriggerType = null;

        $workflow = isset ($arrTrigger['workflow_from']) ? $arrTrigger['workflow_from'] : $arrTrigger['moveTo']['workflow_id'];
        $triggerType = isset ($arrTrigger['trigger_type']) ? $arrTrigger['trigger_type'] : $arrTrigger['moveTo']['trigger_type'];


        if ( $arrTrigger !== false && !empty ($arrTrigger) )
        {
            if ( $workflow == $this->arrWorkflowObject['workflow_id'] )
            {
                if ( $triggerType == "step" )
                {
                    $this->arrWorkflowObject['current_step'] = $arrTrigger['step_to'];
                    $blHasTrigger = true;
                    $this->blMove = true;

                    if ( isset ($this->arrWorkflowObject['elements'][$this->parentId]) )
                    {

                        if ( $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'] == $arrTrigger['workflow_from'] )
                        {
                            $this->arrWorkflowObject['elements'][$this->parentId]['current_step'] = $arrTrigger['step_to'];
                            $blHasTrigger = true;
                        }
                    }
                }
                elseif ( $triggerType == "workflow" )
                {
                    $this->arrWorkflowObject['workflow_id'] = $arrTrigger['workflow_to'];
                }
                elseif ( $triggerType == "gateway" )
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
                                 if(isset($this->arrWorkflowObject['elements'][$this->elementId])) {
                                    $this->arrWorkflowObject['elements'][$this->elementId]['current_step'] = $trueField;
                                }
                            }
                            else
                            {
    
                                if(isset($this->arrWorkflowObject['elements'][$this->elementId])) {
                                    $this->arrWorkflowObject['elements'][$this->elementId]['current_step'] = $falseField;
                                }
                            }
                            break;
                    }
                    
                     $blHasTrigger = true;
                        $this->blMove = false;
                }
            }
            else
            {
                if ( $this->arrWorkflowObject['elements'][$this->elementId]['workflow_id'] == $workflow )
                {
                    if ( $triggerType == "step" )
                    {
                        $this->arrWorkflowObject['elements'][$this->elementId]['current_step'] = $arrTrigger['step_to'];
                        $strTriggerType = "step";
                        $this->blMove = true;
                        $blHasTrigger = true;
                    }
                    elseif ( $triggerType == "workflow" )
                    {
                        $this->arrWorkflowObject['elements'][$this->elementId]['workflow_id'] = $arrTrigger['workflow_to'];
                        $strTriggerType = "workflow";
                    }
                    elseif ( $triggerType == "gateway" )
                    {
                        $arrField = $this->objMysql->_select ("workflow.fields", array(), array("field_id" => $arrTrigger['moveTo']['field']));
                        $strField = $arrField[0]['field_identifier'];
                        $strValue = $objMike->arrElement[$strField];
                        $conditionalValue = $arrTrigger['moveTo']['conditionValue'];
                        $trueField = $arrTrigger['moveTo']['step_to'];
                        $falseField = $arrTrigger['moveTo']['else'];

                        switch ($arrTrigger['moveTo']['condition']) {
                            case "=":
                                if ( trim ($strValue) == trim ($conditionalValue) )
                                {
                                    $this->arrWorkflowObject['elements'][$this->elementId]['current_step'] = $trueField;
                                }
                                else
                                {
                                    $this->arrWorkflowObject['elements'][$this->elementId]['current_step'] = $falseField;
                                }
                                break;
                        }

                        $blHasTrigger = true;
                        $this->blMove = false;
                    }
                }
            }
        }

        return $blHasTrigger;
    }

    /**
     * 
     * @param type $step
     * @return boolean
     */
    private function stepExists ($step)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("id" => $step));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return false;
    }

    public function getAllTriggersForStep ()
    {
        if ( !$this->stepTriggerExists ($this->nextStep) )
        {
            //throw new Exception("ID_RECORD_DOES_NOT_EXIST_IN_TABLE");
        }

        $arrTriggers = $this->objMysql->_select ("workflow.status_mapping", array("step_trigger"), array("id" => $this->nextStep), array());

        if ( empty ($arrTriggers) )
        {
            return [];
        }

        $arrTriggers = json_decode ($arrTriggers[0]['step_trigger'], true);

        return $arrTriggers;
    }

    /**
     * Assign Trigger to a Step
     *
     * @param string $stepUid    Unique id of Step
     * @param array  $aData  Data
     *
     * return array Data of the Trigger assigned to a Step
     */
    public function create ($stepUid, $aData)
    {
        try {

            if ( !$this->stepExists ($stepUid) )
            {
                throw new Exception ("ID_STEP_DOES_NOT_EXIST");
            }

            $objects = $this->objMysql->_select ("workflow.status_mapping", array(), array("id" => $stepUid));

            foreach ($objects as $row) {

                if ( !empty ($row['step_trigger']) )
                {
                    $this->remove ($stepUid);
                }
            }


            if ( $aData['trigger_type'] === "gateway" )
            {
                $objClass = new Gateway ($stepUid);

                $objClass->setCondition ($aData['condition']);
                $objClass->setConditionValue ($aData['conditionValue']);
                $objClass->setElse ($aData['else']);
                $objClass->setField ($aData['field']);
                $objClass->setStep_to ($aData['step_to']);
                $objClass->setTriggerType ($aData['trigger_type']);
                $objClass->setWorkflowId ($aData['workflow_id']);
            }
            else
            {
                $objClass = $this;

                $objClass->setStepTo ($aData['step_to']);
                $objClass->setWorkflowId ($aData['workflow_id']);
                $objClass->setId ($stepUid);
                $objClass->setTriggerType ($aData['trigger_type']);
            }

            if ( $objClass->validate () )
            {
                $result = $objClass->save ();
                return $result;
            }
            else
            {
                throw (new Exception ("Failed Validation in class " . get_class ($this) . "."));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * get trigger by id
     * @param type $id
     * @return type
     */
    private function retrieveByPK ($id)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("id" => $id));
        return $result;
    }

    /**
     * remove triggers from step
     * @param type $StepUid
     * @return type
     * @throws type
     */
    public function remove ($StepUid)
    {
        try {
            $oStepTrigger = $this->retrieveByPK ($StepUid);
            if ( isset ($oStepTrigger[0]) && !empty ($oStepTrigger[0]) )
            {
                $iResult = $this->delete ($StepUid);
                return $iResult;
            }
            else
            {
                throw (new Exception ("The row '$StepUid, $TasUid, $TriUid, $StType' in table StepTrigger doesn't exist!"));
            }
        } catch (Exception $oError) {
            $oConnection->rollback ();
            throw ($oError);
        }
    }

    /**
     * check if step has a trigger
     * @param type $StepUid
     * @return boolean
     * @throws type
     */
    public function stepTriggerExists ($StepUid)
    {
        try {
            $oObj = $this->retrieveByPK ($StepUid);
            if ( isset ($oStepTrigger[0]) && !empty ($oStepTrigger[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
