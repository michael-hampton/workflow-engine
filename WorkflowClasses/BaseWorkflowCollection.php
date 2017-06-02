<?php
abstract class BaseWorkflowCollection
{
  
    private $deptId;
    private $name;
    private $description;
    private $requestId;
    private $objMike;
    private $parentId;
    private $systemId;
    private $objMysql;
    private $arrValidationErrors;
    private $new;  

 private $arrFieldMapping = array(
        "description" => array("accessor" => "getDescription", "mutator" => "setDescription", "required" => false),
        "request_type" => array("accessor" => "getName", "mutator" => "setName", "required" => true),
        "system_id" => array("accessor" => "getSystemId", "mutator" => "setSystemId", "required" => true),
        "dept_id" => array("accessor" => "getDeptId", "mutator" => "setDeptId", "required" => true)
    );
    public $arrCollection = array();



public function loadObject ($arrData)
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
    }


/**
     * @return mixed
     */
    public function getDeptId ()
    {
        return $this->deptId;
    }
    /**
     * @param mixed $deptId
     */
    public function setDeptId ($deptId)
    {
        $this->arrCollection['dept_id'] = $deptId;
        $this->deptId = $deptId;
    }
    /**
     * @return mixed
     */
    public function getName ()
    {
        return $this->name;
    }
    /**
     * @param mixed $name
     */
    public function setName ($name)
    {
        $this->name = $name;
        $this->arrCollection['request_type'] = $name;
    }
    public function getSystemId ()
    {
        return $this->systemId;
    }
    public function setSystemId ($systemId)
    {
        $this->systemId = $systemId;
        $this->arrCollection['system_id'] = $systemId;
    }
    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }
    /**
     * @param mixed $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
        $this->arrCollection['description'] = $description;
    }
    /**
     * @return mixed
     */
    public function getRequestId ()
    {
        return $this->requestId;
    }
    public function getNew ()
    {
        return $this->new;
    }
    public function setNew ($new)
    {
        $this->new = $new;
    }
    /**
     * @param mixed $requestId
     */
    public function setRequestId ($requestId)
    {
        $this->requestId = $requestId;
    }
    /**
     * 
     * @return type
     */
    function getParentId ()
    {
        return $this->parentId;
    }
    /**
     * 
     * @param type $parentId
     */
    function setParentId ($parentId)
    {
        $this->parentId = $parentId;
    }


 public function validate ()
    {
        $errorCount = 0;
        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                if ( !isset ($this->arrCollection[$fieldName]) || trim ($this->arrCollection[$fieldName]) == "" )
                {
                    $this->arrValidationErrors[] = $fieldName;
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
        if ( $this->new === true )
        {
            $this->objMysql->_insert ("workflow.request_types", $this->arrCollection);
        }
        else
        {
            
        }
    }
   
    public function delete ()
    {
        if ( !is_numeric ($this->requestId) )
        {
            throw new Exception ("REQUEST ID HAS NOT BEEN SET");
        }
        $this->objMysql->_delete ("workflow.request_types", array("request_id" => $this->requestId));
    }

}
