<?php

class StepPermissions
{

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
    public function __construct ($stepId)
    {
        $this->stepId = $stepId;
        $this->objMysql = new Mysql2();
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
        $results = $this->objMysql->_select ("workflow.step_permission", array(), array("step_id" => $this->stepId));


        if ( !empty ($results) )
        {
            return $results;
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

            $objPermissions = new Permissions ($this->stepId);
            $objPermissions->setPermissionType ($data['permissionType']);

            foreach ($data['masterPermissions'] as $id) {
                if ( $data['permissionType'] == "team" )
                {

                    $objPermissions->setTeamId ($id);
                }
                elseif ( $data['permissionType'] == "user" )
                {


                    $objPermissions->setUserId ($id);
                }
                else
                {

                    $objPermissions->setDeptId ($id);
                }
            }

            $objPermissions->save ("master");

            foreach ($data['selectedPermissions'] as $id) {
                if ( $data['permissionType'] == "team" )
                {

                    $objPermissions->setTeamId ($id);
                }
                elseif ( $data['permissionType'] == "user" )
                {


                    $objPermissions->setUserId ($id);
                }
                else
                {

                    $objPermissions->setDeptId ($id);
                }
            }

            $objPermissions->save ();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * validates if user has permission for a step
     * @param Users $objUser
     * @return boolean
     */
    public function validateUserPermissions (Users $objUser)
    {
        
        $permissions = $this->getProcessPermissions ();

        if ( empty ($permissions) )
        {
            return true;
        }

        $permissionType = $permissions[0]['permission_type'];

        $lists = $permissions[0]['permission'];
        $masterList = $permissions[0]['master_permission'];
        $arrLists = explode (",", $lists);
        $arrMaster = explode (",", $masterList);

        switch ($permissionType) {
            case "team":
                $teamId = $objUser->getTeam_id ();
                
                if ( in_array ($teamId, $arrMaster) )
                {
                    return 1;
                }
                elseif ( in_array ($teamId, $arrLists) )
                {
                    return 2;
                }
                else
                {
                    return false;
                }
                break;

            case "user":
                $userId = $objUser->getUserId ();

                if ( in_array ($userId, $arrMaster) )
                {
                    return 1;
                }
                elseif ( in_array ($userId, $arrLists) )
                {
                    return 2;
                }
                else
                {
                    return false;
                }
                break;

            case "department":
                $deptId = $objUser->getDept_id ();

                if ( in_array ($deptId, $arrMaster) )
                {
                    return 1;
                }
                elseif ( in_array ($deptId, $arrLists) )
                {
                    return 2;
                }
                else
                {
                    return false;
                }
                break;
        }
    }

}
