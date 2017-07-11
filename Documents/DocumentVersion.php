<?php

class DocumentVersion extends BaseDocumentVersion
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get last Document Version based on filename
     *
     * @params $filename
     * @return integer
     *
     */
    public function getLastDocVersionByFilename ($filename)
    {
        try {

            $result = $this->objMysql->_query ("SELECT MAX(document_version) AS VERSION FROM task_manager.document_version WHERE filename = ?", [$filename]);

            if ( isset ($result[0]['VERSION']) && trim ($result[0]['VERSION']) != "" )
            {
                return $result[0]['VERSION'];
            }
            else
            {
                return 0;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Get last Document Version based on Doc UID
     *
     * @params $docId
     * @return integer
     *
     */
    public function getLastDocVersion ($docId)
    {
        try {

            $result = $this->objMysql->_query ("SELECT MAX(document_version) AS VERSION FROM task_manager.document_version WHERE document_id = ?", [$docId]);

            if ( isset ($result[0]['VERSION']) && trim ($result[0]['VERSION']) != "" )
            {
                return $result[0]['VERSION'];
            }
            else
            {
                return 0;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function load ($documentId, $documentVersion, $id = null, $returnArray = true)
    {
        $arrWhere = [];

        if ( $id !== null )
        {
            $arrWhere['id'] = $id;
        }
        else
        {
            $arrWhere['document_id'] = $documentId;
            $arrWhere['document_version'] = $documentVersion;
        }

        $result = $this->objMysql->_select ("task_manager.document_version", [], $arrWhere);

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            if ( $returnArray === true )
            {
                return $result[0];
            }

            $this->setAppDocCreateDate ($result[0]['date_created']);
            $this->setDocUid ($result[0]['document_id']);
            $this->setAppDocUid ($result[0]['id']);
            $this->setAppDocFilename ($result[0]['filename']);

            return $this;
        }

        return false;
    }

    /**
     * Create the application document registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function create ($aData)
    {
        try {
            $docVersion = $this->getLastDocVersionByFilename ($aData['filename']);
            $docVersion += 1;

            $objVersioning = new DocumentVersion();
            $objVersioning->setDocVersion ($docVersion);
            $objVersioning->setUsrUid ($_SESSION['user']['usrid']);
            $objVersioning->setDocUid ($aData['document_id']);
            $objVersioning->setAppDocCreateDate (date ("Y-m-d H:i:s"));
            $objVersioning->setAppDocFilename ($aData['filename']);

            $docType = isset ($aData['document_type']) ? $aData['document_type'] : 'INPUT';

            if ( $docType === "OUTPUT" )
            {
                $o = new OutputDocument();
                $oOutputDocument = $o->retrieveByPk ($aData['document_id']);

                if ( !$oOutputDocument->getOutDocVersioning () )
                {
                    throw (new Exception ('The Output document has not versioning enabled!'));
                }
            }

            if ( $docType === "INPUT" )
            {
                $o = new \BusinessModel\InputDocument(null);
                $oInputDocument = $o->getInputDocument ($aData['document_id']);
                
                
                if ( !$oInputDocument[$aData['document_id']]->getVersioning () )
                {
                    throw (new Exception ('This Input document does not have the versioning enabled, for this reason this operation cannot be completed'));
                }
            }

            $objVersioning->setAppDocType ($docType);

            if ( $objVersioning->validate () )
            {
                $objVersioning->save ();
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $objVersioning->getValidationFailures ();
                foreach ($aValidationFailures as $strValidationFailure) {
                    $sMessage .= $strValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $ex) {
            throw ($ex);
        }
    }

}
