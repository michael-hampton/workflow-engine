<?php

class DocumentVersion extends BaseDocumentVersion
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getFileByName ($name)
    {
        $result = $this->objMysql->_select ("APP_DOCUMENT", [], ["filename" => $name]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
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

            $result = $this->objMysql->_query ("SELECT MAX(document_version) AS VERSION FROM task_manager.APP_DOCUMENT WHERE filename = ?", [$filename]);

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

            $result = $this->objMysql->_query ("SELECT MAX(document_version) AS VERSION FROM task_manager.APP_DOCUMENT WHERE document_id = ?", [$docId]);

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
    public function retrieveByPK ($app_doc_uid, $doc_version)
    {
        $result = $this->objMysql->_select ("task_manager.APP_DOCUMENT", [], ["id" => $app_doc_uid, "document_version" => $doc_version]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return null;
        }

        $objDocumentVersion = new DocumentVersion();

        $objDocumentVersion->setAppDocCreateDate ($result[0]['date_created']);
        $objDocumentVersion->setDocUid ($result[0]['document_id']);
        $objDocumentVersion->setAppDocUid ($result[0]['id']);
        $objDocumentVersion->setAppDocFilename ($result[0]['filename']);
        $objDocumentVersion->setUsrUid ($result[0]['user_id']);
        $objDocumentVersion->setAppDocType ($result[0]['document_type']);
        $objDocumentVersion->setDocVersion ($result[0]['document_version']);
        $objDocumentVersion->setAppUid ($result[0]['app_id']);

        return $objDocumentVersion;
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

        $result = $this->objMysql->_select ("task_manager.APP_DOCUMENT", [], $arrWhere);

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
        $this->setUsrUid ($result[0]['user_id']);
        $this->setAppDocType ($result[0]['document_type']);
        $this->setDocVersion ($result[0]['document_version']);

        return $this;
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
            $objVersioning->setFolderUid ($aData['folderId']);
            $objVersioning->setAppDocTitle ($aData['document_title']);
            $objVersioning->setAppDocComment ($aData['document_comment']);
            $objVersioning->setDelIndex ($aData['del_index']);
           

            $docType = isset ($aData['document_type']) ? $aData['document_type'] : '';

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
                $id = $objVersioning->save ();

                return $id;
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
            $oAppDocument = $this->retrieveByPK ($sAppDocUid, $iVersion);

            if ( !is_null ($oAppDocument) )
            {
                $arrayDocumentsToDelete = array();
                if ( $oAppDocument->getAppDocType () == "INPUT" )
                {
                    $results = $this->objMysql->_select ("task_manager.APP_DOCUMENT", [], ["id" => $sAppDocUid]);

                    foreach ($results as $result) {
                        $arrayDocumentsToDelete[] = array('sAppDocUid' => $result['id'], 'iVersion' => $result['document_version']);
                    }
                }
                else
                {
                    $arrayDocumentsToDelete[] = array('sAppDocUid' => $sAppDocUid, 'iVersion' => $iVersion);
                }

                foreach ($arrayDocumentsToDelete as $docToDelete) {
                    $aFields = array('APP_DOC_UID' => $docToDelete['sAppDocUid'], 'DOC_VERSION' => $docToDelete['iVersion'], 'APP_DOC_STATUS' => 'DELETED');
                    $oAppDocument->update ($aFields);
                }
            }
            else
            {
                throw new Exception ("Document Doesnt exist");
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Update the application document registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function update ($aData)
    {
        try {
            $oAppDocument = $this->retrieveByPK ($aData['APP_DOC_UID'], $aData['DOC_VERSION']);

            if ( !is_null ($oAppDocument) )
            {
                /* ----------------------------------********--------------------------------- */
                $oAppDocument->loadObject ($aData);
                if ( $oAppDocument->validate () )
                {

                    $oAppDocument->save ();
                    return true;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oAppDocument->getValidationFailures ();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function getDocumentsForFolder ($folderUid)
    {
        $result = $this->objMysql->_select ("APP_DOCUMENT", [], ["FOLDER_UID" => $folderUid]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
    }

}
