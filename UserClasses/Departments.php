<?php

class Departments
{

    private $id;
    private $department;
    private $departmentManager;
    private $objMysql;
    private $status;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    private $validationFailures = array();
    
    private $arrFieldMapping = array(
        "id" => array("accessor" => "getId", "mutator" => "setId", "required" => false),
        "department" => array("accessor" => "getDepartment", "mutator" => "setDepartment", "required" => true),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => true),
        "department_manager" => array("accessor" => "getDepartmentManager", "mutator" => "setDepartmentManager", "required" => true)
    );
    public $arrDepartment = array();

    /**
     * 
     * @param type $deptId
     */
    public function __construct ($deptId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $deptId !== null )
        {
            $this->id = $deptId;
        }
    }

    /**
     * 
     * @param type $arrDepartment
     * @return boolean
     */
    public function loadObject ($arrDepartment)
    {
        foreach ($arrDepartment as $formField => $formValue) {

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
    function getDepartment ()
    {
        return $this->department;
    }

    /**
     * 
     * @param type $department
     */
    function setDepartment ($department)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $department !== null && !is_string ($department) )
        {
            $department = (string) $department;
        }

        $this->department = $department;
        $this->arrDepartment['department'] = $department;
    }

    /**
     * 
     * @return type
     */
    function getId ()
    {
        return $this->id;
    }

    /**
     * 
     * @param type $id
     */
    function setId ($id)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $id !== null && !is_int ($id) && is_numeric ($id) )
        {
            $id = (int) $id;
        }

        $this->id = $id;
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
        $this->arrDepartment['status'] = $status;
    }

    /**
     * 
     * @return type
     */
    function getDepartmentManager ()
    {
        return $this->departmentManager;
    }

    /**
     * 
     * @param type $departmentManager
     */
    function setDepartmentManager ($departmentManager)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $departmentManager !== null && !is_string ($departmentManager) )
        {
            $departmentManager = (string) $departmentManager;
        }

        $this->departmentManager = $departmentManager;
        $this->arrDepartment['department_manager'] = $departmentManager;
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
        if ( isset ($this->id) && is_numeric ($this->id) )
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_update ("user_management.departments", $this->arrDepartment, array("id" => $this->id));
                return true;
            }
            
            return false;
        }
        else
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_insert ("user_management.departments", $this->arrDepartment);
                return true;
            }
            
            return false;
        }
    }

    /**
     * 
     */
    public function deleteDepartmentAction ()
    {
        $this->objMysql->_delete ("user_management.departments", array("id" => $this->id));
    }

    /**
     * 
     */
    public function disableDepartment ()
    {
        $this->objMysql->_update ("user_management.departments", array("status" => $this->status), array("id" => $this->id));
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    public function checkNameExists ($name)
    {
        $result = $this->objMysql->_select ("user_management.departments", array(), array("department" => $name));

        if ( isset ($result[0]['department']) && !empty ($result[0]['department']) )
        {
            return true;
        }
        
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;

        if ( $this->checkNameExists ($this->department) )
        {
            $this->validationFailures[] = "exists";
            $errorCount++;
        }

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrDepartment[$fieldName]) || trim ($this->arrDepartment[$fieldName]) == "" )
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
