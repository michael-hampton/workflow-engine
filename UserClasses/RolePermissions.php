<?php

class RolePermissions extends Roles
{

    public $permName;
    private $arrFieldMapping = array(
        "perm_id" => array("accessor" => "getPermId", "mutator" => "setPermId", "required" => false),
        "perm_name" => array("accessor" => "getPermName", "mutator" => "setPermName", "required" => false),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => false),
        "role_id" => array("accessor" => "getRoleId", "mutator" => "setRoleId", "required" => false),
         "role_name" => array("accessor" => "getRoleName", "mutator" => "setRoleName", "required" => false),
    );

    /**
     * 
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
                } else {
                    echo "No";
                }
            }
        }

        return true;
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    private function checkNameExists ($name)
    {
        $result = $this->objMysql->_select ("user_management.permissions", array(), array("perm_name" => $name));

        if ( isset ($result[0]['perm_name']) && !empty ($result[0]['perm_name']) )
        {
            return true;
        }
    }

    private function validate ()
    {
        $errorCount = 0;

        if ( $this->checkNameExists ($this->permName) )
        {
            $this->validationFailures[] = "exists";
            $errorCount++;
        }

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrPermissions[$fieldName]) || trim ($this->arrPermissions[$fieldName]) == "" )
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

    /**
     * 
     */
    public function save ()
    {
        if ( $this->validate () )
        {
            if ( isset ($this->permId) && is_numeric ($this->permId) )
            {
                $this->objMysql->_update ("user_management.permissions", array("perm_name" => $this->permName, "module" => "task_manager"), array("id" => $this->permId));
            }
            else
            {
                $permissionId = $this->objMysql->_insert ("user_management.permissions", array("perm_name" => $this->permName, "module" => "task_manager"));

                if ( isset ($this->roleId) && is_numeric ($this->roleId) )
                {
                    $this->setPermId($permissionId);
                    $this->addRolePerms();
                }
            }
        } else {
            return false;
        }
    }

}
