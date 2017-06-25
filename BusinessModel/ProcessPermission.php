<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProcessPermission
 *
 * @author michael.hampton
 */
class ProcessPermission
{

    use Validator;

    private $workflowId;
    private $objMysql;

    public function __construct (Workflow $objWorkflow = null)
    {
        if ( $objWorkflow !== null )
        {
            $this->workflowId = $objWorkflow->getWorkflowId ();
        }

        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @return type
     */
    public function getProcessPermissions ()
    {
        $result = $this->objMysql->_select ("workflow.process_permissions", [], ["workflow_id" => $this->workflowId]);

        return $result;
    }

    /**
     * 
     * @return type
     */
    public function getAllProcessPermissions ()
    {
        $results = $this->objMysql->_select ("workflow.process_permissions");

        $processPermissions = [];

        foreach ($results as $result) {
            $processPermissions[$result['workflow_id']][] = $result;
        }

        return $processPermissions;
    }

    /**
     * 
     * @param type $arrPermissions
     * @throws Exception
     */
    public function create ($arrPermissions)
    {

        foreach ($arrPermissions as $arrPermission) {
            if ( $arrPermission['objectType'] === "team" )
            {
                if ( !$this->validateTeamId ($arrPermission['id']) )
                {
                    throw new Exception ("Team doesnt exist");
                }
            }
            else
            {
                if ( !$this->validateUserId ($arrPermission['id']) )
                {
                    throw new Exception ("User doesnt exist");
                }
            }

            $objProcessPermissions = new ProcessPermissions();
            $objProcessPermissions->setUserId ($arrPermission['id']);
            $objProcessPermissions->setPermissionType ($arrPermission['objectType']);
            $objProcessPermissions->setWorkflowId ($this->workflowId);

            if ( $objProcessPermissions->validate () )
            {
                die("OK");
            }
            else
            {
                $message = '';
                
                foreach ($objProcessPermissions->getValidationFailures() as $validationFailure) {
                    $message .= "</br> " . $validationFailure;
                }
                
                throw new Exception("Process permissions could not be saved " . $message);
            }
        }
        echo '<pre>';
        print_r ($arrData);
        die;
    }

}
