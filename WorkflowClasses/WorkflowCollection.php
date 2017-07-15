<?php

class WorkflowCollection extends BaseWorkflowCollection
{

  
    private $objMysql;
   
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

  

    protected function getWorkflowCollectionData ($objMike)
    {

        if ( !method_exists ($objMike, "getParentId") )
        {
            throw new Exception("Cant find parent id");
        }

        if ( method_exists ($objMike, "getSource_id") && $objMike->getSource_id () != "" )
        {
            $parentId = $objMike->getSource_id ();
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

        $result2 = $this->objMysql->_select ("workflow.workflows", array(), array("workflow_id" => $this->workflow));
        $this->setRequestType ($result2[0]['request_id']);

        //$this->setWorkflow ($workflowData['workflow_id']);
        $this->setStatus ($workflowData['start_step']);

        return $workflowData;
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

    

    public function checkNameExists ($name)
    {
        $arrResult = $this->objMysql->_select ("workflow.request_types", array(), array("request_type" => $name));

        if ( isset ($arrResult[0]) && !empty ($arrResult[0]) )
        {
            return true;
        }

        return false;
    }

}
