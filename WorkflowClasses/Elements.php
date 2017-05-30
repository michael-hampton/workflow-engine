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
    private $currentStepId;
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

    private $arrToIgnore = array("claimed", "status", "dateCompleted", "priority", "deptId", "workflow", "added_by", "date_created", "project_status", "dueDate");
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

    /**
     * 
     * @param type $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    /**
     * 
     * @param type $parentId
     */
    public function setParentId ($parentId)
    {
        $this->source_id = $parentId;
    }

    public function getParentId ()
    {
        return $this->source_id;
    }

    /**
     * 
     * @param type $arrElements
     * @return boolean
     */
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
                    echo $formField;
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

    /**
     * 
     * @param type $status
     */
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

    /**
     * 
     * @param type $location
     */
    function setLocation ($location)
    {
        $this->location = $location;
        $this->arrElement['location'] = $location;
    }

    /**
     * 
     * @param type $description
     */
    function setDescription ($description)
    {
        $this->description = $description;
        $this->arrElement['description'] = $description;
        $this->object['scheduler']['description'] = $description;
    }

    /**
     * 
     * @param type $name
     */
    function setName ($name)
    {
        $this->name = $name;
        $this->arrElement['name'] = $name;
        $this->object['scheduler']['name'] = $name;
    }

    /**
     * 
     * @param type $current_step
     */
    function setCurrent_step ($current_step)
    {
        $this->current_step = $current_step;
        $this->arrElement['current_step'] = $current_step;
        $this->object['scheduler']['current_step'] = $current_step;
    }

    /**
     * 
     * @param type $workflow_id
     */
    function setWorkflow_id ($workflow_id)
    {
        $this->workflow_id = $workflow_id;
        $this->arrElement['workflow_id'] = $workflow_id;
    }

    /**
     * 
     * @param type $source_id
     */
    function setSource_id ($source_id)
    {
        $this->source_id = $source_id;
        $this->arrElement['source_id'] = $source_id;
    }

    /**
     * 
     * @param type $sampleRef
     */
    function setSampleRef ($sampleRef)
    {
        $this->sampleRef = $sampleRef;
        $this->arrElement['sampleRef'] = $sampleRef;
    }

    /**
     * 
     * @param string $file2
     */
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

    /**
     * 
     * @param type $batch
     */
    function setBatch ($batch)
    {
        $this->batch = $batch;
        $this->arrElement['batch'] = $batch;
    }

    function getRejectionReason ()
    {
        return $this->rejectionReason;
    }

    /**
     * 
     * @param type $rejectionReason
     */
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

    /**
     * 
     * @param type $workflowName
     */
    public function setWorkflowName ($workflowName)
    {
        $this->workflowName = $workflowName;
    }

    public function getCurrentStepId ()
    {
        return $this->currentStepId;
    }

    public function setCurrentStepId ($currentStepId)
    {
        $this->currentStepId = $currentStepId;
    }

    /**
     * 
     * @param type $current_user
     */
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
            else
            {
                $this->loadObject ($JSON['job']);
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

    /**
     * 
     * @param type $sourceId
     * @param type $workflow
     * @return type
     */
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
    
    /**
     * This function return an array without difference
     *
     *
     * @name arrayRecursiveDiff
     * @param  array $aArray1
     * @param  array $aArray2
     * @access public
     * @return $aReturn
     */
    public function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();
        foreach ($aArray1 as $mKey => $mValue) {
            if (is_array($aArray2) && array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $aArray2[$mKey];
                    }
                }
            } else {
                $aReturn[$mKey] = isset($aArray2[$mKey]) ? $aArray2[$mKey] : null;
            }
        }
        return $aReturn;
    }

    public function array_key_intersect(&$a, &$b) {
        $array = array();
        while (list($key, $value) = each($a)) {
            if (isset($b[$key])) {
                if (is_object($b[$key]) && is_object($value)) {
                    if (serialize($b[$key]) === serialize($value)) {
                        $array[$key] = $value;
                    }
                } else {
                    if ($b[$key] !== $value) {
                        $array[$key] = $value;
                    }
                }
            }
        }
        return $array;
    }

    private function doAudit()
    {
        $result = $this->database->_select($this->tablename, array("system_data"), array("pk" => $this->projectId));
        $data = json_decode($result[0]['system_data'], true);

        $FieldsBefore = $data['elements'][$this->id];

        $aApplicationFields = $this->object['elements'][$this->id];
        $FieldsDifference = $this->arrayRecursiveDiff($FieldsBefore, $aApplicationFields);
        $fieldsOnBoth = $this->array_key_intersect($FieldsBefore, $aApplicationFields);

        if ((is_array($FieldsDifference)) && (count($FieldsDifference) > 0)) {
            $appHistory = new \KondorCoreLibrary\WorkflowAudit();

            $FieldsDifference['before'] = $fieldsOnBoth;

            $aFieldsHistory = array(
                "project_id" => $this->projectId,
                "system_id" => 14,
                "workflow_id" => 120,
                "element_id" => $this->id,
                "update_date" => date("Y-m-d"),
                "username" => $_SESSION['user']['user'][0]['username'],
                "before" => $fieldsOnBoth,
                "message" => "Field Updated"
            );
            $aFieldsHistory['APP_DATA'] = serialize($FieldsDifference);
            $appHistory->insertHistory($aFieldsHistory);
        }
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
            $this->doAudit();
            $this->JSON['elements'][$this->id] = $this->arrElement;

            $objMysql->_update ("task_manager.projects", array("step_data" => json_encode ($this->JSON)), array("id" => $this->source_id));
        }
    }

    public function getCurrent_step ()
    {
        return $this->current_step;
    }

}
