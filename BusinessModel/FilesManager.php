<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of FilesManager
 *
 * @author michael.hampton
 */
class FilesManager
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Return the Process Files Manager
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     *
     * return array
     *
     * @access public
     */
    public function getProcessFilesManager ()
    {
        try {
            $aDirectories[] = array('name' => "templates",
                'type' => "folder",
                'path' => "/",
                'editable' => false);
            $aDirectories[] = array('name' => "public",
                'type' => "folder",
                'path' => "/",
                'editable' => false);
            return $aDirectories;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the Process Files Manager Path
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $path
     *
     * return array
     *
     * @access public
     */
    public function getProcessFilesManagerPath ($sProcessUID, $path, $getContent = true)
    {
        try {
            $checkPath = substr ($path, -1);
            if ( $checkPath == '/' )
            {
                $path = substr ($path, 0, -1);
            }
            $sMainDirectory = current (explode ("/", $path));
            if ( strstr ($path, '/') )
            {
                $sSubDirectory = substr ($path, strpos ($path, "/") + 1) . PATH_SEP;
            }
            else
            {
                $sSubDirectory = '';
            }

            switch ($sMainDirectory) {
                case 'templates':
                    $sDirectory = PATH_DATA_MAILTEMPLATES . $sProcessUID . PATH_SEP . $sSubDirectory;
                    break;
                case 'public':
                    $sDirectory = PATH_DATA_PUBLIC . $sProcessUID . PATH_SEP . $sSubDirectory;
                    break;
                default:
                    throw new \Exception ("ID_INVALID_VALUE_FOR " . $sMainDirectory);
                    break;
            }
            (new FileUpload())->verifyPath ($sDirectory, true);

            $aTheFiles = array();
            $aFiles = array();
            $oDirectory = dir ($sDirectory);
            while ($sObject = $oDirectory->read ()) {
                if ( ($sObject !== '.') && ($sObject !== '..') )
                {
                    $sPath = $sDirectory . $sObject;
                    if ( is_dir ($sPath) )
                    {
                        $aTheFiles[] = array('prf_name' => $sObject,
                            'prf_type' => "folder",
                            'prf_path' => $sMainDirectory);
                    }
                    else
                    {
                        $aAux = pathinfo ($sPath);
                        $aAux['extension'] = (isset ($aAux['extension']) ? $aAux['extension'] : '');
                        $aFiles[] = array('FILE' => $sObject, 'EXT' => $aAux['extension']);
                    }
                }
            }

            foreach ($aFiles as $aFile) {
                $arrayFileUid = $this->getFileManagerUid ($sDirectory . $aFile['FILE'], $aFile['FILE']);
                $fcontent = "";
                if ( $getContent === true )
                {
                    $fcontent = file_get_contents ($sDirectory . $aFile['FILE']);
                }

                $fileUid = isset ($arrayFileUid["id"]) ? $arrayFileUid["id"] : '';

                $derivationScreen = isset ($arrayFileUid["DERIVATION_SCREEN_TPL"]) ? true : false;
                if ( $fileUid != null )
                {
                    $oProcessFiles = (new \ProcessFile())->retrieveByPk ($fileUid);

                    $editable = $oProcessFiles->getPrfEditable ();
                    if ( $editable == '1' )
                    {
                        $editable = 'true';
                    }
                    else
                    {
                        $editable = 'false';
                    }
                    $aTheFiles[$aFile['FILE']] = array('prf_uid' => $oProcessFiles->getId (),
                        'prf_filename' => $aFile['FILE'],
                        'usr_uid' => $oProcessFiles->getUsrUid (),
                        'prf_path' => $sMainDirectory . PATH_SEP . $sSubDirectory,
                        'prf_type' => $oProcessFiles->getPrfType (),
                        'prf_editable' => $editable,
                        'prf_create_date' => $oProcessFiles->getPrfCreateDate (),
                        'prf_content' => $fcontent,
                        'prf_derivation_screen' => $derivationScreen);
                }
                else
                {
                    $extention = end (explode (".", $aFile['FILE']));
                    if ( $extention == 'docx' || $extention == 'doc' || $extention == 'html' || $extention == 'php' || $extention == 'jsp' || $extention == 'xlsx' || $extention == 'xls' || $extention == 'js' || $extention == 'css' || $extention == 'txt' )
                    {
                        $editable = 'true';
                    }
                    else
                    {
                        $editable = 'false';
                    }
                    $aTheFiles[] = array('prf_uid' => '',
                        'prf_filename' => $aFile['FILE'],
                        'usr_uid' => '',
                        'prf_update_usr_uid' => '',
                        'prf_path' => $sMainDirectory . PATH_SEP . $sSubDirectory,
                        'prf_type' => 'file',
                        'prf_editable' => $editable,
                        'prf_create_date' => '',
                        'prf_update_date' => '',
                        'prf_content' => $fcontent,
                        'prf_derivation_screen' => false);
                }
            }

            return $aTheFiles;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the Process File Manager
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $userUID {@min 32} {@max 32}
     * @param array  $aData
     *
     * return array
     *
     * @access public
     */
    public function addProcessFilesManager (\Workflow $objWorkflow, \Users $objUser, $aData, $isImport = false)
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
            if ( $sMainDirectory != 'public' && $sMainDirectory != 'templates' )
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
                    $sDirectory = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . PATH_SEP . $sSubDirectory . $aData['prf_filename'];
                    $sCheckDirectory = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . PATH_SEP . $sSubDirectory;
                    if ( $extention != '.html' )
                    {
                        throw new \Exception ('ID_FILE_UPLOAD_INCORRECT_EXTENSION');
                    }
                    break;
                case 'public':
                    $sDirectory = PATH_DATA_PUBLIC . $objWorkflow->getWorkflowId () . PATH_SEP . $sSubDirectory . $aData['prf_filename'];
                    $sCheckDirectory = PATH_DATA_PUBLIC . $objWorkflow->getWorkflowId () . PATH_SEP . $sSubDirectory;
                    $sEditable = false;
                    if ( $extention == '.exe' )
                    {
                        throw new \Exception ('ID_FILE_UPLOAD_INCORRECT_EXTENSION');
                    }

                    break;
                default:
                    $sDirectory = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . PATH_SEP . $sSubDirectory . $aData['prf_filename'];
                    break;
            }

            $content = $aData['prf_content'];

            if ( file_exists ($sDirectory) )
            {
                throw new \Exception ("ID_EXISTS_FILE");
            }

            if ( !file_exists ($sCheckDirectory) )
            {

                $oProcessFiles = new \ProcessFile();
                $sDate = date ('Y-m-d H:i:s');
                $oProcessFiles->setProUid ($objWorkflow->getWorkflowId ());
                $oProcessFiles->setUsrUid ($objUser->getUsername ());
                $oProcessFiles->setPrfUpdateUsrUid ('');
                $oProcessFiles->setPrfPath ($sCheckDirectory);
                $oProcessFiles->setPrfType ('folder');
                $oProcessFiles->setPrfEditable ('');
                $oProcessFiles->setPrfCreateDate ($sDate);
                $oProcessFiles->save ();
            }

            (new FileUpload())->verifyPath ($sCheckDirectory, true);
            $oProcessFiles = new \ProcessFile();
            $sDate = date ('Y-m-d H:i:s');
            $oProcessFiles->setProUid ($objWorkflow->getWorkflowId ());
            $oProcessFiles->setUsrUid ($objUser->getUsername ());
            $oProcessFiles->setPrfUpdateUsrUid ('');
            $oProcessFiles->setPrfPath ($sDirectory);
            $oProcessFiles->setPrfType ('file');
            $oProcessFiles->setPrfEditable ($sEditable);
            $oProcessFiles->setPrfCreateDate ($sDate);
            $oProcessFiles->setPrfFielname ($aData['prf_filename']);
            $oProcessFiles->save ();

            $fp = fopen ($sDirectory, 'w');
            $content = stripslashes ($aData['prf_content']);
            $content = str_replace ("@amp@", "&", $content);
            fwrite ($fp, $content);
            fclose ($fp);

            $oProcessFile = array('prf_uid' => $oProcessFiles->getId (),
                'prf_filename' => $aData['prf_filename'],
                'usr_uid' => $oProcessFiles->getUsrUid (),
                'prf_update_usr_uid' => $oProcessFiles->getPrfUpdateUsrUid (),
                'prf_path' => $sMainDirectory . PATH_SEP . $sSubDirectory,
                'prf_type' => $oProcessFiles->getPrfType (),
                'prf_editable' => $oProcessFiles->getPrfEditable (),
                'prf_create_date' => $oProcessFiles->getPrfCreateDate (),
                'prf_content' => $content);
            return $oProcessFile;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function existsProcessFile ($prfUid)
    {
        try {
            $obj = (new \ProcessFile())->retrieveByPk ($prfUid);
            return (!is_null ($obj)) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the Process Files Manager
     *
     * @param string $prjUid {@min 32} {@max 32}
     * @param string path
     *
     *
     * @access public
     */
    public function uploadProcessFilesManager ($path, $arrFiles, \Workflow $objWorkflow, \Users $objUser)
    {
        try {

            if ( $path == '' )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            $extention = strstr ($arrFiles['FileUpload']['name'], '.');

            if ( !$extention )
            {
                $extention = '.html';
                $arrFiles['FileUpload']['name'] = $arrFiles['FileUpload']['name'] . $extention;
            }

            $file = explode ("/", $path);
            $file = end ($file);

            if ( strpos ($file, "\\") > 0 )
            {
                $file = str_replace ('\\', '/', $file);
                $file = explode ("/", $file);
                $file = end ($file);
            }

            $path = str_replace ($file, '', $path);

            if ( $file == $arrFiles['FileUpload']['name'] )
            {
                if ( $arrFiles['FileUpload']['error'] != 1 )
                {
                    if ( $arrFiles['FileUpload']['tmp_name'] != '' )
                    {
                        (new FileUpload())->uploadFile ($arrFiles['FileUpload']['tmp_name'], $path, $arrFiles['FileUpload']['name']);
                    }
                }
            }
            else
            {
                throw new \Exception ('ID_PMTABLE_UPLOADING_FILE_PROBLEM');
            }


            $this->addProcessFilesManagerInDb ($objWorkflow, $objUser, array("PRF_PATH" => $path,
                "prf_filename" => $arrFiles['FileUpload']['name']));

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of unique ids of a file and if the template is used in a derivation screen
     *
     * @param string $path
     * @param string $fileName the name of template
     *
     * return array
     */
    public function getFileManagerUid ($path, $fileName = '')
    {
        try {
            if ( strtoupper (substr (PHP_OS, 0, 3)) === 'WIN' )
            {
                $path = str_replace ("/", DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, $path);
            }

            $path = explode (DIRECTORY_SEPARATOR, $path);
            $baseName = $path[count ($path) - 2] . "\\\\" . $path[count ($path) - 1];
            $baseName2 = $path[count ($path) - 2] . "/" . $path[count ($path) - 1];

            $sql = "SELECT id
                    FROM task_manager.attachments pf
                    INNER JOIN workflow.workflows w ON w.workflow_id = pf.PRO_UID
                    WHERE (file_destination LIKE '%" . $baseName . "%' OR file_destination LIKE '%" . $baseName2 . "%')
                    ";

            $results = $this->objMysql->_query ($sql);

            $row = array();
            foreach ($results as $row) {
                if ( isset ($row['PRO_DERIVATION_SCREEN_TPL']) && !empty ($row['PRO_DERIVATION_SCREEN_TPL']) && $row['PRO_DERIVATION_SCREEN_TPL'] == $fileName )
                {
                    $row['DERIVATION_SCREEN_TPL'] = true;
                    return $row;
                }
                elseif ( isset ($row['TAS_DERIVATION_SCREEN_TPL']) && !empty ($row['TAS_DERIVATION_SCREEN_TPL']) && $row['TAS_DERIVATION_SCREEN_TPL'] == $fileName )
                {
                    $row['DERIVATION_SCREEN_TPL'] = true;
                    return $row;
                }
            }
            return $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the Process Files Manager
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $userUID {@min 32} {@max 32}
     * @param array  $aData
     * @param string $prfUid {@min 32} {@max 32}
     *
     * return array
     *
     * @access public
     */
    public function updateProcessFilesManager (\Workflow $objWorkflow, \Users $objUser, $aData, $prfUid)
    {
        try {
            $path = '';

            $result = $this->objMysql->_select ("task_manager.attachments", ["file_destination"], ["id" => $prfUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }

            $path = $result[0]['file_destination'];

            if ( $path == '' )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            $sFile = basename ($path);
            $sPath = str_replace ($sFile, '', $path);

            $sSubDirectory = substr (str_replace ($objWorkflow->getWorkflowId (), '', substr ($sPath, (strpos ($sPath, $objWorkflow->getWorkflowId ())))), 0, -1);
            $sMainDirectory = str_replace (substr ($sPath, strpos ($sPath, $objWorkflow->getWorkflowId ())), '', $sPath);

            if ( $sMainDirectory == PATH_DATA_MAILTEMPLATES )
            {
                $sMainDirectory = 'mailTemplates';
            }
            else
            {
                $sMainDirectory = 'public';
            }

            $extention = explode (".", $sFile)[1];

            if ( $extention == 'docx' || $extention == 'doc' || $extention == 'html' || $extention == 'php' || $extention == 'jsp' ||
                    $extention == 'xlsx' || $extention == 'xls' || $extention == 'js' || $extention == 'css' || $extention == 'txt' )
            {
                $sEditable = true;
            }
            else
            {
                $sEditable = false;
            }


            if ( $sEditable == false )
            {
                throw new \Exception ("ID_UNABLE_TO_EDIT");
            }

            $oProcessFiles = (new \ProcessFile())->retrieveByPk ($prfUid);

            $sDate = date ('Y-m-d H:i:s');
            $oProcessFiles->setPrfUpdateUsrUid ($objUser->getUsername ());
            $oProcessFiles->setPrfUpdateDate ($sDate);
            $oProcessFiles->save ();

            $path = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . DIRECTORY_SEPARATOR . $sFile;
            $fp = fopen ($path, 'w');
            $content = stripslashes ($aData['prf_content']);
            $content = str_replace ("@amp@", "&", $content);
            fwrite ($fp, $content);
            fclose ($fp);
            $oProcessFile = array('prf_uid' => $oProcessFiles->getId (),
                'prf_filename' => $sFile,
                'usr_uid' => $oProcessFiles->getUsrUid (),
                'prf_update_usr_uid' => $oProcessFiles->getPrfUpdateUsrUid (),
                'prf_path' => $sMainDirectory . $sSubDirectory,
                'prf_type' => $oProcessFiles->getPrfType (),
                'prf_editable' => $sEditable,
                'prf_create_date' => $oProcessFiles->getPrfCreateDate (),
                'prf_update_date' => $oProcessFiles->getPrfUpdateDate (),
                'prf_content' => $content);
            return $oProcessFile;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $prfUid {@min 32} {@max 32}
     *
     *
     * @access public
     */
    public function deleteProcessFilesManager (\Workflow $objWorkflow, $prfUid)
    {
        try {
            $path = '';

            $result = $this->objMysql->_select ("task_manager.attachments", ["file_destination"], ["id" => $prfUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }

            $path = $result[0]['file_destination'];

            if ( $path == '' )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            $sFile = explode ("/", $path);

            $sFile = end ($sFile);

            $path = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . DIRECTORY_SEPARATOR . $sFile;

            if ( file_exists ($path) && !is_dir ($path) )
            {
                unlink ($path);
            }
            else
            {
                $path = PATH_DATA_PUBLIC . $objWorkflow->getWorkflowId () . DIRECTORY_SEPARATOR . $sFile;
                if ( file_exists ($path) && !is_dir ($path) )
                {
                    unlink ($path);
                }
            }

            $processFiles = new \ProcessFile();
            $processFiles->setId ($prfUid);
            $processFiles->delete ();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $prfUid {@min 32} {@max 32}
     *
     *
     * @access public
     */
    public function downloadProcessFilesManager (\Workflow $objWorkflow, $prfUid)
    {
        try {
            $path = '';
            $result = $this->objMysql->_select ("task_manager.attachments", ["file_destination"], ["id" => $prfUid]);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return false;
            }

            $path = $result[0]['file_destination'];

            if ( $path == '' )
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }

            $sFile = explode ("/", str_replace ('\\', '/', $path));
            $sFile = end ($sFile);
            $sPath = str_replace ($sFile, '', $path);
            $sSubDirectory = substr (str_replace ($objWorkflow->getWorkflowId (), '', substr ($sPath, (strpos ($sPath, $objWorkflow->getWorkflowId ())))), 0, -1);
            $sMainDirectory = str_replace (substr ($sPath, strpos ($sPath, $objWorkflow->getWorkflowId ())), '', $sPath);

            if ( $sMainDirectory == PATH_DATA_MAILTEMPLATES )
            {
                $sMainDirectory = 'mailTemplates';
            }
            else
            {
                $sMainDirectory = 'public';
            }

            if ( file_exists ($path) )
            {
                $this->downloadFile ($objWorkflow, $sMainDirectory, $sSubDirectory, $sFile);
                die ();
            }
            else
            {
                throw (new \Exception ('Invalid value specified for path.'));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function downloadFile (\Workflow $objWorkflow, $sMainDirectory, $sCurrentDirectory, $sFile)
    {
        switch ($sMainDirectory) {
            case 'mailTemplates':
                $sDirectory = PATH_DATA_MAILTEMPLATES . $objWorkflow->getWorkflowId () . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
                break;
            case 'public':
                $sDirectory = PATH_DATA_PUBLIC . $objWorkflow->getWorkflowId () . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
                break;
            default:
                die ();
                break;
        }
        if ( file_exists ($sDirectory . $sFile) )
        {
            (new FileUpload())->streamFile ($sDirectory . $sFile, true);
        }
    }

    /**
     *
     * @param string $sProcessUID {@min 32} {@max 32}
     * @param string $prfUid {@min 32} {@max 32}
     *
     *
     * @access public
     */
    public function getProcessFileManager ($sProcessUID, $prfUid)
    {
        try {
            $oProcessFiles = \ProcessFilesPeer::retrieveByPK ($prfUid);
            $fcontent = file_get_contents ($oProcessFiles->getPrfPath ());
            $pth = $oProcessFiles->getPrfPath ();
            $pth = str_replace ("\\", "/", $pth);
            $prfPath = explode ("/", $pth);
            $sFile = end ($prfPath);
            $path = $oProcessFiles->getPrfPath ();
            $sPath = str_replace ($sFile, '', $path);
            $sSubDirectory = substr (str_replace ($sProcessUID, '', substr ($sPath, (strpos ($sPath, $sProcessUID)))), 0, -1);
            $sMainDirectory = str_replace (substr ($sPath, strpos ($sPath, $sProcessUID)), '', $sPath);
            if ( $sMainDirectory == PATH_DATA_MAILTEMPLATES )
            {
                $sMainDirectory = 'templates';
            }
            else
            {
                $sMainDirectory = 'public';
            }
            $oProcessFile = array('prf_uid' => $oProcessFiles->getPrfUid (),
                'prf_filename' => $sFile,
                'usr_uid' => $oProcessFiles->getUsrUid (),
                'prf_update_usr_uid' => $oProcessFiles->getPrfUpdateUsrUid (),
                'prf_path' => $sMainDirectory . $sSubDirectory,
                'prf_type' => $oProcessFiles->getPrfType (),
                'prf_editable' => $oProcessFiles->getPrfEditable (),
                'prf_create_date' => $oProcessFiles->getPrfCreateDate (),
                'prf_update_date' => $oProcessFiles->getPrfUpdateDate (),
                'prf_content' => $fcontent);
            return $oProcessFile;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $aData
     * @throws Exception
     * @throws \Exception
     */
    public function addProcessFilesManagerInDb (\Workflow $objWorkflow, \Users $objUser, $aData)
    {
        try {
            $path = $aData['PRF_PATH'];
            $allDirectories = pathinfo ($path);
            $path = explode ('/', $allDirectories['dirname']);

            $fileDirectory = $path[count ($path) - 1];

            switch ($fileDirectory) {
                case 'templates':
                    $sDirectory = PATH_DATA_MAILTEMPLATES . basename ($aData['PRF_PATH']) . "/" . $aData['prf_filename'];
                    break;
                case 'public':
                    $sDirectory = PATH_DATA_PUBLIC . $objWorkflow->getWorkflowId () . PATH_SEP . basename ($aData['PRF_PATH']);
                    break;
                default:
                    if ( strtoupper (substr (PHP_OS, 0, 3)) !== 'WIN' )
                    {
                        error_log ("ID_INVALID_VALUE_FOR");
                    }
                    return;
                    break;
            }

            if ( isset ($aData['PRF_UID']) && $this->existsProcessFile ($aData['PRF_UID']) )
            {
                //$oProcessFiles->setPrfUid ($sPkProcessFiles);
                //$emailEvent = new \ProcessMaker\BusinessModel\EmailEvent();
                //$emailEvent->updatePrfUid ($aData['PRF_UID'], $sPkProcessFiles, $aData['PRO_UID']);
            }

            $oProcessFiles = new \ProcessFile();
            $sDate = date ('Y-m-d H:i:s');
            $oProcessFiles->setProUid ($objWorkflow->getWorkflowId ());
            $oProcessFiles->setUsrUid ($objUser->getUsername ());
            $oProcessFiles->setPrfUpdateUsrUid ('');
            $oProcessFiles->setPrfPath ($sDirectory);
            $oProcessFiles->setPrfType ('file');
            $oProcessFiles->setPrfEditable ('true');
            $oProcessFiles->setPrfCreateDate ($sDate);
            $oProcessFiles->setPrfFielname ($aData['prf_filename']);
            $oProcessFiles->save ();
        } catch (Exception $e) {
            throw $e;
        }
    }

}
