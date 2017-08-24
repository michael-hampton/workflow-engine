<?php

abstract class BaseProcessFile implements Persistent
{

    private $objMysql;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    private $ProUid;

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    private $UsrUid;

    /**
     * The value for the prf_update_usr_uid field.
     * @var        string
     */
    private $PrfUpdateUsrUid;

    /**
     * The value for the prf_path field.
     * @var        string
     */
    private $PrfPath;

    /**
     * The value for the prf_type field.
     * @var        string
     */
    private $PrfType;

    /**
     * The value for the prf_editable field.
     * @var        int
     */
    private $PrfEditable;

    /**
     * The value for the prf_create_date field.
     * @var        int
     */
    private $PrfCreateDate;
    private $id;
    private $New = true;
    private $fileType;
    private $PrfFielname;
    private $downloadPath;

    /**
     * The value for the prf_update_date field.
     * @var        int
     */
    protected $prf_update_date;

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

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

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid ()
    {
        return $this->ProUid;
    }

    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid ($ProUid)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $ProUid !== null && !is_string ($ProUid) )
        {
            $ProUid = (string) $ProUid;
        }
        if ( $this->ProUid !== $ProUid )
        {
            $this->ProUid = $ProUid;
        }
    }

    /**
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid ()
    {
        return $this->UsrUid;
    }

    /**
     * Set the value of [usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setUsrUid ($UsrUid)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $UsrUid !== null && !is_string ($UsrUid) )
        {
            $UsrUid = (string) $UsrUid;
        }

        if ( $this->UsrUid !== $UsrUid )
        {
            $this->UsrUid = $UsrUid;
        }
    }

    /**
     * Get the [prf_update_usr_uid] column value.
     * 
     * @return     string
     */
    public function getPrfUpdateUsrUid ()
    {
        return $this->PrfUpdateUsrUid;
    }

    /**
     * Set the value of [prf_update_usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPrfUpdateUsrUid ($PrfUpdateUsrUid)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $PrfUpdateUsrUid !== null && !is_string ($PrfUpdateUsrUid) )
        {
            $PrfUpdateUsrUid = (string) $PrfUpdateUsrUid;
        }
        if ( $this->PrfUpdateUsrUid !== $PrfUpdateUsrUid )
        {
            $this->PrfUpdateUsrUid = $PrfUpdateUsrUid;
        }
    }

    /**
     * Get the [prf_path] column value.
     * 
     * @return     string
     */
    public function getPrfPath ()
    {
        return $this->PrfPath;
    }

    /**
     * Set the value of [prf_path] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPrfPath ($PrfPath)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $PrfPath !== null && !is_string ($PrfPath) )
        {
            $PrfPath = (string) $PrfPath;
        }
        if ( $this->PrfPath !== $PrfPath || $PrfPath === '' )
        {
            $this->PrfPath = $PrfPath;
        }
    }

    public function getPrfFielname ()
    {
        return $this->PrfFielname;
    }

    public function setPrfFielname ($PrfFielname)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $PrfFielname !== null && !is_string ($PrfFielname) )
        {
            $PrfFielname = (string) $PrfFielname;
        }
        if ( $this->PrfFielname !== $PrfFielname || $PrfFielname === '' )
        {
            $this->PrfFielname = $PrfFielname;
        }
    }

    /**
     * Get the [prf_type] column value.
     * 
     * @return     string
     */
    public function getPrfType ()
    {
        return $this->PrfType;
    }

    /**
     * Set the value of [prf_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPrfType ($PrfType)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $PrfType !== null && !is_string ($PrfType) )
        {
            $PrfType = (string) $PrfType;
        }
        if ( $this->PrfType !== $PrfType || $PrfType === '' )
        {
            $this->PrfType = $PrfType;
        }
    }

    /**
     * Get the [prf_editable] column value.
     * 
     * @return     int
     */
    public function getPrfEditable ()
    {
        return $this->PrfEditable;
    }

    /**
     * Set the value of [prf_editable] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setPrfEditable ($PrfEditable)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $PrfEditable !== null && !is_int ($PrfEditable) && is_numeric ($PrfEditable) )
        {
            $PrfEditable = (int) $PrfEditable;
        }
        if ( $this->PrfEditable !== $PrfEditable || $PrfEditable === 1 )
        {
            $this->PrfEditable = $PrfEditable;
        }
    }

    /**
     * Get the [optionally formatted] [prf_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getPrfUpdateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->prf_update_date === null || $this->prf_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->prf_update_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->prf_update_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [prf_update_date] as date/time value: " .
                var_export ($this->prf_update_date, true));
            }
        }
        else
        {
            $ts = $this->prf_update_date;
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
     * Get the [optionally formatted] [prf_create_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     */
    public function getPrfCreateDate ()
    {
        return $this->PrfCreateDate;
    }

    /**
     * Set the value of [prf_create_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setPrfCreateDate ($PrfCreateDate)
    {
        if ( $PrfCreateDate !== null && !is_int ($PrfCreateDate) )
        {
            $ts = strtotime ($PrfCreateDate);
            //Date/time accepts null values
            if ( $PrfCreateDate == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [prf_create_date] from input: ");
            }
        }
        else
        {
            $ts = $PrfCreateDate;
        }

        if ( $this->PrfCreateDate !== $ts )
        {
            $this->PrfCreateDate = $PrfCreateDate;
        }
    }

    /**
     * Get the [id] column value.
     * 
     * @return     string
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * 
     * @param type $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getNew ()
    {
        return $this->New;
    }

    /**
     * 
     * @param type $New
     */
    public function setNew ($New)
    {
        $this->New = $New;
    }

    public function getFileType ()
    {
        return $this->fileType;
    }

    /**
     * 
     * @param type $fileType
     */
    public function setFileType ($fileType)
    {
        $this->fileType = $fileType;
    }

    public function getDownloadPath ()
    {
        return $this->downloadPath;
    }

    public function setDownloadPath ($downloadPath)
    {
        $this->downloadPath = $downloadPath;
    }

    /**
     * Set the value of [prf_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setPrfUpdateDate ($v)
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
                throw new PropelException ("Unable to parse date/time value for [prf_update_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->prf_update_date !== $ts )
        {
            $this->prf_update_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    public function retrieveByPk ($pk)
    {
        $result = $this->objMysql->_select ("task_manager.PROCESS_FILES", [], ["id" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $processFiles = new ProcessFile();
        $processFiles->setFileType ($result[0]['file_type']);
        $processFiles->setPrfCreateDate ($result[0]['date_uploaded']);
        $processFiles->setPrfFielname ($result[0]['filename']);
        $processFiles->setPrfPath ($result[0]['file_destination']);
        $processFiles->setUsrUid ($result[0]['uploaded_by']);
        $processFiles->setPrfEditable ($result[0]['prf_editable']);
        $processFiles->setId ($result[0]['id']);

        return $processFiles;
    }

    private function doUpdate ()
    {
        
        $this->objMysql->_update ("task_manager.PROCESS_FILES", array(
            "prf_editable" => $this->PrfEditable,
            "date_updated" => $this->prf_update_date,
            "updated_user" => $this->PrfUpdateUsrUid
                ), ["id" => $this->id]
        );
    }

    private function doInsert ()
    {
        $id = $this->objMysql->_insert ("task_manager.PROCESS_FILES", array(
            "date_uploaded" => $this->PrfCreateDate,
            "uploaded_by" => $this->UsrUid,
            "file_destination" => $this->PrfPath,
            "prf_type" => $this->PrfType,
            "prf_editable" => $this->PrfEditable,
            "file_type" => $this->fileType,
            "filename" => $this->PrfFielname,
            "PRO_UID" => $this->ProUid
                )
        );

        $this->setId ($id);
    }

    public function validate ()
    {
        
    }

    public function loadObject (array $arrData)
    {
        ;
    }

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
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     Exception
     */
    public function save ()
    {
        $affectedRows = 0; // initialize var to track total num of affected rows

        if ( !$this->alreadyInSave )
        {
            $this->alreadyInSave = true;
        }

        if ( $this->id != "" && is_numeric ($this->id) )
        {
            $this->doUpdate ();
        }
        else
        {
            $affectedRows += 1;
            $this->doInsert ();
        }

        $this->alreadyInSave = false;
    }

    public function delete ()
    {
        if ( trim ($this->id) == "" || !is_numeric ($this->id) )
        {
            throw new Exception ("Invalid id given");
        }

        $this->objMysql->_delete ("task_manager.PROCESS_FILES", array("id" => $this->id));
    }

}
