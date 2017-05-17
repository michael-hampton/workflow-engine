<?php

class ProcessFiles
{

    private $objMysql;
    private $ProUid;
    private $UsrUid;
    private $PrfUpdateUsrUid;
    private $PrfPath;
    private $PrfType;
    private $PrfEditable;
    private $PrfCreateDate;
    private $id;
    private $New = true;

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

    public function getProUid ()
    {
        return $this->ProUid;
    }

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

    public function getUsrUid ()
    {
        return $this->UsrUid;
    }

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

    public function getPrfUpdateUsrUid ()
    {
        return $this->PrfUpdateUsrUid;
    }

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

    public function getPrfPath ()
    {
        return $this->PrfPath;
    }

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

    public function getPrfType ()
    {
        return $this->PrfType;
    }

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

    public function getPrfEditable ()
    {
        return $this->PrfEditable;
    }

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

    public function getPrfCreateDate ()
    {
        return $this->PrfCreateDate;
    }

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

    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getNew ()
    {
        return $this->New;
    }

    public function setNew ($New)
    {
        $this->New = $New;
    }

    private function doInsert ()
    {
        $id = $this->objMysql->_insert ("task_manager.attachments", array(
            "source_id" => $this->ProUid,
            "date_uploaded" => $this->PrfCreateDate,
            "uploaded_by" => $this->UsrUid,
            "file_destination" => $this->PrfPath,
            "prf_type" => $this->PrfType,
            "prf_editable" => $this->PrfEditable
                )
        );
        
        $this->setId($id);
    }

    private function doUpdate ()
    {
        
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
    
    public function delete()
    {
        $this->objMysql->_delete("task_manager.attachments", array("id" => $this->id));
    }

}
