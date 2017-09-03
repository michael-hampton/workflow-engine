<?php

/**
 * Base class that represents a row from the 'APP_DELAY' table.
 * @author michael.hampton
 *
 *
 */
abstract class BaseAppDelay implements Persistent
{

    /**
     * The value for the app_delay_uid field.
     * @var        string
     */
    protected $app_delay_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid;

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid;

    /**
     * The value for the app_thread_index field.
     * @var        int
     */
    protected $app_thread_index = 0;

    /**
     * The value for the app_del_index field.
     * @var        int
     */
    protected $app_del_index = 0;

    /**
     * The value for the app_type field.
     * @var        string
     */
    protected $app_type = '0';

    /**
     * The value for the app_status field.
     * @var        string
     */
    protected $app_status = '0';

    /**
     * The value for the app_next_task field.
     * @var        string
     */
    protected $app_next_task = '0';

    /**
     * The value for the app_delegation_user field.
     * @var        string
     */
    protected $app_delegation_user = '0';

    /**
     * The value for the app_enable_action_user field.
     * @var        string
     */
    protected $app_enable_action_user;

    /**
     * The value for the app_enable_action_date field.
     * @var        int
     */
    protected $app_enable_action_date;

    /**
     * The value for the app_disable_action_user field.
     * @var        string
     */
    protected $app_disable_action_user = '0';

    /**
     * The value for the app_disable_action_date field.
     * @var        int
     */
    protected $app_disable_action_date;

    /**
     * The value for the app_automatic_disabled_date field.
     * @var        int
     */
    protected $app_automatic_disabled_date;

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
    public $arrFieldMapping = array(
        "PRO_UID" => array("mutator" => "setProUid", "accessor" => "getProUid", "type" => "int", "required" => "true"),
        "APP_UID" => array("mutator" => "setAppUid", "accessor" => "getAppUid", "type" => "int", "required" => "true"),
        "APP_THREAD_INDEX" => array("mutator" => "setAppThreadIndex", "accessor" => "getAppThreadIndex", "type" => "int", "required" => "false"),
        "APP_DEL_INDEX" => array("mutator" => "setAppDelIndex", "accessor" => "getAppDelIndex", "type" => "int", "required" => "false"),
        "APP_TYPE" => array("mutator" => "setAppType", "accessor" => "getAppType", "type" => "int", "required" => "true"),
        "APP_STATUS" => array("mutator" => "setAppStatus", "accessor" => "getAppStatus", "type" => "int", "required" => "true"),
        "APP_NEXT_TASK" => array("mutator" => "setAppNextTask", "accessor" => "getAppNextTask", "type" => "int", "required" => "false"),
        "APP_DELEGATION_USER" => array("mutator" => "setAppDelegationUser", "accessor" => "getAppDelegationUser", "type" => "int", "required" => "true"),
        "APP_ENABLE_ACTION_USER" => array("mutator" => "setAppEnableActionUser", "accessor" => "getAppEnableActionUser", "type" => "int", "required" => "true"),
        "APP_ENABLE_ACTION_DATE" => array("mutator" => "setAppEnableActionDate", "accessor" => "getAppEnableActionDate", "type" => "int", "required" => "true"),
        "APP_DISABLE_ACTION_USER" => array("mutator" => "setAppDisableActionUser", "accessor" => "getAppDisableActionUser", "type" => "int", "required" => "false"),
        "APP_DISABLE_ACTION_DATE" => array("mutator" => "setAppDisableActionDate", "accessor" => "getAppDisableActionDate", "type" => "int", "required" => "false"),
        "APP_AUTOMATIC_DISABLED_DATE" => array("mutator" => "setAppAutomaticDisabledDate", "accessor" => "getAppAutomaticDisabledDate", "type" => "int", "required" => "false"),
    );
    private $objMysql;

    public function __construct ($id = null)
    {
        $this->objMysql = new Mysql2();
    }

    public function loadObject (array $arrData)
    {
        foreach ($arrData as $strFieldKey => $varFieldValue) {
            if ( isset ($this->arrFieldMapping[$strFieldKey]) )
            {
                $strMutatorMethod = $this->arrFieldMapping[$strFieldKey]['mutator'];

                if ( is_callable (array($this, $strMutatorMethod)) && $varFieldValue != "" )
                {
                    call_user_func (array($this, $strMutatorMethod), $varFieldValue);
                }
            }
        }
    }

    /**
     * Get the [app_delay_uid] column value.
     *
     * @return     string
     */
    public function getAppDelayUid ()
    {

        return $this->app_delay_uid;
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
     * Get the [app_uid] column value.
     *
     * @return     string
     */
    public function getAppUid ()
    {

        return $this->app_uid;
    }

    /**
     * Get the [app_thread_index] column value.
     *
     * @return     int
     */
    public function getAppThreadIndex ()
    {

        return $this->app_thread_index;
    }

    /**
     * Get the [app_del_index] column value.
     *
     * @return     int
     */
    public function getAppDelIndex ()
    {

        return $this->app_del_index;
    }

    /**
     * Get the [app_type] column value.
     *
     * @return     string
     */
    public function getAppType ()
    {

        return $this->app_type;
    }

    /**
     * Get the [app_status] column value.
     *
     * @return     string
     */
    public function getAppStatus ()
    {

        return $this->app_status;
    }

    /**
     * Get the [app_next_task] column value.
     *
     * @return     string
     */
    public function getAppNextTask ()
    {

        return $this->app_next_task;
    }

    /**
     * Get the [app_delegation_user] column value.
     *
     * @return     string
     */
    public function getAppDelegationUser ()
    {

        return $this->app_delegation_user;
    }

    /**
     * Get the [app_enable_action_user] column value.
     *
     * @return     string
     */
    public function getAppEnableActionUser ()
    {

        return $this->app_enable_action_user;
    }

    /**
     * Get the [optionally formatted] [app_enable_action_date] column value.
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getAppEnableActionDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_enable_action_date === null || $this->app_enable_action_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_enable_action_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_enable_action_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_enable_action_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_enable_action_date;
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
     * Get the [app_disable_action_user] column value.
     *
     * @return     string
     */
    public function getAppDisableActionUser ()
    {

        return $this->app_disable_action_user;
    }

    /**
     * Get the [optionally formatted] [app_disable_action_date] column value.
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getAppDisableActionDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_disable_action_date === null || $this->app_disable_action_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_disable_action_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_disable_action_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_disable_action_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_disable_action_date;
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
     * Get the [optionally formatted] [app_automatic_disabled_date] column value.
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getAppAutomaticDisabledDate ($format = 'Y-m-d H:i:s')
    {

        if ( $this->app_automatic_disabled_date === null || $this->app_automatic_disabled_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->app_automatic_disabled_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->app_automatic_disabled_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new Exception ("Unable to parse value of [app_automatic_disabled_date] as date/time value: ");
            }
        }
        else
        {
            $ts = $this->app_automatic_disabled_date;
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
     * Set the value of [app_delay_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDelayUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_delay_uid !== $v || $v === '' )
        {
            $this->app_delay_uid = $v;
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

        if ( $this->pro_uid !== $v || $v === '0' )
        {
            $this->pro_uid = $v;
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

        if ( $this->app_uid !== $v || $v === '0' )
        {
            $this->app_uid = $v;
        }
    }

    /**
     * Set the value of [app_thread_index] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppThreadIndex ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->app_thread_index !== $v || $v === 0 )
        {
            $this->app_thread_index = $v;
        }
    }

    /**
     * Set the value of [app_del_index] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppDelIndex ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->app_del_index !== $v || $v === 0 )
        {
            $this->app_del_index = $v;
        }
    }

    /**
     * Set the value of [app_type] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_type !== $v || $v === '0' )
        {
            $this->app_type = $v;
        }
    }

    /**
     * Set the value of [app_status] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppStatus ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_status !== $v || $v === '0' )
        {
            $this->app_status = $v;
        }
    }

    /**
     * Set the value of [app_next_task] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppNextTask ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_next_task !== $v || $v === '0' )
        {
            $this->app_next_task = $v;
        }
    }

    /**
     * Set the value of [app_delegation_user] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDelegationUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_delegation_user !== $v || $v === '0' )
        {
            $this->app_delegation_user = $v;
        }
    }

    /**
     * Set the value of [app_enable_action_user] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppEnableActionUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_enable_action_user !== $v || $v === '0' )
        {
            $this->app_enable_action_user = $v;
        }
    }

    /**
     * Set the value of [app_enable_action_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppEnableActionDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [app_enable_action_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_enable_action_date !== $ts )
        {
            $this->app_enable_action_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_disable_action_user] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDisableActionUser ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_disable_action_user !== $v || $v === '0' )
        {
            $this->app_disable_action_user = $v;
        }
    }

    /**
     * Set the value of [app_disable_action_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppDisableActionDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [app_disable_action_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_disable_action_date !== $ts )
        {
            $this->app_disable_action_date = date ("Y-m-d H:i:s", $ts);
        }
    }

    /**
     * Set the value of [app_automatic_disabled_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppAutomaticDisabledDate ($v)
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
                throw new Exception ("Unable to parse date/time value for [app_automatic_disabled_date] from input: ");
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->app_automatic_disabled_date !== $ts )
        {
            $this->app_automatic_disabled_date = date ("Y-m-d H:i:s", $ts);
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
     * it inserts it; otherwise an update is performed.
     *
     * @return     int The number of rows affected by this insert/update
     * @throws     Exception
     */
    public function save ()
    {

        try {
            if ( trim ($this->app_delay_uid) === "" )
            {
                $id = $this->objMysql->_insert ("workflow.APP_DELAY", [
                    "PRO_UID" => $this->pro_uid,
                    "APP_UID" => $this->app_uid,
                    "APP_THREAD_INDEX" => $this->app_thread_index,
                    "APP_DEL_INDEX" => $this->app_del_index,
                    "APP_TYPE" => $this->app_type,
                    "APP_STATUS" => $this->app_status,
                    "APP_NEXT_TASK" => $this->app_next_task,
                    "APP_DELEGATION_USER" => $this->app_delegation_user,
                    "APP_ENABLE_ACTION_USER" => $this->app_enable_action_user,
                    "APP_ENABLE_ACTION_DATE" => $this->app_enable_action_date,
                    "APP_DISABLE_ACTION_USER" => $this->app_disable_action_user,
                    "APP_DISABLE_ACTION_DATE" => $this->app_disable_action_date,
                    "APP_AUTOMATIC_DISABLED_DATE" => $this->app_automatic_disabled_date
                        ]
                );
            }
            else
            {
                $this->objMysql->_update ("workflow.APP_DELAY", [
                    "PRO_UID" => $this->pro_uid,
                    "APP_UID" => $this->app_uid,
                    "APP_THREAD_INDEX" => $this->app_thread_index,
                    "APP_DEL_INDEX" => $this->app_del_index,
                    "APP_TYPE" => $this->app_type,
                    "APP_STATUS" => $this->app_status,
                    "APP_NEXT_TASK" => $this->app_next_task,
                    "APP_DELEGATION_USER" => $this->app_delegation_user,
                    "APP_ENABLE_ACTION_USER" => $this->app_enable_action_user,
                    "APP_ENABLE_ACTION_DATE" => $this->app_enable_action_date,
                    "APP_DISABLE_ACTION_USER" => $this->app_disable_action_user,
                    "APP_DISABLE_ACTION_DATE" => $this->app_disable_action_date,
                    "APP_AUTOMATIC_DISABLED_DATE" => $this->app_automatic_disabled_date
                        ], ["APP_DELAY_UID" => $this->app_delay_uid]
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
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */
    public function validate ()
    {
        foreach ($this->arrFieldMapping as $strColumnName => $arrFieldMap) {

            $strFormattedColumn = ucfirst (str_replace ("_", " ", $strColumnName));
            $strFormattedColumn = ucfirst (join (preg_split ('/(?<=[a-z])(?=[A-Z])/x', $strColumnName), " "));

            if ( $arrFieldMap['required'] === 'true' )
            {

                if ( trim ($this->{$arrFieldMap['accessor']} ()) === "" )
                {
                    $this->validationFailures[] = $strFormattedColumn . " is missing";
                }
            }
        }

        return count ($this->validationFailures) > 0 ? false : true;
    }

}
