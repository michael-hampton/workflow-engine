<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseAbeRequest
 *
 * @author michael.hampton
 */
abstract class BaseAbeRequest implements Persistent
{

    /**
     * The value for the abe_req_uid field.
     * @var        string
     */
    protected $abe_req_uid = '';

    /**
     * The value for the abe_uid field.
     * @var        string
     */
    protected $abe_uid = '';

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the del_index field.
     * @var        int
     */
    protected $del_index = 0;

    /**
     * The value for the abe_req_sent_to field.
     * @var        string
     */
    protected $abe_req_sent_to = '';

    /**
     * The value for the abe_req_subject field.
     * @var        string
     */
    protected $abe_req_subject = '';

    /**
     * The value for the abe_req_body field.
     * @var        string
     */
    protected $abe_req_body;

    /**
     * The value for the abe_req_date field.
     * @var        int
     */
    protected $abe_req_date;

    /**
     * The value for the abe_req_status field.
     * @var        string
     */
    protected $abe_req_status = '';

    /**
     * The value for the abe_req_answered field.
     * @var        int
     */
    protected $abe_req_answered = 0;

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
     *
     * @var type 
     */
    private $objMysql;

    /**
     *
     * @var type 
     */
    private $arrFieldMapping = array(
        "APP_UID" => array("accessor" => "getAppUid", "mutator" => "setAppUid", "required" => "true"),
        "DEL_INDEX" => array("accessor" => "getDelIndex", "mutator" => "setDelIndex", "required" => "true"),
        "ABE_REQ_SENT_TO" => array("accessor" => "getAbeReqSentTo", "mutator" => "setAbeReqSentTo", "required" => "true"),
        "ABE_REQ_SUBJECT" => array("accessor" => "getAbeReqSubject", "mutator" => "setAbeReqSubject", "required" => "true"),
        "ABE_REQ_BODY" => array("accessor" => "getAbeReqBody", "mutator" => "setAbeReqBody", "required" => "true"),
        "ABE_REQ_ANSWERED" => array("accessor" => "getAbeReqAnswered", "mutator" => "setAbeReqAnswered", "required" => "true"),
        "ABE_REQ_STATUS" => array("accessor" => "getAbeReqStatus", "mutator" => "setAbeReqStatus", "required" => "true"),
        "ABE_REQ_DATE" => array("accessor" => "getAbeReqDate", "mutator" => "setAbeReqDate", "required" => "true"),
    );

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrNotification
     * @return boolean
     */
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

    /**
     * Get the [abe_req_uid] column value.
     * 
     * @return     string
     */
    public function getAbeReqUid ()
    {
        return $this->abe_req_uid;
    }

    /**
     * Get the [abe_uid] column value.
     * 
     * @return     string
     */
    public function getAbeUid ()
    {
        return $this->abe_uid;
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
     * Get the [del_index] column value.
     * 
     * @return     int
     */
    public function getDelIndex ()
    {
        return $this->del_index;
    }

    /**
     * Get the [abe_req_sent_to] column value.
     * 
     * @return     string
     */
    public function getAbeReqSentTo ()
    {
        return $this->abe_req_sent_to;
    }

    /**
     * Get the [abe_req_subject] column value.
     * 
     * @return     string
     */
    public function getAbeReqSubject ()
    {
        return $this->abe_req_subject;
    }

    /**
     * Get the [abe_req_body] column value.
     * 
     * @return     string
     */
    public function getAbeReqBody ()
    {
        return $this->abe_req_body;
    }

    /**
     * Get the [optionally formatted] [abe_req_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAbeReqDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->abe_req_date === null || $this->abe_req_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->abe_req_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->abe_req_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [abe_req_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->abe_req_date;
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
     * Get the [abe_req_status] column value.
     * 
     * @return     string
     */
    public function getAbeReqStatus ()
    {
        return $this->abe_req_status;
    }

    /**
     * Get the [abe_req_answered] column value.
     * 
     * @return     int
     */
    public function getAbeReqAnswered ()
    {
        return $this->abe_req_answered;
    }

    /**
     * Set the value of [abe_req_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_uid !== $v || $v === '' )
        {
            $this->abe_req_uid = $v;
        }
    }

    /**
     * Set the value of [abe_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_uid !== $v || $v === '' )
        {
            $this->abe_uid = $v;
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
     * Set the value of [abe_req_sent_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqSentTo ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_sent_to !== $v || $v === '' )
        {
            $this->abe_req_sent_to = $v;
        }
    }

    /**
     * Set the value of [abe_req_subject] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqSubject ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_subject !== $v || $v === '' )
        {
            $this->abe_req_subject = $v;
        }
    }

    /**
     * Set the value of [abe_req_body] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqBody ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_body !== $v )
        {
            $this->abe_req_body = $v;
        }
    }

    /**
     * Set the value of [abe_req_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAbeReqDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [abe_req_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->abe_req_date !== $ts )
        {
            $this->abe_req_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [abe_req_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAbeReqStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->abe_req_status !== $v || $v === '' )
        {
            $this->abe_req_status = $v;
        }
    }

    /**
     * Set the value of [abe_req_answered] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAbeReqAnswered ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->abe_req_answered !== $v || $v === 0 )
        {
            $this->abe_req_answered = $v;
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     * @throws     Exception
     */
    public function delete ()
    {

        try {
            
        } catch (Exception $e) {
            throw $e;
        }
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

        try {
            if ( trim ($this->abe_req_uid) === "" )
            {
                $id = $this->objMysql->_insert ("workflow.ABE_REQUEST", ["ABE_UID" => $this->abe_uid,
                    "APP_UID" => $this->app_uid,
                    "DEL_INDEX" => $this->del_index,
                    "ABE_REQ_SENT_TO" => $this->abe_req_sent_to,
                    "ABE_REQ_SUBJECT" => $this->abe_req_subject,
                    "ABE_REQ_BODY" => $this->abe_req_body,
                    "ABE_REQ_DATE" => $this->abe_req_date,
                    "ABE_REQ_STATUS" => $this->abe_req_status,
                    "ABE_REQ_ANSWERED" => $this->abe_req_answered
                        ]
                );

                return $id;
            }
            else
            {
                $this->objMysql->_update ("workflow.ABE_REQUEST", ["ABE_UID" => $this->abe_uid,
                    "APP_UID" => $this->app_uid,
                    "DEL_INDEX" => $this->del_index,
                    "ABE_REQ_SENT_TO" => $this->abe_req_sent_to,
                    "ABE_REQ_SUBJECT" => $this->abe_req_subject,
                    "ABE_REQ_BODY" => $this->abe_req_body,
                    "ABE_REQ_DATE" => $this->abe_req_date,
                    "ABE_REQ_STATUS" => $this->abe_req_status,
                    "ABE_REQ_ANSWERED" => $this->abe_req_answered
                        ], ["ABE_REQ_UID" => $this->abe_req_uid]
                );
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
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param      array $columns Array of column names to validate.
     * @return     mixed <code>true</code> if all validations pass; 
      array of <code>ValidationFailed</code> objects otherwise.
     */
    public function validate ()
    {
        return true;
    }

}
