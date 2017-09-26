<?php
class pluginRegistry
{
    private $arrPlugins = array
    (
        "EF_COMMENT" => "",
        "EF_UPLOAD_DOCUMENT" => "",
        "EF_COMPLETE_CASE" => "",
        "EF_REASSIGN" => ""
    );
    
    public function load($type)
    {
        $type = trim($type);
        
        if(!isset($this->arrPlugins[$type])) {
            return false;
        }
        
        return $this->arrPlugins[$type];
    }
}
