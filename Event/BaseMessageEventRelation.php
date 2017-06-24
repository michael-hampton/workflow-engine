<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseMessageEventRelation
 *
 * @author michael.hampton
 */
abstract class BaseMessageEventRelation implements Persistent
{

    /**
     * The value for the msger_uid field.
     * @var        string
     */
    private $MSGER_UID;

    /**
     * The value for the prj_uid field.
     * @var        string
     */
    protected $PRJ_UID;

    /**
     * The value for the evn_uid_throw field.
     * @var        string
     */
    private $EVN_UID_THROW;

    /**
     * The value for the evn_uid_catch field.
     * @var        string
     */
    private $EVN_UID_CATCH;

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
        "EVN_UID_THROW" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getEVN_UID_THROW", "mutator" => "setEVN_UID_THROW"),
        "EVN_UID_CATCH" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getEVN_UID_CATCH", "mutator" => "setEVN_UID_CATCH"),
    );
    private $objMysql;

    /**
     * Get the [msger_uid] column value.
     * 
     * @return     string
     */
    public function getMSGER_UID ()
    {
        return $this->MSGER_UID;
    }

    /**
     * Get the [evn_uid_throw] column value.
     * 
     * @return     string
     */
    public function getEVN_UID_THROW ()
    {
        return $this->EVN_UID_THROW;
    }

    /**
     * Get the [evn_uid_catch] column value.
     * 
     * @return     string
     */
    public function getEVN_UID_CATCH ()
    {
        return $this->EVN_UID_CATCH;
    }

    public function setMSGER_UID ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->MSGER_UID !== $v )
        {
            $this->MSGER_UID = $v;
        }
    }

    /**
     * Set the value of [prj_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setPrjUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->PRJ_UID !== $v )
        {
            $this->PRJ_UID = $v;
        }
    }

// setPrjUid()

    /**
     * Set the value of [evn_uid_throw] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEVN_UID_THROW ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->EVN_UID_THROW !== $v )
        {
            $this->EVN_UID_THROW = $v;
        }
    }

    /**
     * Set the value of [evn_uid_catch] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEVN_UID_CATCH ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->EVN_UID_CATCH !== $v )
        {
            $this->EVN_UID_CATCH = $v;
        }
    }

    /**
     * Get the [prj_uid] column value.
     * 
     * @return     string
     */
    public function getPRJ_UID ()
    {
        return $this->PRJ_UID;
    }

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

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->EVN_UID_CATCH) === "" )
        {
            $this->validationFailures[] = "EVENT CATCH ID IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->EVN_UID_THROW) === "" )
        {
            $this->validationFailures[] = "EVENT THROW ID IS MISSING";
            $errorCount++;
        }

        if ( trim ($this->PRJ_UID) === "" )
        {
            $this->validationFailures[] = "WORKFLOW ID IS MISSING";
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }
        
        $id = $this->objMysql->_insert("workflow.message_event_relation", ["PRJ_UID" => $this->PRJ_UID, "EVN_UID_THROW" => $this->EVN_UID_THROW, "EVN_UID_CATCH" => $this->EVN_UID_CATCH]);
        return $id;
    }

}
