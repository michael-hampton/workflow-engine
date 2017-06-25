<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseProcessPermissions
 *
 * @author michael.hampton
 */
abstract class BaseProcessPermission implements Persistent
{

    private $userId;
    private $permissionType;
    private $workflowId;
    private $objMysql;
    private $ValidationFailures;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getUserId ()
    {
        return $this->userId;
    }

    public function getPermissionType ()
    {
        return $this->permissionType;
    }

    public function getWorkflowId ()
    {
        return $this->workflowId;
    }

    /**
     * 
     * @param type $userId
     */
    public function setUserId ($userId)
    {
        $this->userId = $userId;
    }

    /**
     * 
     * @param type $permissionType
     */
    public function setPermissionType ($permissionType)
    {
        $this->permissionType = $permissionType;
    }

    /**
     * 
     * @param type $workflowId
     */
    public function setWorkflowId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }
    
    public function getValidationFailures ()
    {
        return $this->ValidationFailures;
    }

    public function save ()
    {
        $this->objMysql->_insert("workflow.process_permission", ["permission_type" => $this->permissionType, "user_id" => $this->userId, "workflow_id" => $this->workflowId]);
    }
    
    public function delete()
    {
        $this->objMysql->_delete("workflow.process_permission", ["permission_type" => $this->permissionType, "user_id" => $this->userId, "workflow_id" => $this->workflowId]);
    }

    public function validate ()
    {
        $errorCounter = 0;

        if ( trim ($this->workflowId) === "" )
        {
            $this->ValidationFailures[] = "Workflow id is missing";
            $errorCounter++;
        }

        if ( trim ($this->permissionType) === "" )
        {
            $this->ValidationFailures[] = "Permission type is missing";
            $errorCounter++;
        }

        if ( trim ($this->userId) === "" )
        {
            $this->ValidationFailures[] = "User Id is missing";
            $errorCounter++;
        }

        if ( $errorCounter > 0 )
        {
            return FALSE;
        }

        return TRUE;
    }

    public function loadObject (array $arrData)
    {
        
    }

}
