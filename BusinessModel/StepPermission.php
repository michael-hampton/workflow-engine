<?php

namespace BusinessModel;

class StepPermission
{

    use Validator;

    /**
     *
     * @var type 
     */
    private $stepId;

    /**
     *
     * @var type 
     */
    private $objMysql;

    /**
     * 
     * @param type $stepId
     */
    public function __construct (\Task $objStep = null)
    {
        
        if ( $objStep !== null )
        {
            $this->stepId = $objStep->getTasUid ();
        }

        $this->objMysql = new \Mysql2();
    }

    /**
     * Get list for Process Permissions
     *
     * @var string $pro_uid. Uid for Process
     * @var string $op_uid. Uid for Process Permission
     *
     * @access public
     *
     * @return array
     */
    public function getProcessPermissions ()
    {        
        $arrPermissions = [];
        $this->validateStepUid ();

        $masterPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level = 'master'
                                                        AND step_id = ?
                                                        GROUP BY permission_type", [$this->stepId]);
        $ROPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level = 'RO'
                                                        AND step_id = ?
                                                        GROUP BY permission_type", [$this->stepId]);

        $InputPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level IN('INPUT')
                                                        AND step_id = ?
                                                        GROUP BY permission_type", [$this->stepId]);

        $OuputPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level IN('OUTPUT')
                                                        AND step_id = ?
                                                        GROUP BY permission_type", [$this->stepId]);

        $arrPermissions['master']['team'] = array();
        $arrPermissions['master']['user'] = array();
        $arrPermissions['RO']['team'] = array();
        $arrPermissions['master']['team'] = array();
        $arrPermissions['Input']['team'] = array();
        $arrPermissions['Input']['user'] = array();
        $arrPermissions['Output']['team'] = array();
        $arrPermissions['Output']['user'] = array();

        if ( !empty ($masterPermissions) )
        {

            foreach ($masterPermissions as $masterPermission) {

                if ( $masterPermission['permission_type'] == "team" )
                {
                    $arrPermissions['master']['team'] = $masterPermission['permissions'];
                }
                else
                {
                    $arrPermissions['master']['user'] = $masterPermission['permissions'];
                }
            }
        }

        if ( !empty ($ROPermissions) )
        {
            foreach ($ROPermissions as $ROPermission) {

                if ( $ROPermission['permission_type'] == "team" )
                {
                    $arrPermissions['RO']['team'] = $ROPermission['permissions'];
                }
                else
                {
                    $arrPermissions['RO']['user'] = $ROPermission['permissions'];
                }
            }
        }

        if ( !empty ($InputPermissions) )
        {
            foreach ($InputPermissions as $InputPermission) {

                if ( $InputPermission['permission_type'] == "team" )
                {
                    $arrPermissions['Input']['team'] = $InputPermission['permissions'];
                }
                else
                {
                    $arrPermissions['Input']['user'] = $InputPermission['permissions'];
                }
            }
        }

        if ( !empty ($OuputPermissions) )
        {
            foreach ($OuputPermissions as $OuputPermission) {

                if ( $OuputPermission['permission_type'] == "team" )
                {
                    $arrPermissions['Output']['team'] = $OuputPermission['permissions'];
                }
                else
                {
                    $arrPermissions['Output']['user'] = $OuputPermission['permissions'];
                }
            }
        }


        if ( !empty ($arrPermissions) )
        {
            return $arrPermissions;
        }

        return false;
    }

    /**
     * Save Process Permission
     *
     * @var array $data. Data for Process Permission
     *
     * @access public
     *
     * @return void
     */
    public function saveProcessPermission ($data)
    {
        try {

            $this->validateStepUid ();

            $objTask = new \Task ($this->stepId);

            $objPermissions = new \ObjectPermissions ($objTask);

            if ( isset ($data['selectedPermissions']) && !empty ($data['selectedPermissions']) )
            {
                foreach ($data['selectedPermissions'] as $permission) {


                    if ( $permission['objectType'] == "team" )
                    {
                        $this->validateTeamId ($permission['id']);
                    }
                    else
                    {
                        $this->validateUserId ($permission['id']);
                    }

                    $objPermissions->create ($permission);
                }
            }

            if ( isset ($data['inputList']) && !empty ($data['inputList']) )
            {
                foreach ($data['inputList'] as $input) {
                    if ( $input['objectType'] == "team" )
                    {
                        $this->validateTeamId ($input['id']);
                    }
                    else
                    {
                        $this->validateUserId ($input['id']);
                    }

                    if ( $input['action'] == "add" )
                    {
                        $objPermissions->create ($input);
                    }
                    else
                    {
                        $objPermissions->removeObject ($input['permissionType'], $input['objectType'], $input['id'], $this->stepId);
                    }
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * validates if user has permission for a step
     * @param Users $objUser
     * @return boolean
     */
    public function validateUserPermissions (\Users $objUser)
    {

        $permissions = $this->getProcessPermissions ();
        $teamId = $objUser->getTeam_id ();
        $userId = $objUser->getUserId ();
        
        // 1 for master 2 for RO

        if ( empty ($permissions) )
        {
            return true;
        }

        $userList = [];
        $teamList = [];
        $userMaster = [];
        $teamMaster = [];

        if ( isset ($permissions['RO']['user']) && !empty ($permissions['RO']['user']) )
        {
            $userList = explode (",", $permissions['RO']['user']);
        }

        if ( isset ($permissions['RO']['team']) && !empty ($permissions['RO']['team']) )
        {
            $teamList = explode (",", $permissions['RO']['team']);
        }

        if ( isset ($permissions['master']['user']) && !empty ($permissions['master']['user']) )
        {
            $userMaster = explode (",", $permissions['master']['user']);
        }

        if ( isset ($permissions['master']['team']) && !empty ($permissions['master']['team']) )
        {
            $teamMaster = explode (",", $permissions['master']['team']);
        }

        $ROFlag = 0;
        $masterFlag = 0;

        if ( in_array ($userId, $userList) )
        {
            $ROFlag++;
        }

        if ( in_array ($userId, $userMaster) )
        {
            $masterFlag++;
        }

        if ( in_array ($teamId, $teamList) )
        {
            $ROFlag++;
        }

        if ( in_array ($teamId, $teamMaster) )
        {
            $masterFlag++;
        }

        if ( $masterFlag > 0 )
        {
            return 1;
        }
        elseif ( $ROFlag > 0 )
        {
            return 2;
        }
        else
        {
            return false;
        }

        return $permissionFlag;
    }

    /**
     * Validate Step Uid
     *
     * @return string
     */
    public function validateStepUid ()
    {
        $this->stepId = trim ($this->stepId);
        
        if ( $this->stepId == '' )
        {
            echo "Mike";
            echo debug_backtrace()[1]['function'];
            throw (new \Exception ("STEP ID HAS NOT BEEN SET"));
        }

        $objWorkflowStep = new \WorkflowStep();
        

        if ( !($objWorkflowStep->stepExists ($this->stepId)) )
        {
            throw (new \Exception ("STEP ID DOES NOT EXIST"));
        }
        return $this->stepId;
    }

    /**
     * Check user to group assigned Task (Normal and/or Ad-Hoc Users)
     *
     * @param string $taskUid Unique uid of Task
     * @param string $userUid Unique uid of User
     *
     * return bool
     */
    public function checkUserOrGroupAssignedTask (\Users $objUser)
    {
        $permissions = $this->getProcessPermissions ();

        if ( !isset ($permissions['master']) || empty ($permissions['master']) )
        {
            return false;
        }

        if ( isset ($permissions['master']['user']) && !empty ($permissions['master']['user']) )
        {
            $userList = explode (",", $permissions['master']['user']);

            if ( trim ($objUser->getUserId ()) !== "" && in_array (!empty ($objUser->getUserId ()), $userList) )
            {
                return true;
            }
        }

        if ( isset ($permissions['master']['team']) && !empty ($permissions['master']['team']) )
        {
            $teamList = explode (",", $permissions['master']['team']);

            if ( trim ($objUser->getTeam_id ()) !== "" && in_array (trim ($objUser->getTeam_id ()), $teamList) )
            {
                return TRUE;
            }
        }
        
        return false;
    }

}
