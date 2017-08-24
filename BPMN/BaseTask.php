<?php

abstract class BaseTask implements Persistent
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    protected $arrFieldMapping = array(
        'TAS_TRANSFER_FLY' => array('accessor' => 'getTasTransferFly', 'mutator' => 'setTasTransferFly', 'type' => 'string', 'required' => 'true'),
        'TAS_ASSIGN_TYPE' => array('accessor' => 'getTasAssignType', 'mutator' => 'setTasAssignType', 'type' => 'string', 'required' => 'true'),
        'name' => array('accessor' => 'getTasSelfService', 'mutator' => 'setTasSelfService', 'type' => 'string', 'required' => 'false'),
        'TAS_SELFSERVICE_TIMEOUT' => array('accessor' => 'getTasSelfserviceTimeout', 'mutator' => 'setTasSelfserviceTimeout', 'type' => 'string', 'required' => 'false'),
        'TAS_SELFSERVICE_TIME' => array('accessor' => 'getTasSelfserviceTime', 'mutator' => 'setTasSelfserviceTime', 'type' => 'string', 'required' => 'false'),
        'TAS_SELFSERVICE_TIME_UNIT' => array('accessor' => 'getTasSelfserviceTimeUnit', 'mutator' => 'setTasSelfserviceTimeUnit', 'type' => 'string', 'required' => 'false'),
        'TAS_SELFSERVICE_TRIGGER_UID' => array('accessor' => 'getTasSelfserviceTriggerUid', 'mutator' => 'setTasSelfserviceTriggerUid', 'type' => 'string', 'required' => 'false'),
        'TAS_SELFSERVICE_EXECUTION' => array('accessor' => 'getTasSelfserviceExecution', 'mutator' => 'setTasSelfserviceExecution', 'type' => 'string', 'required' => 'false'),
        'name' => array('accessor' => 'getTasTitle', 'mutator' => 'setTasTitle', 'type' => 'string', 'required' => 'false'),
        'TAS_DEF_MESSAGE' => array('accessor' => 'getTasDefMessage', 'mutator' => 'setTasDefMessage', 'type' => 'string', 'required' => 'false'),
        'TAS_DEF_SUBJECT_MESSAGE' => array('accessor' => 'getTasDefSubjectMessage', 'mutator' => 'setTasDefSubjectMessage', 'type' => 'string', 'required' => 'false'),
        'name' => array('accessor' => 'getTasType', 'mutator' => 'setTasType', 'type' => 'string', 'required' => 'false'),
        'TAS_DURATION' => array('accessor' => 'getTasDuration', 'mutator' => 'setTasDuration', 'type' => 'string', 'required' => 'true'),
        'name' => array('accessor' => 'getTasDelayType', 'mutator' => 'setTasDelayType', 'type' => 'string', 'required' => 'false'),
        'TAS_TIMEUNIT' => array('accessor' => 'getTasTimeunit', 'mutator' => 'setTasTimeunit', 'type' => 'string', 'required' => 'false'),
        'TAS_TYPE_DAY' => array('accessor' => 'getTasTypeDay', 'mutator' => 'setTasTypeDay', 'type' => 'string', 'required' => 'false'),
        'TAS_UID' => array('accessor' => 'getTasUid', 'mutator' => 'setTasUid', 'type' => 'string', 'required' => 'false'),
        'PRO_UID' => array('accessor' => 'getProUid', 'mutator' => 'setProUid', 'type' => 'string', 'required' => 'true'),
        'TAS_SELFSERVICE_TIMEOUT' => array('accessor' => 'getTasSelfserviceTimeout', 'mutator' => 'setTasSelfserviceTimeout', 'type' => 'string', 'required' => 'true'),
    );

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';
    protected $calendarUid;
    protected $firstStep;

    /**
     * The value for the tas_send_last_email field.
     * @var        string
     */
    protected $tas_send_last_email = 'TRUE';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid;

    /**
     * The value for the tas_def_message field.
     * @var        string
     */
    protected $tas_def_message;

    /**
     * The value for the tas_id field.
     * @var        int
     */
    protected $tas_id;
    protected $condition;

    /**
     * The value for the tas_title field.
     * @var        string
     */
    protected $tas_title;

    /**
     * The value for the tas_description field.
     * @var        string
     */
    protected $tas_description;

    /**
     * The value for the tas_type field.
     * @var        string
     */
    protected $tas_type = 'NORMAL';

    /**
     * The value for the tas_duration field.
     * @var        double
     */
    protected $tas_duration = 0;

    /**
     * The value for the tas_delay_type field.
     * @var        string
     */
    protected $tas_delay_type = '';

    /**
     * The value for the tas_type_day field.
     * @var        string
     */
    protected $tas_type_day = '1';

    /**
     * The value for the tas_timeunit field.
     * @var        string
     */
    protected $tas_timeunit = 'DAYS';

    /**
     * The value for the tas_alert field.
     * @var        string
     */
    protected $tas_alert = 'FALSE';

    /**
     * The value for the tas_priority_variable field.
     * @var        string
     */
    protected $tas_priority_variable = '';

    /**
     * The value for the tas_assign_type field.
     * @var        string
     */
    protected $tas_assign_type = 'BALANCED';

    /**
     * The value for the tas_assign_variable field.
     * @var        string
     */
    protected $tas_assign_variable = '@@SYS_NEXT_USER_TO_BE_ASSIGNED';

    /**
     * The value for the tas_assign_location field.
     * @var        string
     */
    protected $tas_assign_location = 'FALSE';

    /**
     * The value for the tas_def_subject_message field.
     * @var        string
     */
    protected $tas_def_subject_message;

    /**
     * The value for the tas_assign_location_adhoc field.
     * @var        string
     */
    protected $tas_assign_location_adhoc = 'FALSE';

    /**
     * The value for the tas_def_title field.
     * @var        string
     */
    protected $tas_def_title;

    /**
     * The value for the tas_transfer_fly field.
     * @var        string
     */
    protected $tas_transfer_fly = 'FALSE';

    /**
     * The value for the tas_last_assigned field.
     * @var        string
     */
    protected $tas_last_assigned = '0';

    /**
     * The value for the tas_user field.
     * @var        string
     */
    protected $tas_user = '0';

    /**
     * The value for the tas_can_upload field.
     * @var        string
     */
    protected $tas_can_upload = 'FALSE';

    /**
     * The value for the tas_view_upload field.
     * @var        string
     */
    protected $tas_view_upload = 'FALSE';

    /**
     * The value for the tas_group_variable field.
     * @var        string
     */
    protected $tas_group_variable;

    /**
     * The value for the tas_view_additional_documentation field.
     * @var        string
     */
    protected $tas_view_additional_documentation = 'FALSE';

    /**
     * The value for the tas_can_cancel field.
     * @var        string
     */
    protected $tas_can_cancel = 'FALSE';

    /**
     * The value for the tas_owner_app field.
     * @var        string
     */
    protected $tas_owner_app = '';

    /**
     * The value for the tas_can_pause field.
     * @var        string
     */
    protected $tas_can_pause = 'FALSE';

    /**
     * The value for the tas_can_send_message field.
     * @var        string
     */
    protected $tas_can_send_message = 'TRUE';

    /**
     * The value for the tas_can_delete_docs field.
     * @var        string
     */
    protected $tas_can_delete_docs = 'FALSE';

    /**
     * The value for the tas_self_service field.
     * @var        string
     */
    protected $tas_self_service = 'FALSE';

    /**
     * The value for the tas_start field.
     * @var        string
     */
    protected $tas_start = 'FALSE';

    /**
     * The value for the tas_to_last_user field.
     * @var        string
     */
    protected $tas_to_last_user = 'FALSE';

    /**
     * The value for the tas_selfservice_timeout field.
     * @var        int
     */
    protected $tas_selfservice_timeout = 0;

    /**
     * The value for the tas_selfservice_time field.
     * @var        int
     */
    protected $tas_selfservice_time = 0;

    /**
     * The value for the tas_selfservice_time_unit field.
     * @var        string
     */
    protected $tas_selfservice_time_unit = '';

    /**
     * The value for the tas_selfservice_trigger_uid field.
     * @var        string
     */
    protected $tas_selfservice_trigger_uid = '';

    /**
     * The value for the tas_selfservice_execution field.
     * @var        string
     */
    protected $tas_selfservice_execution = 'EVERY_TIME';

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
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid ()
    {

        return $this->pro_uid;
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
     * Get the [tas_id] column value.
     * 
     * @return     int
     */
    public function getTasId ()
    {

        return $this->tas_id;
    }

    /**
     * Get the [tas_title] column value.
     * 
     * @return     string
     */
    public function getTasTitle ()
    {

        return $this->tas_title;
    }

    /**
     * Get the [tas_description] column value.
     * 
     * @return     string
     */
    public function getTasDescription ()
    {

        return $this->tas_description;
    }

    /**
     * Get the [tas_def_subject_message] column value.
     * 
     * @return     string
     */
    public function getTasDefSubjectMessage ()
    {
        return $this->tas_def_subject_message;
    }

    /**
     * Get the [tas_type] column value.
     * 
     * @return     string
     */
    public function getTasType ()
    {

        return $this->tas_type;
    }

    /**
     * Get the [tas_def_message] column value.
     * 
     * @return     string
     */
    public function getTasDefMessage ()
    {
        return $this->tas_def_message;
    }

    /**
     * Get the [tas_duration] column value.
     * 
     * @return     double
     */
    public function getTasDuration ()
    {

        return $this->tas_duration;
    }

    public function getCondition ()
    {
        return $this->condition;
    }

    public function setCondition ($condition)
    {
        $this->condition = $condition;
    }

    /**
     * Get the [tas_delay_type] column value.
     * 
     * @return     string
     */
    public function getTasDelayType ()
    {

        return $this->tas_delay_type;
    }

    /**
     * Get the [tas_type_day] column value.
     * 
     * @return     string
     */
    public function getTasTypeDay ()
    {

        return $this->tas_type_day;
    }

    /**
     * Get the [tas_timeunit] column value.
     * 
     * @return     string
     */
    public function getTasTimeunit ()
    {

        return $this->tas_timeunit;
    }

    /**
     * Get the [tas_alert] column value.
     * 
     * @return     string
     */
    public function getTasAlert ()
    {

        return $this->tas_alert;
    }

    /**
     * Get the [tas_priority_variable] column value.
     * 
     * @return     string
     */
    public function getTasPriorityVariable ()
    {

        return $this->tas_priority_variable;
    }

    /**
     * Get the [tas_assign_type] column value.
     * 
     * @return     string
     */
    public function getTasAssignType ()
    {

        return $this->tas_assign_type;
    }

    /**
     * Get the [tas_assign_variable] column value.
     * 
     * @return     string
     */
    public function getTasAssignVariable ()
    {

        return $this->tas_assign_variable;
    }

    /**
     * Get the [tas_assign_location] column value.
     * 
     * @return     string
     */
    public function getTasAssignLocation ()
    {

        return $this->tas_assign_location;
    }

    /**
     * Get the [tas_assign_location_adhoc] column value.
     * 
     * @return     string
     */
    public function getTasAssignLocationAdhoc ()
    {

        return $this->tas_assign_location_adhoc;
    }

    /**
     * Get the [tas_transfer_fly] column value.
     * 
     * @return     string
     */
    public function getTasTransferFly ()
    {

        return $this->tas_transfer_fly;
    }

    /**
     * Get the [tas_last_assigned] column value.
     * 
     * @return     string
     */
    public function getTasLastAssigned ()
    {

        return $this->tas_last_assigned;
    }

    /**
     * Get the [tas_user] column value.
     * 
     * @return     string
     */
    public function getTasUser ()
    {

        return $this->tas_user;
    }

    /**
     * Get the [tas_can_upload] column value.
     * 
     * @return     string
     */
    public function getTasCanUpload ()
    {

        return $this->tas_can_upload;
    }

    /**
     * Get the [tas_view_upload] column value.
     * 
     * @return     string
     */
    public function getTasViewUpload ()
    {

        return $this->tas_view_upload;
    }

    /**
     * Get the [tas_view_additional_documentation] column value.
     * 
     * @return     string
     */
    public function getTasViewAdditionalDocumentation ()
    {

        return $this->tas_view_additional_documentation;
    }

    /**
     * Get the [tas_can_cancel] column value.
     * 
     * @return     string
     */
    public function getTasCanCancel ()
    {

        return $this->tas_can_cancel;
    }

    /**
     * Get the [tas_owner_app] column value.
     * 
     * @return     string
     */
    public function getTasOwnerApp ()
    {

        return $this->tas_owner_app;
    }

    /**
     * Get the [tas_can_pause] column value.
     * 
     * @return     string
     */
    public function getTasCanPause ()
    {

        return $this->tas_can_pause;
    }

    /**
     * Get the [tas_can_send_message] column value.
     * 
     * @return     string
     */
    public function getTasCanSendMessage ()
    {

        return $this->tas_can_send_message;
    }

    /**
     * Get the [tas_can_delete_docs] column value.
     * 
     * @return     string
     */
    public function getTasCanDeleteDocs ()
    {

        return $this->tas_can_delete_docs;
    }

    /**
     * Get the [tas_self_service] column value.
     * 
     * @return     string
     */
    public function getTasSelfService ()
    {

        return $this->tas_self_service;
    }

    /**
     * Get the [tas_start] column value.
     * 
     * @return     string
     */
    public function getTasStart ()
    {

        return $this->tas_start;
    }

    public function getFirstStep ()
    {
        return $this->firstStep;
    }

    public function setFirstStep ($firstStep)
    {
        $this->firstStep = $firstStep;
    }

    /**
     * Get the [tas_send_last_email] column value.
     * 
     * @return     string
     */
    public function getTasSendLastEmail ()
    {

        return $this->tas_send_last_email;
    }

    /**
     * Get the [tas_selfservice_timeout] column value.
     * 
     * @return     int
     */
    public function getTasSelfserviceTimeout ()
    {

        return $this->tas_selfservice_timeout;
    }

    /**
     * Get the [tas_selfservice_time] column value.
     * 
     * @return     int
     */
    public function getTasSelfserviceTime ()
    {

        return $this->tas_selfservice_time;
    }

    /**
     * Get the [tas_selfservice_time_unit] column value.
     * 
     * @return     string
     */
    public function getTasSelfserviceTimeUnit ()
    {

        return $this->tas_selfservice_time_unit;
    }

    /**
     * Get the [tas_selfservice_trigger_uid] column value.
     * 
     * @return     string
     */
    public function getTasSelfserviceTriggerUid ()
    {

        return $this->tas_selfservice_trigger_uid;
    }

    /**
     * Get the [tas_selfservice_execution] column value.
     * 
     * @return     string
     */
    public function getTasSelfserviceExecution ()
    {

        return $this->tas_selfservice_execution;
    }

    /* Set the value of [tas_send_last_email] column.
     * 
     * @param      string $v new value
     * @return     void
     */

    public function setTasSendLastEmail ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tas_send_last_email !== $v || $v === 'TRUE' )
        {
            $this->tas_send_last_email = $v;
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

// setProUid()

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
     * Set the value of [tas_id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTasId ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->tas_id !== $v )
        {
            $this->tas_id = $v;
        }
    }

    /**
     * Set the value of [tas_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasTitle ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_title !== $v )
        {
            $this->tas_title = $v;
        }
    }

    /**
     * Set the value of [tas_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasDescription ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_description !== $v )
        {
            $this->tas_description = $v;
        }
    }

    /**
     * Set the value of [tas_def_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasDefTitle ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_def_title !== $v )
        {
            $this->tas_def_title = $v;
        }
    }

    /**
     * Set the value of [tas_def_subject_message] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasDefSubjectMessage ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_def_subject_message !== $v )
        {
            $this->tas_def_subject_message = $v;
        }
    }

    /**
     * Set the value of [tas_def_message] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasDefMessage ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_def_message !== $v )
        {
            $this->tas_def_message = $v;
        }
    }

    /**
     * Set the value of [tas_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_type !== $v || $v === 'NORMAL' )
        {
            $this->tas_type = $v;
        }
    }

    /**
     * Set the value of [tas_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setTasDuration ($v)
    {

        if ( $this->tas_duration !== $v || $v === 0 )
        {
            $this->tas_duration = $v;
        }
    }

    /**
     * Set the value of [tas_delay_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasDelayType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_delay_type !== $v || $v === '' )
        {
            $this->tas_delay_type = $v;
        }
    }

    /**
     * Set the value of [tas_type_day] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasTypeDay ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_type_day !== $v || $v === '1' )
        {
            $this->tas_type_day = $v;
        }
    }

    /**
     * Set the value of [tas_timeunit] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasTimeunit ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_timeunit !== $v || $v === 'DAYS' )
        {
            $this->tas_timeunit = $v;
        }
    }

    /**
     * Set the value of [tas_priority_variable] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasPriorityVariable ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_priority_variable !== $v || $v === '' )
        {
            $this->tas_priority_variable = $v;
        }
    }

    /**
     * Set the value of [tas_assign_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasAssignType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_assign_type !== $v || $v === 'BALANCED' )
        {
            $this->tas_assign_type = $v;
        }
    }

    /**
     * Set the value of [tas_assign_variable] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasAssignVariable ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_assign_variable !== $v || $v === '@@SYS_NEXT_USER_TO_BE_ASSIGNED' )
        {
            $this->tas_assign_variable = $v;
        }
    }

    /**
     * Set the value of [tas_assign_location] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasAssignLocation ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_assign_location !== $v || $v === 'FALSE' )
        {
            $this->tas_assign_location = $v;
        }
    }

    /**
     * Set the value of [tas_assign_location_adhoc] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasAssignLocationAdhoc ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_assign_location_adhoc !== $v || $v === 'FALSE' )
        {
            $this->tas_assign_location_adhoc = $v;
        }
    }

    /**
     * Set the value of [tas_transfer_fly] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasTransferFly ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_transfer_fly !== $v || $v === 'FALSE' )
        {
            $this->tas_transfer_fly = $v;
        }
    }

    /**
     * Set the value of [tas_last_assigned] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasLastAssigned ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_last_assigned !== $v || $v === '0' )
        {
            $this->tas_last_assigned = $v;
        }
    }

    /**
     * Set the value of [tas_user] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_user !== $v || $v === '0' )
        {
            $this->tas_user = $v;
        }
    }

    /**
     * Set the value of [tas_can_upload] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasCanUpload ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_can_upload !== $v || $v === 'FALSE' )
        {
            $this->tas_can_upload = $v;
        }
    }

    /**
     * Set the value of [tas_view_upload] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasViewUpload ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_view_upload !== $v || $v === 'FALSE' )
        {
            $this->tas_view_upload = $v;
        }
    }

    /**
     * Set the value of [tas_view_additional_documentation] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasViewAdditionalDocumentation ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_view_additional_documentation !== $v || $v === 'FALSE' )
        {
            $this->tas_view_additional_documentation = $v;
        }
    }

    /**
     * Set the value of [tas_can_cancel] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasCanCancel ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_can_cancel !== $v || $v === 'FALSE' )
        {
            $this->tas_can_cancel = $v;
        }
    }

    public function getCalendarUid ()
    {
        return $this->calendarUid;
    }

    public function setCalendarUid ($calendarUid)
    {
        $this->calendarUid = $calendarUid;
    }

    /**
     * Set the value of [tas_owner_app] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasOwnerApp ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_owner_app !== $v || $v === '' )
        {
            $this->tas_owner_app = $v;
        }
    }

    /**
     * Set the value of [tas_can_pause] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasCanPause ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_can_pause !== $v || $v === 'FALSE' )
        {
            $this->tas_can_pause = $v;
        }
    }

    /**
     * Set the value of [tas_can_send_message] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasCanSendMessage ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_can_send_message !== $v || $v === 'TRUE' )
        {
            $this->tas_can_send_message = $v;
        }
    }

    /**
     * Set the value of [tas_can_delete_docs] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasCanDeleteDocs ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_can_delete_docs !== $v || $v === 'FALSE' )
        {
            $this->tas_can_delete_docs = $v;
        }
    }

    /**
     * Set the value of [tas_self_service] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasSelfService ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_self_service !== $v || $v === 'FALSE' )
        {
            $this->tas_self_service = $v;
        }
    }

    /**
     * Set the value of [tas_selfservice_timeout] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTasSelfserviceTimeout ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->tas_selfservice_timeout !== $v || $v === 0 )
        {
            $this->tas_selfservice_timeout = $v;
        }
    }

    /**
     * Set the value of [tas_selfservice_time] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setTasSelfserviceTime ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->tas_selfservice_time !== $v || $v === 0 )
        {
            $this->tas_selfservice_time = $v;
        }
    }

    /**
     * Set the value of [tas_selfservice_time_unit] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasSelfserviceTimeUnit ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_selfservice_time_unit !== $v || $v === '' )
        {
            $this->tas_selfservice_time_unit = $v;
        }
    }

    /**
     * Set the value of [tas_selfservice_trigger_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasSelfserviceTriggerUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_selfservice_trigger_uid !== $v || $v === '' )
        {
            $this->tas_selfservice_trigger_uid = $v;
        }
    }

    /**
     * Set the value of [tas_group_variable] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasGroupVariable ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tas_group_variable !== $v )
        {
            $this->tas_group_variable = $v;
        }
    }

// setTasGroupVariable()

    /**
     * Set the value of [tas_selfservice_execution] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasSelfserviceExecution ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->tas_selfservice_execution !== $v || $v === 'EVERY_TIME' )
        {
            $this->tas_selfservice_execution = $v;
        }
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function save ()
    {
        if ( $this->objMysql === NULL )
        {
            $this->getConnection ();
        }

        if ( trim ($this->tas_uid) === "" )
        {
            $id = $this->objMysql->_insert ("workflow.task", ['PRO_UID' => $this->pro_uid,
                'TAS_DESCRIPTION' => $this->tas_description,
                'TAS_TYPE' => $this->tas_type,
                'TAS_DURATION' => $this->tas_duration,
                'TAS_SELFSERVICE_EXECUTION' => $this->tas_selfservice_execution,
                'TAS_TYPE_DAY' => $this->tas_type_day,
                'TAS_TIMEUNIT' => $this->tas_timeunit,
                'TAS_DELAY_TYPE' => $this->tas_delay_type,
                'TAS_ASSIGN_TYPE' => $this->tas_assign_type,
                'TAS_SELF_SERVICE' => $this->tas_self_service,
                'TAS_SELFSERVICE_TIMEOUT' => $this->tas_selfservice_timeout,
                'TAS_SELFSERVICE_TIME' => $this->tas_selfservice_time,
                'TAS_SELFSERVICE_TIME_UNIT' => $this->tas_selfservice_time_unit,
                'TAS_TRANSFER_FLY' => $this->tas_transfer_fly,
                "step_name" => $this->tas_title,
                'TAS_SELFSERVICE_TRIGGER_UID' => $this->tas_selfservice_trigger_uid]);

            return $id;
        }
        else
        {
            $this->objMysql->_update ("workflow.task", ['PRO_UID' => $this->pro_uid,
                'TAS_DESCRIPTION' => $this->tas_description,
                'TAS_TYPE' => $this->tas_type,
                'TAS_DURATION' => $this->tas_duration,
                'TAS_SELFSERVICE_EXECUTION' => $this->tas_selfservice_execution,
                'TAS_TYPE_DAY' => $this->tas_type_day,
                'TAS_TIMEUNIT' => $this->tas_timeunit,
                'TAS_DELAY_TYPE' => $this->tas_delay_type,
                'TAS_ASSIGN_TYPE' => $this->tas_assign_type,
                'TAS_SELF_SERVICE' => $this->tas_self_service,
                'TAS_SELFSERVICE_TIMEOUT' => $this->tas_selfservice_timeout,
                'TAS_SELFSERVICE_TIME' => $this->tas_selfservice_time,
                'TAS_SELFSERVICE_TIME_UNIT' => $this->tas_selfservice_time_unit,
                'TAS_TRANSFER_FLY' => $this->tas_transfer_fly,
                'TAS_SELFSERVICE_TRIGGER_UID' => $this->tas_selfservice_trigger_uid], ["TAS_UID" => $this->tas_uid]
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

}
