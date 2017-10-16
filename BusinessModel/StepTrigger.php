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

            if ( !isset ($result[0]) || empty ($result[0]) )
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
     * List of Triggers in process
     * @var string $sProcessUID. Uid for Process
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return array
     */
    public function getTriggerList ($sProcessUID = '')
    {
        $arrWhere = [];

        if ( $sProcessUID !== "" )
        {
            $arrWhere = array("workflow_id" => $sProcessUID);
        }

        $results = $this->objMysql->_select ("workflow.step_trigger", [], $arrWhere, ["title" => "ASC"]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        $arrTriggers = [];

        foreach ($results as $result) {
            $objTrigger = new \Trigger();
            $objTrigger->setDescription ($result['description']);
            $objTrigger->setTitle ($result['title']);
            $objTrigger->setWorkflowId ($result['workflow_id']);
            $objTrigger->setTriggerType ($result['trigger_type']);
            $objTrigger->setId ($result['id']);

            $arrTriggers[] = $objTrigger;
        }

        return $arrTriggers;
    }

    /**
     * 
     * @param type $objMike
     * @return boolean
     */
    public function checkTriggers (\WorkflowStep $objWorkflowStep, $objMike, \Users $objUser)
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

        if ( $arrWorkflowData === false )
        {
            return false;
        }

        $this->arrWorkflowObject = json_decode ($arrWorkflowData[0]['workflow_data'], true);

        if ( isset ($this->arrWorkflowObject['elements'][$this->elementId]) )
        {
            $this->currentStep = $this->arrWorkflowObject['elements'][$this->elementId]['current_step'];
            $this->workflowId = $this->arrWorkflowObject['elements'][$this->elementId]['workflow_id'];
        }
        else
        {
            $this->blMove = true;
            $this->currentStep = $this->arrWorkflowObject['elements'][$this->parentId]['current_step'];
            $this->workflowId = $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'];
        }

        $arrTriggers = $this->getTriggers ();
        $blHasTrigger = false;

        $objCase = new Cases();

        foreach ($arrTriggers as $arrTrigger) {

            $arrTrigger['event_type'] = isset ($arrTrigger['event_type']) && trim ($arrTrigger['event_type']) !== "" ? $arrTrigger['event_type'] : "INTERMEDIATE";

            switch ($arrTrigger['event_type']) {

                case "script":

                    break;

                case "START":

                    $projectId = $this->parentId;
                    $workflowTo = $arrTrigger['workflow_to'];

                    $objWorkflow = new \Workflow ($workflowTo);
                    $arrCase = $objCase->addCase ($objWorkflow, $objUser, array(), array(), false, $projectId, true);
                    $this->blAddedCase = true;

                    (new \Log (LOG_FILE))->log (
                            array(
                        "message" => "CREATED NEW CASE BY TRIGGER",
                        'case_id' => $arrCase['case_id'],
                        'project_id' => $this->parentId,
                        'user' => $objUser->getUsername (),
                        'workflow_id' => $this->workflowId,
                        'step_id' => $this->currentStep
                            ), \Log::NOTICE);

                    break;

                default:
                    $strTriggerType = null;

                    $workflow = isset ($arrTrigger['workflow_from']) ? $arrTrigger['workflow_from'] : $arrTrigger['workflow_id'];
                    $triggerType = isset ($arrTrigger['trigger_type']) ? $arrTrigger['trigger_type'] : '';

                    if ( $triggerType === "sendMail" )
                    {
                        $templateName = str_replace (" ", "_", $arrTrigger['template_name']);
                        $objTask = new \Task();
                        $objTask->setTasUid ($objWorkflowStep->getStepId ());

                        $this->executeSendMail ($objUser, $objTask, $templateName);
                    }

                    if ( $arrTrigger !== false && !empty ($arrTrigger) )
                    {

                        if ( $workflow == $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'] )
                        {
                            if ( $triggerType == "step" )
                            {
                                $objTask = (new \Task())->retrieveByPk ($objWorkflowStep->getStepId ());
                                $objTask->setStepId ($arrTrigger['step_to']);
                                (new \AppDelegation())->createAppDelegation ($objWorkflowStep, new \Save ($this->parentId), $objUser, $objTask, 3, false, -1, null, false, "STEP_COMPLETE", "COMPLETE");

                                (new \Log (LOG_FILE))->log (
                                        array(
                                    "message" => "Trigger executed - Updated Step",
                                    'case_id' => $this->elementId,
                                    'project_id' => $this->parentId,
                                    'user' => $objUser->getUsername (),
                                    'workflow_id' => $objWorkflowStep->getWorkflowId (),
                                    'step_id' => $arrTrigger['step_to']
                                        ), \Log::NOTICE);

                                return false;
                            }
                            elseif ( $triggerType == "workflow" )
                            {
                                $this->arrWorkflowObject['elements'][$this->parentId]['workflow_id'] = $arrTrigger['workflow_to'];
                            }
                            elseif ( $triggerType == "gateway" )
                            {
                                $objGateway = new \BusinessModel\StepGateway (new \Task ($this->arrWorkflowObject['elements'][$this->parentId]['current_step']));
                                $this->arrWorkflowObject = $objGateway->updateStep ($arrTrigger, $this->arrWorkflowObject, $objMike);

                                //$objTask = (new \Task())->retrieveByPk ($objWorkflowStep->getStepId ());
                                //(new \AppDelegation())->createAppDelegation ($objWorkflowStep, $objMike, $objUser, $objTask, $objWorkflowStep->getStepId (), 3, false, -1, $this->arrWorkflowObject['elements'][$this->parentId]['current_step'], null, false, "STEP_COMPLETE", "COMPLETE");

                                $blHasTrigger = true;
                                $this->blMove = true;

                                (new \Log (LOG_FILE))->log (
                                        array(
                                    "message" => "Gateway Updated Step",
                                    'case_id' => $this->elementId,
                                    'project_id' => $this->parentId,
                                    'user' => $objUser->getUsername (),
                                    'workflow_id' => $workflow,
                                    'step_id' => $this->arrWorkflowObject['elements'][$this->parentId]['current_step']
                                        ), \Log::NOTICE);
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

                                    (new \Log (LOG_FILE))->log (
                                            array(
                                        "message" => "Gateway Updated Step",
                                        'case_id' => $this->elementId,
                                        'project_id' => $this->parentId,
                                        'user' => $objUser->getUsername (),
                                        'workflow_id' => $this->arrWorkflowObject['elements'][$this->elementId]['workflow_id'],
                                        'step_id' => $this->arrWorkflowObject['elements'][$this->elementId]['current_step']
                                            ), \Log::NOTICE);

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
                return $dataTrigger;
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
                throw (new \Exception ("The row '$StepUid, $TriUid, $StType' in table StepTrigger doesn't exist!"));
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
            $criteria = "SELECT title, event_type, description, template_name, id, workflow_id, workflow_to, trigger_type, step_to, step_id, workflow_from FROM workflow.step_trigger";
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
    public function getDataTrigger ($sTriggerUID = null)
    {
        $triggerO = new \Trigger();
        $triggerArray = $triggerO->load ($sTriggerUID);

        return $triggerArray;
    }

    public function sendComment (\Users $objUser, $comment, $objMike)
    {
        $id = method_exists ($objMike, "getParentId") ? $objMike->getParentId () : $objMike->getId ();
        (new \Comments())->addCaseNote ($id, $objUser->getUsername (), $comment, 1);

        return true;
    }

    public function executeSendMail (\Users $objUser, \Task $objTask, $templateName = null, $id = null)
    {
        if ( $templateName === null && $id === null )
        {
            return false;
        }

        if ( $templateName === null && $id !== null )
        {
            $arrTrigger = $this->getDataTrigger ($id);
            $templateName = $arrTrigger['template_name'];
        }

        if ( isset ($templateName) && trim ($templateName) !== "" )
        {
            $subject = "CASE HAS BEEN " . (isset ($arrTrigger['event_type']) ? $arrTrigger['event_type'] : $templateName) . " BY [USER]";
            $objSendNotification = new \SendNotification();
            $objSendNotification->setProjectId ($this->parentId);
            $objSendNotification->setElementId ($this->elementId);
            $objSendNotification->setStatus ($this->currentStep);
            $objSendNotification->setTemplate ($templateName);
            $objSendNotification->setSubject ($subject);
            $objSendNotification->setSendToAll (1);
            $objSendNotification->buildEmail ($objTask, $objUser, "trigger");
        }
    }

    /**
     * Verify if doesn't exists the Trigger in table TRIGGERS
     *
     * @param string $triggerUid            Unique id of Trigger
     * @param string $processUid            Unique id of Process
     *
     * return void Throw exception if doesn't exists the Trigger in table TRIGGERS
     */
    public function throwExceptionIfNotExistsTrigger ($triggerUid, $processUid)
    {
        try {

            $sql = "SELECT id FROM workflow.step_trigger";

            $sql .= " WHERE id = ? ";
            $arrParameters = array($triggerUid);

            if ( $processUid != "" )
            {
                $sql .= "AND workflow_id = ?";
                $arrParameters[] = $processUid;
            }

            $results = $this->objMysql->_query ($sql, $arrParameters);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                throw new \Exception ("ID_TRIGGER_DOES_NOT_EXIST " . $triggerUid);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
