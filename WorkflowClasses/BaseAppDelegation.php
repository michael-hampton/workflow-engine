<?php

/**
 * Base class that represents a row from the 'APP_DELEGATION' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseAppDelegation implements Persistent
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
     * The value for the delegation_id field.
     * @var        int
     */
    protected $delegation_id;

    /**
     * The value for the app_number field.
     * @var        int
     */
    protected $app_number = 0;

    /**
     * The value for the del_previous field.
     * @var        int
     */
    protected $del_previous = 0;

    /**
     * The value for the del_last_index field.
     * @var        int
     */
    protected $del_last_index = 0;

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
     * The value for the del_type field.
     * @var        string
     */
    protected $del_type = 'NORMAL';

    /**
     * The value for the del_thread field.
     * @var        int
     */
    protected $del_thread = 0;

    /**
     * The value for the del_thread_status field.
     * @var        string
     */
    protected $del_thread_status = 'OPEN';

    /**
     * The value for the del_priority field.
     * @var        string
     */
    protected $del_priority = '3';

    /**
     * The value for the del_delegate_date field.
     * @var        int
     */
    protected $del_delegate_date;

    /**
     * The value for the del_init_date field.
     * @var        int
     */
    protected $del_init_date;

    /**
     * The value for the del_finish_date field.
     * @var        int
     */
    protected $del_finish_date;

    /**
     * The value for the del_task_due_date field.
     * @var        int
     */
    protected $del_task_due_date;

    /**
     * The value for the del_risk_date field.
     * @var        int
     */
    protected $del_risk_date;

    /**
     * The value for the del_duration field.
     * @var        double
     */
    protected $del_duration = 0;

    /**
     * The value for the del_queue_duration field.
     * @var        double
     */
    protected $del_queue_duration = 0;

    /**
     * The value for the del_delay_duration field.
     * @var        double
     */
    protected $del_delay_duration = 0;

    /**
     * The value for the del_started field.
     * @var        int
     */
    protected $del_started = 0;

    /**
     * The value for the del_finished field.
     * @var        int
     */
    protected $del_finished = 0;

    /**
     * The value for the del_delayed field.
     * @var        int
     */
    protected $del_delayed = 0;

    /**
     * The value for the del_data field.
     * @var        string
     */
    protected $del_data;

    /**
     * The value for the app_overdue_percentage field.
     * @var        double
     */
    protected $app_overdue_percentage = 0;

    /**
     * The value for the usr_id field.
     * @var        int
     */
    protected $usr_id = 0;

    /**
     * The value for the pro_id field.
     * @var        int
     */
    protected $pro_id = 0;

    /**
     * The value for the tas_id field.
     * @var        int
     */
    protected $tas_id = 0;

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
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function loadObject (array $arrData)
    {
        
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
     * Get the [del_index] column value.
     * 
     * @return     int
     */
    public function getDelIndex ()
    {
        return $this->del_index;
    }

    /**
     * Get the [delegation_id] column value.
     * 
     * @return     int
     */
    public function getDelegationId ()
    {
        return $this->delegation_id;
    }

    /**
     * Get the [app_number] column value.
     * 
     * @return     int
     */
    public function getAppNumber ()
    {
        return $this->app_number;
    }

    /**
     * Get the [del_previous] column value.
     * 
     * @return     int
     */
    public function getDelPrevious ()
    {
        return $this->del_previous;
    }

    /**
     * Get the [del_last_index] column value.
     * 
     * @return     int
     */
    public function getDelLastIndex ()
    {
        return $this->del_last_index;
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
     * Get the [del_type] column value.
     * 
     * @return     string
     */
    public function getDelType ()
    {
        return $this->del_type;
    }

    /**
     * Get the [del_thread] column value.
     * 
     * @return     int
     */
    public function getDelThread ()
    {
        return $this->del_thread;
    }

    /**
     * Get the [del_thread_status] column value.
     * 
     * @return     string
     */
    public function getDelThreadStatus ()
    {
        return $this->del_thread_status;
    }

    /**
     * Get the [del_priority] column value.
     * 
     * @return     string
     */
    public function getDelPriority ()
    {
        return $this->del_priority;
    }

    /**
     * Get the [optionally formatted] [del_delegate_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelDelegateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_delegate_date === null || $this->del_delegate_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_delegate_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_delegate_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_delegate_date] as date/time value: " .
                var_export ($this->del_delegate_date, true));
            }
        }
        else
        {
            $ts = $this->del_delegate_date;
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
     * Get the [optionally formatted] [del_init_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelInitDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_init_date === null || $this->del_init_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_init_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_init_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_init_date] as date/time value: " .
                var_export ($this->del_init_date, true));
            }
        }
        else
        {
            $ts = $this->del_init_date;
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
     * Get the [optionally formatted] [del_finish_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelFinishDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_finish_date === null || $this->del_finish_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_finish_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_finish_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_finish_date] as date/time value: " .
                var_export ($this->del_finish_date, true));
            }
        }
        else
        {
            $ts = $this->del_finish_date;
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
     * Get the [optionally formatted] [del_task_due_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelTaskDueDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_task_due_date === null || $this->del_task_due_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_task_due_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_task_due_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_task_due_date] as date/time value: " .
                var_export ($this->del_task_due_date, true));
            }
        }
        else
        {
            $ts = $this->del_task_due_date;
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
     * Get the [optionally formatted] [del_risk_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelRiskDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_risk_date === null || $this->del_risk_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_risk_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_risk_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_risk_date] as date/time value: " .
                var_export ($this->del_risk_date, true));
            }
        }
        else
        {
            $ts = $this->del_risk_date;
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
     * Get the [del_duration] column value.
     * 
     * @return     double
     */
    public function getDelDuration ()
    {
        return $this->del_duration;
    }

    /**
     * Get the [del_queue_duration] column value.
     * 
     * @return     double
     */
    public function getDelQueueDuration ()
    {
        return $this->del_queue_duration;
    }

    /**
     * Get the [del_delay_duration] column value.
     * 
     * @return     double
     */
    public function getDelDelayDuration ()
    {
        return $this->del_delay_duration;
    }

    /**
     * Get the [del_started] column value.
     * 
     * @return     int
     */
    public function getDelStarted ()
    {
        return $this->del_started;
    }

    /**
     * Get the [del_finished] column value.
     * 
     * @return     int
     */
    public function getDelFinished ()
    {
        return $this->del_finished;
    }

    /**
     * Get the [del_delayed] column value.
     * 
     * @return     int
     */
    public function getDelDelayed ()
    {
        return $this->del_delayed;
    }

    /**
     * Get the [del_data] column value.
     * 
     * @return     string
     */
    public function getDelData ()
    {
        return $this->del_data;
    }

    /**
     * Get the [app_overdue_percentage] column value.
     * 
     * @return     double
     */
    public function getAppOverduePercentage ()
    {
        return $this->app_overdue_percentage;
    }

    /**
     * Get the [usr_id] column value.
     * 
     * @return     int
     */
    public function getUsrId ()
    {
        return $this->usr_id;
    }

    /**
     * Get the [pro_id] column value.
     * 
     * @return     int
     */
    public function getProId ()
    {
        return $this->pro_id;
    }

    /**
     * Get the [tas_id] column value.
     * 
     * @return     int
     */
    public function getTasId ()
    {
        return $this->tas_id;
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

// setAppUid()
    /**
     * Set the value of [del_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelIndex ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_index !== $v || $v === 0 )
        {
            $this->del_index = $v;
        }
    }

// setDelIndex()
    /**
     * Set the value of [delegation_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelegationId ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->delegation_id !== $v )
        {
            $this->delegation_id = $v;
        }
    }

// setDelegationId()
    /**
     * Set the value of [app_number] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppNumber ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->app_number !== $v || $v === 0 )
        {
            $this->app_number = $v;
        }
    }

// setAppNumber()
    /**
     * Set the value of [del_previous] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelPrevious ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_previous !== $v || $v === 0 )
        {
            $this->del_previous = $v;
        }
    }

// setDelPrevious()
    /**
     * Set the value of [del_last_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelLastIndex ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_last_index !== $v || $v === 0 )
        {
            $this->del_last_index = $v;
        }
    }

// setDelLastIndex()
    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
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

// setTasUid()
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

// setUsrUid()
    /**
     * Set the value of [del_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDelType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->del_type !== $v || $v === 'NORMAL' )
        {
            $this->del_type = $v;
        }
    }

// setDelType()
    /**
     * Set the value of [del_thread] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelThread ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_thread !== $v || $v === 0 )
        {
            $this->del_thread = $v;
        }
    }

// setDelThread()
    /**
     * Set the value of [del_thread_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDelThreadStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->del_thread_status !== $v || $v === 'OPEN' )
        {
            $this->del_thread_status = $v;
        }
    }

// setDelThreadStatus()
    /**
     * Set the value of [del_priority] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDelPriority ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->del_priority !== $v || $v === '3' )
        {
            $this->del_priority = $v;
        }
    }

// setDelPriority()
    /**
     * Set the value of [del_delegate_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelDelegateDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [del_delegate_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_delegate_date !== $ts )
        {
            $this->del_delegate_date = $ts;
        }
    }

// setDelDelegateDate()
    /**
     * Set the value of [del_init_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelInitDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [del_init_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_init_date !== $ts )
        {
            $this->del_init_date = $ts;
        }
    }

// setDelInitDate()
    /**
     * Set the value of [del_finish_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelFinishDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [del_finish_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_finish_date !== $ts )
        {
            $this->del_finish_date = $ts;
        }
    }

// setDelFinishDate()
    /**
     * Set the value of [del_task_due_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelTaskDueDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [del_task_due_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_task_due_date !== $ts )
        {
            $this->del_task_due_date = $ts;
        }
    }

// setDelTaskDueDate()
    /**
     * Set the value of [del_risk_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelRiskDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [del_risk_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_risk_date !== $ts )
        {
            $this->del_risk_date = $ts;
        }
    }

// setDelRiskDate()
    /**
     * Set the value of [del_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setDelDuration ($v)
    {
        if ( $this->del_duration !== $v || $v === 0 )
        {
            $this->del_duration = $v;
        }
    }

// setDelDuration()
    /**
     * Set the value of [del_queue_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setDelQueueDuration ($v)
    {
        if ( $this->del_queue_duration !== $v || $v === 0 )
        {
            $this->del_queue_duration = $v;
        }
    }

// setDelQueueDuration()
    /**
     * Set the value of [del_delay_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setDelDelayDuration ($v)
    {
        if ( $this->del_delay_duration !== $v || $v === 0 )
        {
            $this->del_delay_duration = $v;
        }
    }

// setDelDelayDuration()
    /**
     * Set the value of [del_started] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelStarted ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_started !== $v || $v === 0 )
        {
            $this->del_started = $v;
        }
    }

// setDelStarted()
    /**
     * Set the value of [del_finished] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelFinished ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_finished !== $v || $v === 0 )
        {
            $this->del_finished = $v;
        }
    }

// setDelFinished()
    /**
     * Set the value of [del_delayed] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelDelayed ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->del_delayed !== $v || $v === 0 )
        {
            $this->del_delayed = $v;
        }
    }

// setDelDelayed()
    /**
     * Set the value of [del_data] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDelData ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->del_data !== $v )
        {
            $this->del_data = $v;
        }
    }

// setDelData()
    /**
     * Set the value of [app_overdue_percentage] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppOverduePercentage ($v)
    {
        if ( $this->app_overdue_percentage !== $v || $v === 0 )
        {
            $this->app_overdue_percentage = $v;
        }
    }

// setAppOverduePercentage()
    /**
     * Set the value of [usr_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setUsrId ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->usr_id !== $v || $v === 0 )
        {
            $this->usr_id = $v;
        }
    }

// setUsrId()
    /**
     * Set the value of [pro_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setProId ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->pro_id !== $v || $v === 0 )
        {
            $this->pro_id = $v;
        }
    }

// setProId()
    /**
     * Set the value of [tas_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTasId ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->tas_id !== $v || $v === 0 )
        {
            $this->tas_id = $v;
        }
    }

// setTasId()

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
    public function save ()
    {
      echo $this->usr_uid . " ";
      echo $this->pro_uid . " ";
      echo $this->tas_uid . " ";
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
