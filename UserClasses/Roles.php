<?php

class Roles
{

    protected $roleId;
    protected $roleName;
    protected $permId;
    protected $status;
    public $permName;
    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();
    private $arrFieldMapping = array(
        "role_id" => array("accessor" => "getRoleId", "mutator" => "setRoleId", "required" => false),
        "role_name" => array("accessor" => "getRoleName", "mutator" => "setRoleName", "required" => true),
        "perm_id" => array("accessor" => "getPermId", "mutator" => "setPermId", "required" => false),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => false),
        "perm_name" => array("accessor" => "getPermName", "mutator" => "setPermName", "required" => false),
    );
    public $arrRoles = array();
    private $objMysql;

    /**
     * 
     * @param type $roleId
     * @param type $permId
     */
    public function __construct ($roleId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $roleId !== null )
        {
            $this->roleId = $roleId;
        }
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @return type
     */
    function getPermName ()
    {
        return $this->permName;
    }
    
     /**
     * 
     * @param type $permName
     */
    function setPermName ($permName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $permName !== null && !is_string ($permName) )
        {
            $permName = (string) $permName;
        }

        $this->permName = $permName;
    }

    /**
     * 
     * @param type $arrRole
     * @return boolean
     */
    public function loadObject ($arrRole)
    {
        foreach ($arrRole as $formField => $formValue) {

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

    /**
     * 
     * @return type
     */
    function getRoleId ()
    {
        return $this->roleId;
    }

    /**
     * 
     * @return type
     */
    function getRoleName ()
    {
        return $this->roleName;
    }

    /**
     * 
     * @return type
     */
    function getPermId ()
    {
        return $this->permId;
    }

    /**
     * 
     * @param type $roleId
     */
    function setRoleId ($roleId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $roleId !== null && !is_int ($roleId) && is_numeric ($roleId) )
        {
            $roleId = (int) $roleId;
        }

        $this->roleId = $roleId;
    }

    /**
     * 
     * @param type $roleName
     */
    function setRoleName ($roleName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $roleName !== null && !is_string ($roleName) )
        {
            $roleName = (string) $roleName;
        }

        $this->roleName = $roleName;
        $this->arrRoles['role_name'] = $roleName;
    }

    /**
     * 
     * @param type $permId
     */
    function setPermId ($permId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $permId !== null && !is_int ($permId) && is_numeric ($permId) )
        {
            $permId = (int) $permId;
        }

        $this->permId = $permId;
    }

    /**
     * 
     * @return type
     */
    function getStatus ()
    {
        return $this->status;
    }

    /**
     * 
     * @param type $status
     */
    function setStatus ($status)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $status !== null && !is_int ($status) && is_numeric ($status) )
        {
            $status = (int) $status;
        }

        $this->status = $status;
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

    /**
     * 
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( isset ($this->roleId) && is_numeric ($this->roleId) )
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_update ("user_management.roles", $this->arrRoles, array("id" => $this->roleId));

                return true;
            }

            return false;
        }
        else
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_insert ("user_management.roles", $this->arrRoles);

                return true;
            }

            return false;
        }
    }

    /**
     * 
     */
    public function disableRole ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_update ("user_management.roles", array("status" => $this->status), array("role_id" => $this->roleId));
    }

    /**
     * 
     */
    public function deleteRolePerms ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_delete ("user_management.role_perms", array("role_id" => $this->roleId, "perm_id" => $this->permId));
    }

    public function addRolePerms ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_insert ("user_management.role_perms", array("role_id" => $this->roleId, "perm_id" => $this->permId));
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    private function checkNameExists ($name)
    {

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("user_management.roles", array(), array("role_name" => $name));

        if ( isset ($result[0]['role_name']) && !empty ($result[0]['role_name']) )
        {
            return true;
        }
    }

    /**
     * 
     * @return boolean
     */
    private function validate ()
    {
        $errorCount = 0;

        if ( $this->checkNameExists ($this->roleName) )
        {
            $this->validationFailures[] = "exists";
            $errorCount++;
        }

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrRoles[$fieldName]) || trim ($this->arrRoles[$fieldName]) == "" )
                {
                    $this->validationFailures[] = $fieldName;
                    $errorCount++;
                }
            }
        }

        if ( $errorCount > 0 )
        {
            return FALSE;
        }

        return TRUE;
    }

}
