<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseCaseTracker
 *
 * @author michael.hampton
 */
abstract class BaseCaseTracker implements Persistent
{

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '0';

    /**
     * The value for the ct_map_type field.
     * @var        string
     */
    protected $ct_map_type = '0';

    /**
     * The value for the ct_derivation_history field.
     * @var        int
     */
    protected $ct_derivation_history = 0;

    /**
     * The value for the ct_message_history field.
     * @var        int
     */
    protected $ct_message_history = 0;

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
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid ()
    {
        return $this->pro_uid;
    }

    /**
     * Get the [ct_map_type] column value.
     * 
     * @return     string
     */
    public function getCtMapType ()
    {
        return $this->ct_map_type;
    }

    /**
     * Get the [ct_derivation_history] column value.
     * 
     * @return     int
     */
    public function getCtDerivationHistory ()
    {
        return $this->ct_derivation_history;
    }

    /**
     * Get the [ct_message_history] column value.
     * 
     * @return     int
     */
    public function getCtMessageHistory ()
    {
        return $this->ct_message_history;
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
        if ( $this->pro_uid !== $v || $v === '0' )
        {
            $this->pro_uid = $v;
        }
    }

    /**
     * Set the value of [ct_map_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCtMapType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->ct_map_type !== $v || $v === '0' )
        {
            $this->ct_map_type = $v;
        }
    }

    /**
     * Set the value of [ct_derivation_history] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCtDerivationHistory ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->ct_derivation_history !== $v || $v === 0 )
        {
            $this->ct_derivation_history = $v;
        }
    }

    /**
     * Set the value of [ct_message_history] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCtMessageHistory ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->ct_message_history !== $v || $v === 0 )
        {
            $this->ct_message_history = $v;
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete ()
    {
        
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
    public function save ($con = null)
    {
        
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
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate ()
    {
        return true;
    }

    public function loadObject (array $arrData)
    {
        
    }

}
