<?php

class BaseDocumentVersion implements Persistent
{

    /**
     * The value for the app_doc_uid field.
     * @var        string
     */
    protected $app_doc_uid = '';

    /**
     * The value for the app_doc_filename field.
     * @var        string
     */
    protected $app_doc_filename;

    /**
     * The value for the doc_version field.
     * @var        int
     */
    protected $doc_version = 1;

    /**
     * The value for the doc_uid field.
     * @var        string
     */
    protected $doc_uid = '';

    /**
     * The value for the usr_uid field.
     * @var        string
     */
    protected $usr_uid = '';

    /**
     * The value for the app_doc_type field.
     * @var        string
     */
    protected $app_doc_type = '';

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the app_doc_status field.
     * @var        string
     */
    protected $app_doc_status = 'ACTIVE';

    /**
     * The value for the folder_uid field.
     * @var        string
     */
    protected $folder_uid = '';
    
     /**
     * The value for the app_doc_title field.
     * @var        string
     */
    protected $app_doc_title;
   
    /**
     * The value for the app_doc_comment field.
     * @var        string
     */
    protected $app_doc_comment;
   
    /**
     * The value for the app_doc_tags field.
     * @var        string
     */
    protected $app_doc_tags;

    /**
     * The value for the app_doc_create_date field.
     * @var        int
     */
    protected $app_doc_create_date;
    private $objMysql;
    public $arrFieldMapping = array(
        "APP_DOC_UID" => array("mutator" => "setAppDocUid", "accessor" => "getAppDocUid", "type" => "date", "required" => "true"),
        "DOC_VERSION" => array("mutator" => "setDocVersion", "accessor" => "getDocVersion", "type" => "date", "required" => "true"),
        "APP_DOC_STATUS" => array("mutator" => "setAppDocStatus", "accessor" => "getAppDocStatus", "type" => "int", "required" => "true"),
        "APP_DOC_FILENAME" => array("mutator" => "setAppDocFilename", "accessor" => "getAppDocFilename", "type" => "string", "required" => "true"),
    );

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get the [app_doc_uid] column value.
     *
     * @return     string
     */
    public function getAppDocUid ()
    {

        return $this->app_doc_uid;
    }

    /**
     * Get the [app_doc_filename] column value.
     *
     * @return     string
     */
    public function getAppDocFilename ()
    {

        return $this->app_doc_filename;
    }

    /**
     * Get the [doc_version] column value.
     *
     * @return     int
     */
    public function getDocVersion ()
    {

        return $this->doc_version;
    }

    /**
     * Get the [doc_uid] column value.
     *
     * @return     string
     */
    public function getDocUid ()
    {

        return $this->doc_uid;
    }

    /**
     * Get the [usr_uid] column value.
     *
     * @return     string
     */
    public function getUsrUid ()
    {

        return $this->usr_uid;
    }
      
    /**
     * Get the [app_doc_title] column value.
     * 
     * @return     string
     */
    public function getAppDocTitle()
    {
        return $this->app_doc_title;
    }
    
    /**
     * Get the [app_doc_comment] column value.
     * 
     * @return     string
     */
    public function getAppDocComment()
    {
        return $this->app_doc_comment;
    }

    /**
     * Get the [app_doc_type] column value.
     *
     * @return     string
     */
    public function getAppDocType ()
    {

        return $this->app_doc_type;
    }
    
     /**
     * Get the [app_doc_tags] column value.
     * 
     * @return     string
     */
    public function getAppDocTags()
    {
        return $this->app_doc_tags;
    }

    /**
     * Get the [app_uid] column value.
     *
     * @return     string
     */
    public function getAppUid ()
    {
        return $this->app_uid;
    }

    /**
     * Get the [app_doc_status] column value.
     *
     * @return     string
     */
    public function getAppDocStatus ()
    {
        return $this->app_doc_status;
    }

    /**
     * Get the [folder_uid] column value.
     * 
     * @return     string
     */
    public function getFolderUid ()
    {
        return $this->folder_uid;
    }

    /**
     * Set the value of [app_doc_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_doc_uid !== $v || $v === '' )
        {
            $this->app_doc_uid = $v;
        }
    }

    /**
     * Set the value of [app_doc_filename] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocFilename ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_doc_filename !== $v )
        {
            $this->app_doc_filename = $v;
        }
    }

    /**
     * Set the value of [doc_version] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setDocVersion ($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ( $v !== null && !is_int ($v) && is_numeric ($v) )
        {
            $v = (int) $v;
        }

        if ( $this->doc_version !== $v || $v === 1 )
        {
            $this->doc_version = $v;
        }
    }

    /**
     * Set the value of [doc_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setDocUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->doc_uid !== $v || $v === '' )
        {
            $this->doc_uid = $v;
        }
    }

    /**
     * Set the value of [usr_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setUsrUid ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->usr_uid !== $v || $v === '' )
        {
            $this->usr_uid = $v;
        }
    }

    /**
     * Set the value of [app_doc_type] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocType ($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }

        if ( $this->app_doc_type !== $v || $v === '' )
        {
            $this->app_doc_type = $v;
        }
    }

    /**
     * Set the value of [app_doc_status] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocStatus ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->app_doc_status !== $v || $v === 'ACTIVE' )
        {
            $this->app_doc_status = $v;
        }
    }

    /**
     * Set the value of [app_uid] column.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->app_uid !== $v || $v === '' )
        {
            $this->app_uid = $v;
        }
    }

    /**
     * Set the value of [folder_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setFolderUid ($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $v !== null && !is_string ($v) )
        {
            $v = (string) $v;
        }
        if ( $this->folder_uid !== $v || $v === '' )
        {
            $this->folder_uid = $v;
        }
    }

    public function getAppDocCreateDate ()
    {
        return $this->app_doc_create_date;
    }

    /**
     * Set the value of [app_doc_create_date] column.
     *
     * @param      int $v new value
     * @return     void
     */
    public function setAppDocCreateDate ($v)
    {

        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse date/time value for [app_doc_create_date] from input: " .
                var_export ($v, true));
            }

            $ts = date ("Y-m-d H:i:s", $ts);
        }
        else
        {
            $ts = date ("Y-m-d H:i:s", $ts);
        }
        if ( $this->app_doc_create_date !== $ts )
        {
            $this->app_doc_create_date = $ts;
        }
    }
    
     /**
     * Set the value of [app_doc_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocTitle($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        
        if ($this->app_doc_title !== $v) {
            $this->app_doc_title = $v;
        }
    }
    
    /**
     * Set the value of [app_doc_comment] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocComment($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        
        if ($this->app_doc_comment !== $v) {
            $this->app_doc_comment = $v;
        }
    } 
    
     /**
     * Set the value of [app_doc_tags] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppDocTags($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        
        if ($this->app_doc_tags !== $v) {
            $this->app_doc_tags = $v;
        }
    } 

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save ()
    {

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( trim ($this->app_doc_uid) === "" )
        {
            $id = $this->objMysql->_insert (
                    "task_manager.APP_DOCUMENT", array(
                "document_version" => $this->doc_version,
                "user_id" => $this->usr_uid,
                "document_id" => $this->doc_uid,
                "date_created" => $this->app_doc_create_date,
                "filename" => $this->app_doc_filename,
                "document_type" => $this->app_doc_type,
                "app_id" => $this->app_uid,
                "status" => $this->app_doc_status,
                "FOLDER_UID" => $this->folder_uid
                    )
            );

            return $id;
        }
        else
        {
            $this->objMysql->_update ("task_manager.APP_DOCUMENT", [
                "document_version" => $this->doc_version,
                "user_id" => $this->usr_uid,
                "document_id" => $this->doc_uid,
                "date_created" => $this->app_doc_create_date,
                "filename" => $this->app_doc_filename,
                "document_type" => $this->app_doc_type,
                "app_id" => $this->app_uid,
                "status" => $this->app_doc_status,
                "FOLDER_UID" => $this->folder_uid], ["id" => $this->app_doc_uid]);
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
    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * @return     boolean Whether all columns pass validation.
     * @see        getValidationFailures()
     */
    public function validate ()
    {
        if ( trim ($this->app_uid) === "" )
        {
            $this->validationFailures[] = "App Id is missing";
        }

        if ( count ($this->validationFailures) > 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * @param array $arrData
     * @return bool
     */
    public function loadObject (array $arrData)
    {
        if ( !empty ($arrData) )
        {
            foreach ($this->arrFieldMapping as $strFieldKey => $arrFields) {
                if ( isset ($arrData[$strFieldKey]) )
                {
                    $strMutatorMethod = $arrFields['mutator'];

                    if ( is_callable (array($this, $strMutatorMethod)) )
                    {
                        call_user_func (array($this, $strMutatorMethod), $arrData[$strFieldKey]);
                    }
                }
            }
        }

        return true;
    }

}
