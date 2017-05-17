<?php

class WorkflowCollectionFactory
{
    private $objMysql;

    public function __construct ($requestId = null, $objKondor = null)
    {
        $this->objMysql = new Mysql2();
    }

    public function getSystemWorkflowCollections ($strSystemName = null)
    {

        $sql = "SELECT * FROM workflow.request_types r
                INNER JOIN workflow.workflow_systems s ON s.system_id = r.system_id
                WHERE s.system_name = ?";

        $arrResults = $this->objMysql->_query ($sql, array($strSystemName));


        foreach ($arrResults as $result) {

            $arrWorkflowCollectionObjects[$result['request_type']] = new WorkflowCollection ($result['request_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setDeptId ($result['dept_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setName ($result['request_type']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setDescription ($result['description']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setRequestId ($result['request_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setParentId ($result['parent_id']);
        }

        return $arrWorkflowCollectionObjects;
    }

}
