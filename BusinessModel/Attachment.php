<?php

namespace BusinessModel;

class Attachment
{

    private $stepId;
    private $projectId;
    public $id;
    public $object = array();
    private $filename;
    private $documentId;
    private $objMysql;
    private $arrayValidation;
    
    private $table = "task_manager.attachments";

    public function __construct ()
    {
        if ( !defined ("PATH_DATA_PUBLIC") )
        {
            define ("PATH_DATA_PUBLIC", $_SERVER['DOCUMENT_ROOT'] . "/FormBuilder/public/");
        }

        if ( !defined ("PATH_SEP") )
        {
            define ("PATH_SEP", "/");
        }

        $this->objMysql = new \Mysql2();
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function loadObject ($arrData)
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
                
                $result = $this->uploadDocument ($arrData['files'], $arrData);
                
                return $result;
            }
            else
            {
                $aData = array(
                    "prf_path" => "uploads",
                    "prf_filename" => $arrData['filename']
                );

                $arrResponse = $this->addProcessFilesManager ($arrData['source_id'], $arrData['uploaded_by'], $aData);
                $this->upload ($arrData['source_id'], $arrResponse['prf_uid']);
            }
        }
    }

    public function addProcessFilesManager ($sProcessUID, $userUID, $aData)
    {
        try {
            $aData['prf_path'] = rtrim ($aData['prf_path'], '/') . '/';

            if ( !$aData['prf_filename'] )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            $extention = strstr ($aData['prf_filename'], '.');

            if ( !$extention )
            {
                $extention = '.html';
                $aData['prf_filename'] = $aData['prf_filename'] . $extention;
            }
            if ( $extention == '.docx' || $extention == '.doc' || $extention == '.html' || $extention == '.php' || $extention == '.jsp' ||
                    $extention == '.xlsx' || $extention == '.xls' || $extention == '.js' || $extention == '.css' || $extention == '.txt' )
            {
                $sEditable = true;
            }
            else
            {
                $sEditable = false;
            }


            $sMainDirectory = current (explode ("/", $aData['prf_path']));

            if ( $sMainDirectory != 'uploads' && $sMainDirectory != 'templates' )
            {
                throw new \Exception ("ID_INVALID_PRF_PATH");
            }

            if ( strstr ($aData['prf_path'], '/') )
            {
                $sSubDirectory = substr ($aData['prf_path'], strpos ($aData['prf_path'], "/") + 1);
            }
            else
            {
                $sSubDirectory = '';
            }

            switch ($sMainDirectory) {
                case 'templates':

                    break;
                case 'uploads':
                    $sDirectory = PATH_DATA_PUBLIC . $sMainDirectory . "/" . $sProcessUID . PATH_SEP . $sSubDirectory . $aData['prf_filename'];
                    $sCheckDirectory = PATH_DATA_PUBLIC . $sMainDirectory . "/" . $sProcessUID . PATH_SEP . $sSubDirectory;
                    $sEditable = false;

                    if ( $extention == '.exe' )
                    {
                        throw new \Exception ('ID_FILE_UPLOAD_INCORRECT_EXTENSION');
                    }
                    break;
                default:
                    break;
            }
            if ( file_exists ($sDirectory) )
            {
                $directory = $sMainDirectory . PATH_SEP . $sSubDirectory . $aData['prf_filename'];
                //throw new Exception ("ID_EXISTS_FILE");
            }

            if ( !file_exists ($sCheckDirectory) )
            {
//                $oProcessFiles = new \ProcessFile();
//                $sDate = date ('Y-m-d H:i:s');
//                $oProcessFiles->setProUid ($sProcessUID);
//                $oProcessFiles->setUsrUid ($userUID);
//                $oProcessFiles->setPrfUpdateUsrUid ('');
//                $oProcessFiles->setPrfPath ($sCheckDirectory);
//                $oProcessFiles->setPrfType ('folder');
//                $oProcessFiles->setPrfEditable ('');
//                $oProcessFiles->setPrfCreateDate ($sDate);
//                $oProcessFiles->save ();
//                $oProcessFiles->setPrfFielname ('test');
            }
            
          

            $oProcessFiles = new \ProcessFile();
            $sDate = date ('Y-m-d H:i:s');
            $oProcessFiles->setProUid ($sProcessUID);
            $oProcessFiles->setUsrUid ($userUID);
            $oProcessFiles->setPrfUpdateUsrUid ('');
            $oProcessFiles->setPrfPath ($sDirectory);
            $oProcessFiles->setPrfType ('file');
            $oProcessFiles->setPrfEditable ($sEditable);
            $oProcessFiles->setPrfCreateDate ($sDate);
            $oProcessFiles->setPrfFielname ($aData['prf_filename']);

            if ( isset ($aData['file_type']) )
            {
                $oProcessFiles->setFileType ($aData['file_type']);
            }

            $oProcessFiles->save ();

            $oProcessFile = array('prf_uid' => $oProcessFiles->getId (),
                'prf_filename' => $aData['prf_filename'],
                'usr_uid' => $oProcessFiles->getUsrUid (),
                'prf_update_usr_uid' => $oProcessFiles->getPrfUpdateUsrUid (),
                'prf_path' => $sMainDirectory . PATH_SEP . $sSubDirectory,
                'prf_type' => $oProcessFiles->getPrfType (),
                'prf_editable' => $oProcessFiles->getPrfEditable (),
                'prf_create_date' => $oProcessFiles->getPrfCreateDate ());

            return $oProcessFile;
        } catch (Exception $e) {
            throw $e;
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
    private function uploadDocument ($arrFiles, $arrData)
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
                $arrExtensions[$key] = str_replace (".", "", $extension);
            }

            $inputName = $objStepDocument[$this->documentId]->getTitle (); // input document name
            //$projectId = 1;
            $intCount = 0;
            $versioning = $objStepDocument[$this->documentId]->getVersioning ();
            $dir2 = $objStepDocument[$this->documentId]->getDestinationPath ();
            $maxFileSize = $objStepDocument[$this->documentId]->getMaxFileSize ();
        }

        $arrUploadedFiles = array();
        
        foreach ($_FILES['fileUpload']['name'] as $key => $name) {
            $size = $_FILES['fileUpload']['size'][$key];
            $file_tmp = $_FILES['fileUpload']['tmp_name'][$key];
            $arrName = explode ('.', $name);
            $file_ext = strtolower (end ($arrName));

            $filename = $this->projectId . "_" . $intCount . date ("YmdHis") . "." . $file_ext;
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/FormBuilder/public/uploads/" . $dir2 . "/";
            $destination = $dir . $filename;

            if ( !empty ($objStepDocument) )
            {
                if ( $unit == "MB" )
                {
                    $actualSize = $this->formatSizeUnits ($size, "MB");
                }
                else
                {
                    $actualSize = $this->formatSizeUnits ($size, "KB");
                }

                if ( $actualSize > $size )
                {
                    $this->arrayValidation[] = "TOO BIG";
                }
                
                if ( !in_array ($file_ext, $arrExtensions) === false )
                {
                    $this->arrayValidation[] = "extension not allowed, please choose a JPEG or PNG file.";
                }

                $filename = $inputName . "_" . $this->projectId . "_" . $intCount . "." . $file_ext;
                $dir = $_SERVER['DOCUMENT_ROOT'] . "/FormBuilder/public/uploads/" . $dir2 . "/";
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
                $objFile->verifyPath($dir, TRUE);
            }

            if ( !move_uploaded_file ($file_tmp, $destination) )
            {
                $this->arrayValidation[] = "File Could not be uploaded";
                return false;
            }

            $this->object['file_destination'] = $destination;

            $aData = array(
                "prf_path" => "uploads",
                "prf_filename" => $filename,
                "file_type" => $arrData['file_type']
            );

            // save document
            $arrResponse = $this->addProcessFilesManager ($arrData['source_id'], $arrData['uploaded_by'], $aData);

            // update version
            $objVersioning->create (array("filename" => $originalFilename, "document_id" => $this->documentId));
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
            $objProcessFiles->setDownloadPath($filePath);

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
            throw $e;
        }
    }

}
