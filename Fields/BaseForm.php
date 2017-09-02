<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseForm
 *
 * @author michael.hampton
 */
abstract class BaseForm implements Persistent
{

    private $objMysql;

    /**
     * The value for the dyn_uid field.
     * @var        string
     */
    protected $dyn_uid = '';

    /**
     * The value for the dyn_title field.
     * @var        string
     */
    protected $dyn_title;

    /**
     * The value for the dyn_description field.
     * @var        string
     */
    protected $dyn_description;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '0';

    /**
     * The value for the dyn_type field.
     * @var        string
     */
    protected $dyn_type = 'xmlform';

    /**
     * The value for the dyn_filename field.
     * @var        string
     */
    protected $dyn_filename = '';

    /**
     * The value for the dyn_content field.
     * @var        string
     */
    protected $dyn_content;

    /**
     * The value for the dyn_label field.
     * @var        string
     */
    protected $dyn_label;

    /**
     * The value for the dyn_version field.
     * @var        int
     */
    protected $dyn_version;

    /**
     * The value for the dyn_update_date field.
     * @var        int
     */
    protected $dyn_update_date;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     *
     * @var type 
     */
    protected $columns;
    private $arrayFieldDefinition = array(
        "DYN_TITLE" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDynTitle", "mutator" => "setDynTitle"),
        "DYN_DESCRIPTION" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDynDescription", "mutator" => "setDynDescription"),
        "DYN_VERSION" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getDynVersion", "mutator" => "setDynVersion"),
        "columns" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getColumns", "mutator" => "setColumns"),
        "DYN_LABEL" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getDynLabel", "mutator" => "setDynLabel"),
        "DYN_TYPE" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDynType", "mutator" => "setDynType"),
        "PRO_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getProUid", "mutator" => "setProUid"),
    );

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [dyn_uid] column value.
     * 
     * @return     string
     */
    public function getDynUid ()
    {

        return $this->dyn_uid;
    }

    /**
     * Get the [dyn_uid] column value.
     * 
     * @return     string
     */
    public function getColumns ()
    {

        return $this->columns;
    }

    /**
     * Get the [dyn_title] column value.
     * 
     * @return     string
     */
    public function getDynTitle ()
    {

        return $this->dyn_title;
    }

    /**
     * Get the [dyn_description] column value.
     * 
     * @return     string
     */
    public function getDynDescription ()
    {

        return $this->dyn_description;
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
     * Get the [dyn_type] column value.
     * 
     * @return     string
     */
    public function getDynType ()
    {

        return $this->dyn_type;
    }

    /**
     * Get the [dyn_filename] column value.
     * 
     * @return     string
     */
    public function getDynFilename ()
    {

        return $this->dyn_filename;
    }

    /**
     * Get the [dyn_content] column value.
     * 
     * @return     string
     */
    public function getDynContent ()
    {

        return $this->dyn_content;
    }

    /**
     * Get the [dyn_label] column value.
     * 
     * @return     string
     */
    public function getDynLabel ()
    {

        return $this->dyn_label;
    }

    /**
     * Get the [dyn_version] column value.
     * 
     * @return     int
     */
    public function getDynVersion ()
    {

        return $this->dyn_version;
    }

    /**
     * Get the [optionally formatted] [dyn_update_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDynUpdateDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->dyn_update_date === null || $this->dyn_update_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->dyn_update_date) )
        {

            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->dyn_update_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [dyn_update_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->dyn_update_date;
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
     * Set the value of [dyn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_uid !== $v || $v === '' )
        {
            $this->dyn_uid = $v;
        }
    }

    /**
     * Set the value of [dyn_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynTitle ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_title !== $v )
        {
            $this->dyn_title = $v;
        }
    }

    /**
     * Set the value of [dyn_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynDescription ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_description !== $v )
        {
            $this->dyn_description = $v;
        }
    }

    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_uid !== $v || $v === '0' )
        {
            $this->pro_uid = $v;
        }
    }

    /**
     * Set the value of [dyn_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynType ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_type !== $v || $v === 'xmlform' )
        {
            $this->dyn_type = $v;
        }
    }

    /**
     * Set the value of [dyn_filename] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynFilename ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_filename !== $v || $v === '' )
        {
            $this->dyn_filename = $v;
        }
    }

    /**
     * Set the value of [dyn_content] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynContent ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_content !== $v )
        {
            $this->dyn_content = $v;
        }
    }

    /**
     * Set the value of [dyn_label] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setDynLabel ($v)
    {
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->dyn_label !== $v )
        {
            $this->dyn_label = $v;
        }
    }

    /**
     * Set the value of [dyn_version] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setColumns ($v)
    {
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->columns !== $v )
        {
            $this->columns = $v;
        }
    }

    /**
     * Set the value of [dyn_version] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDynVersion ($v)
    {
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->dyn_version !== $v )
        {
            $this->dyn_version = $v;
        }
    }

    /**
     * Set the value of [dyn_update_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDynUpdateDate ($v)
    {

        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);

            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [dyn_update_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->dyn_update_date !== $ts )
        {
            $this->dyn_update_date = date ("Y-m-d H:i:s", $ts);
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
    public function delete ()
    {

        try {
            if ( trim ($this->dyn_uid) === "" )
            {
                return false;
            }

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            $this->objMysql->_delete ("workflow.form", ["DYN_UID" => $this->dyn_uid]);
        } catch (Exception $e) {
            throw $e;
        }
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
    public function save ()
    {

        try {

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            if ( trim ($this->dyn_uid) !== "" )
            {
                $id = $this->objMysql->_update ("workflow.form", [
                    "DYN_TITLE" => $this->dyn_title,
                    "DYN_DESCRIPTION" => $this->dyn_description,
                    "PRO_UID" => $this->pro_uid,
                    "DYN_TYPE" => $this->dyn_type,
                    "DYN_FILENAME" => $this->dyn_filename,
                    "DYN_CONTENT" => $this->dyn_content,
                    "DYN_LABEL" => $this->dyn_label,
                    "DYN_VERSION" => $this->dyn_version,
                    "DYN_UPDATE_DATE" => $this->dyn_update_date,
                    "columns" => $this->columns
                        ], ["DYN_UID" => $this->dyn_uid]
                );
            }
            else
            {
                $id = $this->objMysql->_insert ("workflow.form", [
                    "DYN_TITLE" => $this->dyn_title,
                    "DYN_DESCRIPTION" => $this->dyn_description,
                    "PRO_UID" => $this->pro_uid,
                    "DYN_TYPE" => $this->dyn_type,
                    "DYN_FILENAME" => $this->dyn_filename,
                    "DYN_CONTENT" => $this->dyn_content,
                    "DYN_LABEL" => $this->dyn_label,
                    "DYN_VERSION" => $this->dyn_version,
                    "DYN_UPDATE_DATE" => $this->dyn_update_date,
                    "columns" => $this->columns
                        ]
                );

                return $id;
            }
        } catch (Exception $e) {
            throw $e;
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

        foreach ($this->arrayFieldDefinition as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $this->arrayFieldDefinition[$fieldName]['accessor'];

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

}
