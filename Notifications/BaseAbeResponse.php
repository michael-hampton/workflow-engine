<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseAbeResponse
 *
 * @author michael.hampton
 */
abstract class BaseAbeResponse implements Persistent
{

    /**
     * The value for the abe_res_uid field.
     * @var        string
     */
    protected $abe_res_uid = '';

    /**
     * The value for the abe_req_uid field.
     * @var        string
     */
    protected $abe_req_uid = '';

    /**
     * The value for the abe_res_client_ip field.
     * @var        string
     */
    protected $abe_res_client_ip = '';

    /**
     * The value for the abe_res_data field.
     * @var        string
     */
    protected $abe_res_data;

    /**
     * The value for the abe_res_date field.
     * @var        int
     */
    protected $abe_res_date;

    /**
     * The value for the abe_res_status field.
     * @var        string
     */
    protected $abe_res_status = '';

    /**
     * The value for the abe_res_message field.
     * @var        string
     */
    protected $abe_res_message = '';

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
     * Get the [abe_res_uid] column value.
     * 
     * @return     string
     */
    public function getAbeResUid ()
    {
        return $this->abe_res_uid;
    }

    /**
     * Get the [abe_req_uid] column value.
     * 
     * @return     string
     */
    public function getAbeReqUid ()
    {
        return $this->abe_req_uid;
    }

    /**
     * Get the [abe_res_client_ip] column value.
     * 
     * @return     string
     */
    public function getAbeResClientIp ()
    {
        return $this->abe_res_client_ip;
    }

    /**
     * Get the [abe_res_data] column value.
     * 
     * @return     string
     */
    public function getAbeResData ()
    {
        return $this->abe_res_data;
    }

    /**
     * Get the [optionally formatted] [abe_res_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAbeResDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->abe_res_date === null || $this->abe_res_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->abe_res_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->abe_res_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [abe_res_date] as date/time value: " .
                var_export ($this->abe_res_date, true));
            }
        }
        else
        {
            $ts = $this->abe_res_date;
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
     * Get the [abe_res_status] column value.
     * 
     * @return     string
     */
    public function getAbeResStatus ()
    {
        return $this->abe_res_status;
    }

    /**
     * Get the [abe_res_message] column value.
     * 
     * @return     string
     */
    public function getAbeResMessage ()
    {
        return $this->abe_res_message;
    }

    /**
     * Set the value of [abe_res_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeResUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_res_uid !== $v || $v === '' )
        {
            $this->abe_res_uid = $v;
        }
    }

    /**
     * Set the value of [abe_req_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_uid !== $v || $v === '' )
        {
            $this->abe_req_uid = $v;
        }
    }

    /**
     * Set the value of [abe_res_client_ip] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeResClientIp ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_res_client_ip !== $v || $v === '' )
        {
            $this->abe_res_client_ip = $v;
        }
    }

    /**
     * Set the value of [abe_res_data] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeResData ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_res_data !== $v )
        {
            $this->abe_res_data = $v;
        }
    }

    /**
     * Set the value of [abe_res_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAbeResDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [abe_res_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->abe_res_date !== $ts )
        {
            $this->abe_res_date = $ts;
        }
    }

    /**
     * Set the value of [abe_res_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeResStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_res_status !== $v || $v === '' )
        {
            $this->abe_res_status = $v;
        }
    }

    /**
     * Set the value of [abe_res_message] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeResMessage ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_res_message !== $v || $v === '' )
        {
            $this->abe_res_message = $v;
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     * @throws     Exception
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

        try {
            
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
    public function validate ($columns = null)
    {
        
    }

}
