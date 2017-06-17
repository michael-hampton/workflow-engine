<?php

abstract class BaseTimerEvent
{

    protected $objMysql;

    /**
     * The value for the tmrevn_uid field.
     * @var        string
     */
    protected $tmrevn_uid;

    /**
     * The value for the prj_uid field.
     * @var        string
     */
    protected $prj_uid;

    /**
     * The value for the evn_uid field.
     * @var        string
     */
    protected $evn_uid;

    /**
     * The value for the tmrevn_option field.
     * @var        string
     */
    protected $tmrevn_option = 'DAILY';

    /**
     * The value for the tmrevn_start_date field.
     * @var        int
     */
    protected $tmrevn_start_date;

    /**
     * The value for the tmrevn_end_date field.
     * @var        int
     */
    protected $tmrevn_end_date;

    /**
     * The value for the tmrevn_day field.
     * @var        string
     */
    protected $tmrevn_day = '';

    /**
     * The value for the tmrevn_hour field.
     * @var        string
     */
    protected $tmrevn_hour = '';

    /**
     * The value for the tmrevn_minute field.
     * @var        string
     */
    protected $tmrevn_minute = '';

    /**
     * The value for the tmrevn_configuration_data field.
     * @var        string
     */
    protected $tmrevn_configuration_data;

    /**
     * The value for the tmrevn_next_run_date field.
     * @var        int
     */
    protected $tmrevn_next_run_date;

    /**
     * The value for the tmrevn_last_run_date field.
     * @var        int
     */
    protected $tmrevn_last_run_date;

    /**
     * The value for the tmrevn_last_execution_date field.
     * @var        int
     */
    protected $tmrevn_last_execution_date;

    /**
     * The value for the tmrevn_status field.
     * @var        string
     */
    protected $tmrevn_status = 'ACTIVE';

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [tmrevn_uid] column value.
     * 
     * @return     string
     */
    public function getTmrevnUid ()
    {
        return $this->tmrevn_uid;
    }

    /**
     * Get the [prj_uid] column value.
     * 
     * @return     string
     */
    public function getPrjUid ()
    {
        return $this->prj_uid;
    }

    /**
     * Get the [evn_uid] column value.
     * 
     * @return     string
     */
    public function getEvnUid ()
    {
        return $this->evn_uid;
    }

    /**
     * Get the [tmrevn_option] column value.
     * 
     * @return     string
     */
    public function getTmrevnOption ()
    {
        return $this->tmrevn_option;
    }

    /**
     * Get the [optionally formatted] [tmrevn_start_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getTmrevnStartDate ($format = 'Y-m-d')
    {
        if ( $this->tmrevn_start_date === null || $this->tmrevn_start_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->tmrevn_start_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->tmrevn_start_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [tmrevn_start_date] as date/time value: " .
                var_export ($this->tmrevn_start_date, true));
            }
        }
        else
        {
            $ts = $this->tmrevn_start_date;
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
     * Get the [optionally formatted] [tmrevn_end_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getTmrevnEndDate ($format = 'Y-m-d')
    {
        if ( $this->tmrevn_end_date === null || $this->tmrevn_end_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->tmrevn_end_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->tmrevn_end_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [tmrevn_end_date] as date/time value: " .
                var_export ($this->tmrevn_end_date, true));
            }
        }
        else
        {
            $ts = $this->tmrevn_end_date;
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
     * Get the [tmrevn_day] column value.
     * 
     * @return     string
     */
    public function getTmrevnDay ()
    {
        return $this->tmrevn_day;
    }

    /**
     * Get the [tmrevn_hour] column value.
     * 
     * @return     string
     */
    public function getTmrevnHour ()
    {
        return $this->tmrevn_hour;
    }

    /**
     * Get the [tmrevn_minute] column value.
     * 
     * @return     string
     */
    public function getTmrevnMinute ()
    {
        return $this->tmrevn_minute;
    }

    /**
     * Get the [tmrevn_configuration_data] column value.
     * 
     * @return     string
     */
    public function getTmrevnConfigurationData ()
    {
        return $this->tmrevn_configuration_data;
    }

    /**
     * Get the [optionally formatted] [tmrevn_next_run_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getTmrevnNextRunDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->tmrevn_next_run_date === null || $this->tmrevn_next_run_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->tmrevn_next_run_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->tmrevn_next_run_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [tmrevn_next_run_date] as date/time value: " .
                var_export ($this->tmrevn_next_run_date, true));
            }
        }
        else
        {
            $ts = $this->tmrevn_next_run_date;
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
     * Get the [optionally formatted] [tmrevn_last_run_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getTmrevnLastRunDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->tmrevn_last_run_date === null || $this->tmrevn_last_run_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->tmrevn_last_run_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->tmrevn_last_run_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [tmrevn_last_run_date] as date/time value: " .
                var_export ($this->tmrevn_last_run_date, true));
            }
        }
        else
        {
            $ts = $this->tmrevn_last_run_date;
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
     * Get the [optionally formatted] [tmrevn_last_execution_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getTmrevnLastExecutionDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->tmrevn_last_execution_date === null || $this->tmrevn_last_execution_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->tmrevn_last_execution_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->tmrevn_last_execution_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [tmrevn_last_execution_date] as date/time value: " .
                var_export ($this->tmrevn_last_execution_date, true));
            }
        }
        else
        {
            $ts = $this->tmrevn_last_execution_date;
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
     * Get the [tmrevn_status] column value.
     * 
     * @return     string
     */
    public function getTmrevnStatus ()
    {
        return $this->tmrevn_status;
    }

    /**
     * Set the value of [tmrevn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_uid !== $v )
        {
            $this->tmrevn_uid = $v;
        }
    }

// setTmrevnUid()
    /**
     * Set the value of [prj_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPrjUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->prj_uid !== $v )
        {
            $this->prj_uid = $v;
        }
    }

// setPrjUid()
    /**
     * Set the value of [evn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_uid !== $v )
        {
            $this->evn_uid = $v;
        }
    }

// setEvnUid()
    /**
     * Set the value of [tmrevn_option] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnOption ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_option !== $v || $v === 'DAILY' )
        {
            $this->tmrevn_option = $v;
        }
    }

// setTmrevnOption()
    /**
     * Set the value of [tmrevn_start_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTmrevnStartDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [tmrevn_start_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->tmrevn_start_date !== $ts )
        {
            $this->tmrevn_start_date = date ("Y-m-d", $ts);
        }
    }

// setTmrevnStartDate()
    /**
     * Set the value of [tmrevn_end_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTmrevnEndDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [tmrevn_end_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->tmrevn_end_date !== $ts )
        {
            $this->tmrevn_end_date = date ("Y-m-d", $ts);
        }
    }

// setTmrevnEndDate()
    /**
     * Set the value of [tmrevn_day] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnDay ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_day !== $v || $v === '' )
        {
            $this->tmrevn_day = $v;
        }
    }

// setTmrevnDay()
    /**
     * Set the value of [tmrevn_hour] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnHour ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_hour !== $v || $v === '' )
        {
            $this->tmrevn_hour = $v;
        }
    }

// setTmrevnHour()
    /**
     * Set the value of [tmrevn_minute] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnMinute ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_minute !== $v || $v === '' )
        {
            $this->tmrevn_minute = $v;
        }
    }

// setTmrevnMinute()
    /**
     * Set the value of [tmrevn_configuration_data] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnConfigurationData ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_configuration_data !== $v )
        {
            $this->tmrevn_configuration_data = $v;
        }
    }

// setTmrevnConfigurationData()
    /**
     * Set the value of [tmrevn_next_run_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTmrevnNextRunDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [tmrevn_next_run_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->tmrevn_next_run_date !== $ts )
        {
            $this->tmrevn_next_run_date = date ("Y-m-d H:i:s", $ts);
        }
    }

// setTmrevnNextRunDate()
    /**
     * Set the value of [tmrevn_last_run_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTmrevnLastRunDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [tmrevn_last_run_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->tmrevn_last_run_date !== $ts )
        {
            $this->tmrevn_last_run_date = $ts;
        }
    }

// setTmrevnLastRunDate()
    /**
     * Set the value of [tmrevn_last_execution_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTmrevnLastExecutionDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [tmrevn_last_execution_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->tmrevn_last_execution_date !== $ts )
        {
            $this->tmrevn_last_execution_date = $ts;
        }
    }

// setTmrevnLastExecutionDate()
    /**
     * Set the value of [tmrevn_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTmrevnStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tmrevn_status !== $v || $v === 'ACTIVE' )
        {
            $this->tmrevn_status = $v;
        }
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function fromArray ($arr)
    {
        $keys = array("timer_id", "WORKFLOW_ID", "event_id", "TMREVN_OPTION", "TMREVN_START_DATE", "TMREVN_END_DATE", "TMREVN_DAY", "TMREVN_HOUR", "TMREVN_MINUTE", "TMREVN_CONFIGURATION_DATA", "TMREVN_NEXT_RUN_DATE");

        if ( array_key_exists ($keys[0], $arr) )
        {
            $this->setTmrevnUid ($arr[$keys[0]]);
        }
        if ( array_key_exists ($keys[1], $arr) )
        {
            $this->setPrjUid ($arr[$keys[1]]);
        }
        if ( array_key_exists ($keys[2], $arr) )
        {
            $this->setEvnUid ($arr[$keys[2]]);
        }
        if ( array_key_exists ($keys[3], $arr) )
        {
            $this->setTmrevnOption ($arr[$keys[3]]);
        }
        if ( array_key_exists ($keys[4], $arr) )
        {
            $this->setTmrevnStartDate ($arr[$keys[4]]);
        }
        if ( array_key_exists ($keys[5], $arr) )
        {
            $this->setTmrevnEndDate ($arr[$keys[5]]);
        }
        if ( array_key_exists ($keys[6], $arr) )
        {
            $this->setTmrevnDay ($arr[$keys[6]]);
        }
        if ( array_key_exists ($keys[7], $arr) )
        {
            $this->setTmrevnHour ($arr[$keys[7]]);
        }
        if ( array_key_exists ($keys[8], $arr) )
        {
            $this->setTmrevnMinute ($arr[$keys[8]]);
        }
        if ( array_key_exists ($keys[9], $arr) )
        {
            $this->setTmrevnConfigurationData ($arr[$keys[9]]);
        }
        if ( array_key_exists ($keys[10], $arr) )
        {
            $this->setTmrevnNextRunDate ($arr[$keys[10]]);
        }
//        if ( array_key_exists ($keys[11], $arr) )
//        {
//            $this->setTmrevnLastRunDate ($arr[$keys[11]]);
//        }
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->tmrevn_uid) !== "" && is_numeric ($this->tmrevn_uid) )
        {   
            $this->objMysql->_update ("workflow.timer_event", array("workflow_id" => $this->prj_uid,
                "EVN_UID" => $this->evn_uid,
                "TMREVN_OPTION" => $this->tmrevn_option,
                "TMREVN_START_DATE" => $this->tmrevn_start_date,
                "TMREVN_END_DATE" => $this->tmrevn_end_date,
                "TMREVN_DAY" => $this->tmrevn_day,
                "TMREVN_HOUR" => $this->tmrevn_hour,
                "TMREVN_MINUTE" => $this->tmrevn_minute,
                "TMREVN_CONFIGURATION_DATA" => $this->tmrevn_configuration_data,
                "TMREVN_NEXT_RUN_DATE" => $this->tmrevn_next_run_date,
                "TMREVN_STATUS" => $this->tmrevn_status), array("TMREVN_UID" => $this->tmrevn_uid)
            );
        }
        else
        {
            $id = $this->objMysql->_insert ("workflow.timer_event", array("workflow_id" => $this->prj_uid,
                "EVN_UID" => $this->evn_uid,
                "TMREVN_OPTION" => $this->tmrevn_option,
                "TMREVN_START_DATE" => $this->tmrevn_start_date,
                "TMREVN_END_DATE" => $this->tmrevn_end_date,
                "TMREVN_DAY" => $this->tmrevn_day,
                "TMREVN_HOUR" => $this->tmrevn_hour,
                "TMREVN_MINUTE" => $this->tmrevn_minute,
                "TMREVN_CONFIGURATION_DATA" => $this->tmrevn_configuration_data,
                "TMREVN_NEXT_RUN_DATE" => $this->tmrevn_next_run_date,
                "TMREVN_STATUS" => $this->tmrevn_status));

            return $id;
        }
    }

    public function validate ()
    {
        return true;
    }

}
