<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Base class that represents a row from the 'APP_CACHE_VIEW' table.
 *
 * @author michael.hampton
 */
abstract class BaseAppCacheView
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
     * The value for the del_last_index field.
     * @var        int
     */
    protected $del_last_index = 0;

    /**
     * The value for the app_number field.
     * @var        int
     */
    protected $app_number = 0;

    /**
     * The value for the app_status field.
     * @var        string
     */
    protected $app_status = '';

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';

    /**
     * The value for the previous_usr_uid field.
     * @var        string
     */
    protected $previous_usr_uid = '';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

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
     * The value for the del_thread_status field.
     * @var        string
     */
    protected $del_thread_status = 'OPEN';

    /**
     * The value for the app_thread_status field.
     * @var        string
     */
    protected $app_thread_status = 'OPEN';

    /**
     * The value for the app_title field.
     * @var        string
     */
    protected $app_title = '';

    /**
     * The value for the app_pro_title field.
     * @var        string
     */
    protected $app_pro_title = '';

    /**
     * The value for the app_tas_title field.
     * @var        string
     */
    protected $app_tas_title = '';

    /**
     * The value for the app_current_user field.
     * @var        string
     */
    protected $app_current_user = '';

    /**
     * The value for the app_del_previous_user field.
     * @var        string
     */
    protected $app_del_previous_user = '';

    /**
     * The value for the del_priority field.
     * @var        string
     */
    protected $del_priority = '3';

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
     * The value for the app_create_date field.
     * @var        int
     */
    protected $app_create_date;

    /**
     * The value for the app_finish_date field.
     * @var        int
     */
    protected $app_finish_date;

    /**
     * The value for the app_update_date field.
     * @var        int
     */
    protected $app_update_date;

    /**
     * The value for the app_overdue_percentage field.
     * @var        double
     */
    protected $app_overdue_percentage;

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

    private function getConnection ()
    {
        $this->objMysql = new \Mysql2();
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
     * Get the [del_last_index] column value.
     * 
     * @return     int
     */
    public function getDelLastIndex ()
    {

        return $this->del_last_index;
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
     * Get the [app_status] column value.
     * 
     * @return     string
     */
    public function getAppStatus ()
    {

        return $this->app_status;
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
     * Get the [previous_usr_uid] column value.
     * 
     * @return     string
     */
    public function getPreviousUsrUid ()
    {

        return $this->previous_usr_uid;
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
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid ()
    {

        return $this->pro_uid;
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
                throw new Exception ("Unable to parse value of [del_delegate_date] as date/time value: ");
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
                throw new Exception ("Unable to parse value of [del_init_date] as date/time value: ");
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
                throw new Exception ("Unable to parse value of [del_finish_date] as date/time value: ");
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
                throw new PropelException ("Unable to parse value of [del_task_due_date] as date/time value: ");
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
                throw new Exception ("Unable to parse value of [del_risk_date] as date/time value: ");
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
     * Get the [del_thread_status] column value.
     * 
     * @return     string
     */
    public function getDelThreadStatus ()
    {

        return $this->del_thread_status;
    }

    /**
     * Get the [app_thread_status] column value.
     * 
     * @return     string
     */
    public function getAppThreadStatus ()
    {

        return $this->app_thread_status;
    }

    /**
     * Get the [app_title] column value.
     * 
     * @return     string
     */
    public function getAppTitle ()
    {

        return $this->app_title;
    }

    /**
     * Get the [app_pro_title] column value.
     * 
     * @return     string
     */
    public function getAppProTitle ()
    {

        return $this->app_pro_title;
    }

    /**
     * Get the [app_tas_title] column value.
     * 
     * @return     string
     */
    public function getAppTasTitle ()
    {

        return $this->app_tas_title;
    }

    /**
     * Get the [app_current_user] column value.
     * 
     * @return     string
     */
    public function getAppCurrentUser ()
    {

        return $this->app_current_user;
    }

    /**
     * Get the [app_del_previous_user] column value.
     * 
     * @return     string
     */
    public function getAppDelPreviousUser ()
    {

        return $this->app_del_previous_user;
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
     * Get the [optionally formatted] [app_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppCreateDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_create_date === null || $this->app_create_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_create_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_create_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_create_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_create_date;
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
     * Get the [optionally formatted] [app_finish_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppFinishDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_finish_date === null || $this->app_finish_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_finish_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_finish_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_finish_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_finish_date;
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
     * Get the [optionally formatted] [app_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppUpdateDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_update_date === null || $this->app_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_update_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_update_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [app_update_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_update_date;
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
     * Get the [app_overdue_percentage] column value.
     * 
     * @return     double
     */
    public function getAppOverduePercentage ()
    {

        return $this->app_overdue_percentage;
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
     * Set the value of [previous_usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPreviousUsrUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->previous_usr_uid !== $v || $v === '' )
        {
            $this->previous_usr_uid = $v;
        }
    }

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
            $this->del_delegate_date = date ("Y-m-d H:i:s", $ts);
        }
    }

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
                throw new PropelException ("Unable to parse date/time value for [del_init_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_init_date !== $ts )
        {
            $this->del_init_date = date ("Y-m-d H:i:s", $ts);
        }
    }

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
                throw new PropelException ("Unable to parse date/time value for [del_finish_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_finish_date !== $ts )
        {
            $this->del_finish_date = date ("Y-m-d H:i:s", $ts);
        }
    }

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
            $this->del_task_due_date = date ("Y-m-d H:i:s", $ts);
        }
    }

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
            $this->del_risk_date = date ("Y-m-d H:i:s", $ts);
        }
    }

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

    /**
     * Set the value of [app_thread_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppThreadStatus ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_thread_status !== $v || $v === 'OPEN' )
        {
            $this->app_thread_status = $v;
        }
    }

    /**
     * Set the value of [app_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppTitle ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_title !== $v || $v === '' )
        {
            $this->app_title = $v;
        }
    }

    /**
     * Set the value of [app_pro_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppProTitle ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_pro_title !== $v || $v === '' )
        {
            $this->app_pro_title = $v;
        }
    }

    /**
     * Set the value of [app_tas_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppTasTitle ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_tas_title !== $v || $v === '' )
        {
            $this->app_tas_title = $v;
        }
    }

    /**
     * Set the value of [app_current_user] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppCurrentUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_current_user !== $v || $v === '' )
        {
            $this->app_current_user = $v;
        }
    }

    /**
     * Set the value of [app_del_previous_user] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppDelPreviousUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_del_previous_user !== $v || $v === '' )
        {
            $this->app_del_previous_user = $v;
        }
    }

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

    /**
     * Set the value of [app_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppCreateDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [app_create_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_create_date !== $ts )
        {
            $this->app_create_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_finish_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppFinishDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [app_finish_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_finish_date !== $ts )
        {
            $this->app_finish_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppUpdateDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [app_update_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_update_date !== $ts )
        {
            $this->app_update_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_overdue_percentage] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppOverduePercentage ($v)
    {

        if ( $this->app_overdue_percentage !== $v )
        {
            $this->app_overdue_percentage = $v;
        }
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_insert ("workflow.APP_CACHE_VIEW", [
            "APP_UID" => $this->app_uid,
            "DEL_INDEX" => $this->del_index,
            "DEL_LAST_INDEX" => $this->del_last_index,
            "APP_NUMBER" => $this->app_number,
            "APP_STATUS" => $this->app_status,
            "USR_UID" => $this->usr_uid,
            "PREVIOUS_USR_UID" => $this->previous_usr_uid,
            "TAS_UID" => $this->tas_uid,
            "PRO_UID" => $this->pro_uid,
            "DEL_DELEGATE_DATE" => $this->del_delegate_date,
            "DEL_INIT_DATE" => $this->del_init_date,
            "DEL_FINISH_DATE" => $this->del_finish_date,
            "DEL_TASK_DUE_DATE" => $this->del_task_due_date,
            "DEL_RISK_DATE" => $this->del_risk_date,
            "APP_TITLE" => $this->app_title,
            "APP_PRO_TITLE" => $this->app_pro_title,
            "APP_TAS_TITLE" => $this->app_tas_title,
            "APP_CURRENT_USER" => $this->app_current_user,
            "APP_DEL_PREVIOUS_USER" => $this->app_del_previous_user,
            "DEL_PRIORITY" => $this->del_priority,
            "DEL_DURATION" => $this->del_duration,
            "APP_CREATE_DATE" => $this->app_create_date,
            "APP_FINISH_DATE" => $this->app_finish_date]
        );
    }

}
