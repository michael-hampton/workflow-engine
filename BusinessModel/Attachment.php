<?php

namespace BusinessModel;

class Attachment
{

    private $stepId;
    private $projectId;
    public $id;
    public $object = array();
    private $documentId;
    private $objMysql;
    private $arrayValidation;
    private $table = "task_manager.attachments";

    public function __construct ()
    {

        $this->objMysql = new \Mysql2();
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function loadObject ($arrData, \Users $objUser)
    {
        
        if ( isset ($arrData['files']) )
        {
            if ( isset ($arrData['step']) && !$arrData['step'] instanceof \WorkflowStep )
            {
                throw new \Exception ("Invalid step object given.");
            }

            if ( isset ($arrData['file_type']) )
            {
                $this->documentId = $arrData['file_type'];
                $this->stepId = $arrData['step']->getStepId ();
                $this->projectId = $arrData['source_id'];

                $result = $this->uploadDocument ($arrData['files'], $arrData, $objUser);

                return $result;
            }
            else
            {
                
                $objVersioning = new \DocumentVersion();
                $objVersioning->create (array("filename" => $arrData['filename'], "document_id" => 1, "app_uid" => $this->projectId), $objUser);
                $this->upload ($arrData['source_id'], $arrResponse['prf_uid']);
            }
        }
    }

    /**
     * 
     * @param type $prjUid
     * @param type $prfUid
     * @return type
     * @throws Exception
     */
    public function upload ($prjUid, $prfUid)
    {
        try {

            $aRow = $this->retrieveByPK ($prfUid);
            $path = $aRow[0]['file_destination'];

            if ( $path == '' )
            {
                throw new \Exception ('UPLOADING_FILE_PROBLEM');
            }

            $objFileUpload = new \BusinessModel\FileUpload();

            if ( isset ($_FILES['fileUpload']) )
            {
                foreach ($_FILES['fileUpload']['name'] as $key => $name) {
                    $objFileUpload->doUpload ($name, $path, $_FILES['fileUpload']['error'][$key], $_FILES['fileUpload']['tmp_name'][$key]);
                }
            }
            else
            {
                $objFileUpload->doUpload ($_FILES['file']['name'], $path, $_FILES['file']['error'], $_FILES['file']['tmp_name']);
            }

            return array();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Function for uploading Iput Documents for a step
     * @param type $arrFiles
     * @return boolean
     * @throws Exception
     */
    private function uploadDocument ($arrFiles, $arrData, \Users $objUser)
    {
        $stepDocument = new \BusinessModel\InputDocument (new \Task ($this->stepId));

        if ( !is_numeric ($this->documentId) )
        {
            throw new \Exception ("Invalid document id given");
        }

        $objStepDocument = $stepDocument->getInputDocument ($this->documentId);


        if ( !empty ($objStepDocument) )
        {

            $unit = $objStepDocument[$this->documentId]->getFilesizeUnit ();
            $extensions = $objStepDocument[$this->documentId]->getFileType ();
            $arrExtensions = explode (",", $extensions);

            foreach ($arrExtensions as $key => $extension) {
                $arrExtensions[$key] = trim(str_replace (".", "", $extension));
            }

            $inputName = $objStepDocument[$this->documentId]->getTitle (); // input document name
            $intCount = 0;
            $versioning = $objStepDocument[$this->documentId]->getVersioning ();
            $dir2 = $objStepDocument[$this->documentId]->getDestinationPath ();
            $maxFileSize = $objStepDocument[$this->documentId]->getMaxFileSize ();
        }

        $arrUploadedFiles = array();

        foreach ($arrFiles['fileUpload']['name'] as $key => $name) {
            $size = $arrFiles['fileUpload']['size'][$key];
            $file_tmp = $arrFiles['fileUpload']['tmp_name'][$key];
            $arrName = explode ('.', $name);
            $file_ext = strtolower (end ($arrName));

            $filename = $this->projectId . "_" . $intCount . date ("YmdHis") . "." . $file_ext;
            $dir = UPLOADS_DIR . $dir2 . "/";
            $destination = $dir . $filename;

            $actualSize = $maxFileSize * (($unit == "MB") ? 1024 * 1024 : 1024); //Bytes

            if ( !empty ($objStepDocument) )
            {   
                if ( $actualSize > 0 && $size > 0 )
                {
                    if ( $size > $actualSize )
                    {
                        throw new Exception ("ID_SIZE_VERY_LARGE_PERMITTED");
                    }
                }

                if ( !in_array (trim ($file_ext), $arrExtensions) )
                {
                    $this->arrayValidation[] = "extension not allowed, please choose a JPEG or PNG file.";
                }

                $filename = $inputName . "_" . $this->projectId . "_" . $intCount . "." . $file_ext;
                $dir = UPLOADS_DIR . $dir2 . "/";
                $destination = $dir . $filename;

                $objVersioning = new \DocumentVersion();
                $originalFilename = $filename;
                $version = $objVersioning->getLastDocVersionByFilename ($filename);
                $version += 1;


                // check if file exists if it does check versioning if no versioning dont do upload
                if ( file_exists ($destination) )
                {
                    if ( $versioning == 0 )
                    {
                        $this->arrayValidation[] = "Cant overwrite file that already exists. no versioning allowed";
                    }
                    else
                    {
                        $filename = $inputName . "_" . $this->projectId . "_" . $version . "." . $file_ext;
                        $destination = $dir . $filename;
                    }
                }

                if ( !empty ($this->arrayValidation) )
                {
                    return false;
                }

                $objFile = new \BusinessModel\FileUpload();
                $objFile->verifyPath ($dir, TRUE);
            }

            if ( !move_uploaded_file ($file_tmp, $destination) )
            {
                $this->arrayValidation[] = "File Could not be uploaded";
                return false;
            }

            $this->object['file_destination'] = $destination;
            
            // update version
            $objVersioning->create (array("filename" => $originalFilename, "document_id" => $this->documentId, "app_uid" => $this->projectId), $objUser);
            $arrUploadedFiles[] = $arrResponse['prf_uid'];

            $intCount++;
        }

        return $arrUploadedFiles;
    }

    public function getArrayValidation ()
    {
        return $this->arrayValidation;
    }

    /**
     * 
     * @return type
     */
    public function getAttachment ()
    {
        $objMysql = new \Mysql2();
        return $objMysql->_select ($this->table, array(), array("id" => $this->id));
    }

    /**
     * 
     * @param type $sourceId
     * @return type
     */
    public function getAllAttachments ($sourceId)
    {
        $objMysql = new \Mysql2();
        $arrAttachments = $objMysql->_select ("task_manager.attachments", array(), array("source_id" => $sourceId));

        $aFields = array();

        foreach ($arrAttachments as $arrAttachment) {

            $arrExploded = explode ("_", $arrAttachment['filename']);
            $version = substr (end ($arrExploded), 0, strpos (end ($arrExploded), "."));

            $version = $version == 0 ? 1 : $version;

            $filePath = str_replace ("C:/xampp/htdocs", "", $arrAttachment['file_destination']);
            $filePath = str_replace ($arrAttachment['filename'], "", $filePath);

            $objProcessFiles = new \ProcessFile();
            $objProcessFiles->setFileType ($arrAttachment['file_type']);
            $objProcessFiles->setId ($arrAttachment['id']);
            $objProcessFiles->setPrfCreateDate ($arrAttachment['date_uploaded']);
            $objProcessFiles->setPrfFielname ($arrAttachment['filename']);
            $objProcessFiles->setUsrUid ($arrAttachment['uploaded_by']);
            $objProcessFiles->setPrfPath ($arrAttachment['file_destination']);
            $objProcessFiles->setDownloadPath ($filePath);

            $aFields[] = $objProcessFiles;
        }

        return $aFields;
    }

    /**
     * 
     * @param type $bytes
     * @param type $type
     * @return type
     */
    function formatSizeUnits ($bytes, $type)
    {
        //$GB = number_format ($bytes / 1073741824, 2);

        $MB = number_format ($bytes / 1048576, 2);

        $KB = number_format ($bytes / 1024, 2);

        if ( $type == "KB" )
        {
            return $KB;
        }
        else
        {
            return $MB;
        }
    }

    /**
     * @param $aData
     * @throws Exception
     */
    public function updateProcessFilesManagerInDb ($aData)
    {
        try {
            //update database
            if ( $this->existsProcessFile ($aData['prf_uid']) )
            {
                $oProcessFiles = new \ProcessFile();
                $sDate = date ('Y-m-d H:i:s');
                $oProcessFiles->setPrfUpdateDate ($sDate);
                $oProcessFiles->setProUid ($aData['PRO_UID']);
                $oProcessFiles->setPrfPath ($aData['PRF_PATH']);
                $oProcessFiles->setId ($aData['prf_uid']);
                $oProcessFiles->save ();
            }
            else
            {
                //$this->addProcessFilesManagerInDb($aData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    private function retrieveByPK ($id)
    {
        $result = $this->objMysql->_select ("task_manager.attachments", array(), array("id" => $id));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }

        return [];
    }

    /**
     * 
     * @param type $prfUid
     * @return type
     * @throws Exception
     */
    public function existsProcessFile ($prfUid)
    {
        try {
            $obj = $this->retrieveByPK ($prfUid);
            return (!empty ($obj)) ? true : false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $sProcessUID
     * @param type $prfUid
     * @throws type
     * @throws Exception
     */
    public function deleteProcessFilesManager ($sProcessUID, $prfUid)
    {
        try {
            $path = '';

            $result = $this->retrieveByPK ($prfUid);

            if ( empty ($result) )
            {
                throw new \Exception ('Cannot find record.');
            }

            $path = $result[0]['file_destination'];

            if ( $path == '' )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            if ( file_exists ($path) && !is_dir ($path) )
            {
                unlink ($path);
            }

            $processFiles = new \ProcessFile();
            $processFiles->setId ($prfUid);
            $processFiles->delete ();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
