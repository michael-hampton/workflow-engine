<?php

class StepPermissions extends Permissions
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
        $arrPermissions = [];

        $masterPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level = 'master'
                                                        GROUP BY permission_type");
        $ROPermissions = $this->objMysql->_query ("SELECT permission_type, GROUP_CONCAT(permission SEPARATOR ', ') AS permissions
                                                        FROM workflow.step_permission 
                                                        WHERE access_level != 'master'
                                                        GROUP BY permission_type");

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

            $objPermissions = new Permissions ($this->stepId);

            foreach ($data['selectedPermissions'] as $permission) {
                $objPermissions->setPermissionType (trim ($permission['objectType']));
                $objPermissions->setAccessLevel (trim ($permission['permissionType']));

                if ( $permission['objectType'] == "team" )
                {
                    $objPermissions->setTeamId ($permission['id']);
                }
                else
                {
                    $objPermissions->setUserId ($permission['id']);
                }

                $objPermissions->save ();
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
    public function validateUserPermissions (Users $objUser)
    {

        $permissions = $this->getProcessPermissions ();
        $teamId = $objUser->getTeam_id ();
        $userId = $objUser->getUserId ();

        // 1 for master 2 for RO

        if ( empty ($permissions) )
        {
            return true;
        }

        $userList = explode (",", $permissions['RO']['user']);
        $teamList = explode (",", $permissions['RO']['team']);
        $userMaster = explode (",", $permissions['master']['user']);
        $teamMaster = explode (",", $permissions['master']['team']);

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
        } else {
            return false;
        }

        return $permissionFlag;
    }

}
