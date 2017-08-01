<?php

abstract class BaseProcess implements Persistent
{

    private $objMysql;

    /**
     * The value for the pro_title field.
     * @var        string
     */
    protected $workflowName;

    /**
     * The value for the pro_category field.
     * @var        string
     */
    protected $requestId;
    protected $systemId;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $id;

    /**
     * The value for the pro_description field.
     * @var        string
     */
    protected $description;

    /**
     * The value for the pro_create_date field.
     * @var        int
     */
    protected $ProCreateDate;

    /**
     * @var
     */
    protected $ProStatus;

    /**
     * @var
     */
    protected $ProCreateUser;

    /**
     * @var
     */
    protected $parentId;

    /**
     * @var
     */
    protected $categoryName;

    /**
     * @var
     */
    protected $status;

    /**
     * The value for the pro_subprocess field.
     * @var        int
     */
    protected $pro_subprocess = 0;

    /**
     * The value for the pro_tri_create field.
     * @var        string
     */
    protected $pro_tri_create = '';

    /**
     * The value for the pro_show_dynaform field.
     * @var        int
     */
    protected $pro_show_dynaform = 0;

    /**
     * The value for the pro_tri_open field.
     * @var        string
     */
    protected $pro_tri_open = '';

    /**
     * The value for the pro_tri_deleted field.
     * @var        string
     */
    protected $pro_tri_deleted = '';

    /**
     * The value for the pro_tri_canceled field.
     * @var        string
     */
    protected $pro_tri_canceled = '';
    protected $workflowId;

    /**
     * The value for the pro_tri_paused field.
     * @var        string
     */
    protected $pro_tri_paused = '';

    /**
     * The value for the pro_tri_reassigned field.
     * @var        string
     */
    protected $pro_tri_reassigned = '';

    /**
     * The value for the pro_tri_unpaused field.
     * @var        string
     */
    protected $pro_tri_unpaused = '';

    /**
     * The value for the pro_type_process field.
     * @var        string
     */
    protected $pro_type_process = 'PUBLIC';

    /**
     * The value for the pro_sub_category field.
     * @var        string
     */
    protected $pro_sub_category = '';

    /**
     * The value for the pro_dynaforms field.
     * @var        string
     */
    protected $pro_dynaforms;
    public $arrValidationErrors = array();
    private $arrayFieldDefinition = array(
        "PRO_CREATE_USER" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getProCreateUser", "mutator" => "setProCreateUser"),
        "PRO_CATEGORY" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getRequestId", "mutator" => "setRequestId"),
        "PRO_TITLE" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getWorkflowName", "mutator" => "setWorkflowName"),
        "PRO_DESCRIPTION" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getDescription", "mutator" => "setDescription"),
        "PRO_DATE_CREATED" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getProCreateDate", "mutator" => "setProCreateDate")
    );

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     *
     * @param type $arrData
     * @return boolean
     */
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

    public function getWorkflowName ()
    {
        return $this->workflowName;
    }

    public function getRequestId ()
    {
        return $this->requestId;
    }

    public function getSystemId ()
    {
        return $this->systemId;
    }

    public function getId ()
    {
        return $this->workflowId;
    }

    public function setId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     * Get the [pro_parent] column value.
     *
     * @return     string
     */
    public function getParentId ()
    {
        return $this->parentId;
    }

    public function setParentId ($parentId)
    {
        $this->parentId = $parentId;
    }

    public function getCategoryName ()
    {
        return $this->categoryName;
    }

    public function setCategoryName ($categoryName)
    {
        $this->categoryName = $categoryName;
    }

    /**
     * Get the [pro_subprocess] column value.
     *
     * @return     int
     */
    public function getProSubprocess ()
    {

        return $this->pro_subprocess;
    }

    /**
     * Get the [pro_status] column value.
     *
     * @return     string
     */
    public function getStatus ()
    {
        return $this->status;
    }

    public function setStatus ($status)
    {
        $this->status = $status;
    }

    /**
     * Get the [pro_tri_create] column value.
     *
     * @return     string
     */
    public function getProTriCreate ()
    {

        return $this->pro_tri_create;
    }

    /**
     * Get the [pro_tri_open] column value.
     *
     * @return     string
     */
    public function getProTriOpen ()
    {

        return $this->pro_tri_open;
    }

    /**
     * Get the [pro_tri_deleted] column value.
     *
     * @return     string
     */
    public function getProTriDeleted ()
    {

        return $this->pro_tri_deleted;
    }

    /**
     * Get the [pro_tri_canceled] column value.
     *
     * @return     string
     */
    public function getProTriCanceled ()
    {

        return $this->pro_tri_canceled;
    }

    /**
     * Get the [pro_tri_paused] column value.
     *
     * @return     string
     */
    public function getProTriPaused ()
    {

        return $this->pro_tri_paused;
    }

    /**
     * Get the [pro_tri_reassigned] column value.
     *
     * @return     string
     */
    public function getProTriReassigned ()
    {

        return $this->pro_tri_reassigned;
    }

    /**
     * Get the [pro_tri_unpaused] column value.
     *
     * @return     string
     */
    public function getProTriUnpaused ()
    {

        return $this->pro_tri_unpaused;
    }

    /**
     * Get the [pro_type_process] column value.
     *
     * @return     string
     */
    public function getProTypeProcess ()
    {

        return $this->pro_type_process;
    }

    /**
     * Get the [pro_show_dynaform] column value.
     *
     * @return     int
     */
    public function getProShowDynaform ()
    {

        return $this->pro_show_dynaform;
    }

    /**
     *
     * @param type $workflowName
     */
    public function setWorkflowName ($workflowName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $workflowName !== null && !is_string ($workflowName) )
        {
            $workflowName = (string) $workflowName;
        }

        $this->workflowName = $workflowName;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * Get the [pro_sub_category] column value.
     *
     * @return     string
     */
    public function getProSubCategory ()
    {

        return $this->pro_sub_category;
    }

    /**
     * Get the [pro_dynaforms] column value.
     *
     * @return     string
     */
    public function getProDynaforms ()
    {

        return $this->pro_dynaforms;
    }

    /**
     * Set the value of [pro_parent] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProParent ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_parent !== $v || $v === '0' )
        {
            $this->pro_parent = $v;
        }
    }

    /**
     *
     * @param type $description
     */
    public function setDescription ($description)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $description !== null && !is_string ($description) )
        {
            $description = (string) $description;
        }

        $this->description = $description;
    }

    /**
     * Set the value of [pro_id] column.
     *
     * @param      int $requestId new value
     * @return     void
     */
    public function setRequestId ($requestId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $requestId !== null && !is_int ($requestId) && is_numeric ($requestId) )
        {
            $requestId = (int) $requestId;
        }
        if ( $this->requestId !== $requestId )
        {
            $this->requestId = $requestId;
        }
    }

    /**
     *
     * @param type $systemId
     */
    public function setSystemId ($systemId)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $systemId !== null && !is_int ($systemId) && is_numeric ($systemId) )
        {
            $systemId = (int) $systemId;
        }
        if ( $this->systemId !== $systemId )
        {
            $this->systemId = $systemId;
        }
    }

    /**
     *
     * @return type
     */
    public function getProCreateDate ()
    {
        return $this->ProCreateDate;
    }

    /**
     *
     * @param type $ProCreateDate
     */
    public function setProCreateDate ($ProCreateDate)
    {
        $this->ProCreateDate = $ProCreateDate;
    }

    public function getProStatus ()
    {
        return $this->ProStatus;
    }

    /**
     * Set the value of [pro_subprocess] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setProSubprocess ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->pro_subprocess !== $v || $v === 0 )
        {
            $this->pro_subprocess = $v;
        }
    }

    /**
     * Set the value of [pro_tri_create] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriCreate ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_create !== $v || $v === '' )
        {
            $this->pro_tri_create = $v;
        }
    }

    /**
     * Set the value of [pro_tri_open] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriOpen ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_open !== $v || $v === '' )
        {
            $this->pro_tri_open = $v;
        }
    }

    /**
     * Set the value of [pro_tri_deleted] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriDeleted ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_deleted !== $v || $v === '' )
        {
            $this->pro_tri_deleted = $v;
        }
    }

    /**
     * Set the value of [pro_tri_canceled] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriCanceled ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_canceled !== $v || $v === '' )
        {
            $this->pro_tri_canceled = $v;
        }
    }

    /**
     * Set the value of [pro_tri_paused] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriPaused ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_paused !== $v || $v === '' )
        {
            $this->pro_tri_paused = $v;
        }
    }

    /**
     * Set the value of [pro_tri_reassigned] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriReassigned ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_reassigned !== $v || $v === '' )
        {
            $this->pro_tri_reassigned = $v;
        }
    }

    /**
     * Set the value of [pro_tri_unpaused] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTriUnpaused ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_tri_unpaused !== $v || $v === '' )
        {
            $this->pro_tri_unpaused = $v;
        }
    }

    /**
     * Set the value of [pro_type_process] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProTypeProcess ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_type_process !== $v || $v === 'PUBLIC' )
        {
            $this->pro_type_process = $v;
        }
    }

    /**
     * Set the value of [pro_show_dynaform] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setProShowDynaform ($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->pro_show_dynaform !== $v || $v === 0 )
        {
            $this->pro_show_dynaform = $v;
        }
    }

    /**
     * Set the value of [pro_dynaforms] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setProDynaforms ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->pro_dynaforms !== $v )
        {
            $this->pro_dynaforms = $v;
        }
    }

    /**
     * Set the value of [pro_status] column.
     *
     * @param      int $ProStatus new value
     * @return     void
     */
    public function setProStatus ($ProStatus)
    {
        if ( $ProStatus !== null && !is_int ($ProStatus) && is_numeric ($ProStatus) )
        {
            $ProStatus = (int) $ProStatus;
        }
        if ( $this->ProStatus !== $ProStatus )
        {
            $this->ProStatus = $ProStatus;
        }
    }

    public function getProCreateUser ()
    {
        return $this->ProCreateUser;
    }

    /**
     *
     * @param type $ProCreateUser
     */
    public function setProCreateUser ($ProCreateUser)
    {
        if ( $ProCreateUser !== null && !is_int ($ProCreateUser) && is_numeric ($ProCreateUser) )
        {
            $ProCreateUser = (int) $ProCreateUser;
        }
        if ( $this->ProCreateUser !== $ProCreateUser )
        {
            $this->ProCreateUser = $ProCreateUser;
        }
    }

    public function getArrValidationErrors ()
    {
        return $this->arrValidationErrors;
    }

    /**
     *
     * @param type $arrValidationErrors
     */
    public function setArrValidationErrors ($arrValidationErrors)
    {
        $this->arrValidationErrors = $arrValidationErrors;
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
            return FALSE;
        }

        return TRUE;
    }

    public function save ()
    {

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->workflowId) === "" )
        {
            $id = $this->objMysql->_insert (
                    "workflow.workflows", array(
                "workflow_name" => $this->workflowName,
                "system_id" => 1,
                "request_id" => $this->requestId,
                "created_by" => $this->ProCreateUser,
                "date_created" => $this->ProCreateDate,
                "description" => $this->description,
                "PRO_SUBPROCESS" => $this->pro_subprocess,
                "PRO_TRI_CREATE" => $this->pro_tri_create,
                "PRO_TRI_OPEN" => $this->pro_tri_open,
                "PRO_TRI_DELETED" => $this->pro_tri_deleted,
                "PRO_TRI_CANCELED" => $this->pro_tri_canceled,
                "PRO_TRI_PAUSED" => $this->pro_tri_paused,
                "PRO_TRI_REASSIGNED" => $this->pro_tri_reassigned,
                "PRO_TRI_UNPAUSED" => $this->pro_tri_unpaused,
                "PRO_TYPE_PROCESS" => $this->pro_type_process,
                "PRO_DYNAFORMS" => $this->pro_dynaforms
                    )
            );

            return $id;
        }
        else
        {
            $this->objMysql->_update (
                    "workflow.workflows", array(
                "workflow_name" => $this->workflowName,
                "system_id" => 1,
                "request_id" => $this->requestId,
                "created_by" => $this->ProCreateUser,
                "date_created" => $this->ProCreateDate,
                "description" => $this->description,
                "PRO_SUBPROCESS" => $this->pro_subprocess,
                "PRO_TRI_CREATE" => $this->pro_tri_create,
                "PRO_TRI_OPEN" => $this->pro_tri_open,
                "PRO_TRI_DELETED" => $this->pro_tri_deleted,
                "PRO_TRI_CANCELED" => $this->pro_tri_canceled,
                "PRO_TRI_PAUSED" => $this->pro_tri_paused,
                "PRO_TRI_REASSIGNED" => $this->pro_tri_reassigned,
                "PRO_TRI_UNPAUSED" => $this->pro_tri_unpaused,
                "PRO_TYPE_PROCESS" => $this->pro_type_process,
                "PRO_DYNAFORMS" => $this->pro_dynaforms
                    ), ["workflow_id" => $this->workflowId]
            );
        }
    }

}
