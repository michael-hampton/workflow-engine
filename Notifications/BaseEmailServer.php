<?php

/**
 * Base class that represents a row from the 'EMAIL_SERVER' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseEmailServer implements Persistent
{

    private $objMysql;

    /**
     * The value for the mess_uid field.
     * @var        string
     */
    protected $mess_uid = '';

    /**
     * The value for the mess_engine field.
     * @var        string
     */
    protected $mess_engine = '';

    /**
     * The value for the mess_server field.
     * @var        string
     */
    protected $mess_server = '';

    /**
     * The value for the mess_port field.
     * @var        int
     */
    protected $mess_port = 0;

    /**
     * The value for the mess_rauth field.
     * @var        int
     */
    protected $mess_rauth = 0;

    /**
     * The value for the mess_account field.
     * @var        string
     */
    protected $mess_account = '';

    /**
     * The value for the mess_password field.
     * @var        string
     */
    protected $mess_password = '';

    /**
     * The value for the mess_from_mail field.
     * @var        string
     */
    protected $mess_from_mail = '';

    /**
     * The value for the mess_from_name field.
     * @var        string
     */
    protected $mess_from_name = '';

    /**
     * The value for the smtpsecure field.
     * @var        string
     */
    protected $smtpsecure = 'No';

    /**
     * The value for the mess_try_send_inmediatly field.
     * @var        int
     */
    protected $mess_try_send_inmediatly = 0;

    /**
     * The value for the mail_to field.
     * @var        string
     */
    protected $mail_to = '';

    /**
     * The value for the mess_default field.
     * @var        int
     */
    protected $mess_default = 0;

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
    private $arrFieldMapping = array(
        "MESS_ENGINE" => array("accessor" => "getMessEngine", "mutator" => "setMessEngine", "required" => "true"),
        "MESS_SERVER" => array("accessor" => "getMessServer", "mutator" => "setMessServer", "required" => "true"),
        "MESS_PORT" => array("accessor" => "getMessPort", "mutator" => "setMessPort", "required" => "false"),
        "MESS_ACCOUNT" => array("accessor" => "getMessAccount", "mutator" => "setMessAccount", "required" => "true"),
        "SMTPSECURE" => array("accessor" => "getSmtpsecure", "mutator" => "setSmtpsecure", "required" => "true"),
        "MESS_RAUTH" => array("accessor" => "getMessRauth", "mutator" => "setMessRauth", "required" => "true"),
        "MESS_PASSWORD" => array("accessor" => "getMessPassword", "mutator" => "setMessPassword", "required" => "true"),
        "MESS_FROM_MAIL" => array("accessor" => "getMessFromMail", "mutator" => "setMessFromMail", "required" => "true"),
        "MESS_FROM_NAME" => array("accessor" => "getMessFromName", "mutator" => "setMessFromName", "required" => "true"),
        "MESS_DEFAULT" => array("accessor" => "GetMessDefault", "mutator" => "setMessDefault", "required" => "true"),
    );

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [mess_uid] column value.
     * 
     * @return     string
     */
    public function getMessUid ()
    {
        return $this->mess_uid;
    }

    /**
     * Get the [mess_engine] column value.
     * 
     * @return     string
     */
    public function getMessEngine ()
    {
        return $this->mess_engine;
    }

    /**
     * Get the [mess_server] column value.
     * 
     * @return     string
     */
    public function getMessServer ()
    {
        return $this->mess_server;
    }

    /**
     * Get the [mess_port] column value.
     * 
     * @return     int
     */
    public function getMessPort ()
    {
        return $this->mess_port;
    }

    /**
     * Get the [mess_rauth] column value.
     * 
     * @return     int
     */
    public function getMessRauth ()
    {
        return $this->mess_rauth;
    }

    /**
     * Get the [mess_account] column value.
     * 
     * @return     string
     */
    public function getMessAccount ()
    {
        return $this->mess_account;
    }

    /**
     * Get the [mess_password] column value.
     * 
     * @return     string
     */
    public function getMessPassword ()
    {
        return $this->mess_password;
    }

    /**
     * Get the [mess_from_mail] column value.
     * 
     * @return     string
     */
    public function getMessFromMail ()
    {
        return $this->mess_from_mail;
    }

    /**
     * Get the [mess_from_name] column value.
     * 
     * @return     string
     */
    public function getMessFromName ()
    {
        return $this->mess_from_name;
    }

    /**
     * Get the [smtpsecure] column value.
     * 
     * @return     string
     */
    public function getSmtpsecure ()
    {
        return $this->smtpsecure;
    }

    /**
     * Get the [mess_try_send_inmediatly] column value.
     * 
     * @return     int
     */
    public function getMessTrySendInmediatly ()
    {
        return $this->mess_try_send_inmediatly;
    }

    /**
     * Get the [mail_to] column value.
     * 
     * @return     string
     */
    public function getMailTo ()
    {
        return $this->mail_to;
    }

    /**
     * Get the [mess_default] column value.
     * 
     * @return     int
     */
    public function getMessDefault ()
    {
        return $this->mess_default;
    }

    /**
     * Set the value of [mess_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_uid !== $v || $v === '' )
        {
            $this->mess_uid = $v;
        }
    }

    /**
     * Set the value of [mess_engine] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessEngine ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_engine !== $v || $v === '' )
        {
            $this->mess_engine = $v;
        }
    }

    /**
     * Set the value of [mess_server] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessServer ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_server !== $v || $v === '' )
        {
            $this->mess_server = $v;
        }
    }

    /**
     * Set the value of [mess_port] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMessPort ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->mess_port !== $v || $v === 0 )
        {
            $this->mess_port = $v;
        }
    }

    /**
     * Set the value of [mess_rauth] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMessRauth ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->mess_rauth !== $v || $v === 0 )
        {
            $this->mess_rauth = $v;
        }
    }

    /**
     * Set the value of [mess_account] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessAccount ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_account !== $v || $v === '' )
        {
            $this->mess_account = $v;
        }
    }

    /**
     * Set the value of [mess_password] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessPassword ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_password !== $v || $v === '' )
        {
            $this->mess_password = $v;
        }
    }

    /**
     * Set the value of [mess_from_mail] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessFromMail ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_from_mail !== $v || $v === '' )
        {
            $this->mess_from_mail = $v;
        }
    }

    /**
     * Set the value of [mess_from_name] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMessFromName ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mess_from_name !== $v || $v === '' )
        {
            $this->mess_from_name = $v;
        }
    }

    /**
     * Set the value of [smtpsecure] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setSmtpsecure ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->smtpsecure !== $v || $v === 'No' )
        {
            $this->smtpsecure = $v;
        }
    }

    /**
     * Set the value of [mess_try_send_inmediatly] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMessTrySendInmediatly ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->mess_try_send_inmediatly !== $v || $v === 0 )
        {
            $this->mess_try_send_inmediatly = $v;
        }
    }

    /**
     * Set the value of [mail_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMailTo ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->mail_to !== $v || $v === '' )
        {
            $this->mail_to = $v;
        }
    }

    /**
     * Set the value of [mess_default] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setMessDefault ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->mess_default !== $v || $v === 0 )
        {
            $this->mess_default = $v;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     *
     */
    public function save ()
    {
        if ( $this->mess_uid === "" )
        {
            $id = $this->objMysql->_insert ("task_manager.email_server", [
                "MESS_ENGINE" => $this->mess_engine,
                "MESS_SERVER" => $this->mess_server,
                "MESS_PORT" => $this->mess_port,
                "MESS_RAUTH" => $this->mess_rauth,
                "MESS_ACCOUNT" => $this->mess_account,
                "MESS_PASSWORD" => $this->mess_password,
                "MESS_FROM_MAIL" => $this->mess_from_mail,
                "MESS_FROM_NAME" => $this->mess_from_name,
                "SMTPSECURE" => $this->smtpsecure,
                "MESS_DEFAULT" => $this->mess_default
                    ]
            );

            return $id;
        }
        else
        {
            $this->objMysql->_update ("task_manager.email_server", [
                "MESS_ENGINE" => $this->mess_engine,
                "MESS_SERVER" => $this->mess_server,
                "MESS_PORT" => $this->mess_port,
                "MESS_RAUTH" => $this->mess_rauth,
                "MESS_ACCOUNT" => $this->mess_account,
                "MESS_PASSWORD" => $this->mess_password,
                "MESS_FROM_MAIL" => $this->mess_from_mail,
                "MESS_FROM_NAME" => $this->mess_from_name,
                "SMTPSECURE" => $this->smtpsecure,
                "MESS_DEFAULT" => $this->mess_default
                    ], ["MESS_UID" => $this->mess_uid]
            );
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

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === 'true' )
            {
                $accessor = $this->arrFieldMapping[$fieldName]['accessor'];

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

    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrFieldMapping[$formField]) )
            {
                $mutator = $this->arrFieldMapping[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrFieldMapping[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }

}
