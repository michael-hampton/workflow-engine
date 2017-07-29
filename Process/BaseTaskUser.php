abstract class BaseTaskUser implements Persistent
{
     /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';
    
    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';
    
    /**
     * The value for the tu_type field.
     * @var        int
     */
    protected $tu_type = 1;
    
    /**
     * The value for the tu_relation field.
     * @var        int
     */
    protected $tu_relation = 0;
    
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

    private $arrayFieldDefinition = array(
        "TAS_UID" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getTasUid", "mutator" => "setTasUid"),
        "USR_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getUsrUId", "mutator" => "setUsrUId"),
        "TU_TYPE" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getTuType", "mutator" => "setTuType"),
        "TU_RELATION" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getTuRelation", "mutator" => "setTuRelation"),
    );
    
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
     * Get the [usr_uid] column value.
     * 
     * @return     string
     */
    public function getUsrUid()
    {
        return $this->usr_uid;
    }
    
    /**
     * Get the [tu_type] column value.
     * 
     * @return     int
     */
    public function getTuType()
    {
        return $this->tu_type;
    }
    
    /**
     * Get the [tu_relation] column value.
     * 
     * @return     int
     */
    public function getTuRelation()
    {
        return $this->tu_relation;
    }
    
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
        if ($this->tas_uid !== $v || $v === '') {
            $this->tas_uid = $v;
        }
    } 
    
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
    }
    
    /**
     * Set the value of [tu_type] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTuType($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->tu_type !== $v || $v === 1) {
            $this->tu_type = $v;
        }
    }
    
    /**
     * Set the value of [tu_relation] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTuRelation($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->tu_relation !== $v || $v === 0) {
            $this->tu_relation = $v;
        }
    } 
    
    public function save()
    
    }
    
    /**
     * 
     * @param type $arrData
     * @return boolean
     */
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

   public function getArrValidationErrors ()
    {
        return $this->arrValidationErrors;
    }

    /**
     * 
     * @param type $arrValidationErrors
     */
    public function setArrValidationErrors ($arrValidationErrors)
    {
        $this->arrValidationErrors = $arrValidationErrors;
    }

    /**
     * 
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;
        foreach ($this->arrayFieldDefinition as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $this->arrayFieldDefinition[$fieldName]['accessor'];
                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->arrValidationErrors[] = $fieldName . " Is empty. It is a required field";
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
}
