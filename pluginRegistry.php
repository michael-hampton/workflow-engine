<?php
class pluginRegistry
{
    
    private $objTask;
    private $objUser;
    private $objMike;
    private $functionName;
    
    public function __construct(Users $objUser, $objMike, Task $objTask)
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
    
    public function load($type)
    {
        $type = trim($type);
        
        if(!isset($this->arrPlugins[$type])) {
            return false;
        }
        
        //$this->arrPlugins[$type]();
        //return $this->arrPlugins[$type];
        
        $this->functionName = $this->arrPlugins[$type];
    }
    
    public function execute()
    {
        if(trim($this->functionName) === '') {
            return false;
        }
        
        this->functionName();
    }
    
    public function sendComment()
    {
    }
    
    public function uploadDocument()
    {
    }
    
    public function startCase()
    {
    }
    
    
    public function completeCase()
    {
    }
}
