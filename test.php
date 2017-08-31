$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        $fileName = $_SERVER['DOCUMENT_ROOT']."public/downloads/test.jpeg";
        //$fileName = $_SERVER['DOCUMENT_ROOT']."public/downloads/mike.jpg";
        $info = pathinfo($fileName);
        $ext = (isset($info['extension']) ? $info['extension'] : '');

        $filename = $info['basename'];
        $mimeType = $this->mime_content_type($filename);

        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Content-Transfer-Encoding: binary');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . filesize($fileName));
        header("Content-Type: $mimeType");
        header("Content-Description: File Transfer");

        if ($fp = fopen($fileName, 'rb')) {
            ob_end_clean();
            while (!feof($fp) and (connection_status() == 0)) {
                print(fread($fp, 8192));
                flush();
            }
            @fclose($fp);
        }



// old
 $this->_model->setSystemId(SYSTEM_ID);
        $this->_model->setId($id);
        $attachmentArray = $this->_model->getAttachments();

        $fileContent = $attachmentArray[0]["content"];
        $tempFilename = "/tmp/" . hash('ripemd160', rand(10000, 99999));
        $file = file_put_contents($tempFilename, $fileContent);

        $response = $this->response;
        $response->setHeader("Content-Type", mime_content_type($tempFilename));
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        $response->setHeader("Content-Disposition", 'attachment; filename="' . $attachmentArray[0]["filename"] . '"');
        $response->setContent($fileContent);
        $response->send();





 private $objMysql;

    private function getConnection()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     *
     * @param string $folderName
     * @param strin(32) $folderParent
     * @return Ambigous <>|number
     */
    public function createFolder ($folderName, $folderParent = "/", $action = "createifnotexists")
    {
        if($this->objMysql === null) {
            $this->getConnection();
        }

        $validActions = array ("createifnotexists","create","update");
        if (! in_array( $action, $validActions )) {
            $action = "createifnotexists";
        }

        //Clean Folder and Parent names (delete spaces...)
        $folderName = trim( $folderName );
        $folderParent = trim( $folderParent );

        //Try to Load the folder (Foldername+FolderParent)

        $result = $this->objMysql->_select("workflow.APP_FOLDER", [], ["FOLDER_NAME" => $folderName, "FOLDER_PARENT_UID" => $folderParent]);

       if(isset($result[0]) && !empty($result[0])) {
            //Folder exist, then return the ID
            $response['success'] = false;
            $response['message'] = $response['error'] = "CANT_CREATE_FOLDER_A_FOLDER_WITH_SAME_NAME_ALREADY_EXIST"  . $folderName;
            $response['folderUID'] = $result[0]['FOLDER_UID'];
            //return ($aRow ['FOLDER_UID']);
            return ($response);
        } else {
            //Folder doesn't exist. Create and return the ID
            $tr = new AppFolder();
            $tr->setFolderParentUid( $folderParent );
            $tr->setFolderName( $folderName );
            $tr->setFolderCreateDate( 'now' );
            $tr->setFolderUpdateDate( 'now' );

            if ($tr->validate()) {
                // we save it, since we get no validation errors, or do whatever else you like.
                $folderUID = $tr->save();
                $response['success'] = true;
                $response['message'] = "Folder successfully created. <br /> $folderName";
                $response['folderUID'] = $folderUID;
                return ($response);
                //return $folderUID;
            } else {
                // Something went wrong. We can now get the validationFailures and handle them.
                $msg = '';
                $validationFailuresArray = $tr->getValidationFailures();
                foreach ($validationFailuresArray as $objValidationFailure) {
                    $msg .= $objValidationFailure . "<br/>";
                }
                $response['success'] = false;
                $response['message'] = $response['error'] =  "CANT_CREATE_FOLDER_A"  . $msg;
                return ($response);
            }
        }
    }

    /**
     * @param $pk
     */
    public function retrieveByPk($pk)
    {
        if($this->objMysql === null) {
            $this->getConnection();
        }

	$result = $this->objMysql->_select("workflow.APP_FOLDER", [], ["FOLDER_UID" => $pk]);

	if(!isset($result[0]) || empty($result[0])) {
		return false;
	}

	$appFolder = new AppFolder();

	return $appFolder;

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

            $oAppFolder = $this->retrieveByPK( $aData['FOLDER_UID'] );
            if ($oAppFolder !== false) {

                if ($oAppFolder->validate()) {
                    if (isset( $aData['FOLDER_NAME'] )) {
                        $oAppFolder->setFolderName( $aData['FOLDER_NAME'] );
                    }
                    if (isset( $aData['FOLDER_UID'] )) {
                        $oAppFolder->setFolderUid( $aData['FOLDER_UID'] );
                    }
                    if (isset( $aData['FOLDER_UPDATE_DATE'] )) {
                        $oAppFolder->setFolderUpdateDate( $aData['FOLDER_UPDATE_DATE'] );
                    }
                    $iResult = $oAppFolder->save();
                    return $iResult;
                } else {
                    $sMessage = '';
                    $aValidationFailures = $oAppFolder->getValidationFailures();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception( 'The registry cannot be updated!<br />' . $sMessage ));
                }
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }


    /**
     *
     * @param string $folderPath
     * @return string Last Folder ID generated
     */
    public function createFromPath ($folderPath)
    {

        $folderPathParsedArray = explode( "/", $folderPath );
        $folderRoot = "/"; //Always starting from Root
        foreach ($folderPathParsedArray as $folderName) {
            if (trim( $folderName ) != "") {
                $response = $this->createFolder( $folderName, $folderRoot );
                $folderRoot = $response['folderUID'];
            }
        }
        return $folderRoot != "/" ? $folderRoot : "";
    }

    public function remove($FolderUid, $rootfolder)
    {
        $appFolder = new AppFolder();
        $appFolder->setFolderUid($FolderUid);
        $appFolder->delete();
    }



/**
 * Base class that represents a row from the 'APP_FOLDER' table.
 *
 *
 *
 */
abstract class BaseAppFolder implements Persistent
{

    /**
     * The value for the folder_uid field.
     * @var        string
     */
    protected $folder_uid = '';

    /**
     * The value for the folder_parent_uid field.
     * @var        string
     */
    protected $folder_parent_uid = '';

    /**
     * The value for the folder_name field.
     * @var        string
     */
    protected $folder_name;

    /**
     * The value for the folder_create_date field.
     * @var        int
     */
    protected $folder_create_date;

    /**
     * The value for the folder_update_date field.
     * @var        int
     */
    protected $folder_update_date;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    private $objMysql;

    private $arrFieldMapping = array(
        "FOLDER_NAME" => array("mutator" => "setFolderName", "accessor" => "getFolderName", "type" => "int", "required" => "true"),
        "FOLDER_PARENT" => array("mutator" => "setFolderParentUid", "accessor" => "getFolderParentUid", "type" => "int", "required" => "true"),
        "UPDATE_DATE" => array("mutator" => "setFolderUpdateDate", "accessor" => "getFolderUpdateDate", "type" => "int", "required" => "true"),
        "CREATE_DATE" => array("mutator" => "setFolderCreateDate", "accessor" => "getFolderCreateDate", "type" => "int", "required" => "true"),
    );

    public function __construct()
    {
        $this->objMysql = new Mysql2();
    }

    public function loadObject(array $arrData)
    {
        foreach ($arrData as $strFieldKey => $varFieldValue) {
            if (isset($this->arrFieldMapping[$strFieldKey])) {
                $strMutatorMethod = $this->arrFieldMapping[$strFieldKey]['mutator'];

                if (is_callable(array($this, $strMutatorMethod)) && $varFieldValue != "") {
                    call_user_func(array($this, $strMutatorMethod), $varFieldValue);
                }
            }
        }
    }

    /**
     * Get the [folder_uid] column value.
     *
     * @return     string
     */
    public function getFolderUid()
    {

        return $this->folder_uid;
    }

    /**
     * Get the [folder_parent_uid] column value.
     *
     * @return     string
     */
    public function getFolderParentUid()
    {

        return $this->folder_parent_uid;
    }

    /**
     * Get the [folder_name] column value.
     *
     * @return     string
     */
    public function getFolderName()
    {

        return $this->folder_name;
    }

    /**
     * Get the [optionally formatted] [folder_create_date] column value.
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getFolderCreateDate($format = 'Y-m-d H:i:s')
    {

        if ($this->folder_create_date === null || $this->folder_create_date === '') {
            return null;
        } elseif (!is_int($this->folder_create_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->folder_create_date);
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse value of [folder_create_date] as date/time value: " );
            }
        } else {
            $ts = $this->folder_create_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Get the [optionally formatted] [folder_update_date] column value.
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     Exception - if unable to convert the date/time to timestamp.
     */
    public function getFolderUpdateDate($format = 'Y-m-d H:i:s')
    {

        if ($this->folder_update_date === null || $this->folder_update_date === '') {
            return null;
        } elseif (!is_int($this->folder_update_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->folder_update_date);
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse value of [folder_update_date] as date/time value: ");
            }
        } else {
            $ts = $this->folder_update_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Set the value of [folder_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setFolderUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->folder_uid !== $v || $v === '') {
            $this->folder_uid = $v;
        }

    }

    /**
     * Set the value of [folder_parent_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setFolderParentUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->folder_parent_uid !== $v || $v === '') {
            $this->folder_parent_uid = $v;
        }

    }

    /**
     * Set the value of [folder_name] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setFolderName($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->folder_name !== $v) {
            $this->folder_name = $v;
        }

    }

    /**
     * Set the value of [folder_create_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setFolderCreateDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [folder_create_date] from input: ");
            }
        } else {
            $ts = $v;
        }
        if ($this->folder_create_date !== $ts) {
            $this->folder_create_date = date("Y-m-d H:i:s", $ts);
        }

    }

    /**
     * Set the value of [folder_update_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setFolderUpdateDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new Exception("Unable to parse date/time value for [folder_update_date] from input: ");
            }
        } else {
            $ts = $v;
        }
        if ($this->folder_update_date !== $ts) {
            $this->folder_update_date = date("Y-m-d H:i:s", $ts);
        }

    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @return     void
     */
    public function delete()
    {
        $result = $this->objMysql->_delete("workflow.APP_FOLDER", ["FOLDER_UID" => $this->folder_uid]);
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed. 
     *
     * @return     int The number of rows affected by this insert/update
     */
    public function save()
    {
	if(trim($this->folder_uid) === "") {
		$id = $this->objMysql->_insert("workflow.APP_FOLDER", [
			"FOLDER_PARENT_UID" => $this->folder_parent_uid, 
			"FOLDER_NAME" => $this->folder_name, 
			"FOLDER_CREATE_DATE" => $this->folder_create_date, 
			"FOLDER_UPDATE_DATE" => $this->folder_update_date
			]);

		return $id;

	} else {
		$this->objMysql->_update("workflow.APP_FOLDER", [
			"FOLDER_PARENT_UID" => $this->folder_parent_uid, 
			"FOLDER_NAME" => $this->folder_name, 
			"FOLDER_CREATE_DATE" => $this->folder_create_date, 
			"FOLDER_UPDATE_DATE" => $this->folder_update_date
			], ["FOLDER_UID" => $this->folder_uid]);
	}
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    private function validate()
    {
        foreach ($this->arrFieldMapping as $strColumnName => $arrFieldMap) {

            $strFormattedColumn = ucfirst(str_replace("_", " ", $strColumnName));
            $strFormattedColumn = ucfirst(join(preg_split('/(?<=[a-z])(?=[A-Z])/x', $strColumnName), " "));

            if ($arrFieldMap['required'] === 'true') {

                if (trim($this->{$arrFieldMap['accessor']}()) === "") {
                    $this->validationFailures[] = $strFormattedColumn . " is missing";
                }
            }
        }

        return count($this->validationFailures) > 0 ? false : true;
    }
}
