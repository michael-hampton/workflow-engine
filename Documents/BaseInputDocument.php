<?php

abstract class BaseInputDocument implements Persistent
{

    private $title;
    private $description;
    private $versioning;
    private $destinationPath;
    private $fileType;
    private $maxFileSize;
    private $filesizeUnit;
    private $id;
    public $arrValidationErrors = array();
    private $objMysql;
    private $arrayFieldDefinition = array(
        "INP_DOC_UID" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getId", "mutator" => "setId"),
        "INP_DOC_TITLE" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getTitle", "mutator" => "setTitle"),
        "INP_DOC_DESCRIPTION" => array("type" => "string", "required" => false, "empty" => true, "accessor" => "getDescription", "mutator" => "setDescription"),
        "INP_DOC_FORM_NEEDED" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "", "mutator" => ""),
        "INP_DOC_ORIGINAL" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "", "mutator" => ""),
        "INP_DOC_PUBLISHED" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "", "mutator" => ""),
        "INP_DOC_VERSIONING" => array("type" => "int", "required" => false, "empty" => false, "accessor" => "getVersioning", "mutator" => "setVersioning"),
        "INP_DOC_DESTINATION_PATH" => array("type" => "string", "required" => false, "empty" => true, "accessor" => "getDestinationPath", "mutator" => "setDestinationPath"),
        "INP_DOC_TAGS" => array("type" => "string", "required" => false, "empty" => true, "accessor" => "", "mutator" => ""),
        "INP_DOC_TYPE_FILE" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFileType", "mutator" => "setFileType"),
        "INP_DOC_MAX_FILESIZE" => array("type" => "int", "required" => true, "empty" => false, "accessor" => "getMaxFileSize", "mutator" => "setMaxFileSize"),
        "INP_DOC_MAX_FILESIZE_UNIT" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFilesizeUnit", "mutator" => "setFilesizeUnit")
    );

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrDocument
     * @return boolean
     */
    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrayFieldDefinition[$formField]) )
            {
                $mutator = $this->arrayFieldDefinition[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrayFieldDefinition[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }

    public function getId ()
    {
        return $this->id;
    }

    /**
     * 
     * @param type $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getTitle ()
    {
        return $this->title;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function getVersioning ()
    {
        return $this->versioning;
    }

    public function getDestinationPath ()
    {
        return $this->destinationPath;
    }

    public function getFileType ()
    {
        return $this->fileType;
    }

    public function getMaxFileSize ()
    {
        return $this->maxFileSize;
    }

    public function getFilesizeUnit ()
    {
        return $this->filesizeUnit;
    }

    /**
     * 
     * @param type $title
     */
    public function setTitle ($title)
    {
        $this->title = $title;
    }

    /**
     * 
     * @param type $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
    }

    /**
     * 
     * @param type $versioning
     */
    public function setVersioning ($versioning)
    {
        $this->versioning = $versioning;
    }

    /**
     * 
     * @param type $destinationPath
     */
    public function setDestinationPath ($destinationPath)
    {
        $this->destinationPath = $destinationPath;
    }

    /**
     * 
     * @param type $fileType
     */
    public function setFileType ($fileType)
    {
        $this->fileType = $fileType;
    }

    /**
     * 
     * @param type $maxFileSize
     */
    public function setMaxFileSize ($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * 
     * @param type $filesizeUnit
     */
    public function setFilesizeUnit ($filesizeUnit)
    {
        $this->filesizeUnit = $filesizeUnit;
    }

    /**
     * 
     * @return boolean
     */
    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrayFieldDefinition as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $this->arrayFieldDefinition[$fieldName]['accessor'];

                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->arrValidationErrors[] = $fieldName . " Is empty. It is a required field";
                    $errorCount++;
                }
            }
        }

        if ( $errorCount > 0 )
        {
            return FALSE;
        }

        return TRUE;
    }

    public function getArrValidationErrors ()
    {
        return $this->arrValidationErrors;
    }

    /**
     * 
     * @param type $arrValidationErrors
     */
    public function setArrValidationErrors ($arrValidationErrors)
    {
        $this->arrValidationErrors = $arrValidationErrors;
    }

    /**
     * 
     * @return type
     */
    public function save ()
    {
        $id = $this->objMysql->_insert (
                "workflow.documents", array(
            "max_filesize" => $this->maxFileSize,
            "filetype" => $this->fileType,
            "name" => $this->title,
            "description" => $this->description,
            "allow_versioning" => $this->versioning,
            "destination_path" => $this->destinationPath,
            "filesize_unit" => $this->filesizeUnit,
                )
        );

        return $id;
    }

    /**
     * 
     */
    public function doUpdate ()
    {
        $this->objMysql->_update (
                "workflow.documents", array(
            "max_filesize" => $this->maxFileSize,
            "filetype" => $this->fileType,
            "name" => $this->title,
            "description" => $this->description,
            "allow_versioning" => $this->versioning,
            "destination_path" => $this->destinationPath,
            "filesize_unit" => $this->filesizeUnit
                ), array("id" => $this->id)
        );
    }

    public function remove ()
    {
        if ( trim ($this->id) == "" || !is_numeric ($this->id) )
        {
            throw new Exception ("Invalid id given");
        }

        $this->objMysql->_delete ("workflow.documents", array("id" => $this->id));
    }

}
