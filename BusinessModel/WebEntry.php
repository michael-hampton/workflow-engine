<?php

namespace BusinessModel;

class WebEntry
{

    use Validator;

    private $objMysql;
    private $pathDataPublic;

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
        $this->pathDataPublic = defined ("PATH_DATA_PUBLIC") ? PATH_DATA_PUBLIC : $_SERVER['DOCUMENT_ROOT'] . "/core/public/webentry/";
    }

    /**
     * Sanitizes a filename
     *
     * @param string $name Filename
     *
     * return string Return the filename sanitizes
     */
    public function sanitizeFilename ($name)
    {
        $name = trim ($name);

        $arraySpecialCharSearch = array("/", "\\", " ");
        $arraySpecialCharReplace = array("_", "_", "_");

        $newName = str_replace ($arraySpecialCharSearch, $arraySpecialCharReplace, $name);

        $arraySpecialCharSearch = array("/[\!-\)\:-\@]/", "/[\{\}\[\]\|\¿\?\+\*]/");
        $arraySpecialCharReplace = array("", "");

        $newName = preg_replace ($arraySpecialCharSearch, $arraySpecialCharReplace, $newName);

        return $newName;
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("workflow.web_entry", [], ["WE_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
    }

    /**
     * Verify if exists the Web Entry
     *
     * @param string $webEntryUid Unique id of Web Entry
     *
     * return bool Return true if exists the Web Entry, false otherwise
     */
    public function exists ($webEntryUid)
    {
        try {
            $obj = $this->retrieveByPK ($webEntryUid);

            return $obj !== false ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a Web Entry
     *
     * @param string $processUid         Unique id of Process
     * @param string $webEntryTitle      Title
     * @param string $webEntryUidExclude Unique id of Web Entry to exclude
     *
     * return bool Return true if exists the title of a Web Entry, false otherwise
     */
    public function existsTitle ($processUid, $webEntryTitle, $webEntryUidExclude = "")
    {
        try {

            $sql = "SELECT WE_UID FROM web_entry WHERE PRO_UID = ?";



            if ( $webEntryUidExclude != "" )
            {
                $sql .= " AND WE_UID != ?";
            }

            $sql .= " AND WE_TITLE = ?";

            $results = $this->objMysql->_query ($sql);

            if ( isset ($results[0]) && !empty ($results[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the Web Entry
     *
     * @param string $webEntryUid           Unique id of Web Entry
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exists the Web Entry
     */
    public function throwExceptionIfNotExistsWebEntry ($webEntryUid)
    {
        try {
            if ( !$this->exists ($webEntryUid) )
            {
                throw new \Exception ("ID_WEB_ENTRY_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a Web Entry
     *
     * @param string $processUid            Unique id of Process
     * @param string $webEntryTitle         Title
     * @param string $fieldNameForException Field name for the exception
     * @param string $webEntryUidExclude    Unique id of Web Entry to exclude
     *
     * return void Throw exception if exists the title of a Web Entry
     */
    public function throwExceptionIfExistsTitle ($processUid, $webEntryTitle, $fieldNameForException, $webEntryUidExclude = "")
    {
        try {
            if ( $this->existsTitle ($processUid, $webEntryTitle, $webEntryUidExclude) )
            {
                throw new \Exception ("ID_WEB_ENTRY_TITLE_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $webEntryUid Unique id of Web Entry
     * @param string $processUid  Unique id of Process
     * @param array  $arrayData   Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($webEntryUid, $processUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayWebEntryData = ($webEntryUid == "") ? array() : $this->getWebEntry ($webEntryUid, true);
            $flagInsert = ($webEntryUid == "") ? true : false;

            $arrayDataMain = array_merge ($arrayWebEntryData, $arrayData);

            //Verify data - Field definition
            //Verify data
            if ( isset ($arrayData["WE_TITLE"]) )
            {
                $this->throwExceptionIfExistsTitle ($processUid, $arrayData["WE_TITLE"], $webEntryUid);
            }

            if ( isset ($arrayData["TAS_UID"]) )
            {
                $task = new \Flow();
                $task->throwExceptionIfNotExistsTask ($arrayData["TAS_UID"]);
            }

            if ( isset ($arrayData["DYN_UID"]) )
            {
                $dynaForm = new Form();

                $dynaForm->throwExceptionIfNotExistsDynaForm ($arrayData["DYN_UID"]);
            }

            $process = new Process();

            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["USR_UID"]) )
            {
                $process->throwExceptionIfNotExistsUser ($arrayData["USR_UID"]);
            }

            $task = new \Flow();

            $arrayTaskData = $task->retrieveByPk ($arrayDataMain["TAS_UID"]);

            if ( trim ($arrayTaskData->getStepFrom ()) !== "" )
            {
                if ( (int) $arrayTaskData->getFirstStep () !== 1 )
                {
                    throw new \Exception ("ID_ACTIVITY_IS_NOT_INITIAL_ACTIVITY");
                }

                if ( trim ($arrayTaskData->getStepTo ()) === "" )
                {
                    throw new \Exception ("ID_WEB_ENTRY_ACTIVITY_DOES_NOT_HAVE_VALID_ASSIGNMENT_TYPE");
                }
            }

            $objStepPermissions = new StepPermission (new \Task ($arrayData['TAS_UID']));
            $arrStepPermissions = $objStepPermissions->getProcessPermissions ();
            $arrMasterUser = explode (",", $arrStepPermissions['master']['user']);

            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["TAS_UID"]) )
            {
                if ( count ($arrMasterUser) == 0 )
                {
                    throw new \Exception ("ID_ACTIVITY_DOES_NOT_HAVE_USERS");
                }
            }


            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["USR_UID"]) )
            {
                if ( !in_array ($arrayData['USR_UID'], $arrMasterUser) )
                {
                    throw new \Exception ("ID_USER_DOES_NOT_HAVE_ACTIVITY_ASSIGNED");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Set value in WE_DATA
     *
     * @param string $webEntryUid Unique id of Web Entry
     *
     * return void
     */
    public function setWeData ($webEntryUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsWebEntry ($webEntryUid);

            //Set variables
            $arrayWebEntryData = $this->getWebEntry ($webEntryUid, true);

            $processUid = $arrayWebEntryData["PRO_UID"];
            $taskUid = $arrayWebEntryData["TAS_UID"];
            $dynaFormUid = $arrayWebEntryData["DYN_UID"];
            $webEntryMethod = $arrayWebEntryData["WE_METHOD"];
            $webEntryInputDocumentAccess = $arrayWebEntryData["WE_INPUT_DOCUMENT_ACCESS"];
            $webEntryData = "";

            $wsRoundRobin = 0; //0, 1 //0 - Cyclical Assignment

            $pathDataPublicProcess = $this->pathDataPublic . $processUid;

            //Delete previous files
            if ( trim ($arrayWebEntryData["WE_DATA"]) != "" )
            {
                $fileName = str_replace (".php", "", trim ($arrayWebEntryData["WE_DATA"]));
                $file = $pathDataPublicProcess . PATH_SEP . $fileName . ".php";

                if ( is_file ($file) && file_exists ($file) )
                {
                    unlink ($file);
                    unlink ($pathDataPublicProcess . PATH_SEP . $fileName . "Post.php");
                }
            }

            //Create files
            $objFileUpload = new FileUpload();
            $objFileUpload->mk_dir ($pathDataPublicProcess, 0777);

            $http = "http://";

            switch ($webEntryMethod) {
                case "WS":
                    //require_once(PATH_RBAC . "model" . PATH_SEP . "RbacUsers.php");
                    //$user = new \RbacUsers();

                    $arrayUserData = (new UsersFactory())->getUser ($arrayWebEntryData["USR_UID"]);


                    $usrUsername = $arrayUserData->getUsername ();
                    $usrPassword = $arrayUserData->getPassword ();

                    $dynaForm = new Form (new \Task ($arrayWebEntryData["DYN_UID"]));
                    $arrayDynaFormData = $dynaForm->getFields ();

                    //Creating sys.info;
                    $sitePublicPath = "";

                    if ( file_exists ($sitePublicPath . "") )
                    {
                        
                    }

                    //Creating the first file
                    $weTitle = $this->sanitizeFilename ($arrayWebEntryData["WE_TITLE"]);
                    $fileName = $weTitle;

                    $header = "<?php\n";
                    $header .= "global \$_DBArray;\n";
                    $header .= "if (!isset(\$_DBArray)) {\n";
                    $header .= "  \$_DBArray = array();\n";
                    $header .= "}\n";
                    $header .= "\$_SESSION[\"PROCESS\"] = \"" . $processUid . "\";\n";
                    $header .= "\$_SESSION[\"CURRENT_DYN_UID\"] = \"" . $dynaFormUid . "\";\n";
                    $header .= "?>";
                    
                    $header .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>';


                    //Creating the second file, the  post file who receive the post form.
                    $pluginTpl = $_SERVER['DOCUMENT_ROOT'] . "/core/public/webentry/template.phtml";

                    $objFormBuilder = new FormBuilder ("AddNewForm");
                    $objFormBuilder->buildForm ($arrayDynaFormData);
                    $html = $objFormBuilder->render ();
                    $html .= '<input type="hidden" id="workflowid" name="workflowid" value="' . $processUid . '">';
                    $html .= '<input type="hidden" id="stepId" name="stepId" value="' . $dynaFormUid . '">';
                    $fileTemplate = file_get_contents ($pluginTpl);
                    $fileTemplate = str_replace ("<!-- CONTENT -->", $html, $fileTemplate);

                    $fileContent = $header . $fileTemplate;

                    file_put_contents ($pathDataPublicProcess . PATH_SEP . $fileName . ".php", $fileContent);

                    //WE_DATA
                    $webEntryData = $weTitle . ".php";
                    break;
                case "HTML":

                    $dynaForm = new Form (new \Task ($arrayWebEntryData["DYN_UID"]));
                    $arrayDynaFormData = $dynaForm->getFields ();

                    //Creating the second file, the  post file who receive the post form.
                    $pluginTpl = $_SERVER['DOCUMENT_ROOT'] . "/core/public/webentry/template.phtml";

                    $objFormBuilder = new FormBuilder ("AddNewForm");
                    $objFormBuilder->buildForm ($arrayDynaFormData);
                    $html = $objFormBuilder->render ();
                    $html .= '<input type="hidden" id="workflowId" name="workflowId" value="' . $processUid . '">';
                    $html .= '<input type="hidden" id="stepId" name="stepId" value="' . $dynaFormUid . '">';
                    $fileTemplate = file_get_contents ($pluginTpl);
                    $fileTemplate = str_replace ("<!-- CONTENT -->", $html, $fileTemplate);

                    $webEntryData = $fileTemplate;

                    break;
            }

            $this->objMysql->_update("workflow.web_entry", ["WE_DATA" => $webEntryData], ["WE_UID" => $webEntryUid]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Web Entry for a Process
     *
     * @param string $processUid     Unique id of Process
     * @param string $userUidCreator Unique id of creator User
     * @param array  $arrayData      Data
     *
     * return array Return data of the new Web Entry created
     */
    public function create ($processUid, $userUidCreator, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Set data

            unset ($arrayData["WE_UID"]);
            unset ($arrayData["WE_DATA"]);

            //Verify data
            $this->throwExceptionIfDataIsInvalid ("", $processUid, $arrayData);

            //Create

            try {
                $webEntry = new \WebEntry();

                $webEntry->loadObject ($arrayData);

                $webEntry->setProUid ($processUid);
                $webEntry->setWeCreateUsrUid ($userUidCreator);
                $webEntry->setWeCreateDate (date ("Y-m-d H:i:s"));


                if ( $webEntry->validate () )
                {

                    $webEntryUid = $webEntry->save ();

                    //Set WE_DATA
                    $this->setWeData ($webEntryUid);

                    //Return
                    return $this->getWebEntry ($webEntryUid);
                }
                else
                {
                    $msg = "";

                    foreach ($webEntry->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }

                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Web Entry
     *
     * @param string $webEntryUid Unique id of Web Entry
     *
     * return void
     */
    public function delete ($webEntryUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsWebEntry ($webEntryUid);

            //Set variables
            $arrayWebEntryData = $this->getWebEntry ($webEntryUid, true);

            //Delete web entry
            //Delete files
            if ( $arrayWebEntryData["WE_METHOD"] == "WS" )
            {
                $pathDataPublicProcess = PATH_DATA_PUBLIC . $arrayWebEntryData["PRO_UID"];

                $fileName = str_replace (".php", "", trim ($arrayWebEntryData["WE_DATA"]));
                $file = $pathDataPublicProcess . PATH_SEP . $fileName . ".php";

                if ( is_file ($file) && file_exists ($file) )
                {
                    unlink ($file);
                    unlink ($pathDataPublicProcess . PATH_SEP . $fileName . "Post.php");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Web Entry
     *
     * return object
     */
    public function getWebEntryCriteria ()
    {
        try {

            $criteria = "SELECT 
                    `WE_UID`, 
                    WE_TITLE,
                    `PRO_UID`, 
                    `TAS_UID`, 
                    `DYN_UID`, 
                    `USR_UID`, 
                    `WE_METHOD`, 
                    `WE_INPUT_DOCUMENT_ACCESS`, 
                    `WE_DATA`, 
                    `WE_CREATE_USR_UID`, 
                    `WE_UPDATE_USR_UID`, 
                    `WE_CREATE_DATE`, 
                    `WE_UPDATE_DATE`
                    FROM workflow.web_entry";


            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Web Entry from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Web Entry
     */
    public function getWebEntryDataFromRecord (array $record)
    {
        try {
            if ( $record["WE_METHOD"] == "WS" )
            {
                
                $http = "http://";
                $url = $http . $_SERVER["HTTP_HOST"] . "/public/webentry/" . $record["PRO_UID"];
                
                $record["WE_DATA"] = $url . "/" . $record["WE_DATA"];
            }


            $dateTime = new \DateTime ($record["WE_CREATE_DATE"]);
            $webEntryCreateDate = $dateTime->format ("Y-m-d H:i:s");

            $webEntryUpdateDate = "";

            if ( !empty ($record["WE_UPDATE_DATE"]) )
            {
                $dateTime = new \DateTime ($record["WE_UPDATE_DATE"]);
                $webEntryUpdateDate = $dateTime->format ($confEnvSetting["dateFormat"]);
            }
           
            $webEntry = new \WebEntry();
            $webEntry->setWeUid($record["WE_UID"]);
            $webEntry->setTasUid($record["TAS_UID"]);
            $webEntry->setWeTitle($record["WE_TITLE"] . "");
            $webEntry->setWeInputDocumentAccess((int) ($record["WE_INPUT_DOCUMENT_ACCESS"]));
            $webEntry->setWeData($record["WE_DATA"]);
            $webEntry->setUsrUid($record["USR_UID"] . "");
            $webEntry->setDynUid($record["DYN_UID"]);
            $webEntry->setWeMethod($record["WE_METHOD"]);
            $webEntry->setWeCreateUsrUid($record["WE_CREATE_USR_UID"]);
            $webEntry->setWeUpdateUsrUid($record["WE_UPDATE_USR_UID"] . "");
            $webEntry->setWeCreateDate($webEntryCreateDate);
            $webEntry->setWeUpdateDate($webEntryUpdateDate);
            
           return $webEntry;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Web Entries
     *
     * @param string $processUid Unique id of Process
     *
     * return array Return an array with all Web Entries
     */
    public function getWebEntries ($processUid)
    {
        try {
            $arrayWebEntry = array();

            //Get data
            $criteria = $this->getWebEntryCriteria ();

            $criteria .= " WHERE PRO_UID = ?";
            $criteria .= "ORDER BY WE_TITLE ASC";


            $results = $this->objMysql->_query ($criteria, [$processUid]);

            foreach ($results as $result) {

                $arrayWebEntry[] = $this->getWebEntryDataFromRecord ($result);
            }

            //Return
            return $arrayWebEntry;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Web Entry
     *
     * @param string $webEntryUid   Unique id of Web Entry
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Web Entry
     */
    public function getWebEntry ($webEntryUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsWebEntry ($webEntryUid);

            //Get data
            //SQL
            $criteria = $this->getWebEntryCriteria ();

            $criteria .= " WHERE WE_UID = ?";
            $results = $this->objMysql->_query ($criteria, [$webEntryUid]);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                return false;
            }

            $row = $results[0];

            //Return
            return (!$flagGetRecord) ? $this->getWebEntryDataFromRecord ($row) : $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
