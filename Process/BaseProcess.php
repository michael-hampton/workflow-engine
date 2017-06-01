<?php

abstract class BaseProcess
{

    private $objMysql;
    private $workflowName;
    private $requestId;
    private $systemId;
    private $id;
    private $description;
    private $ProCreateDate;
    private $ProStatus;
    private $ProCreateUser;
    public $arrValidationErrors = array();
    private $arrayFieldDefinition = array(
        "PRO_CREATE_USER" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getProCreateUser", "mutator" => "setProCreateUser"),
        "PRO_CATEGORY" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getRequestId", "mutator" => "setRequestId"),
        "PRO_TITLE" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getWorkflowName", "mutator" => "setWorkflowName"),
        "PRO_DESCRIPTION" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDescription", "mutator" => "setDescription"),
        "PRO_DATE_CREATED" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getProCreateDate", "mutator" => "setProCreateDate")
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
    public function loadObject ($arrData)
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

        $id = $this->objMysql->_insert (
                "workflow.workflows", array(
            "workflow_name" => $this->workflowName,
            "system_id" => 1,
            "request_id" => $this->requestId,
            "created_by" => $this->ProCreateUser,
            "date_created" => $this->ProCreateDate,
            "description" => $this->description
                )
        );

        return $id;
    }

}
