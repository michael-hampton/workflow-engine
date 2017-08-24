<?php

/**
 * Base class that represents a row from the 'ADDITIONAL_TABLES' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseAdditionalTables implements Persistent
{

    /**
     * The value for the add_tab_uid field.
     * @var        string
     */
    protected $add_tab_uid = '';

    /**
     * The value for the add_tab_name field.
     * @var        string
     */
    protected $add_tab_name = '';

    /**
     * The value for the add_tab_class_name field.
     * @var        string
     */
    protected $add_tab_class_name = '';

    /**
     * The value for the add_tab_description field.
     * @var        string
     */
    protected $add_tab_description;

    /**
     * The value for the add_tab_sdw_log_insert field.
     * @var        int
     */
    protected $add_tab_sdw_log_insert = 0;

    /**
     * The value for the add_tab_sdw_log_update field.
     * @var        int
     */
    protected $add_tab_sdw_log_update = 0;

    /**
     * The value for the add_tab_sdw_log_delete field.
     * @var        int
     */
    protected $add_tab_sdw_log_delete = 0;

    /**
     * The value for the add_tab_sdw_log_select field.
     * @var        int
     */
    protected $add_tab_sdw_log_select = 0;

    /**
     * The value for the add_tab_sdw_max_length field.
     * @var        int
     */
    protected $add_tab_sdw_max_length = 0;

    /**
     * The value for the add_tab_sdw_auto_delete field.
     * @var        int
     */
    protected $add_tab_sdw_auto_delete = 0;

    /**
     * The value for the add_tab_plg_uid field.
     * @var        string
     */
    protected $add_tab_plg_uid = '';

    /**
     * The value for the dbs_uid field.
     * @var        string
     */
    protected $dbs_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the add_tab_type field.
     * @var        string
     */
    protected $add_tab_type = '';

    /**
     * The value for the add_tab_grid field.
     * @var        string
     */
    protected $add_tab_grid = '';

    /**
     * The value for the add_tab_tag field.
     * @var        string
     */
    protected $add_tab_tag = '';

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
        "ADD_TAB_UID" => array("accessor" => "getAddTabUid", "mutator" => "setAddTabUid", "required" => "false"),
        "ADD_TAB_NAME" => array("accessor" => "getAddTabName", "mutator" => "setAddTabName", "required" => "true"),
        "ADD_TAB_CLASS_NAME" => array("accessor" => "getAddTabClassName", "mutator" => "setAddTabClassName", "required" => "false"),
        "ADD_TAB_DESCRIPTION" => array("accessor" => "getAddTabDescription", "mutator" => "setAddTabDescription", "required" => "true"),
        "ADD_TAB_PLG_UID" => array("accessor" => "getAddTabPlgUid", "mutator" => "setAddTabPlgUid", "required" => "false"),
        "DBS_UID" => array("accessor" => "getDbsUid", "mutator" => "setDbsUid", "required" => "true"),
        "PRO_UID" => array("accessor" => "getProUid", "mutator" => "setProUid", "required" => "true"),
        "ADD_TAB_TYPE" => array("accessor" => "getAddTabType", "mutator" => "setAddTabType", "required" => "true"),
        "ADD_TAB_GRID" => array("accessor" => "getAddTabGrid", "mutator" => "setAddTabGrid", "required" => "false"),
    );
    
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
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
     * Get the [add_tab_name] column value.
     * 
     * @return     string
     */
    public function getAddTabName ()
    {
        return $this->add_tab_name;
    }

    /**
     * Get the [add_tab_class_name] column value.
     * 
     * @return     string
     */
    public function getAddTabClassName ()
    {
        return $this->add_tab_class_name;
    }

    /**
     * Get the [add_tab_description] column value.
     * 
     * @return     string
     */
    public function getAddTabDescription ()
    {
        return $this->add_tab_description;
    }

    /**
     * Get the [add_tab_sdw_log_insert] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwLogInsert ()
    {
        return $this->add_tab_sdw_log_insert;
    }

    /**
     * Get the [add_tab_sdw_log_update] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwLogUpdate ()
    {
        return $this->add_tab_sdw_log_update;
    }

    /**
     * Get the [add_tab_sdw_log_delete] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwLogDelete ()
    {
        return $this->add_tab_sdw_log_delete;
    }

    /**
     * Get the [add_tab_sdw_log_select] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwLogSelect ()
    {
        return $this->add_tab_sdw_log_select;
    }

    /**
     * Get the [add_tab_sdw_max_length] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwMaxLength ()
    {
        return $this->add_tab_sdw_max_length;
    }

    /**
     * Get the [add_tab_sdw_auto_delete] column value.
     * 
     * @return     int
     */
    public function getAddTabSdwAutoDelete ()
    {
        return $this->add_tab_sdw_auto_delete;
    }

    /**
     * Get the [add_tab_plg_uid] column value.
     * 
     * @return     string
     */
    public function getAddTabPlgUid ()
    {
        return $this->add_tab_plg_uid;
    }

    /**
     * Get the [dbs_uid] column value.
     * 
     * @return     string
     */
    public function getDbsUid ()
    {
        return $this->dbs_uid;
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
     * Get the [add_tab_type] column value.
     * 
     * @return     string
     */
    public function getAddTabType ()
    {
        return $this->add_tab_type;
    }

    /**
     * Get the [add_tab_grid] column value.
     * 
     * @return     string
     */
    public function getAddTabGrid ()
    {
        return $this->add_tab_grid;
    }

    /**
     * Get the [add_tab_tag] column value.
     * 
     * @return     string
     */
    public function getAddTabTag ()
    {
        return $this->add_tab_tag;
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

// setAddTabUid()
    /**
     * Set the value of [add_tab_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabName ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_name !== $v || $v === '' )
        {
            $this->add_tab_name = $v;
        }
    }

// setAddTabName()
    /**
     * Set the value of [add_tab_class_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabClassName ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_class_name !== $v || $v === '' )
        {
            $this->add_tab_class_name = $v;
        }
    }

// setAddTabClassName()
    /**
     * Set the value of [add_tab_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabDescription ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_description !== $v )
        {
            $this->add_tab_description = $v;
        }
    }

// setAddTabDescription()
    /**
     * Set the value of [add_tab_sdw_log_insert] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwLogInsert ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_log_insert !== $v || $v === 0 )
        {
            $this->add_tab_sdw_log_insert = $v;
        }
    }

// setAddTabSdwLogInsert()
    /**
     * Set the value of [add_tab_sdw_log_update] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwLogUpdate ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_log_update !== $v || $v === 0 )
        {
            $this->add_tab_sdw_log_update = $v;
        }
    }

// setAddTabSdwLogUpdate()
    /**
     * Set the value of [add_tab_sdw_log_delete] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwLogDelete ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_log_delete !== $v || $v === 0 )
        {
            $this->add_tab_sdw_log_delete = $v;
        }
    }

// setAddTabSdwLogDelete()
    /**
     * Set the value of [add_tab_sdw_log_select] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwLogSelect ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_log_select !== $v || $v === 0 )
        {
            $this->add_tab_sdw_log_select = $v;
        }
    }

// setAddTabSdwLogSelect()
    /**
     * Set the value of [add_tab_sdw_max_length] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwMaxLength ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_max_length !== $v || $v === 0 )
        {
            $this->add_tab_sdw_max_length = $v;
        }
    }

// setAddTabSdwMaxLength()
    /**
     * Set the value of [add_tab_sdw_auto_delete] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAddTabSdwAutoDelete ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->add_tab_sdw_auto_delete !== $v || $v === 0 )
        {
            $this->add_tab_sdw_auto_delete = $v;
        }
    }

// setAddTabSdwAutoDelete()
    /**
     * Set the value of [add_tab_plg_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabPlgUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_plg_uid !== $v || $v === '' )
        {
            $this->add_tab_plg_uid = $v;
        }
    }

// setAddTabPlgUid()
    /**
     * Set the value of [dbs_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDbsUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->dbs_uid !== $v || $v === '' )
        {
            $this->dbs_uid = $v;
        }
    }

// setDbsUid()
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
        if ( $this->pro_uid !== $v || $v === '' )
        {
            $this->pro_uid = $v;
        }
    }

// setProUid()
    /**
     * Set the value of [add_tab_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_type !== $v || $v === '' )
        {
            $this->add_tab_type = $v;
        }
    }

// setAddTabType()
    /**
     * Set the value of [add_tab_grid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabGrid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_grid !== $v || $v === '' )
        {
            $this->add_tab_grid = $v;
        }
    }

// setAddTabGrid()
    /**
     * Set the value of [add_tab_tag] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAddTabTag ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->add_tab_tag !== $v || $v === '' )
        {
            $this->add_tab_tag = $v;
        }
    }

// setAddTabTag()

  
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

    public function save ()
    {
        if ( trim ($this->add_tab_uid) !== "" )
        {
            $this->objMysql->_update ("report_tables.additional_tables", [
                "PRO_UID" => $this->pro_uid,
                "ADD_TAB_TYPE" => $this->add_tab_type,
                "ADD_TAB_CLASS_NAME" => $this->add_tab_class_name,
                "ADD_TAB_DESCRIPTION" => $this->add_tab_description,
                "ADD_TAB_SDW_LOG_INSERT" => $this->add_tab_sdw_log_insert,
                "ADD_TAB_SDW_LOG_UPDATE" => $this->add_tab_sdw_log_update,
                "ADD_TAB_SDW_LOG_DELETE" => $this->add_tab_sdw_log_delete,
                "ADD_TAB_SDW_LOG_SELECT" => $this->add_tab_sdw_log_select,
                "ADD_TAB_SDW_MAX_LENGTH" => $this->add_tab_sdw_max_length,
                "ADD_TAB_SDW_AUTO_DELETE" => $this->add_tab_sdw_auto_delete,
                "ADD_TAB_PLG_UID" => $this->add_tab_plg_uid
                    ], ["ADD_TAB_UID" => $this->add_tab_uid]
            );
        }
        else
        {
            $id = $this->objMysql->_insert ("report_tables.additional_tables", [
                "PRO_UID" => $this->pro_uid,
                "ADD_TAB_TYPE" => $this->add_tab_type,
                "ADD_TAB_CLASS_NAME" => $this->add_tab_class_name,
                "ADD_TAB_DESCRIPTION" => $this->add_tab_description,
                "ADD_TAB_SDW_LOG_INSERT" => $this->add_tab_sdw_log_insert,
                "ADD_TAB_SDW_LOG_UPDATE" => $this->add_tab_sdw_log_update,
                "ADD_TAB_SDW_LOG_DELETE" => $this->add_tab_sdw_log_delete,
                "ADD_TAB_SDW_LOG_SELECT" => $this->add_tab_sdw_log_select,
                "ADD_TAB_SDW_MAX_LENGTH" => $this->add_tab_sdw_max_length,
                "ADD_TAB_SDW_AUTO_DELETE" => $this->add_tab_sdw_auto_delete,
                "ADD_TAB_PLG_UID" => $this->add_tab_plg_uid
                    ]
            );
            
            return $id;
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

}
