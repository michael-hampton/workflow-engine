<?php

namespace BusinessModel;

class StepTrigger
{

    public $arrWorkflowObject = array();
    private $objMysql;
    private $elementId;
    public $blMove = false;
    private $workflowId;
    private $currentStep;
    private $nextStep;
    public $blAddedCase = false;

    /**
     * 
     * @param type $nextStep
     */
    public function __construct ($currentStep = null, $nextStep = null)
    {

        $this->objMysql = new \Mysql2();

        $this->currentStep = $currentStep;
        $this->nextStep = $nextStep;
    }

    /**
     * 
     * @return boolean
     */
    public function getTriggers ()
    {
        $arrTriggers = $this->getAllTriggersForStep ();

        // check gateways

        $objGateway = new \BusinessModel\StepGateway (new \Task ($this->currentStep));
        $arrGateways = $objGateway->getGateways ();

        if ( !empty ($arrGateways) )
        {
            $arrTriggers = array_merge ($arrTriggers, $arrGateways);
        }

        return $arrTriggers;
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
    public function checkTriggers ($objMike, \Users $objUser)
    {
        $this->elementId = $objMike->getId ();

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
            $this->currentStep = $this->arrWorkflowObject['elements'][$this->elementId]['current_step'];
            $this->workflowId = $this->arrWorkflowObject['elements'][$this->elementId]['workflow_id'];
        }
        else
        {
            $this->blMove = true;
            $this->currentStep = $this->arrWorkflowObject['current_step'];
            $this->workflowId = $this->arrWorkflowObject['workflow_id'];
        }

        $arrTriggers = $this->getTriggers ();
        $blHasTrigger = false;

        $objCase = new Cases();

        foreach ($arrTriggers as $arrTrigger) {

            $arrTrigger['event_type'] = isset ($arrTrigger['event_type']) && trim ($arrTrigger['event_type']) !== "" ? $arrTrigger['event_type'] : "INTERMEDIATE";

            switch ($arrTrigger['event_type']) {
                case "START":

                    $projectId = $this->parentId;
                    $workflowTo = $arrTrigger['workflow_to'];

                    $objWorkflow = new \Workflow ($workflowTo);
                    $objCase->addCase ($objWorkflow, $objUser, array(), array(), false, $projectId);
                    $this->blAddedCase = true;
                    break;

                default:
                    $strTriggerType = null;

                    $workflow = isset ($arrTrigger['workflow_from']) ? $arrTrigger['workflow_from'] : $arrTrigger['workflow_id'];
                    $triggerType = isset ($arrTrigger['trigger_type']) ? $arrTrigger['trigger_type'] : '';

                    if ( $triggerType === "sendMail" )
                    {
                        switch ($arrTrigger["event_type"]) {
                            case "claimCase":
                                $template = PATH_DATA_MAILTEMPLATES . "claimCase.html";
                                $content = file_get_contents ($template);
                                $subject = "CASE HAS BEEN CLAIMED BY [USER]";
                                break;
                        }

                        $objSendNotification = new \SendNotification();
                        $objSendNotification->setProjectId ($this->parentId);
                        $recipients = $objSendNotification->getTaskUsers ();

                        if ( empty ($recipients) )
                        {
                            return false;
                        }

                        $recipients = implode (",", $recipients);

                        $Fields = $objCase->getCaseVariables ((int) $this->elementId, (int) $this->parentId, (int) $this->currentStep);

                        if ( trim ($content) !== "" && trim ($subject) !== "" )
                        {
                            $subject = $objCase->replaceDataField ($subject, $Fields);
                            $body = $objCase->replaceDataField ($content, $Fields);
                            
                            $objSendNotification->notificationEmail($recipients, $subject, $body);
                        }
                    }

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
                                    if ( $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'] == $arrTrigger['workflow_to'] || $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'] == $arrTrigger['workflow_id']
                                    )
                                    {
                                        $this->arrWorkflowObject['elements'][$this->parentId]['current_step'] = $arrTrigger['step_to'];
                                    }
                                }
                            }
                            elseif ( $triggerType == "workflow" )
                            {
                                $this->arrWorkflowObject['workflow_id'] = $arrTrigger['workflow_to'];
                            }
                            elseif ( $triggerType == "gateway" )
                            {
                                $objGateway = new \BusinessModel\StepGateway (new \Task ($this->arrWorkflowObject['elements'][$this->parentId]['current_step']));
                                $this->arrWorkflowObject = $objGateway->updateStep ($arrTrigger, $this->arrWorkflowObject, $objMike);
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
                                    $objGateway = new \BusinessModel\StepGateway (null);
                                    $this->arrWorkflowObject = $objGateway->updateStep ($arrTrigger, $this->arrWorkflowObject, $objMike);

                                    $blHasTrigger = true;
                                    $this->blMove = false;
                                }
                            }
                        }
                    }

                    break;
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

        $criteria = $this->getTriggerCriteria ();

        $criteria .= " WHERE step_id = ?";
        $criteria .= " ORDER BY title ASC";

        $result = $this->objMysql->_query ($criteria, array($this->currentStep));

        if ( empty ($result) )
        {
            return [];
        }

        return $result;
    }

    /**
     * Save Data for Trigger
     * @var string $sProcessUID. Uid for Process
     * @var string $dataTrigger. Data for Trigger
     * @var string $create. Create o Update Trigger
     * @var string $sTriggerUid. Uid for Trigger
     *
     * @return array
     */
    public function create ($stepUid = '', $dataTrigger = array(), $create = false, $sTriggerUid = '')
    {
        try {

            if ( ($stepUid == '') || (count ($dataTrigger) == 0) )
            {
                return false;
            }

            if ( !$this->stepExists ($stepUid) )
            {
                throw new \Exception ("ID_STEP_DOES_NOT_EXIST");
            }

            if ( $create && (isset ($dataTrigger['triggerId'])) )
            {
                unset ($dataTrigger['triggerId']);
            }

            $dataTrigger = (array) $dataTrigger;

            if ( isset ($dataTrigger['title']) )
            {
                if ( $this->verifyNameTrigger ($stepUid, $dataTrigger['title'], $sTriggerUid) )
                {
                    throw new \Exception ("ID_CANT_SAVE_TRIGGER");
                }
            }

            $dataTrigger['step_id'] = $stepUid;

            $oTrigger = new \Trigger ($stepUid);

            if ( $create )
            {
                $oTrigger->create ($stepUid, $dataTrigger);
                $dataTrigger['TRI_UID'] = $oTrigger->getTriggerId ();
            }
            else
            {
                $oTrigger->update ($dataTrigger);
            }

            return true;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Delete Trigger
     * @var string $sTriggerUID. Uid for Trigger
     * @return void
     */
    public function deleteTrigger ($sTriggerUID)
    {
        $oTrigger = new \Trigger();
        $oTrigger->remove ($sTriggerUID);
        //$oStepTrigger->removeTrigger( $sTriggerUID );
    }

    /**
     * Verify name for trigger in process
     * @var string $stepId. Uid for Step
     * @var string $sTriggerName. Name for Trigger
     * @var string $sTriggerUid. Uid for Trigger
     *
     * @return boolean
     */
    public function verifyNameTrigger ($stepId, $sTriggerName, $sTriggerUid = '')
    {
        $arrWhere = array();

        $sql = "SELECT id from workflow.step_trigger WHERE step_id = ? AND title = ?";
        $arrWhere[] = $stepId;
        $arrWhere[] = $sTriggerName;


        if ( $sTriggerUid != '' )
        {
            $sql .= " AND id != ?";
            $arrWhere[] = $sTriggerUid;
        }

        $result = $this->objMysql->_query ($sql, $arrWhere);

        return (isset ($result[0]) && !empty ($result[0])) ? true : false;
    }

    /**
     * get trigger by id
     * @param type $id
     * @return type
     */
    public function retrieveByPK ($id)
    {
        $result = $this->objMysql->_select ("workflow.step_trigger", array(), array("step_id" => $id));
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
                throw (new \Exception ("The row '$StepUid, $TasUid, $TriUid, $StType' in table StepTrigger doesn't exist!"));
            }
        } catch (Exception $oError) {
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
            if ( isset ($oObj[0]) && !empty ($oObj[0]) )
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

    /**
     * Get criteria for Trigger
     *
     * return object
     */
    public function getTriggerCriteria ()
    {
        try {
            $criteria = "SELECT title, event_type, description, id, workflow_id, workflow_to, trigger_type, step_to, step_id, workflow_from FROM workflow.step_trigger";
            return $criteria;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data for TriggerUid
     * @var int $sTriggerUID. Uid for Trigger
     *
     *
     * @return array
     */
    public function getDataTrigger ($sTriggerUID = NULL)
    {
        $triggerO = new \Trigger();
        $triggerArray = $triggerO->load ($sTriggerUID);

        return $triggerArray;
    }

}
