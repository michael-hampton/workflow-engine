<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseEvent
 *
 * @author michael.hampton
 */
abstract class BaseEvent
{

    private $objMysql;
    private $event;
    private $ValidationFailures;

    /**
     * The value for the evn_uid field.
     * @var        string
     */
    protected $evn_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the evn_status field.
     * @var        string
     */
    protected $evn_status;

    /**
     * The value for the evn_when_occurs field.
     * @var        string
     */
    protected $evn_when_occurs = 'SINGLE';

    /**
     * The value for the evn_related_to field.
     * @var        string
     */
    protected $evn_related_to = 'SINGLE';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';

    /**
     * The value for the evn_tas_uid_from field.
     * @var        string
     */
    protected $evn_tas_uid_from = '';

    /**
     * The value for the evn_tas_uid_to field.
     * @var        string
     */
    protected $evn_tas_uid_to = '';

    /**
     * The value for the evn_tas_estimated_duration field.
     * @var        double
     */
    protected $evn_tas_estimated_duration = 0;

    /**
     * The value for the evn_time_unit field.
     * @var        string
     */
    protected $evn_time_unit = 'DAYS';

    /**
     * The value for the evn_when field.
     * @var        double
     */
    protected $evn_when = 0;

    /**
     * The value for the evn_max_attempts field.
     * @var        int
     */
    protected $evn_max_attempts = 3;

    /**
     * The value for the evn_action field.
     * @var        string
     */
    protected $evn_action = '';

    /**
     * The value for the evn_conditions field.
     * @var        string
     */
    protected $evn_conditions;

    /**
     * The value for the evn_action_parameters field.
     * @var        string
     */
    protected $evn_action_parameters;

    /**
     * The value for the tri_uid field.
     * @var        string
     */
    protected $tri_uid = '';

    /**
     * The value for the evn_posx field.
     * @var        int
     */
    protected $evn_posx = 0;

    /**
     * The value for the evn_posy field.
     * @var        int
     */
    protected $evn_posy = 0;

    /**
     * The value for the evn_type field.
     * @var        string
     */
    protected $evn_type = '';

    /**
     * The value for the tas_evn_uid field.
     * @var        string
     */
    protected $tas_evn_uid = '';

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
     * Get the [evn_uid] column value.
     * 
     * @return     string
     */
    public function getEvnUid ()
    {
        return $this->evn_uid;
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
     * Get the [evn_status] column value.
     * 
     * @return     string
     */
    public function getEvnStatus ()
    {
        return $this->evn_status;
    }

    /**
     * Get the [evn_when_occurs] column value.
     * 
     * @return     string
     */
    public function getEvnWhenOccurs ()
    {
        return $this->evn_when_occurs;
    }

    /**
     * Get the [evn_related_to] column value.
     * 
     * @return     string
     */
    public function getEvnRelatedTo ()
    {
        return $this->evn_related_to;
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
     * Get the [evn_tas_uid_from] column value.
     * 
     * @return     string
     */
    public function getEvnTasUidFrom ()
    {
        return $this->evn_tas_uid_from;
    }

    /**
     * Get the [evn_tas_uid_to] column value.
     * 
     * @return     string
     */
    public function getEvnTasUidTo ()
    {
        return $this->evn_tas_uid_to;
    }

    /**
     * Get the [evn_tas_estimated_duration] column value.
     * 
     * @return     double
     */
    public function getEvnTasEstimatedDuration ()
    {
        return $this->evn_tas_estimated_duration;
    }

    /**
     * Get the [evn_time_unit] column value.
     * 
     * @return     string
     */
    public function getEvnTimeUnit ()
    {
        return $this->evn_time_unit;
    }

    /**
     * Get the [evn_when] column value.
     * 
     * @return     double
     */
    public function getEvnWhen ()
    {
        return $this->evn_when;
    }

    /**
     * Get the [evn_max_attempts] column value.
     * 
     * @return     int
     */
    public function getEvnMaxAttempts ()
    {
        return $this->evn_max_attempts;
    }

    /**
     * Get the [evn_action] column value.
     * 
     * @return     string
     */
    public function getEvnAction ()
    {
        return $this->evn_action;
    }

    /**
     * Get the [evn_conditions] column value.
     * 
     * @return     string
     */
    public function getEvnConditions ()
    {
        return $this->evn_conditions;
    }

    /**
     * Get the [evn_action_parameters] column value.
     * 
     * @return     string
     */
    public function getEvnActionParameters ()
    {
        return $this->evn_action_parameters;
    }

    /**
     * Get the [tri_uid] column value.
     * 
     * @return     string
     */
    public function getTriUid ()
    {
        return $this->tri_uid;
    }

    /**
     * Get the [evn_posx] column value.
     * 
     * @return     int
     */
    public function getEvnPosx ()
    {
        return $this->evn_posx;
    }

    /**
     * Get the [evn_posy] column value.
     * 
     * @return     int
     */
    public function getEvnPosy ()
    {
        return $this->evn_posy;
    }

    /**
     * Get the [evn_type] column value.
     * 
     * @return     string
     */
    public function getEvnType ()
    {
        return $this->evn_type;
    }

    /**
     * Get the [tas_evn_uid] column value.
     * 
     * @return     string
     */
    public function getTasEvnUid ()
    {
        return $this->tas_evn_uid;
    }

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
        if ( $this->evn_uid !== $v || $v === '' )
        {
            $this->evn_uid = $v;
        }
    }

// setEvnUid()
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
     * Set the value of [evn_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_status !== $v || $v === 'OPEN' )
        {
            $this->evn_status = $v;
        }
    }

// setEvnStatus()
    /**
     * Set the value of [evn_when_occurs] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnWhenOccurs ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_when_occurs !== $v || $v === 'SINGLE' )
        {
            $this->evn_when_occurs = $v;
        }
    }

// setEvnWhenOccurs()
    /**
     * Set the value of [evn_related_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnRelatedTo ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_related_to !== $v || $v === 'SINGLE' )
        {
            $this->evn_related_to = $v;
        }
    }

// setEvnRelatedTo()
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

// setTasUid()
    /**
     * Set the value of [evn_tas_uid_from] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnTasUidFrom ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_tas_uid_from !== $v || $v === '' )
        {
            $this->evn_tas_uid_from = $v;
        }
    }

// setEvnTasUidFrom()
    /**
     * Set the value of [evn_tas_uid_to] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnTasUidTo ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_tas_uid_to !== $v || $v === '' )
        {
            $this->evn_tas_uid_to = $v;
        }
    }

// setEvnTasUidTo()
    /**
     * Set the value of [evn_tas_estimated_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setEvnTasEstimatedDuration ($v)
    {
        if ( $this->evn_tas_estimated_duration !== $v || $v === 0 )
        {
            $this->evn_tas_estimated_duration = $v;
        }
    }

// setEvnTasEstimatedDuration()
    /**
     * Set the value of [evn_time_unit] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnTimeUnit ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_time_unit !== $v || $v === 'DAYS' )
        {
            $this->evn_time_unit = $v;
        }
    }

// setEvnTimeUnit()
    /**
     * Set the value of [evn_when] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setEvnWhen ($v)
    {
        if ( $this->evn_when !== $v || $v === 0 )
        {
            $this->evn_when = $v;
        }
    }

// setEvnWhen()
    /**
     * Set the value of [evn_max_attempts] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setEvnMaxAttempts ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->evn_max_attempts !== $v || $v === 3 )
        {
            $this->evn_max_attempts = $v;
        }
    }

// setEvnMaxAttempts()
    /**
     * Set the value of [evn_action] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnAction ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_action !== $v || $v === '' )
        {
            $this->evn_action = $v;
        }
    }

// setEvnAction()
    /**
     * Set the value of [evn_conditions] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnConditions ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_conditions !== $v )
        {
            $this->evn_conditions = $v;
        }
    }

// setEvnConditions()
    /**
     * Set the value of [evn_action_parameters] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnActionParameters ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_action_parameters !== $v )
        {
            $this->evn_action_parameters = $v;
        }
    }

// setEvnActionParameters()
    /**
     * Set the value of [tri_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTriUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tri_uid !== $v || $v === '' )
        {
            $this->tri_uid = $v;
        }
    }

// setTriUid()
    /**
     * Set the value of [evn_posx] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setEvnPosx ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->evn_posx !== $v || $v === 0 )
        {
            $this->evn_posx = $v;
        }
    }

// setEvnPosx()
    /**
     * Set the value of [evn_posy] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setEvnPosy ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->evn_posy !== $v || $v === 0 )
        {
            $this->evn_posy = $v;
        }
    }

// setEvnPosy()
    /**
     * Set the value of [evn_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setEvnType ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->evn_type !== $v || $v === '' )
        {
            $this->evn_type = $v;
        }
    }

// setEvnType()
    /**
     * Set the value of [tas_evn_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasEvnUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->tas_evn_uid !== $v || $v === '' )
        {
            $this->tas_evn_uid = $v;
        }
    }

// setTasEvnUid()

    public function getValidationFailures ()
    {
        return $this->ValidationFailures;
    }

    public function setValidationFailures ($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getEvent ()
    {
        return $this->event;
    }

    public function setEvent ($event)
    {
        $this->event = $event;
    }

    public function getEventsForTask ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $results = $this->objMysql->_select ("workflow.status_mapping", [], ["id" => $this->tas_uid]);


        if ( isset ($results[0]['step_condition']) && !empty ($results[0]['step_condition']) )
        {
            return $results[0]['step_condition'];
        }
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->event) === "" )
        {
            $this->ValidationFailures[] = "Event is missing";
            $errorCount++;
        }

        if ( trim ($this->tas_uid) === "" )
        {
            $this->ValidationFailures[] = "Task id is missing";
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function doSave ()
    {
        $conditions = $this->getEventsForTask ();

        if ( !empty ($conditions) )
        {
            $conditions = json_decode ($conditions, TRUE);
        }
        else
        {
            $conditions = [];
        }
        
        $conditions[$this->event] = $this->evn_status;
        $conditions['params'][$this->event]['max_attempts'] = $this->evn_max_attempts;
        $conditions['params'][$this->event]['time_unit'] = $this->evn_time_unit;
        $conditions['params'][$this->event]['estimated_duration'] = $this->evn_tas_estimated_duration;
        $conditions['params'][$this->event]['event_when'] = $this->evn_when;
        $conditions['params'][$this->event]['when_occurs'] = $this->evn_when_occurs;
        $conditions['params'][$this->event]['action_params'] = $this->evn_action_parameters;
        
        $this->objMysql->_update("workflow.status_mapping", ["step_condition" => json_encode ($conditions)], ["id" => $this->tas_uid]);
    }
}
