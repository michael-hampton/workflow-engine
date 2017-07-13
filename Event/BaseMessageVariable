
<?php
/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 13/07/2017
 * Time: 10:32
 */


    /**
     * Base class that represents a row from the 'MESSAGE_TYPE_VARIABLE' table.
     *
     *
     *
     * @package    workflow.classes.model.om
     */
abstract class BaseMessageTypeVariable implements Persistent
{

    /**
     * The value for the msgtv_uid field.
     * @var        string
     */
    protected $msgtv_uid;

    /**
     * The value for the msgt_uid field.
     * @var        string
     */
    protected $msgt_uid;

    /**
     * The value for the msgtv_name field.
     * @var        string
     */
    protected $msgtv_name = '';

    /**
     * The value for the msgtv_default_value field.
     * @var        string
     */
    protected $msgtv_default_value = '';

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

    protected $arrFieldMapping = array(
        'MSGT_UID' => array('accessor' => 'getMsgtUid', 'mutator' => 'setMsgtUid', 'type' => 'int', 'required' => 'true'),
        'MSGTV_NAME' => array('accessor' => 'getMsgtvName', 'mutator' => 'setMsgtvName', 'type' => 'string', 'required' => 'true'),
        'MSGTV_DEFAULT_VALUE' => array('accessor' => 'getMsgtvDefaultValue', 'mutator' => 'SetMsgtvDefaultValue', 'type' => 'string', 'required' => 'false'),
    );

    private $objMysql;

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [msgtv_uid] column value.
     *
     * @return     string
     */
    public function getMsgtvUid()
    {

        return $this->msgtv_uid;
    }

    /**
     * Get the [msgt_uid] column value.
     *
     * @return     string
     */
    public function getMsgtUid()
    {

        return $this->msgt_uid;
    }

    /**
     * Get the [msgtv_name] column value.
     *
     * @return     string
     */
    public function getMsgtvName()
    {

        return $this->msgtv_name;
    }

    /**
     * Get the [msgtv_default_value] column value.
     *
     * @return     string
     */
    public function getMsgtvDefaultValue()
    {

        return $this->msgtv_default_value;
    }

    /**
     * Set the value of [msgtv_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setMsgtvUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->msgtv_uid !== $v) {
            $this->msgtv_uid = $v;
        }

    }

    /**
     * Set the value of [msgt_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setMsgtUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->msgt_uid !== $v) {
            $this->msgt_uid = $v;
        }

    }

    /**
     * Set the value of [msgtv_name] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setMsgtvName($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->msgtv_name !== $v || $v === '') {
            $this->msgtv_name = $v;
        }

    }

    /**
     * Set the value of [msgtv_default_value] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setMsgtvDefaultValue($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->msgtv_default_value !== $v || $v === '') {
            $this->msgtv_default_value = $v;
        }

    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete()
    {

    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save($con = null)
    {
        if(trim($this->msgtv_uid) === "") {
            $id = $this->objMysql->_insert("workflow.MESSAGE_TYPE_VARIABLE", ["MSGT_UID" => $this->msgt_uid, "MSGTV_NAME" => $this->msgtv_name, "MSGTV_DEFAULT_VALUE" => $this->msgtv_default_value]);

            return $id;
        } else {

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
        foreach($this->arrFieldMapping as $strColumnName => $arrFieldMap){

            if($arrFieldMap['required'] === 'true'){

                if( trim($this->{$arrFieldMap['accessor']}()) === "" ) {
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
}
