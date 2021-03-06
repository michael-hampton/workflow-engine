<?php

/**
 * Description of BaseCalendarDefinition
 *
 * @author michael.hampton
 */
abstract class BaseCalendarDefinition implements Persistent
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * The value for the calendar_uid field.
     * @var        string
     */
    protected $calendar_uid = '';

    /**
     * The value for the calendar_name field.
     * @var        string
     */
    protected $calendar_name = '';

    /**
     * The value for the calendar_create_date field.
     * @var        int
     */
    protected $calendar_create_date;

    /**
     * The value for the calendar_update_date field.
     * @var        int
     */
    protected $calendar_update_date;

    /**
     * The value for the calendar_work_days field.
     * @var        string
     */
    protected $calendar_work_days = '';

    /**
     * The value for the calendar_description field.
     * @var        string
     */
    protected $calendar_description;

    /**
     * The value for the calendar_status field.
     * @var        string
     */
    protected $calendar_status = 'ACTIVE';

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;
    protected $workHours;
    protected $holidays;
    protected $totalProcesses = 0;
    protected $totalUsers = 0;

    public function getWorkHours ()
    {
        return $this->workHours;
    }

    public function getHolidays ()
    {
        return $this->holidays;
    }

    public function getTotalProcesses ()
    {
        return $this->totalProcesses;
    }

    public function getTotalUsers ()
    {
        return $this->totalUsers;
    }

    public function setWorkHours ($workHours)
    {
        $this->workHours = $workHours;
    }

    public function setHolidays ($holidays)
    {
        $this->holidays = $holidays;
    }

    public function setTotalProcesses ($totalProcesses)
    {
        $this->totalProcesses = $totalProcesses;
    }

    public function setTotalUsers ($totalUsers)
    {
        $this->totalUsers = $totalUsers;
    }

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
    public function getCalendarUid ()
    {

        return $this->calendar_uid;
    }

    /**
     * Get the [calendar_name] column value.
     * 
     * @return     string
     */
    public function getCalendarName ()
    {

        return $this->calendar_name;
    }

    /**
     * Get the [optionally formatted] [calendar_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getCalendarCreateDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->calendar_create_date === null || $this->calendar_create_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->calendar_create_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->calendar_create_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [calendar_create_date] as date/time value: " .
                var_export ($this->calendar_create_date, true));
            }
        }
        else
        {
            $ts = $this->calendar_create_date;
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
     * Get the [optionally formatted] [calendar_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     */
    public function getCalendarUpdateDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->calendar_update_date === null || $this->calendar_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->calendar_update_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->calendar_update_date);
        }
        else
        {
            $ts = $this->calendar_update_date;
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
     * Get the [calendar_work_days] column value.
     * 
     * @return     string
     */
    public function getCalendarWorkDays ()
    {

        return $this->calendar_work_days;
    }

    /**
     * Get the [calendar_description] column value.
     * 
     * @return     string
     */
    public function getCalendarDescription ()
    {

        return $this->calendar_description;
    }

    /**
     * Get the [calendar_status] column value.
     * 
     * @return     string
     */
    public function getCalendarStatus ()
    {

        return $this->calendar_status;
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
     * Set the value of [calendar_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalendarName ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->calendar_name !== $v || $v === '' )
        {
            $this->calendar_name = $v;
        }
    }

// setCalendarName()

    /**
     * Set the value of [calendar_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCalendarCreateDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [calendar_create_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->calendar_create_date !== $ts )
        {
            $this->calendar_create_date = date ("Y-m-d H:i:s", $ts);
        }
    }

// setCalendarCreateDate()

    /**
     * Set the value of [calendar_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCalendarUpdateDate ($v)
    {

        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->calendar_update_date !== $ts )
        {
            $this->calendar_update_date = $ts;
        }
    }

// setCalendarUpdateDate()

    /**
     * Set the value of [calendar_work_days] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalendarWorkDays ($v)
    {
        if ( is_array ($v) )
        {
            $this->calendar_work_days = $v;
        }
        else
        {

            // Since the native PHP type for this column is string,
            // we will cast the input to a string (if it is not).
            if ( $v !== null && !is_string ($v) )
            {
                $v = (string) $v;
            }

            if ( $this->calendar_work_days !== $v || $v === '' )
            {
                $this->calendar_work_days = $v;
            }
        }
    }

// setCalendarWorkDays()

    /**
     * Set the value of [calendar_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalendarDescription ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->calendar_description !== $v )
        {
            $this->calendar_description = $v;
        }
    }

// setCalendarDescription()

    /**
     * Set the value of [calendar_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCalendarStatus ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->calendar_status !== $v || $v === 'ACTIVE' )
        {
            $this->calendar_status = $v;
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     */
    public function delete ()
    {
        try {
            CalendarDefinitionPeer::doDelete ($this);
            $this->setDeleted (true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  
     *      *
     * @return     int The number of rows affected by this insert/update
     */
    public function save ()
    {
        if ( trim ($this->calendar_uid) !== "" )
        {
            $id = $this->objMysql->_update ("calendar.calendar", [
                "CALENDAR_NAME" => $this->calendar_name,
                "CALENDAR_STATUS" => $this->calendar_status,
                "CALENDAR_CREATE_DATE" => $this->calendar_create_date,
                "CALENDAR_UPDATE_DATE" => $this->calendar_update_date,
                "CALENDAR_WORK_DAYS" => $this->calendar_work_days,
                "CALENDAR_DESCRIPTION" => $this->calendar_description
                    ], ["CALENDAR_UID" => $this->calendar_uid]
            );

            return $this->calendar_uid;
        }
        else
        {
            $id = $this->objMysql->_insert ("calendar.calendar", [
                "CALENDAR_NAME" => $this->calendar_name,
                "CALENDAR_STATUS" => $this->calendar_status,
                "CALENDAR_CREATE_DATE" => $this->calendar_create_date,
                "CALENDAR_UPDATE_DATE" => $this->calendar_update_date,
                "CALENDAR_WORK_DAYS" => $this->calendar_work_days,
                "CALENDAR_DESCRIPTION" => $this->calendar_description
                    ]
            );
        }

        return $id;
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     Exception
     * @see        save()
     */
    protected function doSave ($con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if ( !$this->alreadyInSave )
        {
            $this->alreadyInSave = true;


            // If this object has been modified, then save it to the database.
            if ( $this->isModified () )
            {
                if ( $this->isNew () )
                {
                    $pk = CalendarDefinitionPeer::doInsert ($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                    // should always be true here (even though technically
                    // BasePeer::doInsert() can insert multiple rows).

                    $this->setNew (false);
                }
                else
                {
                    $affectedRows += CalendarDefinitionPeer::doUpdate ($this, $con);
                }
                $this->resetModified (); // [HL] After being saved an object is no longer 'modified'
            }

            $this->alreadyInSave = false;
        }
        return $affectedRows;
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
     * @see        getValidationFailures()
     */
    public function validate ($columns = null)
    {
        return true;
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
        if ( !$this->alreadyInValidation )
        {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if ( ($retval = CalendarDefinitionPeer::doValidate ($this, $columns)) !== true )
            {
                $failureMap = array_merge ($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty ($failureMap) ? $failureMap : true);
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
        $pos = CalendarDefinitionPeer::translateFieldName ($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getCalendarName ();
                break;
            case 2:
                return $this->getCalendarCreateDate ();
                break;
            case 3:
                return $this->getCalendarUpdateDate ();
                break;
            case 4:
                return $this->getCalendarWorkDays ();
                break;
            case 5:
                return $this->getCalendarDescription ();
                break;
            case 6:
                return $this->getCalendarStatus ();
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
        $keys = CalendarDefinitionPeer::getFieldNames ($keyType);
        $result = array(
            $keys[0] => $this->getCalendarUid (),
            $keys[1] => $this->getCalendarName (),
            $keys[2] => $this->getCalendarCreateDate (),
            $keys[3] => $this->getCalendarUpdateDate (),
            $keys[4] => $this->getCalendarWorkDays (),
            $keys[5] => $this->getCalendarDescription (),
            $keys[6] => $this->getCalendarStatus (),
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
        $pos = CalendarDefinitionPeer::translateFieldName ($name, $type, BasePeer::TYPE_NUM);
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
                $this->setCalendarName ($value);
                break;
            case 2:
                $this->setCalendarCreateDate ($value);
                break;
            case 3:
                $this->setCalendarUpdateDate ($value);
                break;
            case 4:
                $this->setCalendarWorkDays ($value);
                break;
            case 5:
                $this->setCalendarDescription ($value);
                break;
            case 6:
                $this->setCalendarStatus ($value);
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
        $keys = CalendarDefinitionPeer::getFieldNames ($keyType);

        if ( array_key_exists ($keys[0], $arr) )
        {
            $this->setCalendarUid ($arr[$keys[0]]);
        }

        if ( array_key_exists ($keys[1], $arr) )
        {
            $this->setCalendarName ($arr[$keys[1]]);
        }

        if ( array_key_exists ($keys[2], $arr) )
        {
            $this->setCalendarCreateDate ($arr[$keys[2]]);
        }

        if ( array_key_exists ($keys[3], $arr) )
        {
            $this->setCalendarUpdateDate ($arr[$keys[3]]);
        }

        if ( array_key_exists ($keys[4], $arr) )
        {
            $this->setCalendarWorkDays ($arr[$keys[4]]);
        }

        if ( array_key_exists ($keys[5], $arr) )
        {
            $this->setCalendarDescription ($arr[$keys[5]]);
        }

        if ( array_key_exists ($keys[6], $arr) )
        {
            $this->setCalendarStatus ($arr[$keys[6]]);
        }
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria ()
    {
        $criteria = new Criteria (CalendarDefinitionPeer::DATABASE_NAME);

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_UID) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_UID, $this->calendar_uid);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_NAME) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_NAME, $this->calendar_name);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_CREATE_DATE) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_CREATE_DATE, $this->calendar_create_date);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_UPDATE_DATE) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_UPDATE_DATE, $this->calendar_update_date);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_WORK_DAYS) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_WORK_DAYS, $this->calendar_work_days);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_DESCRIPTION) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_DESCRIPTION, $this->calendar_description);
        }

        if ( $this->isColumnModified (CalendarDefinitionPeer::CALENDAR_STATUS) )
        {
            $criteria->add (CalendarDefinitionPeer::CALENDAR_STATUS, $this->calendar_status);
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
        $criteria = new Criteria (CalendarDefinitionPeer::DATABASE_NAME);

        $criteria->add (CalendarDefinitionPeer::CALENDAR_UID, $this->calendar_uid);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return     string
     */
    public function getPrimaryKey ()
    {
        return $this->getCalendarUid ();
    }

    /**
     * Generic method to set the primary key (calendar_uid column).
     *
     * @param      string $key Primary key.
     * @return     void
     */
    public function setPrimaryKey ($key)
    {
        $this->setCalendarUid ($key);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of CalendarDefinition (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto ($copyObj, $deepCopy = false)
    {

        $copyObj->setCalendarName ($this->calendar_name);

        $copyObj->setCalendarCreateDate ($this->calendar_create_date);

        $copyObj->setCalendarUpdateDate ($this->calendar_update_date);

        $copyObj->setCalendarWorkDays ($this->calendar_work_days);

        $copyObj->setCalendarDescription ($this->calendar_description);

        $copyObj->setCalendarStatus ($this->calendar_status);


        $copyObj->setNew (true);

        $copyObj->setCalendarUid (''); // this is a pkey column, so set to default value
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
     * @return     CalendarDefinition Clone of current object.
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
     * @return     CalendarDefinitionPeer
     */
    public function getPeer ()
    {
        if ( self::$peer === null )
        {
            self::$peer = new CalendarDefinitionPeer();
        }
        return self::$peer;
    }

    public function loadObject (array $arrData)
    {
        ;
    }

}
