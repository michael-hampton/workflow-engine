<?php
class Save
{

    private $description;
    private $name;
    private $priority;
    private $deptId;
    private $workflow;
    private $addedBy;
    private $createdBy;
    private $projectStatus;
    private $currentStep;
    private $dueDate;
    public $object = array();
    public $objMysql;
    public $id;
    private $requestId;
    private $startStep;
    private $completedBy;
    private $completedDate;
    private $rejectionReason;
    private $dateRejected;
    private $rejectedBy;
    private $JSON;
    public $objJobFields = array(
        "description" => array("required" => "true", "type" => "string", "accessor" => "getDescription", "mutator" => "setDescription"),
        "name" => array("required" => "true", "type" => "string", "accessor" => "getName", "mutator" => "setName"),
        "priority" => array("required" => "true", "type" => "int", "accessor" => "getPriority", "mutator" => "setPriority"),
        "deptId" => array("required" => "true", "type" => "int", "accessor" => "getDeptId", "mutator" => "setDeptId"),
        "workflow_id" => array("required" => "true", "type" => "int", "accessor" => "getWorkflow", "mutator" => "setWorkflow"),
        "added_by" => array("required" => "true", "type" => "string", "accessor" => "getAddedBy", "mutator" => "setAddedBy"),
        "date_created" => array("required" => "true", "type" => "date", "accessor" => "getCreatedBy", "mutator" => "setCreatedBy"),
        "project_status" => array("required" => "true", "type" => "int", "accessor" => "getProjectStatus", "mutator" => "setProjectStatus"),
        "current_step" => array("required" => "true", "type" => "int", "accessor" => "getCurrentStep", "mutator" => "setCurrentStep"),
        "dueDate" => array("required" => "true", "type" => "date", "accessor" => "getDueDate", "mutator" => "setDueDate"),
        "request_id" => array("required" => "true", "type" => "date", "accessor" => "getRequestId", "mutator" => "setRequestId"),
        "start_step" => array("required" => "true", "type" => "date", "accessor" => "getRequestId", "mutator" => "setRequestId"),
        "completed_by" => array("required" => "true", "type" => "date", "accessor" => "getCompletedBy", "mutator" => "setCompletedBy"),
        "completed_date" => array("required" => "true", "type" => "date", "accessor" => "getCompletedDate", "mutator" => "setCompletedDate"),
        "rejection_reason" => array("required" => "true", "type" => "date", "accessor" => "getRejectionReason", "mutator" => "setRejectionReason"),
        "date_rejected" => array("required" => "true", "type" => "date", "accessor" => "getDateRejected", "mutator" => "setDateRejected"),
        "rejected_by" => array("required" => "true", "type" => "date", "accessor" => "getRejectedBy", "mutator" => "setRejectedBy"),
    );

    public function __construct ($id = null)
    {
        $this->objMysql = new Mysql2();

        if ( $id !== null )
        {
            $this->setId ($id);
            $this->getProjectById ();
        }
    }

    public function loadObject ($arrProject)
    {
        foreach ($arrProject as $formField => $formValue) {
            if ( isset ($this->objJobFields[$formField]) )
            {
                $mutator = $this->objJobFields[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->objJobFields[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }

    public function getPriorities ()
    {
        $objMysql = new Mysql2();
        return $objMysql->_select ("task_manager.priority", array(), array());
    }

    public function getProjectById ()
    {
        $objMysql = new Mysql2();
        $arrWorkflowObject = $objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $this->id));

        $result = $this->objMysql->_select ("projects", array(), array("id" => $this->id));

        if ( isset ($result[0]['step_data']) && !empty ($result[0]['step_data']) )
        {
            $stepData = json_decode ($result[0]['step_data'], true);
            $this->loadObject ($stepData['job']);
        }

        if ( isset ($arrWorkflowObject[0]) && !empty ($arrWorkflowObject[0]) )
        {
            $this->object['workflow_data'] = json_decode ($arrWorkflowObject[0]['workflow_data'], true);
            $this->object['audit_data'] = json_decode ($arrWorkflowObject[0]['audit_data'], true);
        }
    }
    
    public function doAudit(Users $objUser) {
        if(trim($objUser->getUserId()) === "") {
            return false;
            }
        }

    public function save (Users $objUser)
    {
        if ( isset ($this->object['step_data']) )
        {
            $this->object['step_data'] = json_encode ($this->object['step_data']);
        }

        unset ($this->object['workflow_data']);
        unset ($this->object['audit_data']);

        if ( is_numeric ($this->id) )
        {
            $this->objMysql->_update ("task_manager.projects", $this->object, array("id" => $this->id));
        }
        else
        {
            $id = $this->objMysql->_insert ("task_manager.projects", $this->object);
            $this->setId ($id);
        }

$this->doAudit($objUser);


        return true;
    }

    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        $this->id = $id;

        $objMysql = new Mysql2();
        $result = $objMysql->_select ("task_manager.projects", array(), array("id" => $this->id));

        if ( isset ($result[0]['step_data']) && !empty ($result[0]['step_data']) )
        {
            $JSON = json_decode ($result[0]['step_data'], true);
            $this->object['step_data'] = $JSON;
        }
    }

    function getRequestId ()
    {
        return $this->requestId;
    }

    function setRequestId ($requestId)
    {
        $this->requestId = $requestId;
        $this->object['step_data']['job']['request_id'] = $requestId;
    }

    function getStartStep ()
    {
        return $this->startStep;
    }

    function setStartStep ($startStep)
    {
        $this->startStep = $startStep;
        $this->object['step_data']['job']['start_step'] = $startStep;
    }

    function getDescription ()
    {
        return $this->description;
    }

    function getName ()
    {
        return $this->name;
    }

    function getPriority ()
    {
        return $this->priority;
    }

    function getDeptId ()
    {
        return $this->deptId;
    }

    function getWorkflow ()
    {
        return $this->workflow;
    }

    function getAddedBy ()
    {
        return $this->addedBy;
    }

    function getCreatedBy ()
    {
        return $this->createdBy;
    }

    function getProjectStatus ()
    {
        return $this->projectStatus;
    }

    function getCurrentStep ()
    {
        return $this->currentStep;
    }

    function getDueDate ()
    {
        return $this->dueDate;
    }

    function setDescription ($description)
    {
        $this->description = $description;
        $this->object['step_data']['job']['description'] = $description;
    }

    function setName ($name)
    {
        $this->name = $name;
        $this->object['step_data']['job']['name'] = $name;
        $this->object['step_data']['scheduler']['name'] = $name;
    }

    function setPriority ($priority)
    {
        $this->priority = $priority;
        $this->object['step_data']['job']['priority'] = $priority;
        $this->object['step_data']['scheduler']['priority'] = $priority;
        $this->object['priority'] = $priority;
    }

    function setDeptId ($deptId)
    {
        $this->deptId = $deptId;
        $this->object['step_data']['job']['deptId'] = $deptId;
        $this->object['department_id'] = $deptId;
    }

    function setWorkflow ($workflow)
    {
        $this->workflow = $workflow;
        $this->object['step_data']['job']['workflow'] = $workflow;
    }

    function setAddedBy ($addedBy)
    {
        $this->addedBy = $addedBy;
        $this->object['step_data']['job']['added_by'] = $addedBy;
        $this->object['step_data']['scheduler']['added_by'] = $addedBy;
    }

    function setCreatedBy ($createdBy)
    {
        $this->createdBy = $createdBy;
        $this->object['step_data']['job']['date_created'] = $createdBy;
        $this->object['step_data']['scheduler']['date_created'] = $createdBy;
    }

    function setProjectStatus ($projectStatus)
    {
        $this->projectStatus = $projectStatus;
        $this->object['step_data']['job']['project_status'] = $projectStatus;
        $this->object['step_data']['scheduler']['project_status'] = $projectStatus;
        $this->object['project_status'] = $projectStatus;
    }

    function setCurrentStep ($currentStep)
    {
        $this->currentStep = $currentStep;
        $this->object['step_data']['job']['current_step'] = $currentStep;
    }

    function setDueDate ($dueDate)
    {
        $this->dueDate = $dueDate;
        $this->object['step_data']['job']['dueDate'] = $dueDate;
        $this->object['step_data']['scheduler']['dueDate'] = $dueDate;
    }

    function getCompletedBy ()
    {
        return $this->completedBy;
    }

    function getCompletedDate ()
    {
        return $this->completedDate;
    }

    function getRejectionReason ()
    {
        return $this->rejectionReason;
    }

    function getDateRejected ()
    {
        return $this->dateRejected;
    }

    function getRejectedBy ()
    {
        return $this->rejectedBy;
    }

    function setCompletedBy ($completedBy)
    {
        $this->completedBy = $completedBy;
        $this->object['step_data']['job']['completed_by'] = $completedBy;
    }

    function setCompletedDate ($completedDate)
    {
        $this->completedDate = $completedDate;
        $this->object['step_data']['job']['completed_date'] = $completedDate;
    }

    function setRejectionReason ($rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
        $this->object['step_data']['job']['rejection_reason'] = $rejectionReason;
    }

    function setDateRejected ($dateRejected)
    {
        $this->dateRejected = $dateRejected;
        $this->object['step_data']['job']['date_rejected'] = $dateRejected;
    }

    function setRejectedBy ($rejectedBy)
    {
        $this->rejectedBy = $rejectedBy;
        $this->object['step_data']['job']['rejected_by'] = $rejectedBy;
    }

    public function update ()
    {
        if ( isset ($this->object['step_data']) )
        {
            $this->object['step_data'] = json_encode ($this->object['step_data']);
        }

        $this->objMysql->_update ("task_manager.projects", $this->object, array("id" => $this->id));
    }

    public function saveStep ($arrSteps)
    {
        $objSteps = new StepsObject();
        $objSteps->loadObject ($arrSteps);

        return $objSteps->object;
    }
    
    public function getProjectVariables()
    {
        
    }

}
