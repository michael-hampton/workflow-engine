<?php

/**
 * Class InputDocument
 */
class InputDocuments extends BaseInputDocument
{

    private $stepId;
    private $documentId;
    private $objMysql;

    public function __construct ($stepId)
    {
        $this->stepId = $stepId;
        $this->objMysql = new Mysql2();
        parent::__construct ();
    }

    public function create ($arrData)
    {
        $this->loadObject ($arrData);

        try {
            if ( $this->validate () )
            {
                $documentId = $this->save ();

                return $documentId;
            }
            else
            {
                $msg = '';
                foreach ($this->getArrValidationErrors () as $strMessage) {
                    $msg .= $strMessage . "<br/>";
                }
                throw (new Exception ('The row cannot be created! ' . $msg));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update ($documentId, $arrData)
    {
        try {

            $this->setId ($documentId);
            $this->loadObject ($arrData);

            if ( $this->validate () )
            {
                $this->doUpdate ();
            }
            else
            {
                $msg = '';
                foreach ($this->getArrValidationErrors () as $strMessage) {
                    $msg .= $strMessage . "<br/>";
                }
                throw (new Exception ('The row cannot be created! ' . $msg));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete ($inputDocumentUid)
    {
        $this->setId($inputDocumentUid);
        $this->remove();
    }

    public function saveStepDocument ($documentId)
    {
        $this->objMysql->_insert ("workflow.step_document", array("step_id" => $this->stepId, "document_id" => $documentId));
    }

}
