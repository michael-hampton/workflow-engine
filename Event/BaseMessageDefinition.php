<?php

/**
 * Base class that represents a row from the 'MESSAGE_EVENT_DEFINITION' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseMessageDefinition
{

    protected $objMysql;

    /**
     * The value for the msged_uid field.
     * @var        string
     */
    protected $msged_uid;

    /**
     * The value for the prj_uid field.
     * @var        string
     */
    protected $prj_uid;

    /**
     * The value for the evn_uid field.
     * @var        string
     */
    protected $evn_uid;

    /**
     * The value for the msgt_uid field.
     * @var        string
     */
    protected $msgt_uid = '';

    /**
     * The value for the msged_usr_uid field.
     * @var        string
     */
    protected $msged_usr_uid = '';

    /**
     * The value for the msged_variables field.
     * @var        string
     */
    protected $msged_variables;

    /**
     * The value for the msged_correlation field.
     * @var        string
     */
    protected $msged_correlation = '';

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
     * Get the [msged_uid] column value.
     * 
     * @return     string
     */
    public function getMsgedUid ()
    {
        return $this->msged_uid;
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
     * Get the [evn_uid] column value.
     * 
     * @return     string
     */
    public function getEvnUid ()
    {
        return $this->evn_uid;
    }

    /**
     * Get the [msgt_uid] column value.
     * 
     * @return     string
     */
    public function getMsgtUid ()
    {
        return $this->msgt_uid;
    }

    /**
     * Get the [msged_usr_uid] column value.
     * 
     * @return     string
     */
    public function getMsgedUsrUid ()
    {
        return $this->msged_usr_uid;
    }

    /**
     * Get the [msged_variables] column value.
     * 
     * @return     string
     */
    public function getMsgedVariables ()
    {
        return $this->msged_variables;
    }

    /**
     * Get the [msged_correlation] column value.
     * 
     * @return     string
     */
    public function getMsgedCorrelation ()
    {
        return $this->msged_correlation;
    }

    /**
     * Set the value of [msged_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgedUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msged_uid !== $v )
        {
            $this->msged_uid = $v;
        }
    }

// setMsgedUid()
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
        if ( $this->prj_uid !== $v )
        {
            $this->prj_uid = $v;
        }
    }

// setPrjUid()
    /**
     * Set the value of [evn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_uid !== $v )
        {
            $this->evn_uid = $v;
        }
    }

// setEvnUid()
    /**
     * Set the value of [msgt_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgtUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msgt_uid !== $v || $v === '' )
        {
            $this->msgt_uid = $v;
        }
    }

// setMsgtUid()
    /**
     * Set the value of [msged_usr_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgedUsrUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msged_usr_uid !== $v || $v === '' )
        {
            $this->msged_usr_uid = $v;
        }
    }

// setMsgedUsrUid()
    /**
     * Set the value of [msged_variables] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgedVariables ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        
        if(  is_array ($v)) {
            $v = serialize ($v);
        }
        
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msged_variables !== $v )
        {
            $this->msged_variables = $v;
        }
    }

// setMsgedVariables()
    /**
     * Set the value of [msged_correlation] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgedCorrelation ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->msged_correlation !== $v || $v === '' )
        {
            $this->msged_correlation = $v;
        }
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
        
        $id = $this->objMysql->_insert("workflow.message_definition", ["MSGT_UID" => $this->msgt_uid, "workflow_id" => $this->prj_uid, "MSGT_VARIABLES" => $this->msged_variables]);
        
        return $id;
    }

    public function validate ()
    {
        return true;
    }

}
