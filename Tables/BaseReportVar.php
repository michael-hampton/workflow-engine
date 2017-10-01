<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseReportVar
 *
 * @author michael.hampton
 */
abstract class BaseReportVar implements Persistent
{

    /**
     * The value for the rep_var_uid field.
     * @var        string
     */
    protected $rep_var_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the rep_tab_uid field.
     * @var        string
     */
    protected $rep_tab_uid = '';

    /**
     * The value for the rep_var_name field.
     * @var        string
     */
    protected $rep_var_name = '';

    /**
     * The value for the rep_var_type field.
     * @var        string
     */
    protected $rep_var_type = '';

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
        'REP_TAB_UID' => array('accessor' => 'getRepTabUid', 'mutator' => 'setRepTabUid', 'type' => 'int', 'required' => 'true'),
        'PRO_UID' => array('accessor' => 'getProUid', 'mutator' => 'setProUid', 'type' => 'string', 'required' => 'true'),
        'REP_VAR_NAME' => array('accessor' => 'getRepVarName', 'mutator' => 'setRepVarName', 'type' => 'string', 'required' => 'true'),
        'REP_VAR_TYPE' => array('accessor' => 'getRepVarType', 'mutator' => 'setRepVarType', 'type' => 'string', 'required' => 'true'),
    );
    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [rep_var_uid] column value.
     * 
     * @return     string
     */
    public function getRepVarUid ()
    {

        return $this->rep_var_uid;
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
     * Get the [rep_tab_uid] column value.
     * 
     * @return     string
     */
    public function getRepTabUid ()
    {

        return $this->rep_tab_uid;
    }

    /**
     * Get the [rep_var_name] column value.
     * 
     * @return     string
     */
    public function getRepVarName ()
    {

        return $this->rep_var_name;
    }

    /**
     * Get the [rep_var_type] column value.
     * 
     * @return     string
     */
    public function getRepVarType ()
    {

        return $this->rep_var_type;
    }

    /**
     * Set the value of [rep_var_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepVarUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->rep_var_uid !== $v || $v === '' )
        {
            $this->rep_var_uid = $v;
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

    /**
     * Set the value of [rep_tab_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepTabUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->rep_tab_uid !== $v || $v === '' )
        {
            $this->rep_tab_uid = $v;
        }
    }

    /**
     * Set the value of [rep_var_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepVarName ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->rep_var_name !== $v || $v === '' )
        {
            $this->rep_var_name = $v;
        }
    }

    /**
     * Set the value of [rep_var_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setRepVarType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->rep_var_type !== $v || $v === '' )
        {
            $this->rep_var_type = $v;
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

    public function loadObject (array $arrData)
    {
        if ( !empty ($arrData) && is_array ($arrData) )
        {
            foreach ($this->arrFieldMapping as $strFieldKey => $arrFields) {
                if ( isset ($arrData[$strFieldKey]) )
                {
                    $strMutatorMethod = $arrFields['mutator'];

                    if ( is_callable (array($this, $strMutatorMethod)) )
                    {
                        call_user_func (array($this, $strMutatorMethod), $arrData[$strFieldKey]);
                    }
                }
            }

            return true;
        }

        return false;
    }

    public function validate ()
    {
        foreach ($this->arrFieldMapping as $strColumnName => $arrFieldMap) {

            if ( $arrFieldMap['required'] === 'true' )
            {

                if ( trim ($this->{$arrFieldMap['accessor']} ()) === "" )
                {
                    $this->validationFailures[] = $strColumnName . " Is missing";
                }
            }
        }

        return count ($this->validationFailures) > 0 ? false : true;
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->rep_var_uid) === "" )
        {

            $id = $this->objMysql->_insert ("workflow.REPORT_VAR", [
                "PRO_UID" => $this->pro_uid,
                "REP_TAB_UID" => $this->rep_tab_uid,
                "REP_VAR_NAME" => $this->rep_var_name,
                "REP_VAR_TYPE" => $this->rep_var_type
                    ]
            );

            return $id;
        }
        else
        {
            $result = $this->objMysql->_update ("workflow.REPORT_VAR", [
                `PRO_UID` => $this->pro_uid,
                `REP_TAB_UID` => $this->rep_tab_uid,
                `REP_VAR_NAME` => $this->rep_var_name,
                `REP_VAR_TYPE` => $this->rep_var_type
                    ], ["REP_VAR_UID" => $this->rep_var_uid]
            );

            return $result;
        }
    }

}
