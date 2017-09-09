<?php

namespace BusinessModel;

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

    public function __construct (\Workflow $objWorkflow = null)
    {
        if ( $objWorkflow !== null )
        {
            $this->workflowId = $objWorkflow->getWorkflowId ();
        }

        $this->objMysql = new \Mysql2();
    }

    /**
     * 
     * @return type
     */
    public function getProcessPermissions ()
    {
        $results = $this->objMysql->_query ("SELECT 
                                                GROUP_CONCAT(`user_id`) AS users, 
                                                permission_type 
                                        FROM workflow.process_permission 
                                        WHERE workflow_id = ?
                                        GROUP BY `permission_type`", [$this->workflowId]);

        $permissions = [];

        foreach ($results as $result) {
            $permissions[$result['permission_type']] = $result['users'];
        }

        return $permissions;
    }

    /**
     * 
     * @return type
     */
    public function getAllProcessPermissions ()
    {
        $results = $this->objMysql->_query ("SELECT 
                                                GROUP_CONCAT(`user_id`) AS users, 
                                                permission_type,
                                                workflow_id
                                        FROM workflow.process_permission 
                                        GROUP BY `permission_type`, workflow_id");

        $processPermissions = [];

        foreach ($results as $result) {
            $processPermissions[$result['workflow_id']][$result['permission_type']] = explode (",", $result['users']);
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
        $objProcessPermissions = new \ProcessPermission();

        $currentLists = $this->getProcessPermissions ();
        $userList = isset ($currentLists['user']) ? explode (",", $currentLists['user']) : array();
        $teamList = isset ($currentLists['team']) ? explode (",", $currentLists['team']) : array();

        $arrUsers = [];
        $arrTeams = [];

        if ( !empty ($arrPermissions) )
        {
            foreach ($arrPermissions as $arrPermission) {
                if ( $arrPermission['objectType'] === "team" )
                {
                    $arrTeams[] = $arrPermission['id'];
                }

                if ( $arrPermission['objectType'] === "user" )
                {
                    $arrUsers[] = $arrPermission['id'];
                }
            }
        }


        if ( !empty ($userList) )
        {
            foreach ($userList as $user) {
                if ( !in_array ($user, $arrUsers) )
                {
                    $objProcessPermissions->setPermissionType ("user");
                    $objProcessPermissions->setWorkflowId ($this->workflowId);
                    $objProcessPermissions->setUserId ($user);
                    $objProcessPermissions->delete ();
                }
            }
        }


        if ( !empty ($teamList) )
        {
            foreach ($teamList as $team) {
                if ( !in_array ($team, $arrTeams) )
                {
                    $objProcessPermissions->setPermissionType ("team");
                    $objProcessPermissions->setWorkflowId ($this->workflowId);
                    $objProcessPermissions->setUserId ($team);
                    $objProcessPermissions->delete ();
                }
            }
        }

        if ( !empty ($arrPermissions) )
        {
            foreach ($arrPermissions as $arrPermission) {
                if ( $arrPermission['objectType'] === "team" )
                {
                    if ( !$this->validateTeamId ($arrPermission['id']) )
                    {
                        throw new \Exception ("Team doesnt exist");
                    }
                }
                else
                {
                    if ( !$this->validateUserId ($arrPermission['id']) )
                    {
                        throw new \Exception ("User doesnt exist");
                    }
                }

                $objProcessPermissions->setUserId ($arrPermission['id']);
                $objProcessPermissions->setPermissionType ($arrPermission['objectType']);
                $objProcessPermissions->setWorkflowId ($this->workflowId);

                if ( $objProcessPermissions->validate () )
                {
                    $objProcessPermissions->save ();
                }
                else
                {
                    $message = '';

                    foreach ($objProcessPermissions->getValidationFailures () as $validationFailure) {
                        $message .= "</br> " . $validationFailure;
                    }

                    throw new \Exception ("Process permissions could not be saved " . $message);
                }
            }
        }
    }

}
