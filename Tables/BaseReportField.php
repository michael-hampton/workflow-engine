<?php

/**
 * Base class that represents a row from the 'FIELDS' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseReportField implements Persistent
{

    /**
     * The value for the fld_uid field.
     * @var        string
     */
    protected $fld_uid = '';

    /**
     * The value for the add_tab_uid field.
     * @var        string
     */
    protected $add_tab_uid = '';

    /**
     * The value for the fld_index field.
     * @var        int
     */
    protected $fld_index = 1;

    /**
     * The value for the fld_name field.
     * @var        string
     */
    protected $fld_name = '';

    /**
     * The value for the fld_description field.
     * @var        string
     */
    protected $fld_description;

    /**
     * The value for the fld_type field.
     * @var        string
     */
    protected $fld_type = '';

    /**
     * The value for the fld_size field.
     * @var        int
     */
    protected $fld_size = 0;

    /**
     * The value for the fld_null field.
     * @var        int
     */
    protected $fld_null = 1;

    /**
     * The value for the fld_auto_increment field.
     * @var        int
     */
    protected $fld_auto_increment = 0;

    /**
     * The value for the fld_key field.
     * @var        int
     */
    protected $fld_key = 0;

    /**
     * The value for the fld_table_index field.
     * @var        int
     */
    protected $fld_table_index = 0;

    /**
     * The value for the fld_foreign_key field.
     * @var        int
     */
    protected $fld_foreign_key = 0;

    /**
     * The value for the fld_foreign_key_table field.
     * @var        string
     */
    protected $fld_foreign_key_table = '';

    /**
     * The value for the fld_dyn_name field.
     * @var        string
     */
    protected $fld_dyn_name = '';

    /**
     * The value for the fld_dyn_uid field.
     * @var        string
     */
    protected $fld_dyn_uid = '';

    /**
     * The value for the fld_filter field.
     * @var        int
     */
    protected $fld_filter = 0;

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
    
    private $arrFieldMapping = array(
        "MESS_ENGINE" => array("accessor" => "getMessEngine", "mutator" => "setMessEngine", "required" => "true"),
        "MESS_SERVER" => array("accessor" => "getMessServer", "mutator" => "setMessServer", "required" => "true"),
        "MESS_PORT" => array("accessor" => "getMessPort", "mutator" => "setMessPort", "required" => "false"),
        "MESS_ACCOUNT" => array("accessor" => "getMessAccount", "mutator" => "setMessAccount", "required" => "true"),
        "SMTPSECURE" => array("accessor" => "getSmtpsecure", "mutator" => "setSmtpsecure", "required" => "true"),
        "MESS_RAUTH" => array("accessor" => "getMessRauth", "mutator" => "setMessRauth", "required" => "true"),
        "MESS_PASSWORD" => array("accessor" => "getMessPassword", "mutator" => "setMessPassword", "required" => "true"),
        "MESS_FROM_MAIL" => array("accessor" => "getMessFromMail", "mutator" => "setMessFromMail", "required" => "true"),
        "MESS_FROM_NAME" => array("accessor" => "getMessFromName", "mutator" => "setMessFromName", "required" => "true"),
        "MESS_DEFAULT" => array("accessor" => "GetMessDefault", "mutator" => "setMessDefault", "required" => "true"),
    );
    

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [fld_uid] column value.
     * 
     * @return     string
     */
    public function getFldUid ()
    {
        return $this->fld_uid;
    }

    /**
     * Get the [add_tab_uid] column value.
     * 
     * @return     string
     */
    public function getAddTabUid ()
    {
        return $this->add_tab_uid;
    }

    /**
     * Get the [fld_index] column value.
     * 
     * @return     int
     */
    public function getFldIndex ()
    {
        return $this->fld_index;
    }

    /**
     * Get the [fld_name] column value.
     * 
     * @return     string
     */
    public function getFldName ()
    {
        return $this->fld_name;
    }

    /**
     * Get the [fld_description] column value.
     * 
     * @return     string
     */
    public function getFldDescription ()
    {
        return $this->fld_description;
    }

    /**
     * Get the [fld_type] column value.
     * 
     * @return     string
     */
    public function getFldType ()
    {
        return $this->fld_type;
    }

    /**
     * Get the [fld_size] column value.
     * 
     * @return     int
     */
    public function getFldSize ()
    {
        return $this->fld_size;
    }

    /**
     * Get the [fld_null] column value.
     * 
     * @return     int
     */
    public function getFldNull ()
    {
        return $this->fld_null;
    }

    /**
     * Get the [fld_auto_increment] column value.
     * 
     * @return     int
     */
    public function getFldAutoIncrement ()
    {
        return $this->fld_auto_increment;
    }

    /**
     * Get the [fld_key] column value.
     * 
     * @return     int
     */
    public function getFldKey ()
    {
        return $this->fld_key;
    }

    /**
     * Get the [fld_table_index] column value.
     * 
     * @return     int
     */
    public function getFldTableIndex ()
    {
        return $this->fld_table_index;
    }

    /**
     * Get the [fld_foreign_key] column value.
     * 
     * @return     int
     */
    public function getFldForeignKey ()
    {
        return $this->fld_foreign_key;
    }

    /**
     * Get the [fld_foreign_key_table] column value.
     * 
     * @return     string
     */
    public function getFldForeignKeyTable ()
    {
        return $this->fld_foreign_key_table;
    }

    /**
     * Get the [fld_dyn_name] column value.
     * 
     * @return     string
     */
    public function getFldDynName ()
    {
        return $this->fld_dyn_name;
    }

    /**
     * Get the [fld_dyn_uid] column value.
     * 
     * @return     string
     */
    public function getFldDynUid ()
    {
        return $this->fld_dyn_uid;
    }

    /**
     * Get the [fld_filter] column value.
     * 
     * @return     int
     */
    public function getFldFilter ()
    {
        return $this->fld_filter;
    }

    /**
     * Set the value of [fld_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_uid !== $v || $v === '' )
        {
            $this->fld_uid = $v;
        }
    }

    /**
     * Set the value of [add_tab_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_uid !== $v || $v === '' )
        {
            $this->add_tab_uid = $v;
        }
    }

    /**
     * Set the value of [fld_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldIndex ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_index !== $v || $v === 1 )
        {
            $this->fld_index = $v;
        }
    }

    /**
     * Set the value of [fld_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldName ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_name !== $v || $v === '' )
        {
            $this->fld_name = $v;
        }
    }

    /**
     * Set the value of [fld_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldDescription ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_description !== $v )
        {
            $this->fld_description = $v;
        }
    }

    /**
     * Set the value of [fld_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_type !== $v || $v === '' )
        {
            $this->fld_type = $v;
        }
    }

    /**
     * Set the value of [fld_size] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldSize ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_size !== $v || $v === 0 )
        {
            $this->fld_size = $v;
        }
    }

    /**
     * Set the value of [fld_null] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldNull ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_null !== $v || $v === 1 )
        {
            $this->fld_null = $v;
        }
    }

    /**
     * Set the value of [fld_auto_increment] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldAutoIncrement ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_auto_increment !== $v || $v === 0 )
        {
            $this->fld_auto_increment = $v;
        }
    }

    /**
     * Set the value of [fld_key] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldKey ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_key !== $v || $v === 0 )
        {
            $this->fld_key = $v;
        }
    }

    /**
     * Set the value of [fld_table_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldTableIndex ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_table_index !== $v || $v === 0 )
        {
            $this->fld_table_index = $v;
        }
    }

    /**
     * Set the value of [fld_foreign_key] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldForeignKey ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_foreign_key !== $v || $v === 0 )
        {
            $this->fld_foreign_key = $v;
        }
    }

    /**
     * Set the value of [fld_foreign_key_table] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldForeignKeyTable ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_foreign_key_table !== $v || $v === '' )
        {
            $this->fld_foreign_key_table = $v;
        }
    }

    /**
     * Set the value of [fld_dyn_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldDynName ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_dyn_name !== $v || $v === '' )
        {
            $this->fld_dyn_name = $v;
        }
    }

    /**
     * Set the value of [fld_dyn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFldDynUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->fld_dyn_uid !== $v || $v === '' )
        {
            $this->fld_dyn_uid = $v;
        }
    }

    /**
     * Set the value of [fld_filter] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setFldFilter ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->fld_filter !== $v || $v === 0 )
        {
            $this->fld_filter = $v;
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
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *     
     */
    public function save ()
    {
        if ( trim ($this->fld_uid) !== "" )
        {
            $this->objMysql->_update ("report_tables.fields", [
                "ADD_TAB_UID" => $this->add_tab_uid,
                "FLD_INDEX" => $this->fld_index,
                "FLD_NAME" => $this->fld_name,
                "FLD_DESCRIPTION" => $this->fld_description,
                "FLD_TYPE" => $this->fld_type,
                "FLD_SIZE" => $this->fld_size,
                "FLD_NULL" => $this->fld_null,
                "FLD_AUTO_INCREMENT" => $this->fld_auto_increment,
                "FLD_TABLE_INDEX" => $this->fld_index,
                "FLD_FOREIGN_KEY" => $this->fld_foreign_key,
                "FLD_FILTER" => $this->fld_filter
                    ], ["FLD_UID" => $this->fld_uid]
            );
        }
        else
        {
            $this->objMysql->_insert ("report_tables.fields", [
                "ADD_TAB_UID" => $this->add_tab_uid,
                "FLD_INDEX" => $this->fld_index,
                "FLD_NAME" => $this->fld_name,
                "FLD_DESCRIPTION" => $this->fld_description,
                "FLD_TYPE" => $this->fld_type,
                "FLD_SIZE" => $this->fld_size,
                "FLD_NULL" => $this->fld_null,
                "FLD_AUTO_INCREMENT" => $this->fld_auto_increment,
                "FLD_TABLE_INDEX" => $this->fld_index,
                "FLD_FOREIGN_KEY" => $this->fld_foreign_key,
                "FLD_FILTER" => $this->fld_filter
                    ]
            );
        }
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
        $errorCount = 0;
        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === 'true' )
            {
                $accessor = $this->arrFieldMapping[$fieldName]['accessor'];
                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->validationFailures[] = $fieldName . " Is empty. It is a required field";
                    $errorCount++;
                }
            }
        }
        if ( $errorCount > 0 )
        {
            return FALSE;
        }
        return TRUE;
    }
    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {
            if ( isset ($this->arrFieldMapping[$formField]) )
            {
                $mutator = $this->arrFieldMapping[$formField]['mutator'];
                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrFieldMapping[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }
        return true;
    }

}
