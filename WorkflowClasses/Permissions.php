<?php

class Permissions
{

    use Validator;

    private $deptId;
    private $userId;
    private $teamId;
    private $permissionType;
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
            $this->lists['users'][] = $userId;
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
            $this->teamId = $teamId;
            $this->lists['teams'][] = $teamId;
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

    /**
     * 
     * @param type $type
     */
    public function save ($type = '')
    {
        switch ($this->permissionType) {
            case "user":
                $lists = implode (",", $this->lists['users']);
                break;

            case "team":
                $lists = implode (",", $this->lists['teams']);

                break;

            case "department":
                $lists = implode (",", $this->lists['dept']);
                break;
        }

        if ( $type == "master" )
        {
            $this->objMysql->_query ("INSERT INTO workflow.step_permission (step_id, master_permission, permission_type) VALUES (?,?, ?)
  				ON DUPLICATE KEY UPDATE master_permission = ?, permission_type = ?", [$this->stepId, $lists, $this->permissionType, $lists, $this->permissionType]);
        }
        else
        {
            $this->objMysql->_query ("INSERT INTO workflow.step_permission (step_id, permission, permission_type) VALUES (?,?, ?)
  				ON DUPLICATE KEY UPDATE permission = ?, permission_type = ?", [$this->stepId, $lists, $this->permissionType, $lists, $this->permissionType]);
        }

        unset ($this->lists);
        unset ($this->userId);
        unset ($this->deptId);
        unset ($this->teamId);
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

}
