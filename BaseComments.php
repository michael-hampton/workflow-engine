<?php

class BaseComments
{

    private $id;
    private $object = array();
    private $ValidationFailures;
    private $objFields = array(
        "source_id" => array("required" => "true", "type" => "int", "accessor" => "getAppUid"),
        "comment" => array("required" => "true", "type" => "string", "accessor" => "getNoteContent"),
        "datetime" => array("required" => "true", "type" => "date", "accessor" => "getNoteDate"),
        "username" => array("required" => "true", "type" => "string", "accessor" => "getUserUid"),
        "comment_type" => array("required" => "false", "type" => "int", "accessor" => "getNoteType"),
    );
    private $table = "task_manager.comments";

    /**
     * The value for the app_uid field.
     * @var        string
     */
    private $AppUid = '';

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    private $UserUid = '';

    /**
     * The value for the note_date field.
     * @var        int
     */
    private $NoteDate;

    /**
     * The value for the note_content field.
     * @var        string
     */
    private $NoteContent;

    /**
     * The value for the note_type field.
     * @var        string
     */
    private $NoteType;

    /**
     * The value for the note_recipients field.
     * @var        string
     */
    private $Recipients;
    private $objMysql;

    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid ()
    {
        return $this->AppUid;
    }

    /**
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUserUid ()
    {
        return $this->UserUid;
    }

    /**
     * Get the [note_date] column value.
     * 
     */
    public function getNoteDate ()
    {
        return $this->NoteDate;
    }

    /**
     * Get the [note_content] column value.
     * 
     * @return     string
     */
    public function getNoteContent ()
    {
        return $this->NoteContent;
    }

    /**
     * Get the [note_type] column value.
     * 
     * @return     string
     */
    public function getNoteType ()
    {
        return $this->NoteType;
    }

    /**
     * Get the [note_recipients] column value.
     * 
     * @return     string
     */
    public function getRecipients ()
    {
        return $this->Recipients;
    }

    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid ($AppUid)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $AppUid !== null && !is_string ($AppUid) )
        {
            $AppUid = (string) $AppUid;
        }
        if ( $this->AppUid !== $AppUid || $AppUid === '' )
        {
            $this->AppUid = $AppUid;
        }
    }

    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUserUid ($UserUid)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $UserUid !== null && !is_string ($UserUid) )
        {
            $UserUid = (string) $UserUid;
        }
        if ( $this->UserUid !== $UserUid || $UserUid === '' )
        {
            $this->UserUid = $UserUid;
        }
    }

    /**
     * Set the value of [note_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setNoteDate ($NoteDate)
    {
        if ( $NoteDate !== null && !is_int ($NoteDate) )
        {
            $ts = strtotime ($NoteDate);
            //Date/time accepts null values
            if ( $NoteDate == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [note_date] from input: ");
            }
        }
        else
        {
            $ts = $NoteDate;
        }
        if ( $this->NoteDate !== $ts )
        {
            $this->NoteDate = date("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [note_content] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setNoteContent ($NoteContent)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $NoteContent !== null && !is_string ($NoteContent) )
        {
            $NoteContent = (string) $NoteContent;
        }
        if ( $this->NoteContent !== $NoteContent )
        {
            $this->NoteContent = $NoteContent;
        }
    }

    /**
     * Set the value of [note_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setNoteType ($NoteType)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $NoteType !== null && !is_string ($NoteType) )
        {
            $NoteType = (string) $NoteType;
        }
        if ( $this->NoteType !== $NoteType || $NoteType === 'USER' )
        {
            $this->NoteType = $NoteType;
        }
    }

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Set the value of [note_recipients] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRecipients ($Recipients)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $Recipients !== null && !is_string ($Recipients) )
        {
            $Recipients = (string) $Recipients;
        }
        if ( $this->Recipients !== $Recipients )
        {
            $this->Recipients = $Recipients;
        }
    }

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailures[]
     */
    public function getValidationFailures ()
    {
        return $this->ValidationFailures;
    }

    public function setValidationFailures ($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */
    public function validate ()
    {
        $intErrorCount = 0;

        foreach ($this->objFields as $fieldName => $arrField) {
            $accessor = $arrField['accessor'];

            $value = call_user_func (array($this, $accessor));

            if ( trim ($value) == "" )
            {
                $this->ValidationFailures[] = $fieldName . " IS EMPTY";
                $intErrorCount++;
            }
        }

        if ( $intErrorCount > 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     */
    public function save ()
    {
        $this->objMysql->_insert ($this->table, array(
            "source_id" => $this->AppUid,
            "comment" => $this->NoteContent,
            "datetime" => $this->NoteDate,
            "username" => $this->UserUid,
            "comment_type" => $this->NoteType,
            "recipients" => $this->Recipients
                )
        );
    }

    public function loadObject ()
    {
        
    }

}
