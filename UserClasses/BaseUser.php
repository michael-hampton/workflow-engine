<?php

abstract class BaseUser
{

    use Validator;

    private $username;
    private $status;
    private $user_email;
    private $firstName;
    private $lastName;
    private $dept_id;
    private $team_id;
    private $img_src;
    private $userId;
    private $password;
    private $roleName;
    private $department;
    private $objMysql;
    private $roleId;
    private $supervisor;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    private $validationFailures = array();
    private $arrFieldMapping = array(
        "username" => array("accessor" => "getUsername", "mutator" => "setUsername", "required" => true),
        "firstName" => array("accessor" => "getFirstName", "mutator" => "setFirstName", "required" => true),
        "lastName" => array("accessor" => "getLastName", "mutator" => "setLastName", "required" => true),
        "dept_id" => array("accessor" => "getDept_id", "mutator" => "setDept_id", "required" => true),
        "team_id" => array("accessor" => "getTeam_id", "mutator" => "setTeam_id", "required" => false),
        "img_src" => array("accessor" => "getImg_src", "mutator" => "setImg_src", "required" => false),
        "userId" => array("accessor" => "getUserId", "mutator" => "setUserId", "required" => false),
        "status" => array("accessor" => "getStatus", "mutator" => "setStatus", "required" => true),
        "USR_PASSWORD" => array("accessor" => "getPassword", "mutator" => "setPassword", "required" => false),
        "user_email" => array("accessor" => "getUser_email", "mutator" => "setUser_email", "required" => true),
        "role_name" => array("accessor" => "getRoleName", "mutator" => "setRoleName", "required" => false),
        "department" => array("accessor" => "getDepartment", "mutator" => "setDepartment", "required" => false),
        "role_id" => array("accessor" => "getRoleId", "mutator" => "setRoleId", "required" => false),
        "USR_CREATE_DATE" => array("accessor" => "getRoleId", "mutator" => "", "required" => false),
        "USR_UPDATE_DATE" => array("accessor" => "getRoleId", "mutator" => "", "required" => false),
    );
    public $arrUser = array();

    /**
     * 
     * @param type $userId
     */
    public function __construct ($userId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $userId !== null )
        {
            $this->userId = $userId;
        }

        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrUser
     */
    public function loadObject ($arrUser)
    {
        foreach ($arrUser as $formField => $formValue) {

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
    }

    /**
     * 
     * @return type
     */
    function getUsername ()
    {
        return $this->username;
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
     * @return type
     */
    function getFirstName ()
    {
        return $this->firstName;
    }

    /**
     * 
     * @return type
     */
    function getLastName ()
    {
        return $this->lastName;
    }

    /**
     * 
     * @return type
     */
    function getDept_id ()
    {
        return $this->dept_id;
    }

    /**
     * 
     * @return type
     */
    function getTeam_id ()
    {
        return $this->team_id;
    }

    /**
     * 
     * @return type
     */
    function getImg_src ()
    {
        return $this->img_src;
    }

    /**
     * 
     * @param type $username
     */
    function setUsername ($username)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $username !== null && !is_string ($username) )
        {
            $username = (string) $username;
        }

        $this->username = $username;
        $this->arrUser['username'] = $username;
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
        $this->arrUser['status'] = $status;
    }

    /**
     * 
     * @param type $firstName
     */
    function setFirstName ($firstName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $firstName !== null && !is_string ($firstName) )
        {
            $firstName = (string) $firstName;
        }

        $this->firstName = $firstName;
        $this->arrUser['firstName'] = $firstName;
    }

    /**
     * 
     * @param type $lastName
     */
    function setLastName ($lastName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $lastName !== null && !is_string ($lastName) )
        {
            $lastName = (string) $lastName;
        }

        $this->lastName = $lastName;
        $this->arrUser['lastName'] = $lastName;
    }

    /**
     * 
     * @param type $dept_id
     */
    function setDept_id ($dept_id)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $dept_id !== null && !is_int ($dept_id) && is_numeric ($dept_id) )
        {
            $dept_id = (int) $dept_id;
        }

        $this->dept_id = $dept_id;
        $this->arrUser['dept_id'] = $dept_id;
    }

    /**
     * 
     * @param type $team_id
     */
    function setTeam_id ($team_id)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $team_id !== null && !is_int ($team_id) && is_numeric ($team_id) )
        {
            $team_id = (int) $team_id;
        }

        $this->team_id = $team_id;
        $this->arrUser['team_id'] = $team_id;
    }

    /**
     * 
     * @param type $img_src
     */
    function setImg_src ($img_src)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $img_src !== null && !is_string ($img_src) )
        {
            $img_src = (string) $img_src;
        }

        $this->img_src = $img_src;
        $this->arrUser['img_src'] = $img_src;
    }

    /**
     * 
     * @return type
     */
    function getUser_email ()
    {
        return $this->user_email;
    }

    /**
     * 
     * @return type
     */
    function getUserId ()
    {
        return $this->userId;
    }

    /**
     * 
     * @param type $user_email
     */
    function setUser_email ($user_email)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $user_email !== null && !is_string ($user_email) )
        {
            $user_email = (string) $user_email;
        }

        $this->user_email = $user_email;
        $this->arrUser['user_email'] = $user_email;
    }

    /**
     * 
     * @param type $userId
     */
    function setUserId ($userId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $userId !== null && !is_int ($userId) && is_numeric ($userId) )
        {
            $userId = (int) $userId;
        }

        $this->userId = $userId;
        $this->arrUser['usrid'] = $userId;
    }

    /**
     * 
     * @return type
     */
    function getPassword ()
    {
        return $this->password;
    }

    /**
     * 
     * @param type $password
     */
    function setPassword ($password)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $password !== null && !is_string ($password) )
        {
            $password = (string) $password;
        }

        $this->password = $password;
        $this->arrUser['password'] = $password;
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
    
    public function getSupervisor ()
    {
        return $this->supervisor;
    }

    public function setSupervisor ($supervisor)
    {
        $this->supervisor = $supervisor;
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
        $this->department = $department;
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
        if ( isset ($this->userId) && is_numeric ($this->userId) )
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_update ("user_management.poms_users", $this->arrUser, array("usrid" => $this->userId));


                if ( $this->roleId != "" && is_numeric ($this->roleId) )
                {
                    $this->objMysql->_update ("user_management.user_roles", array("roleId" => $this->roleId), array("userId" => $this->userId));
                }
                return true;
            }

            return false;
        }
        else
        {
            if ( $this->validate () === true )
            {
                $this->objMysql->_insert ("user_management.poms_users", $this->arrUser);

                if ( $this->roleId != "" && is_numeric ($this->roleId) )
                {
                    $this->objMysql->_insert ("user_management.user_roles", array("roleId" => $this->roleId, "userId" => $this->userId));
                }

                return true;
            }

            return false;
        }
    }
    /**
     * 
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrUser[$fieldName]) || trim ($this->arrUser[$fieldName]) == "" )
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

    public function disableUser ()
    {
        $this->objMysql->_update ("user_management.poms_users", array("status" => $this->status), array("userId" => $this->userId));
    }

}
