<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseCaseTrackerObject
 *
 * @author michael.hampton
 */
abstract class BaseCaseTrackerObject implements Persistent
{

    /**
     * The value for the cto_uid field.
     * @var        string
     */
    protected $cto_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '0';

    /**
     * The value for the cto_type_obj field.
     * @var        string
     */
    protected $cto_type_obj = 'DYNAFORM';

    /**
     * The value for the cto_uid_obj field.
     * @var        string
     */
    protected $cto_uid_obj = '0';

    /**
     * The value for the cto_condition field.
     * @var        string
     */
    protected $cto_condition;

    /**
     * The value for the cto_position field.
     * @var        int
     */
    protected $cto_position = 0;

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
     * Get the [cto_uid] column value.
     * 
     * @return     string
     */
    public function getCtoUid ()
    {
        return $this->cto_uid;
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
     * Get the [cto_type_obj] column value.
     * 
     * @return     string
     */
    public function getCtoTypeObj ()
    {
        return $this->cto_type_obj;
    }

    /**
     * Get the [cto_uid_obj] column value.
     * 
     * @return     string
     */
    public function getCtoUidObj ()
    {
        return $this->cto_uid_obj;
    }

    /**
     * Get the [cto_condition] column value.
     * 
     * @return     string
     */
    public function getCtoCondition ()
    {
        return $this->cto_condition;
    }

    /**
     * Get the [cto_position] column value.
     * 
     * @return     int
     */
    public function getCtoPosition ()
    {
        return $this->cto_position;
    }

    /**
     * Set the value of [cto_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCtoUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->cto_uid !== $v || $v === '' )
        {
            $this->cto_uid = $v;
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
        if ( $this->pro_uid !== $v || $v === '0' )
        {
            $this->pro_uid = $v;
        }
    }

    /**
     * Set the value of [cto_type_obj] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCtoTypeObj ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->cto_type_obj !== $v || $v === 'DYNAFORM' )
        {
            $this->cto_type_obj = $v;
        }
    }

    /**
     * Set the value of [cto_uid_obj] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCtoUidObj ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->cto_uid_obj !== $v || $v === '0' )
        {
            $this->cto_uid_obj = $v;
        }
    }

    /**
     * Set the value of [cto_condition] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCtoCondition ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->cto_condition !== $v )
        {
            $this->cto_condition = $v;
        }
    }

    /**
     * Set the value of [cto_position] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCtoPosition ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->cto_position !== $v || $v === 0 )
        {
            $this->cto_position = $v;
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
    public function save ()
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
