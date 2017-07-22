<?php
/**
 * Base class that represents a row from the 'REPORT_TABLE' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseReportTable implements Persistent
{
    /**
     * The value for the rep_tab_uid field.
     * @var        string
     */
    protected $rep_tab_uid = '';
    /**
     * The value for the rep_tab_title field.
     * @var        string
     */
    protected $rep_tab_title;
    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';
    /**
     * The value for the rep_tab_name field.
     * @var        string
     */
    protected $rep_tab_name = '';
    /**
     * The value for the rep_tab_type field.
     * @var        string
     */
    protected $rep_tab_type = '';
    /**
     * The value for the rep_tab_grid field.
     * @var        string
     */
    protected $rep_tab_grid = '';
    /**
     * The value for the rep_tab_connection field.
     * @var        string
     */
    protected $rep_tab_connection = '';
    /**
     * The value for the rep_tab_create_date field.
     * @var        int
     */
    protected $rep_tab_create_date;
    /**
     * The value for the rep_tab_status field.
     * @var        string
     */
    protected $rep_tab_status = 'ACTIVE';
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
     * Get the [rep_tab_uid] column value.
     * 
     * @return     string
     */
    public function getRepTabUid()
    {
        return $this->rep_tab_uid;
    }
    /**
     * Get the [rep_tab_title] column value.
     * 
     * @return     string
     */
    public function getRepTabTitle()
    {
        return $this->rep_tab_title;
    }
    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid()
    {
        return $this->pro_uid;
    }
    /**
     * Get the [rep_tab_name] column value.
     * 
     * @return     string
     */
    public function getRepTabName()
    {
        return $this->rep_tab_name;
    }
    /**
     * Get the [rep_tab_type] column value.
     * 
     * @return     string
     */
    public function getRepTabType()
    {
        return $this->rep_tab_type;
    }
    /**
     * Get the [rep_tab_grid] column value.
     * 
     * @return     string
     */
    public function getRepTabGrid()
    {
        return $this->rep_tab_grid;
    }
    /**
     * Get the [rep_tab_connection] column value.
     * 
     * @return     string
     */
    public function getRepTabConnection()
    {
        return $this->rep_tab_connection;
    }
    /**
     * Get the [optionally formatted] [rep_tab_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getRepTabCreateDate($format = 'Y-m-d H:i:s')
    {
        if ($this->rep_tab_create_date === null || $this->rep_tab_create_date === '') {
            return null;
        } elseif (!is_int($this->rep_tab_create_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->rep_tab_create_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [rep_tab_create_date] as date/time value: " .
                    var_export($this->rep_tab_create_date, true));
            }
        } else {
            $ts = $this->rep_tab_create_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }
    /**
     * Get the [rep_tab_status] column value.
     * 
     * @return     string
     */
    public function getRepTabStatus()
    {
        return $this->rep_tab_status;
    }
    /**
     * Set the value of [rep_tab_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_uid !== $v || $v === '') {
            $this->rep_tab_uid = $v;
        }
    } 
    
    /**
     * Set the value of [rep_tab_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabTitle($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_title !== $v) {
            $this->rep_tab_title = $v;
        }
    }
    
    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->pro_uid !== $v || $v === '') {
            $this->pro_uid = $v;
        }
    }
    
    /**
     * Set the value of [rep_tab_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabName($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_name !== $v || $v === '') {
            $this->rep_tab_name = $v;
            $this->modifiedColumns[] = ReportTablePeer::REP_TAB_NAME;
        }
    } 
    
    /**
     * Set the value of [rep_tab_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabType($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_type !== $v || $v === '') {
            $this->rep_tab_type = $v;
        }
    } 
    
    /**
     * Set the value of [rep_tab_grid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabGrid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_grid !== $v || $v === '') {
            $this->rep_tab_grid = $v;
        }
    } 
    
    /**
     * Set the value of [rep_tab_connection] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabConnection($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_connection !== $v || $v === '') {
            $this->rep_tab_connection = $v;
        }
    } 
    
    /**
     * Set the value of [rep_tab_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setRepTabCreateDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [rep_tab_create_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->rep_tab_create_date !== $ts) {
            $this->rep_tab_create_date = date("Y-m-d H:i:s", $ts);
        }
    } 
    
    /**
     * Set the value of [rep_tab_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabStatus($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->rep_tab_status !== $v || $v === 'ACTIVE') {
            $this->rep_tab_status = $v;
        }
    } 
    
    public function validate ()
    {
       
    }
    
    public function loadObject (array $arrData)
    {
        
    }
    
    public function save ()
    {
        
    }
    
   
}