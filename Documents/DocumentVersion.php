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
    
        /**
     * Retrieve object using using composite pkey values.
     * @param string $app_doc_uid
       * @param int $doc_version
        * @param      Connection $con
     * @return     AppDocument
     */
    public static function retrieveByPK($app_doc_uid, $doc_version)
    {
        $result = $this->objMysql->_select("task_manager.document_version", [], ["document_version" => $doc_version, "id" => $app_doc_uid]);
        
        return isset($result[0] && !empty($result[0]) ? $result[0] : null;
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

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }


        if ( $returnArray === true )
        {
            return $result[0];
        }

        $this->setAppDocCreateDate ($result[0]['date_created']);
        $this->setDocUid ($result[0]['document_id']);
        $this->setAppDocUid ($result[0]['id']);
        $this->setAppDocFilename ($result[0]['filename']);
        $this->setUsrUid($result[0]['user_id']);
        $this->setAppDocType($result[0]['document_type']);
        $this->setDocVersion($result[0]['document_version']);

        return $this;


        return false;
    }

    /**
     * Create the application document registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function create ($aData, Users $objUser)
    {
        try {
            $docVersion = $this->getLastDocVersionByFilename ($aData['filename']);
            $docVersion += 1;

            $objVersioning = new DocumentVersion();
            $objVersioning->setDocVersion ($docVersion);
            $objVersioning->setUsrUid ($objUser->getUserId ());
            $objVersioning->setDocUid ($aData['document_id']);
            $objVersioning->setAppDocCreateDate (date ("Y-m-d H:i:s"));
            $objVersioning->setAppDocFilename ($aData['filename']);
            $objVersioning->setAppUid ($aData['app_uid']);

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
                $o = new \BusinessModel\InputDocument (null);
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
    
    
    /**
     * Remove the application document registry by changing status only
     * Modified by Hugo Loza hugo@colosa.com
     *
     * @param array $aData
     * @return string
     *
     */
    public function remove ($sAppDocUid, $iVersion = 1)
    {
        try {
            $oAppDocument = AppDocumentPeer::retrieveByPK( $sAppDocUid, $iVersion );
            if (! is_null( $oAppDocument )) {
                $arrayDocumentsToDelete = array ();
                if ($oAppDocument->getAppDocType() == "INPUT") {
                    $oCriteria = new Criteria( 'workflow' );
                    $oCriteria->add( AppDocumentPeer::APP_DOC_UID, $sAppDocUid );
                    $oDataset = AppDocumentPeer::doSelectRS( $oCriteria );
                    $oDataset->setFetchmode( ResultSet::FETCHMODE_ASSOC );
                    $oDataset->next();
                    while ($aRow = $oDataset->getRow()) {
                        $arrayDocumentsToDelete[] = array ('sAppDocUid' => $aRow['APP_DOC_UID'],'iVersion' => $aRow['DOC_VERSION']
                        );
                        $oDataset->next();
                    }
                } else {
                    $arrayDocumentsToDelete[] = array ('sAppDocUid' => $sAppDocUid,'iVersion' => $iVersion
                    );
                }
                foreach ($arrayDocumentsToDelete as $key => $docToDelete) {
                    $aFields = array ('APP_DOC_UID' => $docToDelete['sAppDocUid'],'DOC_VERSION' => $docToDelete['iVersion'],'APP_DOC_STATUS' => 'DELETED'
                    );
                    $oAppDocument->update( $aFields );
                }
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }
}
