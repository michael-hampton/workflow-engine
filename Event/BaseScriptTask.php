<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseScriptTask
 *
 * @author michael.hampton
 */
abstract class BaseScriptTask implements Persistent
{

    private $objMysql;

    /**
     * The value for the scrtas_uid field.
     * @var        string
     */
    protected $scrtas_uid = '';

    /**
     * The value for the title field.
     * @var        string
     */
    protected $title;

    /**
     * The value for the prj_uid field.
     * @var        string
     */
    protected $prj_uid = '';

    /**
     * The value for the act_uid field.
     * @var        string
     */
    protected $act_uid = '';

    /**
     * The value for the scrtas_obj_type field.
     * @var        string
     */
    protected $scrtas_obj_type = 'TRIGGER';

    /**
     * The value for the scrtas_obj_uid field.
     * @var        string
     */
    protected $scrtas_obj_uid = '';

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
        "stepId" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getActUid", "mutator" => "setActUid"),
        "SCRTAS_OBJ_UID" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getScrtasObjUid", "mutator" => "setScrtasObjUid")
    );

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [scrtas_uid] column value.
     * 
     * @return     string
     */
    public function getScrtasUid ()
    {
        return $this->scrtas_uid;
    }

    /**
     * Get the [prj_uid] column value.
     * 
     * @return     string
     */
    public function getPrjUid ()
    {
        return $this->prj_uid;
    }

    /**
     * Get the [act_uid] column value.
     * 
     * @return     string
     */
    public function getActUid ()
    {
        return $this->act_uid;
    }

    /**
     * Get the [scrtas_obj_type] column value.
     * 
     * @return     string
     */
    public function getScrtasObjType ()
    {
        return $this->scrtas_obj_type;
    }

    /**
     * Get the [scrtas_obj_uid] column value.
     * 
     * @return     string
     */
    public function getScrtasObjUid ()
    {
        return $this->scrtas_obj_uid;
    }

    /**
     * Get the [title] column value.
     * 
     * @return     string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * Set the value of [scrtas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setScrtasUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->scrtas_uid !== $v || $v === '' )
        {
            $this->scrtas_uid = $v;
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
        if ( $this->prj_uid !== $v || $v === '' )
        {
            $this->prj_uid = $v;
        }
    }

    /**
     * Set the value of [act_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setActUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->act_uid !== $v || $v === '' )
        {
            $this->act_uid = $v;
        }
    }

    /**
     * Set the value of title column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTitle ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->title !== $v || $v === '' )
        {
            $this->title = $v;
        }
    }

    /**
     * Set the value of [scrtas_obj_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setScrtasObjType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->scrtas_obj_type !== $v || $v === 'TRIGGER' )
        {
            $this->scrtas_obj_type = $v;
        }
    }

    /**
     * Set the value of [scrtas_obj_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setScrtasObjUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->scrtas_obj_uid !== $v || $v === '' )
        {
            $this->scrtas_obj_uid = $v;
        }
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

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->scrtas_uid) === "" )
        {
            $id = $this->objMysql->_insert ("workflow.SCRIPT_TASK", ["PRJ_UID" => $this->prj_uid, "ACT_UID" => $this->act_uid, "SCRTAS_OBJ_TYPE" => $this->scrtas_obj_type, "SCRTAS_OBJ_UID" => $this->scrtas_obj_uid]);

            return $id;
        }
        else
        {
            $this->objMysql->_update ("workflow.SCRIPT_TASK", ["PRJ_UID" => $this->prj_uid, "ACT_UID" => $this->act_uid, "SCRTAS_OBJ_TYPE" => $this->scrtas_obj_type, "SCRTAS_OBJ_UID" => $this->scrtas_obj_uid], ["SCRTAS_UID" => $this->scrtas_uid]);
        }
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
            return false;
        }

        return true;
    }

    public function delete ()
    {
        try {
            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            if ( trim ($this->scrtas_uid) === "" )
            {
                return false;
            }

            $this->objMysql->_delete ("workflow.SCRIPT_TASK", ["SCRTAS_UID" => $this->scrtas_uid]);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function retrieveByPK ($pk)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.SCRIPT_TASK", [], ["SCRTAS_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objScript = new ScriptTask();
        $objScript->setActUid ($result[0]["ACT_UID"]);
        $objScript->setPrjUid ($result[0]["PRJ_UID"]);
        $objScript->setScrtasObjType ($result[0]["SCRTAS_OBJ_TYPE"]);
        $objScript->setScrtasObjUid ($result[0]["SCRTAS_OBJ_UID"]);
        $objScript->setScrtasUid ($result[0]["SCRTAS_UID"]);

        return $objScript;
    }

}
