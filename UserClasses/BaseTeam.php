<?php

abstract class BaseTeam implements Persistent
{

    private $id;
    private $teamName;
    private $objMysql;
    private $status;
    private $deptId;
    private $teamId;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    private $validationFailures = array();
    private $arrFieldMapping = array(
        "id" => array("accessor" => "getId", "mutator" => "setId", "required" => false),
        "team_id" => array("accessor" => "getId", "mutator" => "setId", "required" => false),
        "team_name" => array("accessor" => "getTeamName", "mutator" => "setTeamName", "required" => true),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => true),
        "dept_id" => array("accessor" => "getDeptId", "mutator" => "setDeptId", "required" => true),
    );
    public $arrTeam = array();

    public function __construct ($deptId = null, $teamId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $deptId !== null )
        {
            $this->id = $deptId;
        }

        if ( $teamId !== null )
        {
            $this->teamId = $teamId;
            $this->id = $teamId;
        }
    }

    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrFieldMapping[$formField]) )
            {
                $mutator = $this->arrFieldMapping[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrFieldMapping[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }

    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $id !== null && !is_int ($id) && is_numeric ($id) )
        {
            $id = (int) $id;
        }

        $this->id = $id;
    }

    public function getStatus ()
    {
        return $this->status;
    }

    public function setStatus ($status)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $status !== null && !is_int ($status) && is_numeric ($status) )
        {
            $status = (int) $status;
        }

        $this->status = $status;
        $this->arrTeam['status'] = $status;
    }

    public function getTeamName ()
    {
        return $this->teamName;
    }

    public function getDeptId ()
    {
        return $this->deptId;
    }

    public function setTeamName ($teamName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $teamName !== null && !is_string ($teamName) )
        {
            $teamName = (string) $teamName;
        }

        $this->teamName = $teamName;
        $this->arrTeam['team_name'] = $teamName;
    }

    public function setDeptId ($deptId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $deptId !== null && !is_int ($deptId) && is_numeric ($deptId) )
        {
            $deptId = (int) $deptId;
        }

        $this->deptId = $deptId;
        $this->arrTeam['dept_id'] = $deptId;
    }

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    public function save ()
    {
        if ( isset ($this->id) && is_numeric ($this->id) )
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_update ("user_management.teams", $this->arrTeam, array("team_id" => $this->id));
                return true;
            }

            return false;
        }
        else
        {
            if ( $this->validate () === true )
            {
                $id = $this->objMysql->_insert ("user_management.teams", $this->arrTeam);
                $this->setId ($id);
                return true;
            }

            return false;
        }
    }

    public function disableTeam ()
    {
        $this->objMysql->_update ("user_management.teams", array("status" => $this->status), array("id" => $this->id));
    }

    public function deleteTeam ()
    {

        $this->objMysql->_delete ("user_management.teams", array("team_id" => $this->id));
    }

    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrTeam[$fieldName]) || trim ($this->arrTeam[$fieldName]) == "" )
                {
                    $this->validationFailures[] = $fieldName;
                    $errorCount++;
                }
            }
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function removeUserOfGroup (Users $objUser)
    {
        if ( !is_numeric ($objUser->getUserId ()) )
        {
            throw new Exception ("Invalid ids given");
        }

        $this->objMysql->_update ("user_management.poms_users", array("team_id" => null), array("usrid" => $objUser->getUserId ()));
    }

    public function addUserOfGroup ($groupUid, $userUid)
    {
        if ( !is_numeric ($groupUid) || !is_numeric ($userUid) )
        {
            throw new Exception ("Invalid ids given");
        }

        $this->objMysql->_update ("user_management.poms_users", array("team_id" => $groupUid), array("usrid" => $userUid));
    }

}
