<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseAppThread
 *
 * @author michael.hampton
 */
abstract class BaseAppThread implements Persistent
{

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the app_number field.
     * @var        int
     */
    protected $app_number = 0;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the request_id field.
     * @var        int
     */
    protected $requestId;

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '';

    /**
     * The value for the status field.
     * @var        int
     */
    protected $status;

    /**
     * The value for the app_thread_index field.
     * @var        int
     */
    protected $app_thread_index = 0;

    /**
     * The value for the hasEvent field.
     * @var        int
     */
    protected $hasEvent;
    private $objMysql;

    /**
     *
     * @var type 
     */
    private $arrFieldMapping = array(
        "APP_UID" => array("accessor" => "getAppUid", "mutator" => "setAppUid"),
        "APP_NUMBER" => array("accessor" => "getAppNumber", "mutator" => "setAppNumber"),
        "TAS_UID" => array("accessor" => "getTasUid", "mutator" => "setTasUid"),
        "APP_THREAD_STATUS" => array("accessor" => "getStatus", "mutator" => "setStatus"),
    );

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
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
     * Get the [app_number] column value.
     * 
     * @return     int
     */
    public function getAppNumber ()
    {
        return $this->app_number;
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
     * Get the [tas_uid] column value.
     * 
     * @return     string
     */
    public function getTasUid ()
    {
        return $this->tas_uid;
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
     * Get the [status] column value.
     * 
     * @return     string
     */
    public function getStatus ()
    {
        return $this->status;
    }

    /**
     * Get the [request_id] column value.
     * 
     * @return     int
     */
    public function getRequestId ()
    {
        return $this->requestId;
    }

    /**
     * Get the [hasEvent] column value.
     * 
     * @return     int
     */
    public function getHasEvent ()
    {
        return $this->hasEvent;
    }

    /**
     * Set the value of [app_number] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppNumber ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->app_number !== $v || $v === 0 )
        {
            $this->app_number = $v;
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
     * Set the value of [app_number] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setCollectionId ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->requestId !== $v || $v === 0 )
        {
            $this->requestId = $v;
        }
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
     * Set the value of [status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->status !== $v || $v === '' )
        {
            $this->status = $v;
        }
    }

    /**
     * Set the value of [hasEvent] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setHasEvent ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }
        if ( $this->hasEvent !== $v || $v === 0 )
        {
            $this->hasEvent = $v;
        }
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
     * @return     mixed <code>true</code> if all validations pass; 
      array of <code>ValidationFailed</code> objects otherwise.
     */
    public function validate ()
    {
        if ( trim ($this->app_number) === "" )
        {
            $this->validationFailures[] = "App Number is missing";
        }

        if ( trim ($this->app_uid) === "" )
        {
            $this->validationFailures[] = "App Id is missing";
        }

        if ( trim ($this->pro_uid) === "" )
        {
            $this->validationFailures[] = "Workflow Id is missing";
        }

        if ( trim ($this->tas_uid) === "" )
        {
            $this->validationFailures[] = "Task Id is missing";
        }

        if ( trim ($this->requestId) === "" )
        {
            $this->validationFailures[] = "Request Id is missing";
        }

        return count ($this->validationFailures) > 0 ? false : true;
    }

    private function getObjectIfExists ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.workflow_data", ["workflow_data", "audit_data"], ["object_id" => $this->app_uid]);

        if ( $result === false || !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     PropelException
     * @see        save()
     */
    public function save ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $workflowData = $this->getObjectIfExists ();

        if ( $workflowData !== false )
        {
            $workflowObject = json_decode ($workflowData['0']['workflow_data'], true);
        }

        $workflowObject['elements'][$this->app_number]['request_id'] = $this->requestId;
        $workflowObject['elements'][$this->app_number]['current_step'] = $this->tas_uid;
        $workflowObject['elements'][$this->app_number]['status'] = $this->status;

        if ( !isset ($workflowObject['elements'][$this->app_number]['workflow_id']) )
        {
            $workflowObject['elements'][$this->app_number]['workflow_id'] = $this->pro_uid;
        }

        $workflowObject['elements'][$this->app_number]['hasEvent'] = $this->hasEvent;

        if ( $workflowData === false )
        {
            $id = $this->objMysql->_insert ("workflow.workflow_data", [
                "APP_THREAD_INDEX" => $this->app_thread_index,
                "workflow_data" => json_encode ($workflowObject),
                "object_id" => $this->app_uid,
                    ]
            );

            return $id;
        }
        
        $this->objMysql->_update ("workflow.workflow_data", [
            "workflow_data" => json_encode ($workflowObject),
                ], ["object_id" => $this->app_uid]
        );
               
    }

}
