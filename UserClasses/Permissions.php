<?php

/**
 * Description of Permissions
 *
 * @author michael.hampton
 */
class Permissions
{

    private $permId;
    private $permName;
    private $status;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    private $validationFailures = array();
    private $arrFieldMapping = array(
        "perm_id" => array("accessor" => "getPermId", "mutator" => "setPermId", "required" => false),
        "perm_name" => array("accessor" => "getPermName", "mutator" => "setPermName", "required" => false),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => false),
    );
    public $arrPermissions = array();
    private $objMysql;

    /**
     * 
     * @param type $roleId
     * @param type $permId
     */
    public function __construct ($permId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $permId !== null )
        {
            $this->permId = $permId;
        }
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
    function getPermId ()
    {
        return $this->permId;
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
        $this->arrPermissions['perm_name'] = $permName;
        $this->arrPermissions['module'] = "task_manager";
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
    public function savePermissions ()
    {
        if ( isset ($this->permId) && is_numeric ($this->permId) )
        {
            $this->objMysql->_update ("user_management.permissions", $this->arrPermissions, array("id" => $this->permId));
        }
        else
        {
            $permissionId = $this->objMysql->_insert ("user_management.permissions", $this->arrPermissions);

            if ( isset ($this->roleId) && is_numeric ($this->roleId) )
            {
                $this->objMysql->_insert ("user_management.role_perms", array("role_id" => $this->roleId, "perm_id" => $permissionId));
            }
        }
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    public function checkNameExists ($name)
    {
        $result = $this->objMysql->_select ("user_management.permissions", array(), array("perm_name" => $name));

        if ( isset ($result[0]['perm_name']) && !empty ($result[0]['perm_name']) )
        {
            return true;
        }
    }

}
