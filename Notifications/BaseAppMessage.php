<?php

/**
 * Base class that represents a row from the 'APP_MESSAGE' table.
 *
 * 
 *
 */
abstract class BaseAppMessage implements Persistent
{

    private $objMysql;

    /**
     * The value for the app_msg_uid field.
     * @var        string
     */
    protected $app_msg_uid;
    
    protected $hasRead;
    
    protected $stepName;

    /**
     * The value for the msg_uid field.
     * @var        string
     */
    protected $msg_uid;

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the caseId field.
     * @var        string
     */
    protected $caseId = '';

    /**
     * The value for the del_index field.
     * @var        int
     */
    protected $del_index = 0;

    /**
     * The value for the app_msg_type field.
     * @var        string
     */
    protected $app_msg_type = '';

    /**
     * The value for the app_msg_subject field.
     * @var        string
     */
    protected $app_msg_subject = '';

    /**
     * The value for the app_msg_from field.
     * @var        string
     */
    protected $app_msg_from = '';

    /**
     * The value for the app_msg_to field.
     * @var        string
     */
    protected $app_msg_to;

    /**
     * The value for the app_msg_body field.
     * @var        string
     */
    protected $app_msg_body;

    /**
     * The value for the app_msg_date field.
     * @var        int
     */
    protected $app_msg_date;

    /**
     * The value for the app_msg_cc field.
     * @var        string
     */
    protected $app_msg_cc;

    /**
     * The value for the app_msg_bcc field.
     * @var        string
     */
    protected $app_msg_bcc;

    /**
     * The value for the app_msg_template field.
     * @var        string
     */
    protected $app_msg_template;

    /**
     * The value for the app_msg_status field.
     * @var        string
     */
    protected $app_msg_status;

    /**
     * The value for the app_msg_attach field.
     * @var        string
     */
    protected $app_msg_attach;

    /**
     * The value for the app_msg_send_date field.
     * @var        int
     */
    protected $app_msg_send_date;

    /**
     * The value for the app_msg_show_message field.
     * @var        int
     */
    protected $app_msg_show_message = 1;

    /**
     * The value for the app_msg_error field.
     * @var        string
     */
    protected $app_msg_error;

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

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }
    
    public function getStepName ()
    {
        return $this->stepName;
    }

        /**
     * Get the [app_msg_uid] column value.
     * 
     * @return     string
     */
    public function getAppMsgUid ()
    {

        return $this->app_msg_uid;
    }

    /**
     * Get the [msg_uid] column value.
     * 
     * @return     string
     */
    public function getMsgUid ()
    {

        return $this->msg_uid;
    }

    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid ()
    {

        return $this->app_uid;
    }

    /**
     * Get the [case_uid] column value.
     * 
     * @return     string
     */
    public function getCaseUid ()
    {

        return $this->caseId;
    }

    /**
     * Get the [del_index] column value.
     * 
     * @return     int
     */
    public function getDelIndex ()
    {

        return $this->del_index;
    }

    /**
     * Get the [app_msg_type] column value.
     * 
     * @return     string
     */
    public function getAppMsgType ()
    {

        return $this->app_msg_type;
    }

    /**
     * Get the [app_msg_subject] column value.
     * 
     * @return     string
     */
    public function getAppMsgSubject ()
    {

        return $this->app_msg_subject;
    }

    /**
     * Get the [app_msg_from] column value.
     * 
     * @return     string
     */
    public function getAppMsgFrom ()
    {

        return $this->app_msg_from;
    }

    /**
     * Get the [app_msg_to] column value.
     * 
     * @return     string
     */
    public function getAppMsgTo ()
    {

        return $this->app_msg_to;
    }

    /**
     * Get the [app_msg_body] column value.
     * 
     * @return     string
     */
    public function getAppMsgBody ()
    {

        return $this->app_msg_body;
    }

    /**
     * Get the [optionally formatted] [app_msg_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppMsgDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_msg_date === null || $this->app_msg_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_msg_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_msg_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_msg_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_msg_date;
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
     * Get the [app_msg_cc] column value.
     * 
     * @return     string
     */
    public function getAppMsgCc ()
    {

        return $this->app_msg_cc;
    }

    /**
     * Get the [app_msg_bcc] column value.
     * 
     * @return     string
     */
    public function getAppMsgBcc ()
    {

        return $this->app_msg_bcc;
    }
    
    public function getHasRead ()
    {
        return $this->hasRead;
    }

    /**
     * Get the [app_msg_template] column value.
     * 
     * @return     string
     */
    public function getAppMsgTemplate ()
    {

        return $this->app_msg_template;
    }

    /**
     * Get the [app_msg_status] column value.
     * 
     * @return     string
     */
    public function getAppMsgStatus ()
    {

        return $this->app_msg_status;
    }

    /**
     * Get the [app_msg_attach] column value.
     * 
     * @return     string
     */
    public function getAppMsgAttach ()
    {

        return $this->app_msg_attach;
    }

    /**
     * Get the [optionally formatted] [app_msg_send_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppMsgSendDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_msg_send_date === null || $this->app_msg_send_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_msg_send_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_msg_send_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [app_msg_send_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_msg_send_date;
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
     * Get the [app_msg_show_message] column value.
     * 
     * @return     int
     */
    public function getAppMsgShowMessage ()
    {

        return $this->app_msg_show_message;
    }

    /**
     * Get the [app_msg_error] column value.
     * 
     * @return     string
     */
    public function getAppMsgError ()
    {

        return $this->app_msg_error;
    }
    
    /**
     * 
     * @param type $hasRead
     */
    public function setHasRead ($hasRead)
    {
        $this->hasRead = $hasRead;
    }
    
    public function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }
  
    /**
     * Set the value of [app_msg_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_uid !== $v )
        {
            $this->app_msg_uid = $v;
        }
    }

    /**
     * Set the value of [msg_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMsgUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->msg_uid !== $v )
        {
            $this->msg_uid = $v;
        }
    }

    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_uid !== $v || $v === '' )
        {
            $this->app_uid = $v;
        }
    }

    /**
     * Set the value of [case_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setCaseUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->caseId !== $v || $v === '' )
        {
            $this->caseId = $v;
        }
    }

    /**
     * Set the value of [del_index] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelIndex ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->del_index !== $v || $v === 0 )
        {
            $this->del_index = $v;
        }
    }

    /**
     * Set the value of [app_msg_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_type !== $v || $v === '' )
        {
            $this->app_msg_type = $v;
        }
    }

    /**
     * Set the value of [app_msg_subject] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgSubject ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_subject !== $v || $v === '' )
        {
            $this->app_msg_subject = $v;
        }
    }

    /**
     * Set the value of [app_msg_from] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgFrom ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_from !== $v || $v === '' )
        {
            $this->app_msg_from = $v;
        }
    }

    /**
     * Set the value of [app_msg_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgTo ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_to !== $v )
        {
            $this->app_msg_to = $v;
        }
    }

    /**
     * Set the value of [app_msg_body] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgBody ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_body !== $v )
        {
            $this->app_msg_body = $v;
        }
    }

    /**
     * Set the value of [app_msg_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppMsgDate ($v)
    {

        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [app_msg_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_msg_date !== $ts )
        {
            $this->app_msg_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_msg_cc] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgCc ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_cc !== $v )
        {
            $this->app_msg_cc = $v;
        }
    }

    /**
     * Set the value of [app_msg_bcc] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgBcc ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_bcc !== $v )
        {
            $this->app_msg_bcc = $v;
        }
    }

    /**
     * Set the value of [app_msg_template] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgTemplate ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_template !== $v )
        {
            $this->app_msg_template = $v;
        }
    }

    /**
     * Set the value of [app_msg_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgStatus ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_status !== $v )
        {
            $this->app_msg_status = $v;
        }
    }

    /**
     * Set the value of [app_msg_attach] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgAttach ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_attach !== $v )
        {
            $this->app_msg_attach = $v;
        }
    }

    /**
     * Set the value of [app_msg_send_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppMsgSendDate ($v)
    {

        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse date/time value for [app_msg_send_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_msg_send_date !== $ts )
        {
            $this->app_msg_send_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_msg_show_message] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppMsgShowMessage ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->app_msg_show_message !== $v || $v === 1 )
        {
            $this->app_msg_show_message = $v;
        }
    }

    /**
     * Set the value of [app_msg_error] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppMsgError ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_msg_error !== $v )
        {
            $this->app_msg_error = $v;
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
        
    }

    public function loadObject (array $arrData)
    {
        
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @return     int The number of rows affected by this insert/update
     * @throws     Exception
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->app_msg_uid) === "" )
        {
            $id = $this->objMysql->_insert ("workflow.APP_MESSAGE", [
                "MSG_UID" => $this->msg_uid,
                "APP_UID" => $this->app_uid,
                "CASE_UID" => $this->caseId,
                "DEL_INDEX" => $this->del_index,
                "APP_MSG_TYPE" => $this->app_msg_type,
                "APP_MSG_SUBJECT" => $this->app_msg_subject,
                "APP_MSG_FROM" => $this->app_msg_from,
                "APP_MSG_TO" => $this->app_msg_to,
                "APP_MSG_BODY" => $this->app_msg_body,
                "APP_MSG_DATE" => $this->app_msg_date,
                "APP_MSG_CC" => $this->app_msg_cc,
                "APP_MSG_BCC" => $this->app_msg_bcc,
                "APP_MSG_TEMPLATE" => $this->app_msg_template,
                "APP_MSG_STATUS" => $this->app_msg_status,
                "APP_MSG_ATTACH" => $this->app_msg_attach,
                "APP_MSG_SEND_DATE" => $this->app_msg_send_date,
                "APP_MSG_SHOW_MESSAGE" => $this->app_msg_show_message,
                "APP_MSG_ERROR" => $this->app_msg_error]
            );

            return $id;
        }
        else
        {
            $this->objMysql->_update ("workflow.APP_MESSAGE", [
                "MSG_UID" => $this->msg_uid,
                "APP_UID" => $this->app_uid,
                "CASE_UID" => $this->caseId,
                "DEL_INDEX" => $this->del_index,
                "APP_MSG_TYPE" => $this->app_msg_type,
                "APP_MSG_SUBJECT" => $this->app_msg_subject,
                "APP_MSG_FROM" => $this->app_msg_from,
                "APP_MSG_TO" => $this->app_msg_to,
                "APP_MSG_BODY" => $this->app_msg_body,
                "APP_MSG_DATE" => $this->app_msg_date,
                "APP_MSG_CC" => $this->app_msg_cc,
                "APP_MSG_BCC" => $this->app_msg_bcc,
                "APP_MSG_TEMPLATE" => $this->app_msg_template,
                "APP_MSG_STATUS" => $this->app_msg_status,
                "APP_MSG_ATTACH" => $this->app_msg_attach,
                "APP_MSG_SEND_DATE" => $this->app_msg_send_date,
                "APP_MSG_SHOW_MESSAGE" => $this->app_msg_show_message,
                "APP_MSG_ERROR" => $this->app_msg_error], ["APP_MSG_UID" => $this->app_msg_uid]
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

        return true;
    }

}
