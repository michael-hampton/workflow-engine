<?php

class WorkflowCollection
{

    private $deptId;
    private $name;
    private $description;
    private $requestId;
    private $objMike;
    private $parentId;
    private $systemId;
    private $objMysql;
    private $arrValidationErrors;
    private $new;
    private $processCout = 0;
    private $arrFieldMapping = array(
        "description" => array("accessor" => "getDescription", "mutator" => "setDescription", "required" => false),
        "request_type" => array("accessor" => "getName", "mutator" => "setName", "required" => true),
        "system_id" => array("accessor" => "getSystemId", "mutator" => "setSystemId", "required" => true),
        "dept_id" => array("accessor" => "getDeptId", "mutator" => "setDeptId", "required" => true)
    );
    public $arrCollection = array();

    public function __construct ($intWorkflowCollectionId = null, $objMike = null)
    {
        $this->objMysql = new Mysql2();

        if ( $intWorkflowCollectionId === null && $objMike === null )
        {
            return false;
        }

        if ( $intWorkflowCollectionId !== null )
        {
            $this->requestId = $intWorkflowCollectionId;
        }

        $this->objMike = $objMike;
    }

    public function loadObject ($arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrFieldMapping[$formField]) )
            {
                $mutator = $this->arrFieldMapping[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrFieldMapping[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }
    }

    protected function getWorkflowCollectionData ($objMike)
    {

        if ( method_exists ($objMike, "getParentId") )
        {
            die ("No");
        }
        else
        {
            die ("Yes");
        }

        if ( method_exists ($objMike, "getSource_id") && $objMike->getSource_id () != "" )
        {
            $parentId = $objMike->getSource_id ();
        }
        else
        {
            $id = $objMike->getId ();
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

        $this->setWorkflow ($workflowData['request_id']);

        $result2 = $objMysql->_select ("workflow.workflows", array(), array("workflow_id" => $this->workflow));
        $this->setRequestType ($result2[0]['request_id']);

        //$this->setWorkflow ($workflowData['workflow_id']);
        $this->setStatus ($workflowData['start_step']);

        return $workflowData;
    }

    /**
     * @return mixed
     */
    public function getDeptId ()
    {
        return $this->deptId;
    }

    /**
     * @param mixed $deptId
     */
    public function setDeptId ($deptId)
    {
        $this->arrCollection['dept_id'] = $deptId;
        $this->deptId = $deptId;
    }

    /**
     * @return mixed
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName ($name)
    {
        $this->name = $name;
        $this->arrCollection['request_type'] = $name;
    }

    public function getSystemId ()
    {
        return $this->systemId;
    }

    public function setSystemId ($systemId)
    {
        $this->systemId = $systemId;
        $this->arrCollection['system_id'] = $systemId;
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
        $this->arrCollection['description'] = $description;
    }

    /**
     * @return mixed
     */
    public function getRequestId ()
    {
        return $this->requestId;
    }

    public function getNew ()
    {
        return $this->new;
    }

    public function setNew ($new)
    {
        $this->new = $new;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId ($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * 
     * @return type
     */
    function getParentId ()
    {
        return $this->parentId;
    }

    /**
     * 
     * @param type $parentId
     */
    function setParentId ($parentId)
    {
        $this->parentId = $parentId;
    }
    
    /**
     * 
     * @return type
     */
    public function getProcessCout ()
    {
        return $this->processCout;
    }

    /**
     * 
     * @param type $processCout
     */
    public function setProcessCout ($processCout)
    {
        $this->processCout = $processCout;
    }

    public function getNextWorkflow ()
    {
        if ( $this->objMike !== null )
        {
            $workflowCollection = $this->getWorkflowCollectionData ($this->objMike);
            $id = 1;

            if ( $workflowCollection['id'] == $id )
            {
                return new Workflow ($workflowCollection['workflow_id'], $this->objMike);
            }
        }
        else
        {
            // get starting workflow
            $sql = "SELECT * FROM workflow.workflow_mapping wm
                    INNER JOIN workflow.workflows w ON w.workflow_id = wm.workflow_from
                    WHERE w.request_id = ? AND wm.first_workflow = 1
                    ";
            $arrResult = $this->objMysql->_query ($sql, array($this->requestId));

            if ( empty ($arrResult) )
            {
                return false;
            }

            return new Workflow ($arrResult[0]['workflow_id']);
        }
    }

    public function getMappedWorkflows ()
    {
        $sql = "SELECT w.* FROM workflow.workflows w
                INNER JOIN workflow.request_types r ON r.request_id = w.`request_id`
                                    WHERE w.request_id = ?";

        $arrResult = $this->objMysql->_query ($sql, array($this->requestId));

        return $arrResult;
    }

    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrCollection[$fieldName]) || trim ($this->arrCollection[$fieldName]) == "" )
                {
                    $this->arrValidationErrors[] = $fieldName;
                    $errorCount++;
                }
            }
        }

        if ( $errorCount > 0 )
        {
            return FALSE;
        }

        return TRUE;
    }

    public function save ()
    {
        if ( $this->new === true )
        {
            $this->objMysql->_insert ("workflow.request_types", $this->arrCollection);
        }
        else
        {
            
        }
    }

    public function checkNameExists ($name)
    {
        $arrResult = $this->objMysql->_select ("workflow.request_types", array(), array("request_type" => $name));

        if ( isset ($arrResult[0]) && !empty ($arrResult[0]) )
        {
            return true;
        }

        return false;
    }

    public function delete ()
    {
        if ( !is_numeric ($this->requestId) )
        {
            throw new Exception ("REQUEST ID HAS NOT BEEN SET");
        }

        $this->objMysql->_delete ("workflow.request_types", array("request_id" => $this->requestId));
    }

}
