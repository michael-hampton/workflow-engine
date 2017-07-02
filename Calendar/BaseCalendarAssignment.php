<?php

/**
 * Base class that represents a row from the 'CALENDAR_ASSIGNMENTS' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseCalendarAssignment implements Persistent
{

    /**
     * The value for the object_uid field.
     * @var        string
     */
    protected $object_uid = '';

    /**
     * The value for the calendar_uid field.
     * @var        string
     */
    protected $calendar_uid = '';

    /**
     * The value for the object_type field.
     * @var        string
     */
    protected $object_type = '';

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
     * Get the [object_uid] column value.
     * 
     * @return     string
     */
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getObjectUid ()
    {
        return $this->object_uid;
    }

    /**
     * Get the [calendar_uid] column value.
     * 
     * @return     string
     */
    public function getCalendarUid ()
    {
        return $this->calendar_uid;
    }

    /**
     * Get the [object_type] column value.
     * 
     * @return     string
     */
    public function getObjectType ()
    {
        return $this->object_type;
    }

    /**
     * Set the value of [object_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setObjectUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->object_uid !== $v || $v === '' )
        {
            $this->object_uid = $v;
        }
    }

// setObjectUid()
    /**
     * Set the value of [calendar_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setCalendarUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->calendar_uid !== $v || $v === '' )
        {
            $this->calendar_uid = $v;
        }
    }

// setCalendarUid()
    /**
     * Set the value of [object_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setObjectType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->object_type !== $v || $v === '' )
        {
            $this->object_type = $v;
        }
    }

// setObjectType()

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed. 
     *
     * @return     int The number of rows affected by this insert/update
     */
    public function save ()
    {
        $id = $this->objMysql->_insert ("calendar.calendar_assignees", [
            "CALENDAR_UID" => $this->calendar_uid,
            "OBJECT_TYPE" => $this->object_type,
            "USER_UID" => $this->object_uid
                ]
        );
    }

    public function loadObject (array $arrData)
    {
        ;
    }

    public function validate ()
    {
        $intErrorCount = 0;

        if ( trim ($this->calendar_uid) === "" )
        {
            $intErrorCount++;
        }

        if ( trim ($this->object_type) === "" )
        {
            $intErrorCount++;
        }

        if ( trim ($this->object_uid) === "" )
        {
            $intErrorCount++;
        }

        return $intErrorCount > 0 ? false : true;
    }

}
