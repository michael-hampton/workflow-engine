<?php
/**
 * Base class that represents a row from the 'APP_EVENT' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseAppEvent extends BaseObject implements Persistent
{
    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';
    /**
     * The value for the del_index field.
     * @var        int
     */
    protected $del_index = 0;
    /**
     * The value for the evn_uid field.
     * @var        string
     */
    protected $evn_uid = '';
    /**
     * The value for the app_evn_action_date field.
     * @var        int
     */
    protected $app_evn_action_date;
    /**
     * The value for the app_evn_attempts field.
     * @var        int
     */
    protected $app_evn_attempts = 0;
    /**
     * The value for the app_evn_last_execution_date field.
     * @var        int
     */
    protected $app_evn_last_execution_date;
    /**
     * The value for the app_evn_status field.
     * @var        string
     */
    protected $app_evn_status = 'OPEN';
    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;
    
    protected $id;
    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;
    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid()
    {
        return $this->app_uid;
    }
    /**
     * Get the [del_index] column value.
     * 
     * @return     int
     */
    public function getDelIndex()
    {
        return $this->del_index;
    }
    /**
     * Get the [evn_uid] column value.
     * 
     * @return     string
     */
    public function getEvnUid()
    {
        return $this->evn_uid;
    }
    /**
     * Get the [optionally formatted] [app_evn_action_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppEvnActionDate($format = 'Y-m-d H:i:s')
    {
        if ($this->app_evn_action_date === null || $this->app_evn_action_date === '') {
            return null;
        } elseif (!is_int($this->app_evn_action_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->app_evn_action_date);
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse value of [app_evn_action_date] as date/time value: ");
            }
        } else {
            $ts = $this->app_evn_action_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }
    /**
     * Get the [app_evn_attempts] column value.
     * 
     * @return     int
     */
    public function getAppEvnAttempts()
    {
        return $this->app_evn_attempts;
    }
    /**
     * Get the [optionally formatted] [app_evn_last_execution_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppEvnLastExecutionDate($format = 'Y-m-d H:i:s')
    {
        if ($this->app_evn_last_execution_date === null || $this->app_evn_last_execution_date === '') {
            return null;
        } elseif (!is_int($this->app_evn_last_execution_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->app_evn_last_execution_date);
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse value of [app_evn_last_execution_date] as date/time value: ");
            }
        } else {
            $ts = $this->app_evn_last_execution_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }
    /**
     * Get the [app_evn_status] column value.
     * 
     * @return     string
     */
    public function getAppEvnStatus()
    {
        return $this->app_evn_status;
    }
    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->app_uid !== $v || $v === '') {
            $this->app_uid = $v;
        }
    } 
    
    /**
     * Set the value of [del_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelIndex($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->del_index !== $v || $v === 0) {
            $this->del_index = $v;
        }
    } 
    
    /**
     * Set the value of [evn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->evn_uid !== $v || $v === '') {
            $this->evn_uid = $v;
        }
    }
    
    /**
     * Set the value of [app_evn_action_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppEvnActionDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse date/time value for [app_evn_action_date] from input: ")
            }
        } else {
            $ts = $v;
        }
        if ($this->app_evn_action_date !== $ts) {
            $this->app_evn_action_date = date("Y-m-d H:i:s", $ts);
        }
    } 
    /**
     * Set the value of [app_evn_attempts] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppEvnAttempts($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->app_evn_attempts !== $v || $v === 0) {
            $this->app_evn_attempts = $v;
        }
    }
    
    /**
     * Set the value of [app_evn_last_execution_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppEvnLastExecutionDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse date/time value for [app_evn_last_execution_date] from input: ");
            }
        } else {
            $ts = $v;
        }
        if ($this->app_evn_last_execution_date !== $ts) {
            $this->app_evn_last_execution_date = date("Y-m-d H:i:s", $ts);
           
        }
    }
    
    /**
     * Set the value of [app_evn_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppEvnStatus($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->app_evn_status !== $v || $v === 'OPEN') {
            $this->app_evn_status = $v;
          
        }
    } 
    
    
    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     * @throws     Exception
     */
    public function delete()
    {
        
        try {
            
        } catch (Exception $e) {
           
            throw $e;
        }
    }
    
    private function getConnection() {
        $this->objMysql = new MySql2();
    }
    
    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @return     int The number of rows affected by this insert/update
     * @throws     Exception
     */
    public function save()
    {
        if($this->objMysql === null) {
            $this->getConnection();
        }
        
        try {
            if(trim($this->id) === "") {
            $this->objMysql->_insert("workflow.APP_EVENT",
                  ['APP_UID' => $this->app_uid,
                   'DEL_INDEX' => $this->del_index,
                   'EVN_UID' => $this->evn_uid,
                   'APP_EVN_ACTION_DATE' => $this->app_evn_action_date, 
                   'APP_EVN_ATTEMPTS' => $this->app_evn_attempts,
                   'APP_EVN_LAST_EXECUTION_DATE' => $this->app_evn_last_execution_date,
                   'APP_EVN_STATUS' => $this->app_evn_status,
                   ]
                  );
                
                return true;
            } else {
            
                 $this->objMysql->_update("workflow.APP_EVENT",
                  ['APP_UID' => $this->app_uid,
                   'DEL_INDEX' => $this->del_index,
                   'EVN_UID' => $this->evn_uid,
                   'APP_EVN_ACTION_DATE' => $this->app_evn_action_date, 
                   'APP_EVN_ATTEMPTS' => $this->app_evn_attempts,
                   'APP_EVN_LAST_EXECUTION_DATE' => $this->app_evn_last_execution_date,
                   'APP_EVN_STATUS' => $this->app_evn_status,
                   ], ["id" => $this->id]
                  );
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
    public function getValidationFailures()
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
     * @see        getValidationFailures()
     */
    public function validate ()
    {
        $errorCount = 0;
        foreach ($this->arrayFieldDefinition as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $this->arrayFieldDefinition[$fieldName]['accessor'];
                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->arrValidationErrors[] = $fieldName . " Is empty. It is a required field";
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
    
