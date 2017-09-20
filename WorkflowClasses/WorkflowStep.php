<?php

class WorkflowStep
{

    /**
     *
     * @var type 
     */
    private $_workflowStepId;

    /**
     *
     * @var type 
     */
    private $_stepId;

    /**
     *
     * @var type 
     */
    private $objAudit;

    /**
     *
     * @var type 
     */
    private $objMike;

    /**
     *
     * @var type 
     */
    private $nextStep;

    /**
     *
     * @var type 
     */
    private $workflowId;

    /**
     *
     * @var type 
     */
    private $fieldValidation;

    /**
     *
     * @var type 
     */
    private $collectionId;

    /**
     *
     * @var type 
     */
    private $parentId;

    /**
     *
     * @var type 
     */
    private $elementId;

    /**
     *
     * @var type 
     */
    private $workflowName;

    /**
     *
     * @var type 
     */
    private $_systemName;

    /**
     *
     * @var type 
     */
    private $_stepName;

    /**
     *
     * @var type 
     */
    private $objMysql;

    /**
     *
     * @var type 
     */
    private $objectId;

    /**
     *
     * @var type 
     */
    private $currentStep;

    /**
     *
     * @var type 
     */
    private $nextTask;

    /**
     *
     * @var type 
     */
    private $currentTask;

    /**
     *
     * @var type 
     */
    private $blReview = false;

    /**
     *
     * @var type 
     */
    private $currentStatus;

    /**
     *
     * @var type 
     */
    private $hasEvent;

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

    public function getParentId ()
    {
        return $this->parentId;
    }

    public function getWorkflowStepId ()
    {
        return $this->_workflowStepId;
    }

    public function setWorkflowId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    public function getNextStepId ()
    {
        return $this->nextStep;
    }

    public function getWorkflowId ()
    {
        return $this->workflowId;
    }

    public function getHasEvent ()
    {
        return $this->hasEvent;
    }

    public function getStepId ()
    {
        return $this->_stepId;
    }

    public function getFieldValidation ()
    {
        return $this->fieldValidation;
    }

    public function getNextTask ()
    {
        return $this->nextTask;
    }

    public function getCurrentStep ()
    {
        return $this->currentStep;
    }

    public function getCurrentTask ()
    {
        return $this->currentTask;
    }

    public function getCollectionId ()
    {
        return $this->collectionId;
    }

    public function setStepInformation ()
    {
        $sql = "SELECT
                    sy.system_name,
                    r.request_id,
                    m.workflow_id,
                    m.step_from AS current_step_id,
                    m.id AS current_step,
                    m.TAS_UID AS current_task,
                    t.step_name AS current_step_name,
                    m2.id AS next_step_id,
                    s2.step_name AS  next_step_name,
                    s2.TAS_UID,
                    w.workflow_name
                   FROM workflow.status_mapping m
                    INNER JOIN workflow.workflows w ON w.workflow_id = m.workflow_id
                    INNER JOIN workflow.task t ON t.TAS_UID = m.step_from
                    INNER JOIN workflow.request_types r ON r.request_id = w.request_id
                    INNER JOIN workflow.workflow_systems sy ON sy.system_id = r.system_id
                    LEFT JOIN workflow.status_mapping m2 ON m2.step_from = m.step_to AND m2.workflow_id = m.workflow_id
                    LEFT JOIN workflow.task s2 ON s2.TAS_UID = m2.TAS_UID
                   WHERE m.id = ?";
        $arrResult = $this->objMysql->_query ($sql, array($this->_workflowStepId));
        if ( empty ($arrResult) )
        {
            throw new Exception ("Fsiled to get workflow data");
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
        $this->currentTask = $arrResult[0]['current_task'];
        $this->nextTask = $arrResult[0]['TAS_UID'];
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
        $result = $this->objMysql->_select ("workflow.workflow_data", array("workflow_data", "audit_data"), array("object_id" => $parentId));
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
        $arrAudit = json_decode ($result[0]['audit_data'], true);
        $this->currentStatus = end ($arrAudit['elements'][$id]['steps'])['status'];
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
        $objFieldFactory = new \BusinessModel\FieldFactory();
        $this->arrFields = $objFieldFactory->getFieldsForStep (new Task ($this->_stepId));
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

    public function save ($objMike, array $arrFormData, Users $objUser, $arrEmailAddresses = array())
    {
        $parentId = null;
        if ( method_exists ($objMike, "getParentId") )
        {
            $parentId = $objMike->getParentId ();
        }
        $elementId = $objMike->getId ();
        $objCases = new \BusinessModel\Cases();
        if ( !$objCases->hasPermission ($objUser, $parentId, $elementId) )
        {
            throw new Exception ("You do not have permission to do this");
        }
        if ( !$this->validateWorkflowStep ($arrFormData, $objUser, $objMike) )
        {
            return false;
        }
        if ( !$objMike->loadObject ($arrFormData) )
        {
            return false;
        }
        if ( $objMike->save ($objUser) === false )
        {
            return false;
        }
        // cannot move step if the task has been put in review;
        if ( $this->blReview === true )
        {
            return false;
        }
        if ( isset ($arrFormData['status']) )
        {
            if ( $this->completeWorkflowObject ($objMike, $objUser, $arrFormData, false, $arrEmailAddresses) === false )
            {
                return false;
            }
        }
        else
        {
            if ( $this->completeWorkflowObject ($objMike, $objUser, array(), false, $arrEmailAddresses) === false )
            {
                return false;
            }
        }
    }

    private function searchArray ($products, $field, $value)
    {
        foreach ($products as $key => $product) {
            if ( isset ($product[$field]) && $product[$field] === $value )
            {
                return $key;
            }
        }
        return false;
    }

    private function saveParallelUsers (Users $objUser, Task $objTask, $isParallel = false)
    {
        if ( $isParallel === true && !isset ($this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers']) )
        {
            $arrUsers = $this->getNextAssignedUser ($objTask, $objUser);

            $parallelUsers = [];

            foreach ($arrUsers as $key => $user) {
                $parallelUsers[$key]['username'] = $user['USR_USERNAME'];
            }

            $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers'] = $parallelUsers;
        }
        if ( $isParallel === true && isset ($this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers']) )
        {
            if ( trim ($objUser->getUsername ()) !== "" )
            {
                foreach ($this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers'] as $key => $parallelUser) {
                    if ( trim ($parallelUser['username']) === trim ($objUser->getUsername ()) )
                    {
                        $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers'][$key]['dateCompleted'] = date ("Y-m-d H:i:s");
                    }
                }
            }
        }

        return true;
    }

    /* get next assigned user

     *

     * @param   Array   $tasInfo

     * @return  Array   $userFields

     */

    private function getNextAssignedUser (Task $objTask, Users $objUser)
    {
        $objTask = (new Task())->retrieveByPk ($this->_stepId);

        $type = trim ($objTask->getTasAssignType ());

        switch ($type) {
            case "REPORT_TO":

                //default error user when the reportsTo is not assigned to that user
                //look for USR_REPORTS_TO to this user

                $userFields['USR_UID'] = '';

                $userFields['USR_FULLNAME'] = 'Current user does not have a valid Reports To user';

                $userFields['USR_USERNAME'] = 'Current user does not have a valid Reports To user';

                $userFields['USR_FIRSTNAME'] = '';

                $userFields['USR_LASTNAME'] = '';

                $userFields['USR_EMAIL'] = '';

                //get the report_to user & its full info

                $useruid = ($objUser->getUserId () != "") ? $this->checkReplacedByUser ($this->getDenpendentUser ($objUser)) : "";

                if ( isset ($useruid) && $useruid != '' )
                {
                    $userFields = $this->getUsersFullNameFromArray ($useruid);
                }
                // if there is no report_to user info, throw an exception indicating this

                if ( !isset ($userFields) || $userFields['USR_UID'] == '' )
                {

                    throw (new Exception ('ID_MSJ_REPORSTO')); // "The current user does not have a valid Reports To user.  Please contact administrator.") ) ;
                }

                break;

            case "MULTIPLE_INSTANCE":

                $userFields = $this->getUsersFullNameFromArray ($this->getAllUsersFromAnyTask ($objTask->getTasUid ()));

                if ( empty ($userFields) )
                {

                    throw (new Exception ('ID_NO_USERS'));
                }

                break;



            default:
                $userFields = $this->getUsersFullNameFromArray ($this->getAllUsersFromAnyTask ($this->_stepId));
                break;
        }

        return $userFields;
    }

    /* getDenpendentUser

     *

     * @param   string   $USR_UID

     * @return  string   $aRow['USR_REPORTS_TO']

     */

    private function getDenpendentUser (Users $objUser)
    {

        $objDepartment = (new \BusinessModel\Department())->getDepartment ($objUser->getDept_id ());
        $departmentManager = $objDepartment->getDepartmentManager ();

        return $departmentManager;
    }

    /**
     * Puts a task into review state if(incorrect data given or report_to task assign type is set
     * @param type $arrFormData
     * @param Users $objUser
     * @param type $objMike
     * @return boolean
     */
    private function validateWorkflowStep ($arrFormData, Users $objUser, $objMike)
    {
        $objValidate = new FieldValidator ($this->_stepId);
        $arrErrorsCodes = $objValidate->validate ($arrFormData);
        $objTask = (new Task())->retrieveByPk ($this->_stepId);

        if ( !empty ($arrErrorsCodes) || trim ($objTask->getTasAssignType ()) === "REPORT_TO" )
        {
            // put into review if incorrect data
            if ( $this->searchArray ($arrErrorsCodes, "message", "incorrect_data") !== false || trim ($objTask->getTasAssignType ()) === "REPORT_TO" )
            {
                $this->elementId = $objMike->getId ();

                $departmentManager = $this->getNextAssignedUser ($objTask, $objUser);

                $departmentManager = $departmentManager['USR_USERNAME'];

                $isProcessSupervisor = (new BusinessModel\ProcessSupervisor())->isUserProcessSupervisor (new Workflow ($this->workflowId), $objUser);
                if ( method_exists ($objMike, "getParentId") )
                {
                    $this->parentId = $objMike->getParentId ();
                }
                else
                {
                    $this->parentId = $objMike->getId ();
                }
                /*                 * ************** Determine next step if there is one else stay at current step ********************** */
                $arrWorkflowData = $this->getWorkflowData ();
                $this->objAudit = json_decode ($arrWorkflowData[0]['audit_data'], true);
                if ( (trim ($objUser->getUsername ()) === trim ($departmentManager)) || $isProcessSupervisor === true )
                {
                    if ( trim ($objTask->getTasAssignType ()) === "REPORT_TO" )
                    {
                        // notify department manager
                    }
                }
                else
                {
                    if ( trim ($objTask->getTasAssignType ()) === "REPORT_TO" )
                    {
                        // notify department manager
                    }

                    $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['status'] = "IN REVIEW";
                    $this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['dateCompleted'] = date ("Y-m-d H:i:s");
                    $strAudit = json_encode ($this->objAudit);
                    $this->objMysql->_update ("workflow.workflow_data", ["audit_data" => $strAudit], ["id" => $this->objectId]);
                    $this->blReview = true;
                    $this->fieldValidation = $arrErrorsCodes;
                    return true;
                    $this->fieldValidation = $arrErrorsCodes;
                    return false;
                }
            }
        }
        return true;
    }

    private function sendNotification (Users $objUser, $arrEmailAddresses = [])
    {
        $objNotifications = new SendNotification();
        $objNotifications->setVariables ($this->_stepId, $this->_systemName);
        $objNotifications->setProjectId ($this->parentId);
        $objNotifications->setElementId ($this->elementId);
        if ( !empty ($arrEmailAddresses) )
        {
            $objNotifications->setArrEmailAddresses ($arrEmailAddresses);
        }
        $objStep = new Task ($this->_stepId);
        $objStep->setStepId ($this->_stepId);
        $objNotifications->buildEmail ($objStep, $objUser);
    }

    private function completeWorkflowObject ($objMike, Users $objUser, $arrCompleteData, $complete = false, $arrEmailAddresses = array())
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

        $objAppThread = new AppThread();
        $objAppDelegation = new AppDelegation();

        /*         * ************** Determine next step if there is one else stay at current step ********************** */
        $blHasTrigger = false;
        $objTrigger = new \BusinessModel\StepTrigger ($this->_workflowStepId, $this->nextStep);

        if ( !isset ($arrCompleteData['status']) || trim ($arrCompleteData['status']) !== "REJECT" )
        {
            $blHasTrigger = $objTrigger->checkTriggers ($this, $objMike, $objUser);
        }

        $blWorkflowComplete = false;

        if ( $complete === true && $this->nextStep !== 0 && $this->nextStep != "" )
        {
            $openThreads = $objAppThread->GetOpenThreads ($objMike);

            if ( $openThreads > 1 )
            {
                throw new Exception ("Task cannot be completed. Some tasks havent been completed");
            }

            if ( $blHasTrigger === true )
            {
                $arrWorkflowObject = $objTrigger->arrWorkflowObject;
                $step2 = $arrWorkflowObject['elements'][$this->elementId]['current_step'];
            }
            if ( $objTrigger->blMove === true || $blHasTrigger === false )
            {
                $blHasTrigger = false;
                $step = $this->nextTask;
                $step2 = isset ($step2) && trim ($step2) !== "" ? $step2 : $this->nextStep;

                $objAppDelegation->CloseCurrentDelegation ($objMike, $this);
                $objAppThread->closeAppThread ($objMike, false);
                (new \Log (LOG_FILE))->log (
                        array(
                    "message" => "STEP COMPLETED",
                    'case_id' => $this->elementId,
                    'project_id' => $this->parentId,
                    'user' => $objUser->getUsername (),
                    'workflow_id' => $this->workflowId,
                    'step_id' => $this->nextStep
                        ), \Log::NOTICE);
            }
            $status = "STEP COMPLETED";
        }
        else
        {
            $step = $this->currentTask;
            $step2 = isset ($step2) && trim ($step2) !== "" ? $step2 : $this->_workflowStepId;
            if ( $this->nextStep == 0 || $this->nextStep == "" )
            {
                $blWorkflowComplete = true;
                $arrCompleteData['status'] = !isset ($arrCompleteData['status']) ? "WORKFLOW COMPLETE" : $arrCompleteData['status'];
            }
            else
            {
                $arrCompleteData['status'] = !isset ($arrCompleteData['status']) ? "SAVED" : $arrCompleteData['status'];
                $status = "SAVED";
            }
        }
        if ( !isset ($step) || !isset ($step2) )
        {
            $step = $this->currentTask;
            $step2 = trim ($step2) === "" ? $this->_stepId : $step2;
        }
        /*         * ******************** Get due date for Task ********************** */
        $objTask = new Task();
        $objTask->setTasUid ($step);
        $objTask->setStepId ($step2);
        $blIsParralelTask = false;
        if ( isset ($arrCompleteData['dateCompleted']) && isset ($arrCompleteData['claimed']) )
        {
            $objTask = $objTask->retrieveByPk ($this->_stepId);
            $objTask->setStepId ($step2);
            if ( in_array ($objTask->getTasAssignType (), array("MULTIPLE_INSTANCE", "MULTIPLE_INSTANCE_VALUE_BASED")) )
            {
                $taskType = "PARALLEL";
                $blIsParralelTask = true;
                $arrData = $this->getWorkflowData ();
                if ( isset ($arrData[0]) && isset ($arrData[0]['audit_data']) )
                {
                    $this->objAudit = json_decode ($arrData[0]['audit_data'], true);
                }
                $this->saveParallelUsers ($objUser, $objTask, $blIsParralelTask);
                $strAudit = json_encode ($this->objAudit);
                $this->objMysql->_update ("workflow.workflow_data", ["audit_data" => $strAudit], ["id" => $arrData[0]['id']]);
            }
            else
            {
                $taskType = isset ($arrCompleteData['status']) ? $arrCompleteData['status'] : '';
            }
            $blProcessSupervisor = (new \BusinessModel\ProcessSupervisor())->isUserProcessSupervisor ((new Workflow ($this->workflowId)), $objUser);
            if ( $complete === true || in_array ($taskType, array("HELD", "ABANDONED", "CLAIMED")) )
            {
                if ( trim ($taskType) === "" )
                {
                    throw new Exception ("No task type given");
                }
                if ( !isset ($arrCompleteData['claimed']) || trim ($arrCompleteData['claimed']) === "" )
                {
                    throw new Exception ("No user given");
                }

                $arrUsers = $this->getNextAssignedUser ($objTask, $objUser);

                $arrWorkflowData = $this->getWorkflowData ();
                $auditData = json_decode ($arrWorkflowData[0]['audit_data'], true);

                if ( isset ($auditData['elements'][$this->elementId]['steps'][$this->_workflowStepId]['claimed']) && trim ($objUser->getUsername ()) !== trim ($auditData['elements'][$this->elementId]['steps'][$this->_workflowStepId]['claimed']) )
                {
                    throw new Exception ("Invalid User given");
                }

                $objProcess = (new Workflow ($this->workflowId))->load ($this->workflowId);
                switch ($taskType) {
                    case "PARALLEL":
                        if ( $this->validateParallelUsers () === false )
                        {
                            $step2 = $this->_workflowStepId;
                            $objTask->setStepId ($this->_workflowStepId);
                        }
                        break;
                    case "AUTO_ASSIGN":
                    case "COMPLETE":
                    case "HELD";
                    case "ABANDONED":
                        $blHasValidUser = false;

                        foreach ($arrUsers as $arrUser) {
                            if ( trim ($arrUser['USR_USERNAME']) === trim ($arrCompleteData['claimed']) )
                            {
                                $blHasValidUser = true;
                            }
                        }
                        if ( $blHasValidUser !== true && $blProcessSupervisor !== true )
                        {
//                          $arrWorkflow['current_step'] = $this->_workflowStepId;
                            throw new Exception ("Invalid task user");
                        }
                        /*                         * ************************** Process Triggers ********************************************* */
                        if ( $taskType === "ABANDONED" && trim ($objProcess->getProTriCanceled ()) !== "" )
                        {
                            $objTrigger->executeSendMail ($objUser, $objTask, null, $objProcess->getProTriCanceled ());
                        }
                        if ( $taskType === "HELD" && trim ($objProcess->getProTriPaused ()) !== "" )
                        {
                            $objTrigger->executeSendMail ($objUser, $objTask, null, $objProcess->getProTriPaused ());
                        }
                        if ( trim ($this->currentStatus) === "HELD" && trim ($objProcess->getProTriUnpaused ()) !== "NONE" )
                        {
                            $objTrigger->executeSendMail ($objUser, $objTask, null, $objProcess->getProTriUnpaused ());
                        }
                        break;
                    case "CLAIMED":

                        foreach ($arrUsers as $arrUser) {
                            if ( trim ($arrUser['USR_USERNAME']) === trim ($arrCompleteData['claimed']) )
                            {
                                $blHasValidUser = true;
                            }
                        }
                        if ( $blHasValidUser !== true )
                        {
                            throw new Exception ("Invalid user given");
                        }
                        $step2 = $this->_workflowStepId;
                        $objTask->setStepId ($step2);
                        $objTask->setTasAssignType ("SELF-SERVICE");
                        break;
                }
            }
        }
        if ( isset ($arrCompleteData['status']) && $arrCompleteData['status'] === "AUTO_ASSIGN" )
        {
            if ( !isset ($arrCompleteData['claimed']) )
            {
                $arrUsers = (new \BusinessModel\Task())->getTaskAssigneesAll ($this->workflowId, $this->_stepId, '', 0, 100, "user");
            }
        }

        $objScriptTask = new BusinessModel\ScriptTask();
        $fields = (new BusinessModel\Cases())->getCaseInfo ($this->parentId, $this->elementId);

        $fields = is_object ($fields) ? $fields->arrElement : [];
        $objScriptTask->execScriptByActivityUid ($objTask, $fields);
        /*         * ***************** Check events for task ************************** */
        $hasEvent = isset ($arrCompleteData['hasEvent']) ? 'true' : 'false';
        $this->hasEvent = $hasEvent;

        if ( $hasEvent !== 'true' )
        {
            $this->checkEvents ($objUser);
        }
        if ( ($this->nextStep == 0 || $this->nextStep == "") && $complete === true && $arrCompleteData['status'] == "COMPLETE" )
        {
            $arrCompleteData['status'] = "COMPLETE";
            $status = "WORKFLOW COMPLETE";
        }

        $this->doDerivation ($arrCompleteData, $objMike, $objUser, $objTask, $status, $step, $arrEmailAddresses);
    }

    private function doDerivation ($arrCompleteData, $objMike, Users $objUser, Task $objTask, $status, $step, $arrEmailAddresses)
    {
        $auditStatus = isset ($arrCompleteData['status']) ? $arrCompleteData['status'] : '';
        $objAppThread = new AppThread();
        $objAppDelegation = new AppDelegation();

        if ( is_numeric ($arrCompleteData['DEL_PRIORITY']) )
        {

            $arrCompleteData['DEL_PRIORITY'] = (isset ($arrCompleteData['DEL_PRIORITY']) ? ($arrCompleteData['DEL_PRIORITY'] >= 1 && $arrCompleteData['DEL_PRIORITY'] <= 5 ? $arrCompleteData['DEL_PRIORITY'] : '3') : '3');
        }
        else
        {

            $arrCompleteData['DEL_PRIORITY'] = 3;
        }

        // create app delegation
        $objAppDelegation->createAppDelegation ($this, $objMike, $objUser, $objTask, $this->_stepId, $arrCompleteData['DEL_PRIORITY'], false, $auditStatus);

        // create app thread

        $blWorkflowComplete = $status === "WORKFLOW COMPLETE" ? true : false;

        if ( $blWorkflowComplete === false )
        {
            $objAppThread->createAppThread ($this, $objMike, $objUser, $objTask, $this->_stepId, $status);
        }
        else
        {
            $objAppThread->closeAppThread ($objMike, true);
        }

        // send notifications
        $this->sendNotification ($objUser, $arrEmailAddresses);
        $this->nextTask = $step;
    }

    private function validateParallelUsers ()
    {
        $blTaskUsersCompleted = 0;

        foreach ($this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers'] as $parallelUser) {
            if ( isset ($parallelUser['dateCompleted']) && trim ($parallelUser['dateCompleted']) !== "" )
            {
                $blTaskUsersCompleted++;
            }
        }
        if ( count ($this->objAudit['elements'][$this->elementId]['steps'][$this->_workflowStepId]['parallelUsers']) !== $blTaskUsersCompleted )
        {
            return false;
        }

        return true;
    }

    public function complete ($objMike, $arrCompleteData, Users $objUser, $arrEmailAddresses = array())
    {
        $parentId = null;
        if ( method_exists ($objMike, "getParentId") )
        {
            $parentId = $objMike->getParentId ();
        }
        $elementId = $objMike->getId ();
        $objCases = new \BusinessModel\Cases();
        if ( !$objCases->hasPermission ($objUser, $parentId, $elementId) )
        {
            throw new Exception ("You do not have permission to do this");
        }
        $this->completeWorkflowObject ($objMike, $objUser, $arrCompleteData, true, $arrEmailAddresses);
        if ( method_exists ($objMike, "updateTitle") )
        {
            $objMike->updateTitle ($objUser, $this);
        }
        if ( isset ($this->nextStep) && $this->nextStep !== 0 )
        {
            $this->checkEvents ($objUser);
            $this->_workflowStepId = $this->nextStep;
            $this->currentTask = $this->nextTask;
            return new WorkflowStep ($this->_workflowStepId, $objMike);
        }
        return true;
    }

    public function getFirstStepForWorkflow ()
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("workflow_id" => $this->workflowId, "first_step" => 1));
        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }
        return [];
    }

    public function stepExists ($stepId)
    {
        $result = $this->objMysql->_select ("workflow.task", [], ["TAS_UID" => $stepId]);
        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }
        return false;
    }

    /**
     * Get the assigned groups of a task
     *
     * @param integer $sTaskUID
     * @param integer $iType
     * @return array
     */
    public function getGroupsOfTask ($sTaskUID, $iType)
    {
        try {
            $aGroups = [];
            $sql = "SELECT sp.* FROM workflow.step_permission sp "
                    . "INNER JOIN user_management.teams t ON t.team_id = sp.permission "
                    . "LEFT JOIN user_management.poms_users u ON u.team_id = t.team_id "
                    . "WHERE sp.permission_type = ? "
                    . "AND sp.step_id = ? "
                    . "AND t.status = 1";
            $results = $this->objMysql->_query ($sql, [$iType, $sTaskUID]);
            foreach ($results as $aRow) {
                $aGroups[] = $aRow;
            }
            return $aGroups;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    private function checkEvents (Users $objUser)
    {
        $objEvent = new \BusinessModel\Event();
        $arrEvents = $objEvent->getEvent ($this->_workflowStepId);
        /*         * *************** MESSAGES ****************************** */
        if ( isset ($arrEvents[0]['step_condition']) && !empty ($arrEvents[0]['step_condition']) )
        {
            $arrConditions = json_decode ($arrEvents[0]['step_condition'], true);
            if ( isset ($arrConditions['sendNotification']) && trim (strtolower ($arrConditions['sendNotification'])) == "yes" )
            {
                $objFlow = new Flow();
                $objFlow->setWorkflowId ($this->workflowId);
                $objFlow->setStepFrom ($this->_workflowStepId);
                $objMessageApplication = new \BusinessModel\MessageApplication();
                $arrWorkflowData = $this->getWorkflowData ();
                $objWorkflow = json_decode ($arrWorkflowData[0]['workflow_data'], true);
                $objMessageApplication->create ($objFlow, $this->elementId, $this->parentId, $objWorkflow);
            }
            if ( isset ($arrConditions['receiveNotification']) && trim (strtolower ($arrConditions['receiveNotification'])) == "yes" )
            {
                $objMessageApplication = new \BusinessModel\MessageApplication();
                $objMessageApplication->catchMessageEvent ($objUser);
            }
        }
    }

    private function userVacation ($UsrUid = "")
    {
        $aFields = array();
        $cnt = 0;
        do {
            if ( $UsrUid != "" && $cnt < 100 )
            {
                $aFields = (new \BusinessModel\UsersFactory())->getUser ($UsrUid);
                $UsrUid = $aFields->getUserReplaces ();
            }
            else
            {
                break;
            }
            $cnt ++;
        }
        while ((int) $aFields->getStatus () != 1);
        return $aFields;
    }

    /* get an array of users, and returns the same arrays with User's fullname and other fields
     *
     * @param   Array   $aUsers      the task uidUser
     * @return  Array   $aUsersData  an array with with User's fullname
     */

    public function getUsersFullNameFromArray ($aUsers)
    {
        $aUsersData = array();
        if ( is_array ($aUsers) )
        {
            foreach ($aUsers as $val) {

                $userFields = $this->userVacation ($val);

                $auxFields['USR_UID'] = $userFields->getUserId ();
                $auxFields['USR_USERNAME'] = $userFields->getUsername ();
                $auxFields['USR_FIRSTNAME'] = $userFields->getFirstName ();
                $auxFields['USR_LASTNAME'] = $userFields->getLastName ();
                $auxFields['USR_FULLNAME'] = $userFields->getLastName () . ($userFields->getLastName () != '' ? ', ' : '') . $userFields->getFirstName ();
                $auxFields['USR_EMAIL'] = $userFields->getUser_email ();
                $auxFields['USR_STATUS'] = $userFields->getStatus ();
                $auxFields['DEP_UID'] = $userFields->getDepartment ();
                $auxFields['USR_HIDDEN_FIELD'] = '';
                $aUsersData[] = $auxFields;
            }

            return $aUsersData;
        }
        else
        {
            $userFields = $this->userVacation ($aUsers);

            $auxFields['USR_UID'] = $userFields->getUserId ();
            $auxFields['USR_USERNAME'] = $userFields->getUsername ();
            $auxFields['USR_FIRSTNAME'] = $userFields->getFirstName ();
            $auxFields['USR_LASTNAME'] = $userFields->getLastName ();
            $auxFields['USR_FULLNAME'] = $userFields->getLastName () . ($userFields->getLastName () != '' ? ', ' : '') . $userFields->getFirstName ();
            $auxFields['USR_EMAIL'] = $userFields->getUser_email ();
            $auxFields['USR_STATUS'] = $userFields->getStatus ();
            $auxFields['DEP_UID'] = $userFields->getDepartment ();
            $auxFields['USR_HIDDEN_FIELD'] = '';

            return $auxFields;
        }
    }

    /* get all users, from any task, if the task have Groups, the function expand the group
     *
     * @param   string  $sTasUid  the task uidUser
     * @param   bool    $flagIncludeAdHocUsers
     * @return  Array   $users an array with userID order by USR_UID
     */

    public function getAllUsersFromAnyTask ($sTasUid, $flagIncludeAdHocUsers = false)
    {
        $users = array();
        $arrWhere = array();
        $sql = "SELECT USR_UID, TU_RELATION FROM workflow.task_user WHERE TAS_UID = ?";
        $arrWhere[] = $sTasUid;
        if ( $flagIncludeAdHocUsers )
        {
            $sql .= " AND (TU_TYPE = 1 OR TU_TYPE = 2)";
        }
        else
        {
            $sql .= " AND TU_TYPE = 1";
        }
        $results = $this->objMysql->_query ($sql, $arrWhere);
        foreach ($results as $row) {
            if ( $row['TU_RELATION'] == '2' )
            {
                $sql2 = "SELECT * FROM user_management.teams t
                        INNER JOIN user_management.poms_users u ON u.team_id = t.team_id
                         WHERE t.status = 1 AND t.team_id = ? AND u.status != 0";
                $arrParameters = array($row['USR_UID']);
                $results2 = $this->objMysql->_query ($sql2, $arrParameters);
                foreach ($results2 as $rowGrp) {
                    $users[$rowGrp['usrid']] = $rowGrp['usrid'];
                }
            }
            else
            {
                //filter to users that is in vacation or has an inactive estatus, and others
                $oUser = (new \BusinessModel\UsersFactory())->getUser ($row['USR_UID']);

                if ( $oUser !== false )
                {

                    if ( (int) $oUser->getStatus () == 1 )
                    {

                        $users[$row['USR_UID']] = $row['USR_UID'];
                    }
                    else
                    {
                        $userUID = $this->checkReplacedByUser ($oUser);

                        if ( $userUID != '' )
                        {

                            $users[$userUID] = $userUID;
                        }
                    }
                }
            }
        }
        //to do: different types of sort
        sort ($users);

        return $users;
    }

    private function checkReplacedByUser ($user)
    {

        if ( is_string ($user) )
        {

            $userInstance = (new \BusinessModel\UsersFactory())->getUser ($user);
        }
        else
        {

            $userInstance = $user;
        }

        if ( !is_object ($userInstance) )
        {

            throw new Exception ("The user with the UID '$user' doesn't exist.");
        }

        if ( (int) $userInstance->getStatus () === 1 )
        {

            return $userInstance->getUserId ();
        }
        else
        {

            $userReplace = trim ($userInstance->getUserReplaces ());

            if ( $userReplace != '' )
            {

                return $this->checkReplacedByUser ((new \BusinessModel\UsersFactory())->getUser ($userReplace));
            }
            else
            {

                return '';
            }
        }
    }

}
