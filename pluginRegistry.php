<?php

class pluginRegistry
{

    private $objTask;
    private $objUser;
    private $objMike;
    private $functionName;

    public function __construct (Users $objUser, $objMike, Task $objTask)
    {
        $this->objTask = $objTask;
        $this->objUser = $objUser;
        $this->objMike = $objMike;
    }

    private $arrPlugins = array
        (
        "EF_COMMENT" => "sendComment",
        "EF_UPLOAD_DOCUMENT" => "uploadDocument",
        "EF_COMPLETE_CASE" => "completeCase",
        "EF_REASSIGN" => "reassign",
        "EF_START_CASE" => "startCase"
    );

    public function load ($type)
    {
        $type = trim ($type);

        if ( !isset ($this->arrPlugins[$type]) )
        {
            return false;
        }

        //$this->arrPlugins[$type]();
        //return $this->arrPlugins[$type];

        $this->functionName = $this->arrPlugins[$type];
    }

    public function execute ($arrData)
    {
        if ( trim ($this->functionName) === '' )
        {
            return false;
        }
        if ( is_callable (array($this, $this->functionName)) )
        {
            return call_user_func_array (array($this, $this->functionName), array($arrData));
        }
        else
        {
            throw new Exception ('Invalid function used');
        }
    }

    public function sendComment (array $arrData = array())
    {

    }

    public function uploadDocument (array $arrData = array())
    {
        
    }

    public function startCase (array $arrData = array())
    {
        
    }

    public function completeCase (array $arrData = array())
    {
        
    }

}
