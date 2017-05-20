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
     * Get last Document Version based on App UID
     *
     * @param s $sAppDocUid
     * @return integer
     *
     */
    public function getLastDocVersion ($appUID)
    {
        try {

            $result = $this->objMysql->_query ("SELECT MAX(document_version) AS VERSION FROM task_manager.document_version WHERE app_id = ?", [$appUID]);

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
            $objVersioning->setAppDocType ("INPUT");

            if ( $objVersioning->validate () )
            {
                $objVersioning->save();
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
