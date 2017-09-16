<?php

abstract class BaseUserProperties implements Persistent
{

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';

    /**
     * The value for the usr_last_update_date field.
     * @var        int
     */
    protected $usr_last_update_date;

    /**
     * The value for the usr_logged_next_time field.
     * @var        int
     */
    protected $usr_logged_next_time = 0;

    /**
     * The value for the usr_password_history field.
     * @var        string
     */
    protected $usr_password_history;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;
    private $objMysql;
    public $doUpdate = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;
    private $arrFieldMapping = array(
        "USR_PASSWORD_HISTORY" => array("accessor" => "getUsrPasswordHistory", "mutator" => "setUsrPasswordHistory", "required" => false),
        "USR_UID" => array("accessor" => "getUsrUid", "mutator" => "setUsrUid", "required" => true),
        "USR_LAST_UPDATE_DATE" => array("accessor" => "getUsrLastUpdateDate", "mutator" => "setUsrLastUpdateDate", "required" => true),
        "USR_LOGGED_NEXT_TIME" => array("accessor" => "getUsrLoggedNextTime", "mutator" => "setUsrLoggedNextTime", "required" => true)
    );

    /**
     * 
     * @param type $arrDepartment
     * @return boolean
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

        return true;
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid ()
    {
        return $this->usr_uid;
    }

    /**
     * Get the [optionally formatted] [usr_last_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getUsrLastUpdateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->usr_last_update_date === null || $this->usr_last_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->usr_last_update_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->usr_last_update_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [usr_last_update_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->usr_last_update_date;
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
     * Get the [usr_logged_next_time] column value.
     * 
     * @return     int
     */
    public function getUsrLoggedNextTime ()
    {
        return $this->usr_logged_next_time;
    }

    /**
     * Get the [usr_password_history] column value.
     * 
     * @return     string
     */
    public function getUsrPasswordHistory ()
    {
        return $this->usr_password_history;
    }

    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_uid !== $v || $v === '' )
        {
            $this->usr_uid = $v;
        }
    }

    /**
     * Set the value of [usr_last_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrLastUpdateDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [usr_last_update_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->usr_last_update_date !== $ts )
        {
            $this->usr_last_update_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [usr_logged_next_time] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrLoggedNextTime ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->usr_logged_next_time !== $v || $v === 0 )
        {
            $this->usr_logged_next_time = $v;
        }
    }

    /**
     * Set the value of [usr_password_history] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrPasswordHistory ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->usr_password_history !== $v )
        {
            $this->usr_password_history = $v;
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     * @throws     Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete ()
    {
        try {
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @return     int The number of rows affected by this insert/update
     * @throws     Exception
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            if ( $this->doUpdate === false )
            {
                $this->objMysql->_insert ("user_management.USER_PROPERTIES", [
                    "USR_UID" => $this->usr_uid,
                    "USR_LAST_UPDATE_DATE" => $this->usr_last_update_date,
                    "USR_LOGGED_NEXT_TIME" => $this->usr_logged_next_time,
                    "USR_PASSWORD_HISTORY" => $this->usr_password_history]);
            }
            else
            {
                $this->objMysql->_update ("user_management.USER_PROPERTIES", [
                    "USR_LAST_UPDATE_DATE" => $this->usr_last_update_date,
                    "USR_LOGGED_NEXT_TIME" => $this->usr_logged_next_time,
                    "USR_PASSWORD_HISTORY" => $this->usr_password_history], ["USR_UID" => $this->usr_uid]);
            }
        } catch (Exception $e) {

            throw $e;
        }
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */

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
                $accessor = $this->arrFieldMapping[$fieldName]['accessor'];

                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->validationFailures[] = $fieldName . " Is empty. It is a required field";
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

}
