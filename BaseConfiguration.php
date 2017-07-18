<?php
/**
 * Base class that represents a row from the 'CONFIGURATION' table.
 *
 */
abstract class BaseConfiguration implements Persistent
{
	 protected $arrFieldMapping = array(
        'fk_team' => array('accessor' => 'getFkTeamId', 'mutator' => 'setFkTeamId', 'type' => 'int', 'required' => 'true'),
        'summary' => array('accessor' => 'getSummary', 'mutator' => 'setSummary', 'type' => 'string', 'required' => 'true'),
        'jobRole' => array('accessor' => 'getRole', 'mutator' => 'setRole', 'type' => 'string', 'required' => 'true')
    );

    /**
     * The value for the cfg_uid field.
     */

    protected $cfg_uid = '';
    
    /**
     * The value for the obj_uid field.
     * @var        string
     */
    protected $obj_uid = '';
    
    /**
     * The value for the cfg_value field.
     * @var        string
     */
    protected $cfg_value;
   
   /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';
    
    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';
    
    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';
   
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
     * Get the [cfg_uid] column value.
     * 
     * @return     string
     */
    public function getCfgUid()
    {
        return $this->cfg_uid;
    }
   
   /**
     * Get the [obj_uid] column value.
     * 
     * @return     string
     */
    public function getObjUid()
    {
        return $this->obj_uid;
    }
    
    /**
     * Get the [cfg_value] column value.
     * 
     * @return     string
     */
    public function getCfgValue()
    {
        return $this->cfg_value;
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
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid()
    {
        return $this->usr_uid;
    }
   
   /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid()
    {
        return $this->app_uid;
    }
   
   /**
     * Set the value of [cfg_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCfgUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->cfg_uid !== $v || $v === '') {
            $this->cfg_uid = $v;
        }
    } // setCfgUid()
    
    /**
     * Set the value of [obj_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setObjUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->obj_uid !== $v || $v === '') {
            $this->obj_uid = $v;
        }
    } // setObjUid()
  
  /**
     * Set the value of [cfg_value] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCfgValue($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->cfg_value !== $v) {
            $this->cfg_value = $v;
        }
    } // setCfgValue()
   
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
    } // setProUid()
    
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
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->app_uid !== $v || $v === '') {
            $this->app_uid = $v;
        }
    } // setAppUid()
    
    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
       
    }
   
   /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @return     int The number of rows affected by this insert/update
     */
    public function save()
    {
      
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
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }
   
   /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */
    public function validate()
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }
	 
   public function loadObject(array $arrData)
    {
        if (!empty($arrData) && is_array($arrData)) {
            foreach ($this->arrFieldMapping as $strFieldKey => $arrFields) {
                if (isset($arrData[$strFieldKey])) {
                    $strMutatorMethod = $arrFields['mutator'];
                    if (is_callable(array($this, $strMutatorMethod))) {
                        call_user_func(array($this, $strMutatorMethod), $arrData[$strFieldKey]);
                    }
                }
            }
            return true;
        }
        return false;
    }
}
