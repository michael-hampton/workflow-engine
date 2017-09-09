<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseCalendarBusinessHours
 *
 * @author michael.hampton
 */
abstract class BaseCalendarBusinessHours implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        CalendarBusinessHoursPeer
     */
    protected static $peer;

    /**
     * The value for the calendar_uid field.
     * @var        string
     */
    protected $calendar_uid = '';

    /**
     * The value for the calendar_business_day field.
     * @var        string
     */
    protected $calendar_business_day = '';

    /**
     * The value for the calendar_business_start field.
     * @var        string
     */
    protected $calendar_business_start = '';

    /**
     * The value for the calendar_business_end field.
     * @var        string
     */
    protected $calendar_business_end = '';

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
     * Get the [calendar_uid] column value.
     * 
     * @return     string
     */
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getCalendarUid ()
    {
        return $this->calendar_uid;
    }

    /**
     * Get the [calendar_business_day] column value.
     * 
     * @return     string
     */
    public function getCalendarBusinessDay ()
    {
        return $this->calendar_business_day;
    }

    /**
     * Get the [calendar_business_start] column value.
     * 
     * @return     string
     */
    public function getCalendarBusinessStart ()
    {
        return $this->calendar_business_start;
    }

    /**
     * Get the [calendar_business_end] column value.
     * 
     * @return     string
     */
    public function getCalendarBusinessEnd ()
    {
        return $this->calendar_business_end;
    }

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
     * Set the value of [calendar_business_day] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setCalendarBusinessDay ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->calendar_business_day !== $v || $v === '' )
        {
            $this->calendar_business_day = $v;
        }
    }

// setCalendarBusinessDay()
    /**
     * Set the value of [calendar_business_start] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setCalendarBusinessStart ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->calendar_business_start !== $v || $v === '' )
        {
            $this->calendar_business_start = $v;
        }
    }

// setCalendarBusinessStart()
    /**
     * Set the value of [calendar_business_end] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setCalendarBusinessEnd ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->calendar_business_end !== $v || $v === '' )
        {
            $this->calendar_business_end = $v;
        }
    }

// setCalendarBusinessEnd()

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete ()
    {
        try {
            CalendarBusinessHoursPeer::doDelete ($this, $con);
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
     * @return     new id
     */
    public function save ()
    {
        $id = $this->objMysql->_insert ("calendar.calendar_business_hours", [
            "CALENDAR_UID" => $this->calendar_uid,
            "CALENDAR_BUSINESS_DAY" => $this->calendar_business_day,
            "CALENDAR_BUSINESS_START" => $this->calendar_business_start,
            "CALENDAR_BUSINESS_END" => $this->calendar_business_end
                ]
        );

        return $id;
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
     * @see        getValidationFailures()
     */
    public function validate ($columns = null)
    {
        return true;
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     mixed Value of field.
     */
    public function getByName ($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = CalendarBusinessHoursPeer::translateFieldName ($name, $type, BasePeer::TYPE_NUM);
        return $this->getByPosition ($pos);
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return     mixed Value of field at $pos
     */
    public function getByPosition ($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getCalendarUid ();
                break;
            case 1:
                return $this->getCalendarBusinessDay ();
                break;
            case 2:
                return $this->getCalendarBusinessStart ();
                break;
            case 3:
                return $this->getCalendarBusinessEnd ();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param      string $keyType One of the class type constants TYPE_PHPNAME,
     *                        TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     an associative array containing the field names (as keys) and field values
     */
    public function toArray ($keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = CalendarBusinessHoursPeer::getFieldNames ($keyType);
        $result = array(
            $keys[0] => $this->getCalendarUid (),
            $keys[1] => $this->getCalendarBusinessDay (),
            $keys[2] => $this->getCalendarBusinessStart (),
            $keys[3] => $this->getCalendarBusinessEnd (),
        );
        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name peer name
     * @param      mixed $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     void
     */
    public function setByName ($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = CalendarBusinessHoursPeer::translateFieldName ($name, $type, BasePeer::TYPE_NUM);
        return $this->setByPosition ($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return     void
     */
    public function setByPosition ($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setCalendarUid ($value);
                break;
            case 1:
                $this->setCalendarBusinessDay ($value);
                break;
            case 2:
                $this->setCalendarBusinessStart ($value);
                break;
            case 3:
                $this->setCalendarBusinessEnd ($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME,
     * TYPE_NUM. The default key type is the column's phpname (e.g. 'authorId')
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return     void
     */
    public function fromArray ($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = CalendarBusinessHoursPeer::getFieldNames ($keyType);
        if ( array_key_exists ($keys[0], $arr) )
        {
            $this->setCalendarUid ($arr[$keys[0]]);
        }
        if ( array_key_exists ($keys[1], $arr) )
        {
            $this->setCalendarBusinessDay ($arr[$keys[1]]);
        }
        if ( array_key_exists ($keys[2], $arr) )
        {
            $this->setCalendarBusinessStart ($arr[$keys[2]]);
        }
        if ( array_key_exists ($keys[3], $arr) )
        {
            $this->setCalendarBusinessEnd ($arr[$keys[3]]);
        }
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria ()
    {
        $criteria = new Criteria (CalendarBusinessHoursPeer::DATABASE_NAME);
        if ( $this->isColumnModified (CalendarBusinessHoursPeer::CALENDAR_UID) )
        {
            $criteria->add (CalendarBusinessHoursPeer::CALENDAR_UID, $this->calendar_uid);
        }
        if ( $this->isColumnModified (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_DAY) )
        {
            $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_DAY, $this->calendar_business_day);
        }
        if ( $this->isColumnModified (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_START) )
        {
            $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_START, $this->calendar_business_start);
        }
        if ( $this->isColumnModified (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_END) )
        {
            $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_END, $this->calendar_business_end);
        }
        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return     Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria ()
    {
        $criteria = new Criteria (CalendarBusinessHoursPeer::DATABASE_NAME);
        $criteria->add (CalendarBusinessHoursPeer::CALENDAR_UID, $this->calendar_uid);
        $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_DAY, $this->calendar_business_day);
        $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_START, $this->calendar_business_start);
        $criteria->add (CalendarBusinessHoursPeer::CALENDAR_BUSINESS_END, $this->calendar_business_end);
        return $criteria;
    }

    /**
     * Returns the composite primary key for this object.
     * The array elements will be in same order as specified in XML.
     * @return     array
     */
    public function getPrimaryKey ()
    {
        $pks = array();
        $pks[0] = $this->getCalendarUid ();
        $pks[1] = $this->getCalendarBusinessDay ();
        $pks[2] = $this->getCalendarBusinessStart ();
        $pks[3] = $this->getCalendarBusinessEnd ();
        return $pks;
    }

    /**
     * Set the [composite] primary key.
     *
     * @param      array $keys The elements of the composite key (order must match the order in XML file).
     * @return     void
     */
    public function setPrimaryKey ($keys)
    {
        $this->setCalendarUid ($keys[0]);
        $this->setCalendarBusinessDay ($keys[1]);
        $this->setCalendarBusinessStart ($keys[2]);
        $this->setCalendarBusinessEnd ($keys[3]);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of CalendarBusinessHours (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto ($copyObj, $deepCopy = false)
    {
        $copyObj->setNew (true);
        $copyObj->setCalendarUid (''); // this is a pkey column, so set to default value
        $copyObj->setCalendarBusinessDay (''); // this is a pkey column, so set to default value
        $copyObj->setCalendarBusinessStart (''); // this is a pkey column, so set to default value
        $copyObj->setCalendarBusinessEnd (''); // this is a pkey column, so set to default value
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return     CalendarBusinessHours Clone of current object.
     * @throws     PropelException
     */
    public function copy ($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class ($this);
        $copyObj = new $clazz();
        $this->copyInto ($copyObj, $deepCopy);
        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return     CalendarBusinessHoursPeer
     */
    public function getPeer ()
    {
        if ( self::$peer === null )
        {
            self::$peer = new CalendarBusinessHoursPeer();
        }
        return self::$peer;
    }

    public function loadObject (array $arrData)
    {
        ;
    }

}
