<?php

abstract class BaseStep implements Persistent
{
    /************************* BASE MODEL ******************************************/
    protected $arrFieldMapping = array(
        'STEP_UID' => array('accessor' => 'getStepUid', 'mutator' => 'setStepUid', 'type' => 'int', 'required' => 'false'),
        'PRO_UID' => array('accessor' => 'getProUid', 'mutator' => 'setProUid', 'type' => 'string', 'required' => 'true'),
        'TAS_UID' => array('accessor' => 'getTasUid', 'mutator' => 'setTasUid', 'type' => 'string', 'required' => 'false'),
        'STEP_TYPE_OBJ' => array('accessor' => 'getStepTypeObj', 'mutator' => 'setStepTypeObj', 'type' => 'string', 'required' => 'true'),
        'STEP_UID_OBJ' => array('accessor' => 'getStepUidObj', 'mutator' => 'setStepUidObj', 'type' => 'string', 'required' => 'true'),
        'STEP_CONDITION' => array('accessor' => 'getStepCondition', 'mutator' => 'setStepCondition', 'type' => 'string', 'required' => 'false'),
        'STEP_MODE' => array('accessor' => 'getStepMode', 'mutator' => 'setStepMode', 'type' => 'string', 'required' => 'true')
    );

    private $objMysql;

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * The value for the step_uid field.
     * @var        string
     */
    protected $step_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '0';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '0';

    /**
     * The value for the step_type_obj field.
     * @var        string
     */
    protected $step_type_obj = 'DYNAFORM';

    /**
     * The value for the step_uid_obj field.
     * @var        string
     */
    protected $step_uid_obj = '0';

    /**
     * The value for the step_condition field.
     * @var        string
     */
    protected $step_condition;

    /**
     * The value for the step_position field.
     * @var        int
     */
    protected $step_position = 0;

    /**
     * The value for the step_mode field.
     * @var        string
     */
    protected $step_mode = 'EDIT';

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

    protected $validationFailures;

    /**
     * Get the [step_uid] column value.
     *
     * @return     string
     */
    public function getStepUid()
    {
        return $this->step_uid;
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
     * Get the [step_type_obj] column value.
     *
     * @return     string
     */
    public function getStepTypeObj()
    {
        return $this->step_type_obj;
    }

    /**
     * Get the [step_uid_obj] column value.
     *
     * @return     string
     */
    public function getStepUidObj()
    {
        return $this->step_uid_obj;
    }

    /**
     * Get the [step_condition] column value.
     *
     * @return     string
     */
    public function getStepCondition()
    {
        return $this->step_condition;
    }

    /**
     * Get the [step_position] column value.
     *
     * @return     int
     */
    public function getStepPosition()
    {
        return $this->step_position;
    }

    /**
     * Get the [step_mode] column value.
     *
     * @return     string
     */
    public function getStepMode()
    {
        return $this->step_mode;
    }

    /**
     * @return mixed
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Set the value of [step_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setStepUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string)$v;
        }
        if ($this->step_uid !== $v || $v === '') {
            $this->step_uid = $v;
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
            $v = (string)$v;
        }
        if ($this->pro_uid !== $v || $v === '0') {
            $this->pro_uid = $v;
        }
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
            $v = (string)$v;
        }
        if ($this->tas_uid !== $v || $v === '0') {
            $this->tas_uid = $v;
        }
    }

    /**
     * Set the value of [step_type_obj] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setStepTypeObj($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string)$v;
        }
        if ($this->step_type_obj !== $v || $v === 'DYNAFORM') {
            $this->step_type_obj = $v;
        }
    }

    /**
     * Set the value of [step_uid_obj] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setStepUidObj($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string)$v;
        }
        if ($this->step_uid_obj !== $v || $v === '0') {
            $this->step_uid_obj = $v;
        }
    }

    /**
     * Set the value of [step_condition] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setStepCondition($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string)$v;
        }
        if ($this->step_condition !== $v) {
            $this->step_condition = $v;
        }
    }

    /**
     * Set the value of [step_position] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setStepPosition($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int)$v;
        }
        if ($this->step_position !== $v || $v === 0) {
            $this->step_position = $v;
        }
    }

    /**
     * Set the value of [step_mode] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setStepMode($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string)$v;
        }
        if ($this->step_mode !== $v || $v === 'EDIT') {
            $this->step_mode = $v;
        }
    }

    public function save()
    {
        if (trim($this->step_uid) === "") {
            $result = $this->objMysql->_insert("workflow.step",
                    ['PRO_UID' => $this->pro_uid, 'TAS_UID' => $this->tas_uid, 'STEP_UID_OBJ' => $this->step_uid_obj, 'STEP_TYPE_OBJ' => $this->step_type_obj, 'STEP_CONDITION' => $this->step_condition, 'STEP_MODE' => $this->step_mode]);
        } else {
            $result = $this - objMysql_update("workflow.step",
                    ['PRO_UID' => $this->pro_uid, 'TAS_UID' => $this->step_uid, 'STEP_TYPE_OBJ' => $this->step_type_obj, 'STEP_CONDITION' => $this->step_condition, 'STEP_MODE' => $this->step_mode],
                    ['STEP_UID' => $this->step_uid]);
        }
        return $result;

    }

    public function validate()
    {
        foreach ($this->arrFieldMapping as $strColumnName => $arrFieldMap) {

            if ($arrFieldMap['required'] === 'true') {

                if (trim($this->{$arrFieldMap['accessor']}()) === "") {
                    $this->validationFailures[] = $strColumnName . " Is missing";
                }
            }
        }

        return count($this->validationFailures) > 0 ? false : true;
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
    
    public function doDelete()
    {
        $this->objMysql->_delete("workflow.step", ["TAS_UID" => $this->tas_uid, "STEP_TYPE_OBJ" => $this->step_type_obj, "STEP_UID_OBJ" => $this->step_uid_obj]);
    }
}
