<?php

class BaseAudit
{

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';

    /**
     * The value for the app_status field.
     * @var        string
     */
    protected $app_status = '';

    /**
     * The value for the history_date field.
     * @var        int
     */
    protected $history_date;

    /**
     * The value for the history_data field.
     * @var        string
     */
    protected $history_data;
    
    private $objMysql;
    
    public function getConnection()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid ()
    {
        return $this->app_uid;
    }

    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid ()
    {
        return $this->pro_uid;
    }

    /**
     * Get the [tas_uid] column value.
     * 
     * @return     string
     */
    public function getTasUid ()
    {
        return $this->tas_uid;
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
     * Get the [app_status] column value.
     * 
     * @return     string
     */
    public function getAppStatus ()
    {
        return $this->app_status;
    }

    /**
     * Get the [optionally formatted] [history_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getHistoryDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->history_date === null || $this->history_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->history_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->history_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new \Exception ("Unable to parse value of [history_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->history_date;
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
     * Get the [history_data] column value.
     * 
     * @return     string
     */
    public function getHistoryData ()
    {
        return $this->history_data;
    }

    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->app_uid !== $v || $v === '' )
        {
            $this->app_uid = $v;
        }
    }

    public function setProUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->pro_uid !== $v || $v === '' )
        {
            $this->pro_uid = $v;
        }
    }

// setProUid()
    /**
     * Set the value of [tas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tas_uid !== $v || $v === '' )
        {
            $this->tas_uid = $v;
        }
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
     * Set the value of [app_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->app_status !== $v || $v === '' )
        {
            $this->app_status = $v;
        }
    }

    /**
     * Set the value of [history_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setHistoryDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [history_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->history_date !== $ts )
        {
            $this->history_date = date("Y-m-d H;i:s", $ts);
        }
    }

    /**
     * Set the value of [history_data] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setHistoryData ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->history_data !== $v )
        {
            $this->history_data = $v;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save ()
    {
       
        if ( $this->objMysql === null )
        {
            $this->getConnection();
        }
        try {
            $this->objMysql->_insert("task_manager.audit", array("username" => $this->usr_uid, "audit_date" => $this->history_date, "project_id" => $this->app_uid, "message" => $this->history_data, "case_id" => $this->tas_uid, "workflow_id" => $this->pro_uid));
        } catch (PropelException $e) {
            throw $e;
        }
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     PropelException
     * @see        save()
     */
    protected function doSave ($con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if ( !$this->alreadyInSave )
        {
            $this->alreadyInSave = true;
            // If this object has been modified, then save it to the database.
            if ( $this->isModified () )
            {
                if ( $this->isNew () )
                {
                    $pk = AppHistoryPeer::doInsert ($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                    // should always be true here (even though technically
                    // BasePeer::doInsert() can insert multiple rows).
                    $this->setNew (false);
                }
                else
                {
                    $affectedRows += AppHistoryPeer::doUpdate ($this, $con);
                }
                $this->resetModified (); // [HL] After being saved an object is no longer 'modified'
            }
            $this->alreadyInSave = false;
        }
        return $affectedRows;
    }

// doSave()
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
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate ($columns = null)
    {
        $res = $this->doValidate ($columns);
        if ( $res === true )
        {
            $this->validationFailures = array();
            return true;
        }
        else
        {
            $this->validationFailures = $res;
            return false;
        }
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param      array $columns Array of column names to validate.
     * @return     mixed <code>true</code> if all validations pass; 
      array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate ($columns = null)
    {
        return true;
    }
}
