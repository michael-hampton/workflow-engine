<?php

class Permissions
{

    use Validator;

    private $deptId;
    private $userId;
    private $teamId;
    private $permissionType;
    private $accessLevel;
    public $lists = array();
    private $objMysql;
    private $stepId;

    /**
     * 
     * @param type $stepId
     */
    public function __construct ($stepId)
    {
        $this->objMysql = new Mysql2();
        $this->stepId = $stepId;
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
        if ( $this->validateDeptId ($deptId) === true )
        {
            $this->deptId[] = $deptId;
            $this->lists['dept'] = $deptId;
        }
    }

    /**
     * @return mixed
     */
    public function getUserId ()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId ($userId)
    {
        if ( $this->validateUserId ($userId) === true )
        {
            $this->userId = $userId;
        }
    }

    /**
     * @return mixed
     */
    public function getTeamId ()
    {
        return $this->teamId;
    }

    /**
     * @param mixed $teamId
     */
    public function setTeamId ($teamId)
    {
        if ( $this->validateTeamId ($teamId) === true )
        {
            $this->userId = $teamId;
        }
    }

    /**
     * @return mixed
     */
    public function getPermissionType ()
    {
        return $this->permissionType;
    }

    /**
     * @param mixed $permissionType
     */
    public function setPermissionType ($permissionType)
    {
        $this->permissionType = $permissionType;
    }

    public function getAccessLevel ()
    {
        return $this->accessLevel;
    }

    public function setAccessLevel ($accessLevel)
    {
        $this->accessLevel = $accessLevel;
    }

    /**
     * 
     * @param type $type
     */
    public function save ()
    {
        $this->objMysql->_query ("INSERT INTO workflow.step_permission (step_id, permission, permission_type, access_level) VALUES (?, ?, ?, ?)
  				ON DUPLICATE KEY UPDATE permission = ?", [$this->stepId, $this->userId, $this->permissionType, $this->accessLevel, $this->userId]);


        unset ($this->userId);
        unset($this->permissionType);
        unset($this->accessLevel);
    }

    /**
     * 
     * @return type
     */
    public function getLists ()
    {
        return $this->database->_select ("workflow.step_permission", array(), array("step_id" => $this->stepId));
    }

    public function delete ()
    {
        if ( empty ($this->getLists ()) )
        {
            throw new Exception ("Lists is empty");
        }

        $lists = explode (",", $this->getLists ());

        foreach ($lists as $key => $list) {
            if ( $list == $this->id )
            {
                unset ($lists[$key]);
            }
        }

        $this->lists = implode (",", $lists);

        // save back again
        $this->save ();
    }
    
    public function deleteAll($permissionType, $permission)
    {
        $this->objMysql->_delete("workflow.step_permission", array("permission_type" => $permission, "permission" => $permission));
    }

}
