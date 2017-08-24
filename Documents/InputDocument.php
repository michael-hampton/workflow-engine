<?php

/**
 * Class InputDocument
 */
class InputDocument extends BaseInputDocument
{

    private $stepId;
    private $objMysql;

    public function __construct ($stepId = null)
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
        $this->setId ($inputDocumentUid);
        $this->remove ();
    }
    
    public function retrieveByPk($pk)
    {
        $result = $this->objMysql->_select("workflow.documents", [], ["id" => $pk]);
        
        if(isset($result[0]) && !empty($result[0])) {
            $objDoc = new InputDocument();
            $objDoc->setId($pk);
            $objDoc->setTitle($result[0]['name']);
            $objDoc->setDescription($result[0]['description']);
            
            return $objDoc;
        }
        
        return FALSE;
    }

    /**
     * verify if Input row specified in [DynUid] exists.
     *
     * @param string $sUid the uid of the Prolication
     */
    public function InputExists ($sUid)
    {
        try {
            $oObj = $this->retrieveByPk ($sUid);
            if ( is_object ($oObj) && get_class ($oObj) == 'InputDocument' )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
