<?php

abstract class BaseUser implements Persistent
{

    use \BusinessModel\Validator;

    /**
     * The value for the usr_username field.
     * @var        string
     */
    protected $username;

    /**
     * The value for the usr_status field.
     * @var        string
     */
    protected $status = 1;

    /**
     * The value for the usr_email field.
     * @var        string
     */
    protected $user_email;

    /**
     * The value for the usr_firstname field.
     * @var        string
     */
    protected $firstName;

    /**
     * The value for the usr_lastname field.
     * @var        string
     */
    protected $lastName;

    /**
     * The value for the dep_uid field.
     * @var        string
     */
    protected $dept_id;
    protected $team_id;
    protected $img_src;

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $userId;

    /**
     * The value for the usr_password field.
     * @var        string
     */
    protected $password;

    /**
     * The value for the usr_create_date field.
     * @var        int
     */
    protected $usr_create_date;

    /**
     * The value for the usr_update_date field.
     * @var        int
     */
    protected $usr_update_date;

    /**
     * The value for the usr_country field.
     * @var        string
     */
    protected $usr_country = '';

    /**
     * The value for the usr_city field.
     * @var        string
     */
    protected $usr_city = '';

    /**
     * The value for the usr_location field.
     * @var        string
     */
    protected $usr_location = '';

    /**
     * The value for the usr_address field.
     * @var        string
     */
    protected $usr_address = '';

    /**
     * The value for the usr_phone field.
     * @var        string
     */
    protected $usr_phone = '';

    /**
     * The value for the usr_fax field.
     * @var        string
     */
    protected $usr_fax = '';

    /**
     * The value for the usr_cellular field.
     * @var        string
     */
    protected $usr_cellular = '';

    /**
     * The value for the usr_birthday field.
     * @var        int
     */
    protected $usr_birthday;

    /**
     * The value for the usr_reports_to field.
     * @var        string
     */
    protected $usr_reports_to = '';

    /**
     * The value for the usr_replaced_by field.
     * @var        string
     */
    protected $usr_replaced_by = '';

    /**
     * The value for the usr_ux field.
     * @var        string
     */
    protected $usr_ux = 'NORMAL';

    /**
     * The value for the usr_last_login field.
     * @var        int
     */
    protected $usr_last_login;

    /**
     * The value for the usr_zip_code field.
     * @var        string
     */
    protected $usr_zip_code = '';
    protected $roleName;
    protected $department;
    private $objMysql;
    protected $roleId;
    protected $supervisor;
    protected $userReplaces;

    /**
     * The value for the usr_due_date field.
     * @var        int
     */
    protected $usr_due_date;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();
    protected $arrFieldMapping = array(
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
        "USR_CREATE_DATE" => array("accessor" => "getUsrCreateDate", "mutator" => "setUsrCreateDate", "required" => false),
        "USR_UPDATE_DATE" => array("accessor" => "getUsrUpdateDate", "mutator" => "setUsrUpdateDate", "required" => false),
        "replacedBy" => array("accessor" => "getUserReplaces", "mutator" => "setUserReplaces", "required" => false),
        //"expirationDate" => array("accessor" => "getRoleId", "mutator" => "", "required" => false),
        "address" => array("accessor" => "getUsrAddress", "mutator" => "setUsrAddress", "required" => false),
        "state" => array("accessor" => "getUsrCity", "mutator" => "setUsrCity", "required" => false),
        "location" => array("accessor" => "getUsrLocation", "mutator" => "setUsrLocation", "required" => false),
        "USR_DUE_DATE" => array("accessor" => "getUsrDueDate", "mutator" => "setUsrDueDate", "required" => false),
        "USR_BIRTHDAY" => array("accessor" => "getUsrBirthday", "mutator" => "setUsrBirthday", "required" => false),
        "USR_LOGGED_NEXT_TIME" => array("accessor" => "getUsrLastLogin", "mutator" => "setUsrLastLogin", "required" => false),
        "country" => array("accessor" => "getUsrCountry", "mutator" => "setUsrCountry", "required" => false),
        "phoneNo" => array("accessor" => "getUsrPhone", "mutator" => "setUsrPhone", "required" => false),
        "postcode" => array("accessor" => "getUsrZipCode", "mutator" => "setUsrZipCode", "required" => false),
    );
    private $arrUser = array();
    private $auditUser;

    /**
     * 
     * @param type $userId
     */
    public function __construct ($userId = null, Users $objAuditUser = null)
    {
        if ( $objAuditUser !== null )
        {
            $this->auditUser = $objAuditUser->getUserId ();
        }

        if ( $userId !== null )
        {
            $this->userId = $userId;
        }
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrUser
     */
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
    }

    /**
     * Get the [usr_username] column value.
     * 
     * @return     string
     */
    public function getUsername ()
    {
        return $this->username;
    }

    /**
     * Get the [usr_reports_to] column value.
     * 
     * @return     string
     */
    public function getUsrReportsTo ()
    {
        return $this->usr_reports_to;
    }

    /**
     * Get the [usr_replaced_by] column value.
     * 
     * @return     string
     */
    public function getUserReplaces ()
    {
        return $this->userReplaces;
    }

    /**
     * Get the [usr_status] column value.
     * 
     * @return     string
     */
    public function getStatus ()
    {
        return $this->status;
    }

    /**
     * Get the [optionally formatted] [usr_last_login] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrLastLogin ($format = 'Y-m-d H:i:s')
    {
        if ( $this->usr_last_login === null || $this->usr_last_login === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_last_login) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_last_login);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [usr_last_login] as date/time value: " .
                var_export ($this->usr_last_login, true));
            }
        }
        else
        {
            $ts = $this->usr_last_login;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    /**
     * Get the [usr_country] column value.
     * 
     * @return     string
     */
    public function getUsrCountry ()
    {
        return $this->usr_country;
    }

    /**
     * Get the [usr_city] column value.
     * 
     * @return     string
     */
    public function getUsrCity ()
    {
        return $this->usr_city;
    }

    /**
     * Get the [usr_location] column value.
     * 
     * @return     string
     */
    public function getUsrLocation ()
    {
        return $this->usr_location;
    }

    /**
     * Get the [usr_address] column value.
     * 
     * @return     string
     */
    public function getUsrAddress ()
    {
        return $this->usr_address;
    }

    /**
     * Get the [usr_phone] column value.
     * 
     * @return     string
     */
    public function getUsrPhone ()
    {
        return $this->usr_phone;
    }

    /**
     * Get the [usr_fax] column value.
     * 
     * @return     string
     */
    public function getUsrFax ()
    {
        return $this->usr_fax;
    }

    /**
     * Get the [usr_cellular] column value.
     * 
     * @return     string
     */
    public function getUsrCellular ()
    {
        return $this->usr_cellular;
    }

    /**
     * Get the [usr_zip_code] column value.
     * 
     * @return     string
     */
    public function getUsrZipCode ()
    {
        return $this->usr_zip_code;
    }

    /**
     * Get the [usr_firstname] column value.
     * 
     * @return     string
     */
    public function getFirstName ()
    {
        return $this->firstName;
    }

    /**
     * Get the [optionally formatted] [usr_birthday] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrBirthday ($format = 'Y-m-d')
    {
        if ( $this->usr_birthday === null || $this->usr_birthday === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_birthday) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_birthday);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [usr_birthday] as date/time value: " .
                var_export ($this->usr_birthday, true));
            }
        }
        else
        {
            $ts = $this->usr_birthday;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    /**
     * Get the [usr_lastname] column value.
     * 
     * @return     string
     */
    public function getLastName ()
    {
        return $this->lastName;
    }

    /**
     * Get the [dep_uid] column value.
     * 
     * @return     string
     */
    public function getDept_id ()
    {
        return $this->dept_id;
    }

    /**
     * 
     * @return type
     */
    public function getTeam_id ()
    {
        return $this->team_id;
    }

    /**
     * 
     * @return type
     */
    public function getImg_src ()
    {
        return $this->img_src;
    }

    /**
     * Set the value of [usr_username] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsername ($username)
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
     * Set the value of [usr_last_login] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrLastLogin ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse date/time value for [usr_last_login] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_last_login !== $ts )
        {
            $this->usr_last_login = date ("Y-m-d H:i:s");
        }
    }

    /**
     * Set the value of [usr_replaced_by] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUserReplaces ($userReplaces)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $userReplaces !== null && !is_int ($userReplaces) && is_numeric ($userReplaces) )
        {
            $userReplaces = (int) $userReplaces;
        }

        $this->userReplaces = $userReplaces;
        $this->arrUser['user_replaces'] = $userReplaces;
    }

    /**
     * Set the value of [usr_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStatus ($status)
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
     * Set the value of [usr_country] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrCountry ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_country !== $v || $v === '' )
        {
            $this->usr_country = $v;
        }
    }

    /**
     * Set the value of [usr_city] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrCity ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_city !== $v || $v === '' )
        {
            $this->usr_city = $v;
        }
    }

    /**
     * Set the value of [usr_location] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrLocation ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_location !== $v || $v === '' )
        {
            $this->usr_location = $v;
        }
    }

    /**
     * Set the value of [usr_address] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrAddress ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_address !== $v || $v === '' )
        {
            $this->usr_address = $v;
        }
    }

    /**
     * Set the value of [usr_phone] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrPhone ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_phone !== $v || $v === '' )
        {
            $this->usr_phone = $v;
        }
    }

    /**
     * Set the value of [usr_fax] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrFax ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_fax !== $v || $v === '' )
        {
            $this->usr_fax = $v;
        }
    }

    /**
     * Set the value of [usr_cellular] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrCellular ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_cellular !== $v || $v === '' )
        {
            $this->usr_cellular = $v;
        }
    }

    /**
     * Set the value of [usr_zip_code] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrZipCode ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_zip_code !== $v || $v === '' )
        {
            $this->usr_zip_code = $v;
        }
    }

    /**
     * Set the value of [usr_firstname] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFirstName ($firstName)
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
     * Set the value of [usr_lastname] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setLastName ($lastName)
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
     * Set the value of [usr_birthday] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrBirthday ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [usr_birthday] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_birthday !== $ts )
        {
            $this->usr_birthday = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [usr_reports_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrReportsTo ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_reports_to !== $v || $v === '' )
        {
            $this->usr_reports_to = $v;
        }
    }

    /**
     * Set the value of [dep_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDept_id ($dept_id)
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
    public function setTeam_id ($team_id)
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
    public function setImg_src ($img_src)
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
     * Get the [usr_email] column value.
     * 
     * @return     string
     */
    public function getUser_email ()
    {
        return $this->user_email;
    }

    /**
     * Get the [optionally formatted] [usr_due_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrDueDate ($format = 'Y-m-d')
    {
        if ( $this->usr_due_date === null || $this->usr_due_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_due_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_due_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [usr_due_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->usr_due_date;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    /**
     * Get the [optionally formatted] [usr_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrCreateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->usr_create_date === null || $this->usr_create_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_create_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_create_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [usr_create_date] as date/time value:");
            }
        }
        else
        {
            $ts = $this->usr_create_date;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    /**
     * Get the [optionally formatted] [usr_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrUpdateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->usr_update_date === null || $this->usr_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_update_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_update_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [usr_update_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->usr_update_date;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    /**
     * Get the [usr_id] column value.
     * 
     * @return     int
     */
    public function getUserId ()
    {
        return $this->userId;
    }

    /**
     * Set the value of [usr_email] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUser_email ($user_email)
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
     * Set the value of [usr_due_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrDueDate ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [usr_due_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_due_date !== $ts )
        {
            $this->usr_due_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [usr_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrCreateDate ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [usr_create_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_create_date !== $ts )
        {
            $this->usr_create_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [usr_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrUpdateDate ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [usr_update_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_update_date !== $ts )
        {
            $this->usr_update_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUserId ($userId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $userId !== null && !is_int ($userId) && is_numeric ($userId) )
        {
            $userId = (int) $userId;
        }

        $this->userId = $userId;
    }

    /**
     * Get the [usr_password] column value.
     * 
     * @return     string
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * Set the value of [usr_password] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPassword ($password)
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
    public function getRoleName ()
    {
        return $this->roleName;
    }

    /**
     * 
     * @param type $roleName
     */
    public function setRoleName ($roleName)
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
    public function getRoleId ()
    {
        return $this->roleId;
    }

    /**
     * 
     * @param type $roleId
     */
    public function setRoleId ($roleId)
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
    public function getDepartment ()
    {
        return $this->department;
    }

    /**
     * 
     * @param type $department
     */
    public function setDepartment ($department)
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
     * @return boolean
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( isset ($this->userId) && is_numeric ($this->userId) )
        {
            $this->objMysql->_update ("user_management.poms_users", [
                "username" => $this->username,
                "status" => $this->status,
                "password" => $this->password,
                "user_email" => $this->user_email,
                "firstName" => $this->firstName,
                "lastName" => $this->lastName,
                "team_id" => $this->team_id,
                "dept_id" => $this->dept_id,
                "user_replaces" => $this->userReplaces,
                "USR_LAST_LOGIN" => $this->usr_last_login,
                "USR_REPORTS_TO" => $this->usr_reports_to,
                "USR_BIRTHDAY" => $this->usr_birthday,
                "USR_ZIP_CODE" => $this->usr_zip_code,
                "USR_CELLULAR" => $this->usr_cellular,
                "USR_FAX" => $this->usr_fax,
                "USR_LOCATION" => $this->usr_location,
                "USR_CITY" => $this->usr_city,
                "USR_COUNTRY" => $this->usr_country,
                "USR_UPDATE_DATE" => $this->usr_update_date,
                "USR_CREATE_DATE" => $this->usr_create_date,
                "USR_DUE_DATE" => $this->usr_due_date,
                "USR_PHONE" => $this->usr_phone,
                "USR_ADDRESS" => $this->usr_address
                    ], ["usrid" => $this->userId]);

            $this->auditLog ('UPD', $objAuditUser);
        }
        else
        {
            $id = $this->objMysql->_insert ("user_management.poms_users", [
                "username" => $this->username,
                "status" => $this->status,
                "password" => $this->password,
                "user_email" => $this->user_email,
                "firstName" => $this->firstName,
                "lastName" => $this->lastName,
                "team_id" => $this->team_id,
                "dept_id" => $this->dept_id,
                "user_replaces" => $this->userReplaces,
                "USR_LAST_LOGIN" => $this->usr_last_login,
                "USR_REPORTS_TO" => $this->usr_reports_to,
                "USR_BIRTHDAY" => $this->usr_birthday,
                "USR_ZIP_CODE" => $this->usr_zip_code,
                "USR_CELLULAR" => $this->usr_cellular,
                "USR_FAX" => $this->usr_fax,
                "USR_LOCATION" => $this->usr_location,
                "USR_CITY" => $this->usr_city,
                "USR_COUNTRY" => $this->usr_country,
                "USR_UPDATE_DATE" => $this->usr_update_date,
                "USR_CREATE_DATE" => $this->usr_create_date,
                "USR_DUE_DATE" => $this->usr_due_date,
                "USR_PHONE" => $this->usr_phone,
                "USR_ADDRESS" => $this->usr_address
                    ]
            );

            $this->auditLog ('INS', $objAuditUser);
            return $id;
        }

        return true;
    }

    /**
     * AuditLog
     *
     * @param string $option    Option
     * @param array  $arrayData Data
     * o
     * @return void
     */
    private function auditLog ($option)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $firstName = trim ($this->firstName) !== '' ? ' - First Name: ' . $this->firstName : '';
            $lastName = trim ($this->lastName) !== '' ? ' - Last Name: ' . $this->lastName : '';
            $email = trim ($this->user_email) !== '' ? ' - Email: ' . $this->user_email : '';
            $dueDate = '';
            $status = trim ($this->status) !== '' ? ' - Status: ' . $this->status : '';
            $address = trim ($this->user_address) !== '' ? ' - Address: ' . $this->usr_address : '';
            $phone = trim ($this->usr_phone) !== '' ? ' - Phone: ' . $this->usr_phone : '';
            $zipCode = trim ($this->usr_zip_code) !== '' ? ' - Zip Code: ' . $this->usr_zip_code : '';
            $position = '';
            //$position = (array_key_exists('USR_POSITION', $arrayData))? ' - Position: ' . $arrayData['USR_POSITION'] : '';
            $role = trim ($this->roleId) !== '' ? ' - Role: ' . $this->roleId : '';

            $str = 'User Name: ' . $this->username . ' - User ID: (' . $this->userId . ')' .
                    $firstName . $lastName . $email . $dueDate . $status . $address . $phone . $zipCode . $position . $role;
            $title = $option === 'INS' ? 'NEW USER ADDED' : 'USER UPDATED';

            $this->objMysql->_insert ("user_management.user_log", ['user_id' => $this->auditUser, 'detail' => $str, 'summary' => $title, 'date_updated' => date ("Y-m-d H:i:s")]);
        } catch (\Exception $e) {
            throw $e;
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
            return false;
        }

        return true;
    }

    public function disableUser ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_update ("user_management.poms_users", array("status" => $this->status), array("userId" => $this->userId));
    }

    public function removeRolesFromUser ($usrId, $roleId = null)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $arrWhere['userId'] = $usrId;

        if ( $roleId !== null )
        {
            $arrWhere['roleId'] = $roleId;
        }

        $this->objMysql->_delete ("user_management.user_roles", $arrWhere);
    }

    public function assignRoleToUser ($userId, $roleId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->objMysql->_insert ("user_management.user_roles", array("userId" => $userId, "roleId" => $roleId));
    }

}
