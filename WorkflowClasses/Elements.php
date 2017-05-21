<?php

class Elements
{

    public $object;
    private $location;
    private $batch;
    private $description;
    private $name;
    private $current_step;
    private $workflow_id;
    private $source_id;
    private $id;
    private $sampleRef;
    private $file2;
    private $rejectionReason;
    private $workflowName;
    private $current_user;
    private $auditData;
    public $arrElement = array();
    public $objJobFields = array(
        "location" => array("required" => "true", "type" => "string", "accessor" => "getLocation", "mutator" => "setLocation"),
        "batch" => array("required" => "true", "type" => "string", "accessor" => "getBatch", "mutator" => "setBatch"),
        "description" => array("required" => "true", "type" => "int", "accessor" => "getDescription", "mutator" => "setDescription"),
        "name" => array("required" => "true", "type" => "int", "accessor" => "getName", "mutator" => "setName"),
        "current_step" => array("required" => "true", "type" => "int", "accessor" => "getCurrent_step", "mutator" => "setCurrent_step"),
        "workflow_id" => array("required" => "true", "type" => "int", "accessor" => "getWorkflow_id", "mutator" => "setWorkflow_id"),
        "source_id" => array("required" => "true", "type" => "int", "accessor" => "getSource_id", "mutator" => "setSource_id"),
        "sampleRef" => array("required" => "true", "type" => "string", "accessor" => "getSampleRef", "mutator" => "setSampleRef"),
        "file2" => array("required" => "true", "type" => "int", "accessor" => "getFile2", "mutator" => "setFile2"),
        "rejectionReason" => array("required" => "false", "type" => "string", "accessor" => "getRejectionReason", "mutator" => "setRejectionReason"),
    );
    public $objSchedulerFields = array(
        "description" => array("fieldName" => "title", "required" => "true", "type" => "int"),
        "name" => array("fieldName" => "title", "required" => "true", "type" => "int"),
        "current_step" => array("fieldName" => "current_step", "required" => "true", "type" => "int"),
    );
    private $objMysql;
    private $arrToIgnore = array("claimed", "status", "dateCompleted");
    
    private $status;

    public function __construct ($parentId, $id = null)
    {
        $this->setParentId ($parentId);
        $this->getProjectById ();

        if ( $id !== null )
        {
            $this->setId ($id);
        }

        $this->getElement ();

        $this->objMysql = new Mysql2();
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function setParentId ($parentId)
    {
        $this->source_id = $parentId;
    }

    public function getParentId ()
    {
        return $this->source_id;
    }

    public function loadObject ($arrElements)
    {
        $objVariables = new StepVariable();

        foreach ($arrElements as $formField => $formValue) {

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
            elseif ( !in_array ($formField, $this->arrToIgnore) )
            {
                $objVariable = $objVariables->getVariableForField ($formField);

                if ( empty ($objVariable) )
                {
                    return false;
                }

                $variableName = $objVariable->getVariableName ();

                switch ($objVariable->getValidationType ()) {
                    case "string":
                        if ( !is_string ($formValue) )
                        {
                            //die("Mike");
                        }
                        break;
                }

                $this->arrElement[$variableName] = $formValue;
            }
        }

        return true;
    }

    function getLocation ()
    {
        return $this->location;
    }

    function getDescription ()
    {
        return $this->description;
    }

    function getName ()
    {
        return $this->name;
    }
    
    public function getStatus ()
    {
        return $this->status;
    }

    public function setStatus ($status)
    {
        $this->status = $status;
    }

    public function getCurrentStep ()
    {
        return $this->current_step;
    }

    function getWorkflow_id ()
    {
        return $this->workflow_id;
    }

    function getSource_id ()
    {
        return $this->source_id;
    }

    function getSampleRef ()
    {
        return $this->sampleRef;
    }

    function getFile2 ()
    {
        return $this->file2;
    }

    function setLocation ($location)
    {
        $this->location = $location;
        $this->arrElement['location'] = $location;
    }

    function setDescription ($description)
    {
        $this->description = $description;
        $this->arrElement['description'] = $description;
        $this->object['scheduler']['description'] = $description;
    }

    function setName ($name)
    {
        $this->name = $name;
        $this->arrElement['name'] = $name;
        $this->object['scheduler']['name'] = $name;
    }

    function setCurrent_step ($current_step)
    {
        $this->current_step = $current_step;
        $this->arrElement['current_step'] = $current_step;
        $this->object['scheduler']['current_step'] = $current_step;
    }

    function setWorkflow_id ($workflow_id)
    {
        $this->workflow_id = $workflow_id;
        $this->arrElement['workflow_id'] = $workflow_id;
    }

    function setSource_id ($source_id)
    {
        $this->source_id = $source_id;
        $this->arrElement['source_id'] = $source_id;
    }

    function setSampleRef ($sampleRef)
    {
        $this->sampleRef = $sampleRef;
        $this->arrElement['sampleRef'] = $sampleRef;
    }

    function setFile2 ($file2)
    {
        if ( isset ($this->file2) )
        {
            $file2 .= "," . $this->file2;
        }

        $this->file2 = $file2;
        $this->arrElement['file2'] = $file2;
    }

    function getBatch ()
    {
        return $this->batch;
    }

    function setBatch ($batch)
    {
        $this->batch = $batch;
        $this->arrElement['batch'] = $batch;
    }

    function getRejectionReason ()
    {
        return $this->rejectionReason;
    }

    function setRejectionReason ($rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
        $this->arrElement['rejectionReason'] = $rejectionReason;
    }
    
    public function getWorkflowName ()
    {
        return $this->workflowName;
    }

    public function getCurrent_user ()
    {
        return $this->current_user;
    }

    public function setWorkflowName ($workflowName)
    {
        $this->workflowName = $workflowName;
    }

    public function setCurrent_user ($current_user)
    {
        $this->current_user = $current_user;
    }

    public function getProjectById ()
    {
        $objMysql = new Mysql2();
        $result = $objMysql->_select ("task_manager.projects", array(), array("id" => $this->source_id));

        if ( !empty ($result[0]['step_data']) )
        {
            $JSON = json_decode ($result[0]['step_data'], true);

            if ( $this->id != "" )
            {
                if ( isset ($JSON['elements'][$this->id]) )
                {
                    foreach ($JSON['elements'] as $element) {
                        $this->loadObject ($element);
                    }
                }
            }
        }



        if ( isset ($JSON) && !empty ($JSON) )
        {
            $this->JSON = $JSON;
        }
    }

    public function getElement ()
    {

        $objMysql = new Mysql2();
        $result = $objMysql->_select ("task_manager.projects", array(), array("id" => $this->source_id));

        if ( !empty ($result[0]['step_data']) )
        {
            $JSON = json_decode ($result[0]['step_data'], true);

            if ( isset ($this->id) && isset ($JSON['elements'][$this->id]) )
            {
                $this->loadObject ($JSON['elements'][$this->id]);
            }
        }
    }

    public function buildObjectId ($sourceId, $workflow)
    {
        $objMysql = new Mysql2();
        $result = $objMysql->_select ("task_manager.projects", array(), array("id" => $sourceId));
        $JSON = json_decode ($result[0]['step_data'], true);

        $arrWorkflowData = $objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $this->source_id));
        $workflowData = json_decode ($arrWorkflowData[0]['workflow_data'], true);

        $count = 0;

        if ( isset ($JSON['elements']) && !empty ($JSON['elements']) )
        {
            foreach ($JSON['elements'] as $arrElements) {
                //foreach ($arrElements as $workflowId => $arrElement) {
                //if ( $workflowId == $workflow )
                //{
                $count++;
                //}
                //}
            }
        }

        return ($count + 1);
    }

    public function getId ()
    {
        return $this->id;
    }

    public function save ()
    {
        $objMysql = new Mysql2();

        if ( $this->id == "" )
        {
            $id = $this->buildObjectId ($this->source_id, $this->workflow_id);

            $this->setId ($id);


            $this->JSON['scheduler']['id'] = $id;
            $this->JSON['scheduler']['status'] = "Not Started";

            $this->JSON['scheduler']['backlogs'][$id] = $this->object['scheduler'];
            $this->JSON['elements'][$id] = $this->arrElement;

            $objMysql->_update ("task_manager.projects", array("step_data" => json_encode ($this->JSON)), array("id" => $this->source_id));
        }
        else
        {
            $this->JSON['elements'][$this->id] = $this->arrElement;

            $objMysql->_update ("task_manager.projects", array("step_data" => json_encode ($this->JSON)), array("id" => $this->source_id));
        }
    }

    public function getAuditData ()
    {
        return $this->auditData;
    }

    public function getCurrent_step ()
    {
        return $this->current_step;
    }

    public function last ($arrData)
    {
        end ($arrData['steps']);
        $key = key ($arrData['steps']);
        return $key;
    }

}
