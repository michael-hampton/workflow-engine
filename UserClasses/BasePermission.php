<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BasePermission
 *
 * @author michael.hampton
 */
abstract class BasePermission implements Persistent
{

    private $objMysql;
    private $permName;
    private $status;
    protected $permId;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    private $validationFailures = array();
    private $arrFieldMapping = array(
        "perm_name" => array("accessor" => "getPermName", "mutator" => "setPermName", "required" => true),
    );
    public $arrUser = array();

    /**
     * 
     * @param type $arrUser
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
    }

    public function getPermName ()
    {
        return $this->permName;
    }

    public function getStatus ()
    {
        return $this->status;
    }

    public function setPermName ($permName)
    {
        $this->permName = $permName;
    }

    public function setStatus ($status)
    {
        $this->status = $status;
    }
    
    public function getPermId ()
    {
        return $this->permId;
    }

    public function setPermId ($permId)
    {
        $this->permId = $permId;
    }

    
    /**
     * 
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrFieldMapping as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $arrField['accessor'];

                if ( $this->$accessor () == "" )
                {
                    $this->validationFailures[] = $fieldName;
                    $errorCount++;
                }
            }
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }
    
    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    public function setValidationFailures ($validationFailures)
    {
        $this->validationFailures = $validationFailures;
    }

    
    public function save()
    {
        $result = $this->objMysql->_insert("user_management.permissions", array("perm_name" => $this->permName));
        
        if(!$result) {
            throw new Exception("FAILED TO SAVE PERMISSIONS");
        }
        return $result;
    }


}
