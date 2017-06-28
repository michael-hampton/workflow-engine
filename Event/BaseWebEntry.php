<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Base class that represents a row from the 'WEB_ENTRY' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseWebEntry implements Persistent
{
    /**
     * The value for the we_uid field.
     * @var        string
     */
    protected $we_uid;
    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid;
    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid;
    /**
     * The value for the dyn_uid field.
     * @var        string
     */
    protected $dyn_uid;
    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';
    /**
     * The value for the we_method field.
     * @var        string
     */
    protected $we_method = 'HTML';
    /**
     * The value for the we_input_document_access field.
     * @var        int
     */
    protected $we_input_document_access = 0;
    /**
     * The value for the we_data field.
     * @var        string
     */
    protected $we_data;
    /**
     * The value for the we_create_usr_uid field.
     * @var        string
     */
    protected $we_create_usr_uid = '';
    /**
     * The value for the we_update_usr_uid field.
     * @var        string
     */
    protected $we_update_usr_uid = '';
    /**
     * The value for the we_create_date field.
     * @var        int
     */
    protected $we_create_date;
    /**
     * The value for the we_update_date field.
     * @var        int
     */
    protected $we_update_date;
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
     * Get the [we_uid] column value.
     * 
     * @return     string
     */
    public function getWeUid()
    {
        return $this->we_uid;
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
     * Get the [tas_uid] column value.
     * 
     * @return     string
     */
    public function getTasUid()
    {
        return $this->tas_uid;
    }
    /**
     * Get the [dyn_uid] column value.
     * 
     * @return     string
     */
    public function getDynUid()
    {
        return $this->dyn_uid;
    }
    /**
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid()
    {
        return $this->usr_uid;
    }
    /**
     * Get the [we_method] column value.
     * 
     * @return     string
     */
    public function getWeMethod()
    {
        return $this->we_method;
    }
    /**
     * Get the [we_input_document_access] column value.
     * 
     * @return     int
     */
    public function getWeInputDocumentAccess()
    {
        return $this->we_input_document_access;
    }
    /**
     * Get the [we_data] column value.
     * 
     * @return     string
     */
    public function getWeData()
    {
        return $this->we_data;
    }
    /**
     * Get the [we_create_usr_uid] column value.
     * 
     * @return     string
     */
    public function getWeCreateUsrUid()
    {
        return $this->we_create_usr_uid;
    }
    /**
     * Get the [we_update_usr_uid] column value.
     * 
     * @return     string
     */
    public function getWeUpdateUsrUid()
    {
        return $this->we_update_usr_uid;
    }
    /**
     * Get the [optionally formatted] [we_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getWeCreateDate($format = 'Y-m-d H:i:s')
    {
        if ($this->we_create_date === null || $this->we_create_date === '') {
            return null;
        } elseif (!is_int($this->we_create_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->we_create_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [we_create_date] as date/time value: " .
                    var_export($this->we_create_date, true));
            }
        } else {
            $ts = $this->we_create_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, strtotime ($ts));
        }
    }
    /**
     * Get the [optionally formatted] [we_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getWeUpdateDate($format = 'Y-m-d H:i:s')
    {
        if ($this->we_update_date === null || $this->we_update_date === '') {
            return null;
        } elseif (!is_int($this->we_update_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->we_update_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [we_update_date] as date/time value: " .
                    var_export($this->we_update_date, true));
            }
        } else {
            $ts = $this->we_update_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, strtotime ($ts));
        }
    }
    /**
     * Set the value of [we_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->we_uid !== $v) {
            $this->we_uid = $v;
            $this->modifiedColumns[] = WebEntryPeer::WE_UID;
        }
    } // setWeUid()
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
        if ($this->pro_uid !== $v) {
            $this->pro_uid = $v;
        }
    } // setProUid()
    /**
     * Set the value of [tas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->tas_uid !== $v) {
            $this->tas_uid = $v;
        }
    } // setTasUid()
    /**
     * Set the value of [dyn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->dyn_uid !== $v) {
            $this->dyn_uid = $v;
        }
    } // setDynUid()
    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->usr_uid !== $v || $v === '') {
            $this->usr_uid = $v;
        }
    } // setUsrUid()
    /**
     * Set the value of [we_method] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeMethod($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->we_method !== $v || $v === 'HTML') {
            $this->we_method = $v;
        }
    } // setWeMethod()
    /**
     * Set the value of [we_input_document_access] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setWeInputDocumentAccess($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->we_input_document_access !== $v || $v === 0) {
            $this->we_input_document_access = $v;
        }
    } // setWeInputDocumentAccess()
    /**
     * Set the value of [we_data] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeData($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->we_data !== $v) {
            $this->we_data = $v;
        }
    } // setWeData()
    /**
     * Set the value of [we_create_usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeCreateUsrUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->we_create_usr_uid !== $v || $v === '') {
            $this->we_create_usr_uid = $v;
        }
    } // setWeCreateUsrUid()
    /**
     * Set the value of [we_update_usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setWeUpdateUsrUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->we_update_usr_uid !== $v || $v === '') {
            $this->we_update_usr_uid = $v;
        }
    } // setWeUpdateUsrUid()
    /**
     * Set the value of [we_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setWeCreateDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [we_create_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->we_create_date !== $ts) {
            $this->we_create_date = $ts;
        }
    } // setWeCreateDate()
    /**
     * Set the value of [we_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setWeUpdateDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [we_update_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->we_update_date !== $ts) {
            $this->we_update_date = $ts;
        }
    }
}