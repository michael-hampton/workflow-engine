<?php

/**
 * Base class that represents a row from the 'CASE_CONSOLIDATED' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseCaseConsolidatedCore implements Persistent
{

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';

    /**
     * The value for the dyn_uid field.
     * @var        string
     */
    protected $dyn_uid = '';

    /**
     * The value for the rep_tab_uid field.
     * @var        string
     */
    protected $rep_tab_uid = '';

    /**
     * The value for the con_status field.
     * @var        string
     */
    protected $con_status = 'ACTIVE';

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
    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [tas_uid] column value.
     * 
     * @return     string
     */
    public function getTasUid ()
    {

        return $this->tas_uid;
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
     * Get the [rep_tab_uid] column value.
     * 
     * @return     string
     */
    public function getRepTabUid ()
    {

        return $this->rep_tab_uid;
    }

    /**
     * Get the [con_status] column value.
     * 
     * @return     string
     */
    public function getConStatus ()
    {

        return $this->con_status;
    }

    /**
     * Set the value of [tas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_uid !== $v || $v === '' )
        {
            $this->tas_uid = $v;
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
     * Set the value of [con_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setConStatus ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->con_status !== $v || $v === 'ACTIVE' )
        {
            $this->con_status = $v;
        }
    }

    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_insert ("workflow.CASE_CONSOLIDATED", [
            "TAS_UID" => $this->tas_uid,
            "DYN_UID" => $this->dyn_uid,
            "REP_TAB_UID" => $this->rep_tab_uid,
            "CON_STATUS" => $this->con_status
                ]
        );

        if ( !$result )
        {
            return false;
        }

        return true;
    }

    public function validate ()
    {
        return true;
    }

    public function loadObject (array $arrData)
    {
        
    }

}
