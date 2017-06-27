<?php

namespace BusinessModel;

class WebEntry
{

    use Validator;

    private $objMysql;

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
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

            return (!is_null ($obj)) ? true : false;
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
    public function throwExceptionIfNotExistsWebEntry ($webEntryUid, $fieldNameForException)
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
                $this->throwExceptionIfExistsTitle ($processUid, $arrayData["WE_TITLE"], $this->arrayFieldNameForException["webEntryTitle"], $webEntryUid);
            }

            if ( isset ($arrayData["TAS_UID"]) )
            {
                $task = new \ProcessMaker\BusinessModel\Task();

                $task->throwExceptionIfNotExistsTask ($processUid, $arrayData["TAS_UID"], $this->arrayFieldNameForException["taskUid"]);
            }

            if ( isset ($arrayData["DYN_UID"]) )
            {
                $dynaForm = new \ProcessMaker\BusinessModel\DynaForm();

                $dynaForm->throwExceptionIfNotExistsDynaForm ($arrayData["DYN_UID"], $processUid, $this->arrayFieldNameForException["dynaFormUid"]);
            }

            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["USR_UID"]) )
            {
                $process->throwExceptionIfNotExistsUser ($arrayData["USR_UID"], $this->arrayFieldNameForException["userUid"]);
            }

            $task = new \Task();

            $arrayTaskData = $task->load ($arrayDataMain["TAS_UID"]);

            if ( isset ($arrayData["TAS_UID"]) )
            {
                if ( $arrayTaskData["TAS_START"] == "FALSE" )
                {
                    throw new \Exception ("ID_ACTIVITY_IS_NOT_INITIAL_ACTIVITY");
                }

                if ( $arrayTaskData["TAS_ASSIGN_TYPE"] != "BALANCED" )
                {
                    throw new \Exception ("ID_WEB_ENTRY_ACTIVITY_DOES_NOT_HAVE_VALID_ASSIGNMENT_TYPE");
                }
            }

            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["TAS_UID"]) )
            {
                $task = new \Tasks();

                if ( $task->assignUsertoTask ($arrayData["TAS_UID"]) == 0 )
                {
                    throw new \Exception ("ID_ACTIVITY_DOES_NOT_HAVE_USERS");
                }
            }

            if ( isset ($arrayData["DYN_UID"]) )
            {
                $dynaForm = new \Dynaform();

                $arrayDynaFormData = $dynaForm->Load ($arrayData["DYN_UID"]);

                $step = new \ProcessMaker\BusinessModel\Step();

                if ( !$step->existsRecord ($arrayDataMain["TAS_UID"], "DYNAFORM", $arrayData["DYN_UID"]) )
                {
                    throw new \Exception ("ID_DYNAFORM_IS_NOT_ASSIGNED_TO_ACTIVITY");
                }
            }

            if ( $arrayDataMain["WE_METHOD"] == "WS" && isset ($arrayData["USR_UID"]) )
            {
                $user = new \Users();

                $arrayUserData = $user->load ($arrayData["USR_UID"]);

                //Verify if User is assigned to Task
                $projectUser = new \ProcessMaker\BusinessModel\ProjectUser();

                if ( !$projectUser->userIsAssignedToTask ($arrayData["USR_UID"], $arrayDataMain["TAS_UID"]) )
                {
                    throw new \Exception (\G::LoadTranslation ("ID_USER_DOES_NOT_HAVE_ACTIVITY_ASSIGNED", array($arrayUserData["USR_USERNAME"], $arrayTaskData["TAS_TITLE"])));
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

            $pathDataPublicProcess = PATH_DATA_PUBLIC . $processUid;

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
                    require_once(PATH_RBAC . "model" . PATH_SEP . "RbacUsers.php");

                    $user = new \RbacUsers();

                    $arrayUserData = $user->load ($arrayWebEntryData["USR_UID"]);

                    $usrUsername = $arrayUserData["USR_USERNAME"];
                    $usrPassword = $arrayUserData["USR_PASSWORD"];

                    $dynaForm = new \Dynaform();

                    $arrayDynaFormData = $dynaForm->Load ($arrayWebEntryData["DYN_UID"]);

                    //Creating sys.info;
                    $sitePublicPath = "";

                    if ( file_exists ($sitePublicPath . "") )
                    {
                        
                    }

                    //Creating the first file
                    $weTitle = $this->sanitizeFilename ($arrayWebEntryData["WE_TITLE"]);
                    $fileName = $weTitle;

                    $fileContent = "<?php\n";
                    $fileContent .= "global \$_DBArray;\n";
                    $fileContent .= "if (!isset(\$_DBArray)) {\n";
                    $fileContent .= "  \$_DBArray = array();\n";
                    $fileContent .= "}\n";
                    $fileContent .= "\$_SESSION[\"PROCESS\"] = \"" . $processUid . "\";\n";
                    $fileContent .= "\$_SESSION[\"CURRENT_DYN_UID\"] = \"" . $dynaFormUid . "\";\n";
                    $fileContent .= "\$G_PUBLISH = new Publisher();\n";

                    $fileContent .= "G::LoadClass(\"pmDynaform\");\n";
                    $fileContent .= "\$a = new pmDynaform(array(\"CURRENT_DYNAFORM\" => \"" . $arrayWebEntryData["DYN_UID"] . "\"));\n";
                    $fileContent .= "if (\$a->isResponsive()) {";
                    $fileContent .= "  \$a->printWebEntry(\"" . $fileName . "Post.php\");";
                    $fileContent .= "} else {";
                    $fileContent .= "  \$G_PUBLISH->AddContent(\"dynaform\", \"xmlform\", \"" . $processUid . PATH_SEP . $dynaFormUid . "\", \"\", array(), \"" . $fileName . "Post.php\");\n";
                    $fileContent .= "  G::RenderPage(\"publish\", \"blank\");";
                    $fileContent .= "}";

                    file_put_contents ($pathDataPublicProcess . PATH_SEP . $fileName . ".php", $fileContent);

                    //Creating the second file, the  post file who receive the post form.
                    $pluginTpl = PATH_TPL . "processes" . PATH_SEP . "webentryPost.tpl";

                    $template = new \TemplatePower ($pluginTpl);
                    $template->prepare ();

                    $template->assign ("wsdlUrl", $http . $_SERVER["HTTP_HOST"] . "/sys" . SYS_SYS . "/" . SYS_LANG . "/" . SYS_SKIN . "/services/wsdl2");
                    $template->assign ("wsUploadUrl", $http . $_SERVER["HTTP_HOST"] . "/sys" . SYS_SYS . "/" . SYS_LANG . "/" . SYS_SKIN . "/services/upload");
                    $template->assign ("processUid", $processUid);
                    $template->assign ("dynaformUid", $dynaFormUid);
                    $template->assign ("taskUid", $taskUid);
                    $template->assign ("wsUser", $usrUsername);
                    $template->assign ("wsPass", \Bootstrap::getPasswordHashType () . ':' . $usrPassword);
                    $template->assign ("wsRoundRobin", $wsRoundRobin);

                    if ( $webEntryInputDocumentAccess == 0 )
                    {
                        //Restricted to process permissions
                        $template->assign ("USR_VAR", "\$cInfo = ws_getCaseInfo(\$caseId);\n\t  \$USR_UID = \$cInfo->currentUsers->userId;");
                    }
                    else
                    {
                        //No Restriction
                        $template->assign ("USR_VAR", "\$USR_UID = -1;");
                    }

                    $template->assign ("dynaform", $arrayDynaFormData["DYN_TITLE"]);
                    $template->assign ("timestamp", date ("l jS \of F Y h:i:s A"));
                    $template->assign ("ws", SYS_SYS);
                    $template->assign ("version", \System::getVersion ());

                    $fileName = $pathDataPublicProcess . PATH_SEP . $weTitle . "Post.php";

                    file_put_contents ($fileName, $template->getOutputContent ());

                    //Creating the third file, only if this wsClient.php file doesn't exist.
                    $fileName = $pathDataPublicProcess . PATH_SEP . "wsClient.php";
                    $pluginTpl = PATH_CORE . "templates" . PATH_SEP . "processes" . PATH_SEP . "wsClient.php";

                    if ( file_exists ($fileName) )
                    {
                        if ( filesize ($fileName) != filesize ($pluginTpl) )
                        {
                            copy ($fileName, $pathDataPublicProcess . PATH_SEP . "wsClient.php.bak");
                            unlink ($fileName);

                            $template = new \TemplatePower ($pluginTpl);
                            $template->prepare ();

                            file_put_contents ($fileName, $template->getOutputContent ());
                        }
                    }
                    else
                    {
                        $template = new \TemplatePower ($pluginTpl);
                        $template->prepare ();

                        file_put_contents ($fileName, $template->getOutputContent ());
                    }

                    //Event
                    $task = new \Task();

                    $arrayTaskData = $task->load ($arrayWebEntryData["TAS_UID"]);

                    $weEventUid = $task->getStartingEvent ();

                    if ( $weEventUid != "" )
                    {
                        $event = new \Event();

                        $arrayEventData = array();

                        $arrayEventData["EVN_UID"] = $weEventUid;
                        $arrayEventData["EVN_RELATED_TO"] = "MULTIPLE";
                        $arrayEventData["EVN_ACTION"] = $dynaFormUid;
                        $arrayEventData["EVN_CONDITIONS"] = $usrUsername;

                        $result = $event->update ($arrayEventData);
                    }

                    //WE_DATA
                    $webEntryData = $weTitle . ".php";
                    break;
                case "HTML":
                    global $G_FORM;

                    if ( !class_exists ("Smarty") )
                    {
                        $loader = \Maveriks\Util\ClassLoader::getInstance ();
                        $loader->addClass ("Smarty", PATH_THIRDPARTY . "smarty" . PATH_SEP . "libs" . PATH_SEP . "Smarty.class.php");
                    }

                    $G_FORM = new \Form ($processUid . "/" . $dynaFormUid, PATH_DYNAFORM, SYS_LANG, false);
                    $G_FORM->action = $http . $_SERVER["HTTP_HOST"] . "/sys" . SYS_SYS . "/" . SYS_LANG . "/" . SYS_SKIN . "/services/cases_StartExternal.php";

                    $scriptCode = "";
                    $scriptCode = $G_FORM->render (PATH_TPL . "xmlform" . ".html", $scriptCode);
                    $scriptCode = str_replace ("/controls/", $http . $_SERVER["HTTP_HOST"] . "/controls/", $scriptCode);
                    $scriptCode = str_replace ("/js/maborak/core/images/", $http . $_SERVER["HTTP_HOST"] . "/js/maborak/core/images/", $scriptCode);

                    //Render the template
                    $pluginTpl = PATH_TPL . "processes" . PATH_SEP . "webentry.tpl";

                    $template = new \TemplatePower ($pluginTpl);
                    $template->prepare ();

                    $step = new \Step();
                    $sUidGrids = $step->lookingforUidGrids ($processUid, $dynaFormUid);

                    $template->assign ("URL_MABORAK_JS", \G::browserCacheFilesUrl ("/js/maborak/core/maborak.js"));
                    $template->assign ("URL_TRANSLATION_ENV_JS", \G::browserCacheFilesUrl ("/jscore/labels/" . SYS_LANG . ".js"));
                    $template->assign ("siteUrl", $http . $_SERVER["HTTP_HOST"]);
                    $template->assign ("sysSys", SYS_SYS);
                    $template->assign ("sysLang", SYS_LANG);
                    $template->assign ("sysSkin", SYS_SKIN);
                    $template->assign ("processUid", $processUid);
                    $template->assign ("dynaformUid", $dynaFormUid);
                    $template->assign ("taskUid", $taskUid);
                    $template->assign ("dynFileName", $processUid . "/" . $dynaFormUid);
                    $template->assign ("formId", $G_FORM->id);
                    $template->assign ("scriptCode", $scriptCode);

                    if ( sizeof ($sUidGrids) > 0 )
                    {
                        foreach ($sUidGrids as $k => $v) {
                            $template->newBlock ("grid_uids");
                            $template->assign ("siteUrl", $http . $_SERVER["HTTP_HOST"]);
                            $template->assign ("gridFileName", $processUid . "/" . $v);
                        }
                    }

                    //WE_DATA
                    $html = str_replace ("</body>", "</form></body>", str_replace ("</form>", "", $template->getOutputContent ()));

                    $webEntryData = $html;
                    break;
            }

            //Update
            //Update where
            $criteriaWhere = new \Criteria ("workflow");
            $criteriaWhere->add (\WebEntryPeer::WE_UID, $webEntryUid);

            //Update set
            $criteriaSet = new \Criteria ("workflow");
            $criteriaSet->add (\WebEntryPeer::WE_DATA, $webEntryData);

            \BasePeer::doUpdate ($criteriaWhere, $criteriaSet, \Propel::getConnection ("workflow"));
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

                $webEntry->setWeUid ($webEntryUid);
                $webEntry->setProUid ($processUid);
                $webEntry->setWeCreateUsrUid ($userUidCreator);
                $webEntry->setWeCreateDate ("now");

                if ( $webEntry->validate () )
                {

                    $result = $webEntry->save ();

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
            $delimiter = \DBAdapter::getStringDelimiter ();

            $criteria = new \Criteria ("workflow");
            
            $sql = "SELECT WE_UID, PRO_UID, TAS_UID, DYN_UID, USR_UID, WE_METHOD, WE_INPUT_DOCUMENT_ACCESS, WE_DATA, WE_CREATE_USR_UID, WE_UPDATE_USR_UID, WE_CREATE_DATE, WE_UPDATE_DATE FROM web_entry";


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
                $http =  "http://";
                $url = $http . $_SERVER["HTTP_HOST"] . "/sys" . SYS_SYS . "/" . SYS_LANG . "/" . SYS_SKIN . "/" . $record["PRO_UID"];

                $record["WE_DATA"] = $url . "/" . $record["WE_DATA"];
            }


            $dateTime = new \DateTime ($record["WE_CREATE_DATE"]);
            $webEntryCreateDate = $dateTime->format ($confEnvSetting["dateFormat"]);

            $webEntryUpdateDate = "";

            if ( !empty ($record["WE_UPDATE_DATE"]) )
            {
                $dateTime = new \DateTime ($record["WE_UPDATE_DATE"]);
                $webEntryUpdateDate = $dateTime->format ($confEnvSetting["dateFormat"]);
            }

            return array(
                $this->getFieldNameByFormatFieldName ("WE_UID") => $record["WE_UID"],
                $this->getFieldNameByFormatFieldName ("TAS_UID") => $record["TAS_UID"],
                $this->getFieldNameByFormatFieldName ("DYN_UID") => $record["DYN_UID"],
                $this->getFieldNameByFormatFieldName ("USR_UID") => $record["USR_UID"] . "",
                $this->getFieldNameByFormatFieldName ("WE_TITLE") => $record["WE_TITLE"] . "",
                $this->getFieldNameByFormatFieldName ("WE_DESCRIPTION") => $record["WE_DESCRIPTION"] . "",
                $this->getFieldNameByFormatFieldName ("WE_METHOD") => $record["WE_METHOD"],
                $this->getFieldNameByFormatFieldName ("WE_INPUT_DOCUMENT_ACCESS") => (int) ($record["WE_INPUT_DOCUMENT_ACCESS"]),
                $this->getFieldNameByFormatFieldName ("WE_DATA") => $record["WE_DATA"],
                $this->getFieldNameByFormatFieldName ("WE_CREATE_USR_UID") => $record["WE_CREATE_USR_UID"],
                $this->getFieldNameByFormatFieldName ("WE_UPDATE_USR_UID") => $record["WE_UPDATE_USR_UID"] . "",
                $this->getFieldNameByFormatFieldName ("WE_CREATE_DATE") => $webEntryCreateDate,
                $this->getFieldNameByFormatFieldName ("WE_UPDATE_DATE") => $webEntryUpdateDate
            );
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


           $results = $this->objMysql->_query($criteria, [$processUid]);

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
            $this->throwExceptionIfNotExistsWebEntry ($webEntryUid, $this->arrayFieldNameForException["webEntryUid"]);

            //Get data
            //SQL
            $criteria = $this->getWebEntryCriteria ();
            
            $criteria .= " WHERE WE_UID = ?";
            $results = $this->objMysql->_query($criteria, [$webEntryUid]);
            
            if(!isset($results[0]) || empty($results[0])) {
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
