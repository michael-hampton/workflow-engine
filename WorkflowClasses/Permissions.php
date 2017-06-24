<?php

abstract class Permissions implements Persistent
{

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

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
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
        $this->deptId[] = $deptId;
        $this->lists['dept'] = $deptId;
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
        $this->userId = $userId;
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
        $this->userId = $teamId;
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
    
    public function loadObject (array $arrData)
    {
        ;
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->stepId) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->userId) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->permissionType) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->accessLevel) === "" )
        {
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
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
        unset ($this->permissionType);
        unset ($this->accessLevel);
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

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     ObjectPermission
     */
    public function retrieveByPK ($permission, $permissionType)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $v = $this->objMysql->_select ("workflow.step_permission", [], ["permission_type" => $permissionType, "permission" => $permission, "step_id" => $this->stepId]);

        return !empty ($v) > 0 ? $v[0] : null;
    }

}
