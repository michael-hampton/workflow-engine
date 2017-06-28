<?php

/**
 * Base class that represents a row from the 'WEB_ENTRY_EVENT' table.
 */
abstract class BaseWebEntryEvent implements Persistent
{

    /**
     * The value for the wee_uid field.
     * @var        string
     */
    protected $wee_uid;

    /**
     * The value for the wee_title field.
     * @var        string
     */
    protected $wee_title;

    /**
     * The value for the wee_description field.
     * @var        string
     */
    protected $wee_description;

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
     * The value for the act_uid field.
     * @var        string
     */
    protected $act_uid;

    /**
     * The value for the dyn_uid field.
     * @var        string
     */
    protected $dyn_uid;

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid;

    /**
     * The value for the wee_status field.
     * @var        string
     */
    protected $wee_status = 'ENABLED';

    /**
     * The value for the wee_we_uid field.
     * @var        string
     */
    protected $wee_we_uid = '';

    /**
     * The value for the wee_we_tas_uid field.
     * @var        string
     */
    protected $wee_we_tas_uid = '';

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
     * Get the [wee_uid] column value.
     * 
     * @return     string
     */
    public function getWeeUid ()
    {
        return $this->wee_uid;
    }

    /**
     * Get the [wee_title] column value.
     * 
     * @return     string
     */
    public function getWeeTitle ()
    {
        return $this->wee_title;
    }

    /**
     * Get the [wee_description] column value.
     * 
     * @return     string
     */
    public function getWeeDescription ()
    {
        return $this->wee_description;
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
     * Get the [act_uid] column value.
     * 
     * @return     string
     */
    public function getActUid ()
    {
        return $this->act_uid;
    }

    /**
     * Get the [dyn_uid] column value.
     * 
     * @return     string
     */
    public function getDynUid ()
    {
        return $this->dyn_uid;
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
     * Get the [wee_status] column value.
     * 
     * @return     string
     */
    public function getWeeStatus ()
    {
        return $this->wee_status;
    }

    /**
     * Get the [wee_we_uid] column value.
     * 
     * @return     string
     */
    public function getWeeWeUid ()
    {
        return $this->wee_we_uid;
    }

    /**
     * Get the [wee_we_tas_uid] column value.
     * 
     * @return     string
     */
    public function getWeeWeTasUid ()
    {
        return $this->wee_we_tas_uid;
    }

    /**
     * Set the value of [wee_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeeUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_uid !== $v )
        {
            $this->wee_uid = $v;
        }
    }

// setWeeUid()
    /**
     * Set the value of [wee_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setWeeTitle ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_title !== $v )
        {
            $this->wee_title = $v;
        }
    }

// setWeeTitle()
    /**
     * Set the value of [wee_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setWeeDescription ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_description !== $v )
        {
            $this->wee_description = $v;
        }
    }

// setWeeDescription()
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
     * Set the value of [act_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setActUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->act_uid !== $v )
        {
            $this->act_uid = $v;
        }
    }

// setActUid()
    /**
     * Set the value of [dyn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setDynUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->dyn_uid !== $v )
        {
            $this->dyn_uid = $v;
        }
    }

// setDynUid()
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
        if ( $this->usr_uid !== $v )
        {
            $this->usr_uid = $v;
        }
    }

// setUsrUid()
    /**
     * Set the value of [wee_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setWeeStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_status !== $v || $v === 'ENABLED' )
        {
            $this->wee_status = $v;
        }
    }

// setWeeStatus()
    /**
     * Set the value of [wee_we_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setWeeWeUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_we_uid !== $v || $v === '' )
        {
            $this->wee_we_uid = $v;
        }
    }

// setWeeWeUid()
    /**
     * Set the value of [wee_we_tas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setWeeWeTasUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->wee_we_tas_uid !== $v || $v === '' )
        {
            $this->wee_we_tas_uid = $v;
        }
    }

// setWeeWeTasUid()

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete ($con = null)
    {

        try {
            
        } catch (PropelException $e) {
            throw $e;
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

}
