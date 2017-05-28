<?php

class WorkflowStep
{

    private $_workflowStepId;
    private $_stepId;
    private $objWorkflow;
    private $objAudit;
    private $objMike;
    private $nextStep;
    private $workflowId;
    private $fieldValidation;
    private $collectionId;
    private $parentId;
    private $elementId;
    private $workflowName;
    private $_systemName;
    private $_stepName;
    private $objMysql;
    private $objectId;
    private $currentStep;

    public function __construct ($intWorkflowStepId = null, $objMike = null)
    {
        $this->objMysql = new Mysql2();

        if ( $intWorkflowStepId !== null )
        {
            $this->_workflowStepId = $intWorkflowStepId;
            if ( !$this->setStepInformation () )
            {

                return false;
            }
        }

        if ( $objMike !== null )
        {
            if ( $this->setWorkflowStepFromObject ($objMike) === false )
            {
                return false;
            }

            if ( !$this->setStepInformation () )
            {
                return false;
            }
        }

        $this->objMike = $objMike;
    }

    public function getWorkflowStepId ()
    {
        return $this->_workflowStepId;
    }

    public function getNextStepId ()
    {
        return $this->nextStep;
    }

    public function getStepId ()
    {
        return $this->_stepId;
    }

    public function getFieldValidation ()
    {
        return $this->fieldValidation;
    }

    public function setStepInformation ()
    {
        $sql = "SELECT
                    sy.system_name,
                    r.request_id,
                    m.workflow_id,
                    m.step_from AS current_step_id,
                    m.id AS current_step,
                    s.step_name AS current_step_name,
                    m2.id AS next_step_id,
                    s2.step_name AS  next_step_name,
                    w.workflow_name
                   FROM workflow.status_mapping m
                    INNER JOIN workflow.workflows w ON w.workflow_id = m.workflow_id
                    INNER JOIN workflow.steps s ON s.step_id = m.step_from
                    INNER JOIN workflow.request_types r ON r.request_id = w.request_id
                    INNER JOIN workflow.workflow_systems sy ON sy.system_id = r.system_id
                    LEFT JOIN workflow.status_mapping m2 ON m2.step_from = m.step_to AND m2.workflow_id = m.workflow_id
                    LEFT JOIN workflow.steps s2 ON s2.step_id = m2.step_from
                   WHERE m.id = ?";

        $arrResult = $this->objMysql->_query ($sql, array($this->_workflowStepId));

        if ( empty ($arrResult) )
        {
            return false;
        }

        $this->_stepId = $arrResult[0]['current_step_id'];
        $this->nextStep = $arrResult[0]['next_step_id'];
        $this->workflowId = $arrResult[0]['workflow_id'];
        $this->_systemName = $arrResult[0]['system_name'];
        $this->collectionId = $arrResult[0]['request_id'];
        $this->_stepName = $arrResult[0]['current_step_name'];
        $this->workflowName = $arrResult[0]['workflow_name'];
        $this->currentStep = $arrResult[0]['current_step'];

        return true;
    }

    private function setWorkflowStepFromObject ($objMike)
    {
        $id = $objMike->getId ();

        if ( method_exists ($objMike, "getSource_id") && $objMike->getSource_id () != "" )
        {
            $parentId = $objMike->getSource_id ();
        }
        else
        {
            $parentId = $id;
        }

        $result = $this->objMysql->_select ("workflow.workflow_data", array("workflow_data"), array("object_id" => $parentId));

        if ( !isset ($result[0]) )
        {
            //return false;
        }

        $workflowData = json_decode ($result[0]['workflow_data'], true);

        if ( empty ($workflowData) )
        {
            return FALSE;
        }

        if ( isset ($workflowData['elements'][$id]) )
        {
            $this->_workflowStepId = $workflowData['elements'][$id]['current_step'];
        }
        else
        {
            $this->_workflowStepId = $workflowData['current_step'];
        }

        return $workflowData;
    }

    public function getConditions ()
    {
        $arrResult = $this->objMysql->_select ("workflow.status_mapping", array("step_condition"), array("id" => $this->_workflowStepId));

        if ( empty ($arrResult) )
        {
            return false;
        }

        $conditions = json_decode ($arrResult[0]['step_condition'], true);
        return $conditions;
    }

    public function getFields ()
    {

        $objFieldFactory = new FieldFactory();
        $this->arrFields = $objFieldFactory->getFieldsForStep ($this->_stepId);
        return $this->arrFields;
    }

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

    /*     * *********** Save Methods ************************ */

    public function assignUserToStep ($objMike, $arrFormData)
    {
        if ( !isset ($arrFormData['claimed']) || !isset ($arrFormData['dateCompleted']) )
        {
            return false;
        }

        if ( $this->completeWorkflowObject ($objMike, $arrFormData, false) === false )
        {
            return false;
        }
    }

    public function save ($objMike, $arrFormData, $arrEmailAddresses = array())
    {

        if ( !$this->validateWorkflowStep ($arrFormData) )
        {
            return false;
        }

        if ( !$objMike->loadObject ($arrFormData) )
        {
            return false;
        }

        if ( $objMike->save () === false )
        {
            return false;
        }

        if ( isset ($arrFormData['status']) )
        {
            if ( $this->completeWorkflowObject ($objMike, $arrFormData, false, $arrEmailAddresses) === false )
            {
                return false;
            }
        }
        else
        {
            if ( $this->completeWorkflowObject ($objMike, array(), false, $arrEmailAddresses) === false )
            {
                return false;
            }
        }
    }

    private function validateWorkflowStep ($arrFormData)
    {
        $objValidate = new FieldValidator ($this->_stepId);
        $arrErrorsCodes = $objValidate->validate ($arrFormData);

        if ( !empty ($arrErrorsCodes) )
        {
            $this->fieldValidation = $arrErrorsCodes;
            return false;
        }

        return true;
    }

    private function sendNotification ($objMike, array $arrCompleteData = [], $arrEmailAddresses = [])
    {
        $objNotifications = new SendNotification();
        $objNotifications->setVariables (5, $this->_systemName);
        $objNotifications->setProjectId ($this->parentId);

        $arrNotificationFields = $objNotifications->getMessageParameters ();

        $arrNotificationData = array();

        foreach ($arrNotificationFields[0] as $strField) {
            if ( isset ($objMike->objJobFields[$strField]) )
            {
                $accessor = $objMike->objJobFields[$strField]['accessor'];

                if ( !empty ($accessor) )
                {
                    $arrNotificationData[$strField] = $objMike->$accessor ();
                }
            }
            elseif ( $strField == "workflow_name" )
            {
                $arrNotificationData[$strField] = $this->workflowName;
            }
            elseif ( $strField == "step_name" )
            {
                $arrNotificationData['step_name'] = $this->_stepName;
            }
        }

        $objNotifications->setProjectId ($this->parentId);
        $objNotifications->setElementId ($this->elementId);

        if ( !empty ($arrEmailAddresses) )
        {
            $objNotifications->setArrEmailAddresses ($arrEmailAddresses);
        }

        $objNotifications->buildEmail (5, $arrNotificationData);
    }

    private function completeAuditObject (array $arrCompleteData = [])
    {
        if ( is_numeric ($this->parentId) && is_numeric ($this->elementId) )
        {
            $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['claimed'] = $arrCompleteData['claimed'];
            $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['dateCompleted'] = $arrCompleteData['dateCompleted'];
            $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['status'] = $arrCompleteData['status'];
        }
        else
        {
            $this->objAudit['claimed'] = $arrCompleteData['claimed'];
            $this->objAudit['dateCompleted'] = $arrCompleteData['dateCompleted'];
            $this->objAudit['status'] = $arrCompleteData['status'];
        }
    }

    private function completeWorkflowObject ($objMike, $arrCompleteData, $complete = false, $arrEmailAddresses)
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
        $arrWorkflowObject = json_decode ($arrWorkflowData[0]['workflow_data'], true);
        $blHasTrigger = false;

        $arrWorkflow['request_id'] = $this->collectionId;

        $objTrigger = new StepTrigger ($this->nextStep);

        if ( $complete === true && $this->nextStep !== 0 && $this->nextStep != "" )
        {
            $blHasTrigger = $objTrigger->checkTriggers ($objMike);

            if ( $blHasTrigger === true )
            {
                $arrWorkflowObject = $objTrigger->arrWorkflowObject;
            }
            
            if ( $objTrigger->blMove === true || $blHasTrigger === false )
            {
                $blHasTrigger = false;
                $arrWorkflow['current_step'] = $this->nextStep;
            }

            $arrWorkflow['status'] = "STEP COMPLETED";
        }
        else
        {
            $arrWorkflow['current_step'] = $this->_workflowStepId;

            if ( $this->nextStep == 0 || $this->nextStep == "" )
            {
                $arrWorkflow['status'] = "WORKFLOW COMPLETE";
                $arrCompleteData['status'] = "COMPLETE";
            }
            else
            {
                $arrWorkflow['status'] = "SAVED";
                //$arrCompleteData['status'] = "SAVED";
            }
        }

        
        $arrWorkflow['workflow_id'] = $this->workflowId;

        if ( !empty ($arrWorkflowData) )
        {
            $this->objWorkflow = $arrWorkflowObject;
            $this->objAudit = json_decode ($arrWorkflowData[0]['audit_data'], true);
        }
        else
        {
            $this->objWorkflow = $arrWorkflow;
        }
    
        if ( is_numeric ($this->parentId) && is_numeric ($this->elementId) && $blHasTrigger !== true )
        {
            $this->objWorkflow['elements'][$this->elementId] = $arrWorkflow;
        }

        if ( ($this->nextStep == 0 || $this->nextStep == "") && $complete === true && $arrCompleteData['status'] == "COMPLETE" )
        {
            $arrCompleteData['status'] = "COMPLETE";
            $this->objWorkflow['elements'][$this->elementId]['status'] = "WORKFLOW COMPLETE";
        }

        if ( isset ($arrCompleteData['dateCompleted']) && isset ($arrCompleteData['claimed']) )
        {
            $this->completeAuditObject ($arrCompleteData);
        }

        $this->sendNotification ($objMike, $arrCompleteData, $arrEmailAddresses);

        // Update workflow and audit object
        $strAudit = json_encode ($this->objAudit);
        $strWorkflow = json_encode ($this->objWorkflow);

        $objectId = isset ($this->parentId) && is_numeric ($this->parentId) ? $this->parentId : $this->elementId;
        
        if ( !empty ($arrWorkflowData) )
        {
            $this->objMysql->_update ("workflow.workflow_data", array(
                "workflow_data" => $strWorkflow,
                "audit_data" => $strAudit), array(
                "object_id" => $objectId
                    ), array(
                "id" => $this->objectId
                    )
            );
        }
        else
        {
            $this->objMysql->_insert ("workflow.workflow_data", array(
                "workflow_data" => $strWorkflow,
                "audit_data" => $strAudit,
                "object_id" => $objectId)
            );
        }
    }

    public function complete ($objMike, $arrCompleteData, $arrEmailAddresses = array())
    {
        $this->completeWorkflowObject ($objMike, $arrCompleteData, true, $arrEmailAddresses);

        if ( isset ($this->nextStep) && $this->nextStep != 0 )
        {
            return new WorkflowStep ($this->nextStep, $objMike);
        }
        return true;
    }
    
    public function getFirstStepForWorkflow()
    {
        $result = $this->objMysql->_select("workflow.status_mapping", array(), array("workflow_id" => $this->workflowId, "first_step" => 1));
    
        if(isset($result[0]) && !empty($result[0])) {
            return $result;
        }
        
        return [];
    }

}
