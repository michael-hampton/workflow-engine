<?php
class Diagram
{
    private $collectionId;
    private $workflowId;
    
    /**
     * 
     * @param type $collectionId
     * @param type $workflowId
     */
    public function __construct ($collectionId, $workflowId)
    {
        $this->collectionId = $collectionId;
        $this->workflowId = $workflowId;
    }
    
    /**
     * 
     * @param type $arrData
     */
    public function saveDiagram($arrData)
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/core/public/BPMNdata/". $this->collectionId."-".$this->workflowId.".json";
        file_put_contents ($file, json_encode ($arrData));
    }
}

