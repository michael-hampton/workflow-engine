<?php

/**
 * Base class that represents a row from the 'WEB_ENTRY_EVENT' table.
 */
abstract class BaseWebEntryEvent implements Persistent
{

    private $objMysql;
    private $arrayFieldDefinition = array(
         "PRO_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getPrjUid", "mutator" => "setPrjUid"),
        "EVN_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getEvnUid", "mutator" => "setEvnUid"),
        "DYN_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDynUid", "mutator" => "setDynUid"),
        "USR_UID" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getUsrUid", "mutator" => "setUsrUid"),
        "WEE_DESCRIPTION" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getWeeDescription", "mutator" => "setWeeDescription"),
        "WEE_STATUS" => array("type" => "int", "required" => true, "empty" => false, "accessor" => "getWeeStatus", "mutator" => "setWeeStatus"),
        "WEE_TITLE" => array("type" => "int", "required" => true, "empty" => false, "accessor" => "getWeeTitle", "mutator" => "setWeeTitle"),
    );

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
    protected $weeUrl;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getWeeUrl ()
    {
        return $this->weeUrl;
    }

    public function setWeeUrl ($weeUrl)
    {
        $this->weeUrl = $weeUrl;
    }

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
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     *
     * @return     int The number of rows affected by this insert/update
     */
    public function save ()
    {
        if ( trim ($this->wee_uid) === "" )
        {
            $id = $this->objMysql->_insert ("workflow.WEB_ENTRY_EVENT", [
                "WEE_TITLE" => $this->wee_title,
                "WEE_DESCRIPTION" => $this->wee_description,
                "PRJ_UID" => $this->prj_uid,
                "EVN_UID" => $this->evn_uid,
                "ACT_UID" => $this->act_uid,
                "DYN_UID" => $this->dyn_uid,
                "USR_UID" => $this->usr_uid,
                "WEE_STATUS" => $this->wee_status,
                "WEE_WE_UID" => $this->wee_we_uid,
                "WEE_WE_TAS_UID" => $this->wee_we_tas_uid
                    ]
            );
        }
        else
        {
            $id = $this->objMysql->_update ("workflow.WEB_ENTRY_EVENT", [
                "WEE_TITLE" => $this->wee_title,
                "WEE_DESCRIPTION" => $this->wee_description,
                "PRJ_UID" => $this->prj_uid,
                "EVN_UID" => $this->evn_uid,
                "ACT_UID" => $this->act_uid,
                "DYN_UID" => $this->dyn_uid,
                "USR_UID" => $this->usr_uid,
                "WEE_STATUS" => $this->wee_status,
                "WEE_WE_UID" => $this->wee_we_uid,
                "WEE_WE_TAS_UID" => $this->wee_we_tas_uid
                    ],
                    ["WEE_UID" => $this->wee_uid]
            );
        }


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
    public function validate ()
    {
        foreach ($this->arrayFieldDefinition as $field => $arrValue) {

            $fieldValue = $this->$arrValue['accessor'] ();

            if ( $arrValue['required'] === true && trim ($fieldValue) === "" )
            {
                $this->validationFailures[] = $field . " Is missing";
            }
        }

        if ( count ($this->validationFailures) > 0 )
        {
            return false;
        }

        return true;
    }

    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrayFieldDefinition[$formField]) )
            {
                $mutator = $this->arrayFieldDefinition[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrayFieldDefinition[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     */
    public function delete ()
    {
        try {
            $result = $this->objMysql->_delete ("workflow.web_entry_event", ["WEE_UID" => $this->wee_uid]);

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
