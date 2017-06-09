<?php

class StepDocument
{

    private $objMysql;
    private $stepId;
    private $documentId;
    private $documentType;

    /**
     * 
     * @param type $stepId
     */
    public function __construct ($stepId = null)
    {

        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }

        $this->objMysql = new Mysql2();
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function getDocumentId ()
    {
        return $this->documentId;
    }

    public function getDocumentType ()
    {
        return $this->documentType;
    }

    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    public function setDocumentId ($documentId)
    {
        $this->documentId = $documentId;
    }

    public function setDocumentType ($documentType)
    {
        $this->documentType = $documentType;
    }

    public function validate ()
    {
        $intErrorCount = 0;

        if ( $this->stepId == "" )
        {
            $intErrorCount++;
        }

        if ( $this->documentId == "" )
        {
            $intErrorCount++;
        }

        if ( $this->documentType == "" )
        {
            $intErrorCount++;
        }

        if ( $intErrorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function save ()
    {
        $this->objMysql->_insert ("workflow.step_document", array("step_id" => $this->stepId, "document_id" => $this->documentId, "document_type" => $this->documentType));
    }
    
    public function delete()
    {
        $this->objMysql->_delete("workflow.step_document", array("step_id" => $this->stepId, "document_id" => $this->documentId, "document_type" => $this->documentType));
    }

}
