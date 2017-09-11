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
    private $files;
    private $workflowId;

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
            $this->files = $arrData['files'];

            if ( isset ($arrData['step']) && !$arrData['step'] instanceof \WorkflowStep )
            {
                throw new \Exception ("Invalid step object given.");
            }

            if ( isset ($arrData['file_type']) )
            {
                $this->documentId = $arrData['file_type'];
                $this->stepId = $arrData['step']->getStepId ();
                $this->projectId = $arrData['source_id'];
                $this->workflowId = $arrData['step']->getWorkflowId ();

                $result = $this->uploadDocument ($arrData['files'], $objUser);

                return $result;
            }
            else
            {
                $objVersioning = new \DocumentVersion();
                $folderId = (new \AppFolder())->createFromPath (basename (UPLOADS_DIR));
                $id = $objVersioning->create (array("folderId" => $folderId, "filename" => $arrData['filename'], "document_id" => 10000, "app_uid" => $arrData['source_id']), $objUser);
                $this->upload ($id);
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
    public function upload ($prfUid)
    {
        try {
            $aRow = $this->retrieveByPK ($prfUid);

            $path = isset ($aRow[0]['file_destination']) && !empty ($aRow[0]['file_destination']) ? $aRow[0]['file_destination'] : UPLOADS_DIR;

            if ( $path == '' )
            {
                throw new \Exception ('UPLOADING_FILE_PROBLEM');
            }

            $objFileUpload = new \BusinessModel\FileUpload();

            if ( isset ($this->files['fileUpload']) )
            {
                foreach ($this->files['fileUpload']['name'] as $key => $name) {
                    $objFileUpload->doUpload ($name, $path, $this->files['fileUpload']['error'][$key], $this->files['fileUpload']['tmp_name'][$key]);
                }
            }
            else
            {
                foreach ($this->files['file']['name'] as $key => $name) {
                    $objFileUpload->doUpload ($this->files['file']['name'][$key], $path, $this->files['file']['error'][$key], $this->files['file']['tmp_name'][$key]);
                }
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
    private function uploadDocument ($arrFiles, \Users $objUser)
    {
        $stepDocument = new \BusinessModel\InputDocument (new \Task ($this->stepId));

        if ( !is_numeric ($this->documentId) )
        {
            throw new \Exception ("Invalid document id given");
        }

        $result = $this->objMysql->_select ("workflow.step_permission", [], ["permission" => $objUser->getUserId (), "step_id" => $this->stepId, "access_level" => "INPUT"]);
        $result2 = $this->objMysql->_select ("workflow.process_supervisors", [], ["user_id" => $objUser->getUserId (), "workflow_id" => $this->workflowId]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            if ( !isset ($result2[0]) || empty ($result2[0]) )
            {
                throw new Exception ("User doesnt have permission to perform this action");
            }
        }

        $objStepDocument = $stepDocument->getInputDocument ($this->documentId);


        if ( !empty ($objStepDocument) )
        {

            $unit = $objStepDocument[$this->documentId]->getFilesizeUnit ();
            $extensions = $objStepDocument[$this->documentId]->getFileType ();
            $arrExtensions = explode (",", $extensions);

            foreach ($arrExtensions as $key => $extension) {
                $arrExtensions[$key] = trim (str_replace (".", "", $extension));
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
                $objFile->verifyPath ($dir, true);

                $folderId = (new \AppFolder())->createFromPath ($dir2);
            }

            if ( !move_uploaded_file ($file_tmp, $destination) )
            {
                $this->arrayValidation[] = "File Could not be uploaded";
                return false;
            }

            $this->object['file_destination'] = $destination;

            // update version
            $id = $objVersioning->create (array("folderId" => $folderId, "filename" => $originalFilename, "document_id" => $this->documentId, "app_uid" => $this->projectId), $objUser);
            $arrUploadedFiles[] = $id;

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
        $arrAttachments = $objMysql->_select ("task_manager.PROCESS_FILES", array(), array("source_id" => $sourceId));

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
    private function formatSizeUnits ($bytes, $type)
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
     * 
     * @param type $id
     * @return type
     */
    private function retrieveByPK ($id)
    {
        $result = $this->objMysql->_select ("task_manager.app_document", array(), array("id" => $id));

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

    public function getDocumentsByType ($docUid, $docType)
    {
        $results = $this->objMysql->_select ("app_document", [], ["document_id" => $docUid, "document_type" => $docType]);

        if ( isset ($results[0]) && !empty ($results[0]) )
        {
            foreach ($results as $key => $result) {

                $oAppDocument = new \DocumentVersion();
                $Fields = $oAppDocument->load ($result['document_id'], $result['document_version'], null, true);

                $info = pathinfo ($Fields['filename']);

                if ( $result['document_type'] === "OUTPUT" )
                {
                    $ext = "pdf";

                    $realPath = UPLOADS_DIR . "OutputDocuments/" . $result['app_id'] . "/" . $result['document_id'] . "_" . $result['document_version'] . "." . $ext;
                    $realPath1 = UPLOADS_DIR . 'OutputDocuments/' . $info['basename'] . "_" . $result['document_version'] . '.' . $ext;

                    $realPath2 = UPLOADS_DIR . 'OutputDocuments/' . $info['basename'] . '.' . $ext;

                    $sw_file_exists = false;

                    if ( file_exists ($realPath) )
                    {
                        $sw_file_exists = true;
                    }
                    elseif ( file_exists ($realPath1) )
                    {
                        $sw_file_exists = true;

                        $realPath = $realPath1;
                    }
                    elseif ( file_exists ($realPath2) )
                    {
                        $sw_file_exists = true;

                        $realPath = $realPath2;
                    }

                    $results[$key]['path'] = $realPath;
                }
                else
                {
                    $inputDocument = new \BusinessModel\InputDocument();
                    $inputDocument->throwExceptionIfNotExistsInputDocument ($result['document_id']);
                    $arrInput = $inputDocument->getInputDocument ($result['document_id']);

                    $ext = (isset ($info['extension']) ? $info['extension'] : '');
                    $realPath1 = "C:/xampp/htdocs/FormBuilder/public/uploads/" . $arrInput[$result['document_id']]->getDestinationPath () . "/" . $info['filename'] . "." . $ext;

                    if ( file_exists ($realPath1) )
                    {
                        $results[$key]['path'] = $realPath1;
                    }
                }
            }
        }

        return $results;
    }

}
