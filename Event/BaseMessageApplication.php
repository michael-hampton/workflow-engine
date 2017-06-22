<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseMessageApplication
 *
 * @author michael.hampton
 */
abstract class BaseMessageApplication implements Persistent
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        MessageApplicationPeer
     */
    protected static $peer;

    /**
     * The value for the msgapp_uid field.
     * @var        string
     */
    protected $msgapp_uid;

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid;

    /**
     * The value for the prj_uid field.
     * @var        string
     */
    protected $prj_uid;

    /**
     * The value for the evn_uid_throw field.
     * @var        string
     */
    protected $evn_uid_throw;

    /**
     * The value for the evn_uid_catch field.
     * @var        string
     */
    protected $evn_uid_catch;

    /**
     * The value for the msgapp_variables field.
     * @var        string
     */
    protected $msgapp_variables;

    /**
     * The value for the msgapp_correlation field.
     * @var        string
     */
    protected $msgapp_correlation = '';

    /**
     * The value for the msgapp_throw_date field.
     * @var        int
     */
    protected $msgapp_throw_date;

    /**
     * The value for the msgapp_catch_date field.
     * @var        int
     */
    protected $msgapp_catch_date;

    /**
     * The value for the msgapp_status field.
     * @var        string
     */
    protected $msgapp_status = 'UNREAD';

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
    protected $validationFailures = array();

    /**
     * Get the [msgapp_uid] column value.
     * 
     * @return     string
     */
    public function getMsgappUid ()
    {
        return $this->msgapp_uid;
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
     * Get the [prj_uid] column value.
     * 
     * @return     string
     */
    public function getPrjUid ()
    {
        return $this->prj_uid;
    }

    /**
     * Get the [evn_uid_throw] column value.
     * 
     * @return     string
     */
    public function getEvnUidThrow ()
    {
        return $this->evn_uid_throw;
    }

    /**
     * Get the [evn_uid_catch] column value.
     * 
     * @return     string
     */
    public function getEvnUidCatch ()
    {
        return $this->evn_uid_catch;
    }

    /**
     * Get the [msgapp_variables] column value.
     * 
     * @return     string
     */
    public function getMsgappVariables ()
    {
        return $this->msgapp_variables;
    }

    /**
     * Get the [msgapp_correlation] column value.
     * 
     * @return     string
     */
    public function getMsgappCorrelation ()
    {
        return $this->msgapp_correlation;
    }

    /**
     * Get the [optionally formatted] [msgapp_throw_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getMsgappThrowDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->msgapp_throw_date === null || $this->msgapp_throw_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->msgapp_throw_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->msgapp_throw_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [msgapp_throw_date] as date/time value: " .
                var_export ($this->msgapp_throw_date, true));
            }
        }
        else
        {
            $ts = $this->msgapp_throw_date;
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
     * Get the [optionally formatted] [msgapp_catch_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getMsgappCatchDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->msgapp_catch_date === null || $this->msgapp_catch_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->msgapp_catch_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->msgapp_catch_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [msgapp_catch_date] as date/time value: " .
                var_export ($this->msgapp_catch_date, true));
            }
        }
        else
        {
            $ts = $this->msgapp_catch_date;
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
     * Get the [msgapp_status] column value.
     * 
     * @return     string
     */
    public function getMsgappStatus ()
    {
        return $this->msgapp_status;
    }

    /**
     * Set the value of [msgapp_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgappUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msgapp_uid !== $v )
        {
            $this->msgapp_uid = $v;
        }
    }

// setMsgappUid()
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
        if ( $this->app_uid !== $v )
        {
            $this->app_uid = $v;
        }
    }

// setAppUid()
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
     * Set the value of [evn_uid_throw] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnUidThrow ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_uid_throw !== $v )
        {
            $this->evn_uid_throw = $v;
        }
    }

// setEvnUidThrow()
    /**
     * Set the value of [evn_uid_catch] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnUidCatch ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_uid_catch !== $v )
        {
            $this->evn_uid_catch = $v;
        }
    }

// setEvnUidCatch()
    /**
     * Set the value of [msgapp_variables] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgappVariables ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msgapp_variables !== $v )
        {
            $this->msgapp_variables = $v;
        }
    }

// setMsgappVariables()
    /**
     * Set the value of [msgapp_correlation] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgappCorrelation ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msgapp_correlation !== $v || $v === '' )
        {
            $this->msgapp_correlation = $v;
        }
    }

// setMsgappCorrelation()
    /**
     * Set the value of [msgapp_throw_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMsgappThrowDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [msgapp_throw_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->msgapp_throw_date !== $ts )
        {
            $this->msgapp_throw_date = date ("Y-m-d H:i:s", $ts);
        }
    }

// setMsgappThrowDate()
    /**
     * Set the value of [msgapp_catch_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMsgappCatchDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [msgapp_catch_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->msgapp_catch_date !== $ts )
        {
            $this->msgapp_catch_date = date ("Y-m-d H:i:s", $ts);
        }
    }

// setMsgappCatchDate()
    /**
     * Set the value of [msgapp_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgappStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msgapp_status !== $v || $v === 'UNREAD' )
        {
            $this->msgapp_status = $v;
        }
    }

// setMsgappStatus()

    public function loadObject (array $arrData)
    {
        
    }

    public function validate ()
    {
        $intEroorCount = 0;

        if ( trim ($this->prj_uid) === "" )
        {
            $this->validationFailures[] = "Project id missing";
            $intEroorCount++;
        }

        if ( trim ($this->app_uid) === "" )
        {
            $this->validationFailures[] = "App id missing";
            $intEroorCount++;
        }

        if ( trim ($this->evn_uid_catch) === "" )
        {
            $this->validationFailures[] = "Catch id missing";
            $intEroorCount++;
        }

        if ( trim ($this->evn_uid_throw) === "" )
        {
            $this->validationFailures[] = "Throw id missing";
            $intEroorCount++;
        }

        if ( trim ($this->msgapp_variables) === "" )
        {
            $this->validationFailures[] = "Variables missing";
            $intEroorCount++;
        }

        if ( trim ($this->msgapp_correlation) === "" )
        {
            $this->validationFailures[] = "Message Correlation missing";
            $intEroorCount++;
        }

        if ( $intEroorCount > 0 )
        {
            return false;
        }

        return true;
    }

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

    public function save ()
    {        
        $this->objMysql->_insert ("workflow.message_application", [
            "APP_UID" => $this->app_uid,
            "PRJ_UID" => $this->prj_uid,
            "EVENT_UID_THROW" => $this->evn_uid_throw,
            "EVN_UID_CATCH" => $this->evn_uid_catch,
            "MSGAPP_THROW_DATE" => $this->msgapp_throw_date,
            "MSGAPP_CORRRELATION" => $this->msgapp_correlation,
            "MSGAPP_VARIABLES" => $this->msgapp_variables,
            "MSGAPP_STATUS" => $this->msgapp_status,
            "MSGAPP_CATCH_DATE" => $this->msgapp_catch_date]
        );
    }

}
