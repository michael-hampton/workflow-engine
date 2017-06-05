<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseOutputDocument
 *
 * @author michael.hampton
 */
abstract class BaseOutputDocument
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        OutputDocumentPeer
    */
    protected static $peer;
    /**
     * The value for the out_doc_uid field.
     * @var        string
     */
    protected $out_doc_uid = '';
    /**
     * The value for the out_doc_title field.
     * @var        string
     */
    protected $out_doc_title;
    /**
     * The value for the out_doc_description field.
     * @var        string
     */
    protected $out_doc_description;
    /**
     * The value for the out_doc_filename field.
     * @var        string
     */
    protected $out_doc_filename;
    /**
     * The value for the out_doc_template field.
     * @var        string
     */
    protected $out_doc_template;
    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';
    /**
     * The value for the out_doc_report_generator field.
     * @var        string
     */
    protected $out_doc_report_generator = 'HTML2PDF';
    /**
     * The value for the out_doc_landscape field.
     * @var        int
     */
    protected $out_doc_landscape = 0;
    /**
     * The value for the out_doc_media field.
     * @var        string
     */
    protected $out_doc_media = 'Letter';
    /**
     * The value for the out_doc_left_margin field.
     * @var        int
     */
    protected $out_doc_left_margin = 30;
    /**
     * The value for the out_doc_right_margin field.
     * @var        int
     */
    protected $out_doc_right_margin = 15;
    /**
     * The value for the out_doc_top_margin field.
     * @var        int
     */
    protected $out_doc_top_margin = 15;
    /**
     * The value for the out_doc_bottom_margin field.
     * @var        int
     */
    protected $out_doc_bottom_margin = 15;
    /**
     * The value for the out_doc_generate field.
     * @var        string
     */
    protected $out_doc_generate = 'BOTH';
    /**
     * The value for the out_doc_type field.
     * @var        string
     */
    protected $out_doc_type = 'HTML';
    /**
     * The value for the out_doc_current_revision field.
     * @var        int
     */
    protected $out_doc_current_revision = 0;
    /**
     * The value for the out_doc_field_mapping field.
     * @var        string
     */
    protected $out_doc_field_mapping;
    /**
     * The value for the out_doc_versioning field.
     * @var        int
     */
    protected $out_doc_versioning = 0;
    /**
     * The value for the out_doc_destination_path field.
     * @var        string
     */
    protected $out_doc_destination_path;
    /**
     * The value for the out_doc_tags field.
     * @var        string
     */
    protected $out_doc_tags;
    /**
     * The value for the out_doc_pdf_security_enabled field.
     * @var        int
     */
    protected $out_doc_pdf_security_enabled = 0;
    /**
     * The value for the out_doc_pdf_security_open_password field.
     * @var        string
     */
    protected $out_doc_pdf_security_open_password = '';
    /**
     * The value for the out_doc_pdf_security_owner_password field.
     * @var        string
     */
    protected $out_doc_pdf_security_owner_password = '';
    /**
     * The value for the out_doc_pdf_security_permissions field.
     * @var        string
     */
    protected $out_doc_pdf_security_permissions = '';
    /**
     * The value for the out_doc_open_type field.
     * @var        int
     */
    protected $out_doc_open_type = 1;
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
    /**
     * Get the [out_doc_uid] column value.
     * 
     * @return     string
     */
    public function getOutDocUid()
    {
        return $this->out_doc_uid;
    }
    /**
     * Get the [out_doc_title] column value.
     * 
     * @return     string
     */
    public function getOutDocTitle()
    {
        return $this->out_doc_title;
    }
    /**
     * Get the [out_doc_description] column value.
     * 
     * @return     string
     */
    public function getOutDocDescription()
    {
        return $this->out_doc_description;
    }
    /**
     * Get the [out_doc_filename] column value.
     * 
     * @return     string
     */
    public function getOutDocFilename()
    {
        return $this->out_doc_filename;
    }
    /**
     * Get the [out_doc_template] column value.
     * 
     * @return     string
     */
    public function getOutDocTemplate()
    {
        return $this->out_doc_template;
    }
    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid()
    {
        return $this->pro_uid;
    }
    /**
     * Get the [out_doc_report_generator] column value.
     * 
     * @return     string
     */
    public function getOutDocReportGenerator()
    {
        return $this->out_doc_report_generator;
    }
    /**
     * Get the [out_doc_landscape] column value.
     * 
     * @return     int
     */
    public function getOutDocLandscape()
    {
        return $this->out_doc_landscape;
    }
    /**
     * Get the [out_doc_media] column value.
     * 
     * @return     string
     */
    public function getOutDocMedia()
    {
        return $this->out_doc_media;
    }
    /**
     * Get the [out_doc_left_margin] column value.
     * 
     * @return     int
     */
    public function getOutDocLeftMargin()
    {
        return $this->out_doc_left_margin;
    }
    /**
     * Get the [out_doc_right_margin] column value.
     * 
     * @return     int
     */
    public function getOutDocRightMargin()
    {
        return $this->out_doc_right_margin;
    }
    /**
     * Get the [out_doc_top_margin] column value.
     * 
     * @return     int
     */
    public function getOutDocTopMargin()
    {
        return $this->out_doc_top_margin;
    }
    /**
     * Get the [out_doc_bottom_margin] column value.
     * 
     * @return     int
     */
    public function getOutDocBottomMargin()
    {
        return $this->out_doc_bottom_margin;
    }
    /**
     * Get the [out_doc_generate] column value.
     * 
     * @return     string
     */
    public function getOutDocGenerate()
    {
        return $this->out_doc_generate;
    }
    /**
     * Get the [out_doc_type] column value.
     * 
     * @return     string
     */
    public function getOutDocType()
    {
        return $this->out_doc_type;
    }
    /**
     * Get the [out_doc_current_revision] column value.
     * 
     * @return     int
     */
    public function getOutDocCurrentRevision()
    {
        return $this->out_doc_current_revision;
    }
    /**
     * Get the [out_doc_field_mapping] column value.
     * 
     * @return     string
     */
    public function getOutDocFieldMapping()
    {
        return $this->out_doc_field_mapping;
    }
    /**
     * Get the [out_doc_versioning] column value.
     * 
     * @return     int
     */
    public function getOutDocVersioning()
    {
        return $this->out_doc_versioning;
    }
    /**
     * Get the [out_doc_destination_path] column value.
     * 
     * @return     string
     */
    public function getOutDocDestinationPath()
    {
        return $this->out_doc_destination_path;
    }
    /**
     * Get the [out_doc_tags] column value.
     * 
     * @return     string
     */
    public function getOutDocTags()
    {
        return $this->out_doc_tags;
    }
    /**
     * Get the [out_doc_pdf_security_enabled] column value.
     * 
     * @return     int
     */
    public function getOutDocPdfSecurityEnabled()
    {
        return $this->out_doc_pdf_security_enabled;
    }
    /**
     * Get the [out_doc_pdf_security_open_password] column value.
     * 
     * @return     string
     */
    public function getOutDocPdfSecurityOpenPassword()
    {
        return $this->out_doc_pdf_security_open_password;
    }
    /**
     * Get the [out_doc_pdf_security_owner_password] column value.
     * 
     * @return     string
     */
    public function getOutDocPdfSecurityOwnerPassword()
    {
        return $this->out_doc_pdf_security_owner_password;
    }
    /**
     * Get the [out_doc_pdf_security_permissions] column value.
     * 
     * @return     string
     */
    public function getOutDocPdfSecurityPermissions()
    {
        return $this->out_doc_pdf_security_permissions;
    }
    /**
     * Get the [out_doc_open_type] column value.
     * 
     * @return     int
     */
    public function getOutDocOpenType()
    {
        return $this->out_doc_open_type;
    }
    /**
     * Set the value of [out_doc_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_uid !== $v || $v === '') {
            $this->out_doc_uid = $v;
        }
    } // setOutDocUid()
    /**
     * Set the value of [out_doc_title] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocTitle($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_title !== $v) {
            $this->out_doc_title = $v;
        }
    } // setOutDocTitle()
    /**
     * Set the value of [out_doc_description] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocDescription($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_description !== $v) {
            $this->out_doc_description = $v;
        }
    } // setOutDocDescription()
    /**
     * Set the value of [out_doc_filename] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocFilename($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_filename !== $v) {
            $this->out_doc_filename = $v;
        }
    } // setOutDocFilename()
    /**
     * Set the value of [out_doc_template] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocTemplate($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_template !== $v) {
            $this->out_doc_template = $v;
        }
    } // setOutDocTemplate()
    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->pro_uid !== $v || $v === '') {
            $this->pro_uid = $v;
        }
    } // setProUid()
    /**
     * Set the value of [out_doc_report_generator] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocReportGenerator($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_report_generator !== $v || $v === 'HTML2PDF') {
            $this->out_doc_report_generator = $v;
        }
    } // setOutDocReportGenerator()
    /**
     * Set the value of [out_doc_landscape] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocLandscape($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_landscape !== $v || $v === 0) {
            $this->out_doc_landscape = $v;
        }
    } // setOutDocLandscape()
    /**
     * Set the value of [out_doc_media] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocMedia($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_media !== $v || $v === 'Letter') {
            $this->out_doc_media = $v;
        }
    } // setOutDocMedia()
    /**
     * Set the value of [out_doc_left_margin] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocLeftMargin($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_left_margin !== $v || $v === 30) {
            $this->out_doc_left_margin = $v;
        }
    } // setOutDocLeftMargin()
    /**
     * Set the value of [out_doc_right_margin] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocRightMargin($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_right_margin !== $v || $v === 15) {
            $this->out_doc_right_margin = $v;
        }
    } // setOutDocRightMargin()
    /**
     * Set the value of [out_doc_top_margin] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocTopMargin($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_top_margin !== $v || $v === 15) {
            $this->out_doc_top_margin = $v;
        }
    } // setOutDocTopMargin()
    /**
     * Set the value of [out_doc_bottom_margin] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocBottomMargin($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_bottom_margin !== $v || $v === 15) {
            $this->out_doc_bottom_margin = $v;
        }
    } // setOutDocBottomMargin()
    /**
     * Set the value of [out_doc_generate] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocGenerate($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_generate !== $v || $v === 'BOTH') {
            $this->out_doc_generate = $v;
        }
    } // setOutDocGenerate()
    /**
     * Set the value of [out_doc_type] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocType($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_type !== $v || $v === 'HTML') {
            $this->out_doc_type = $v;
        }
    } // setOutDocType()
    /**
     * Set the value of [out_doc_current_revision] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocCurrentRevision($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_current_revision !== $v || $v === 0) {
            $this->out_doc_current_revision = $v;
        }
    } // setOutDocCurrentRevision()
    /**
     * Set the value of [out_doc_field_mapping] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocFieldMapping($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_field_mapping !== $v) {
            $this->out_doc_field_mapping = $v;
        }
    } // setOutDocFieldMapping()
    /**
     * Set the value of [out_doc_versioning] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocVersioning($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_versioning !== $v || $v === 0) {
            $this->out_doc_versioning = $v;
        }
    } // setOutDocVersioning()
    /**
     * Set the value of [out_doc_destination_path] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocDestinationPath($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_destination_path !== $v) {
            $this->out_doc_destination_path = $v;
        }
    } // setOutDocDestinationPath()
    /**
     * Set the value of [out_doc_tags] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocTags($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_tags !== $v) {
            $this->out_doc_tags = $v;
        }
    } // setOutDocTags()
    /**
     * Set the value of [out_doc_pdf_security_enabled] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocPdfSecurityEnabled($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_pdf_security_enabled !== $v || $v === 0) {
            $this->out_doc_pdf_security_enabled = $v;
        }
    } // setOutDocPdfSecurityEnabled()
    /**
     * Set the value of [out_doc_pdf_security_open_password] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocPdfSecurityOpenPassword($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_pdf_security_open_password !== $v || $v === '') {
            $this->out_doc_pdf_security_open_password = $v;
        }
    } // setOutDocPdfSecurityOpenPassword()
    /**
     * Set the value of [out_doc_pdf_security_owner_password] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocPdfSecurityOwnerPassword($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_pdf_security_owner_password !== $v || $v === '') {
            $this->out_doc_pdf_security_owner_password = $v;
        }
    } // setOutDocPdfSecurityOwnerPassword()
    /**
     * Set the value of [out_doc_pdf_security_permissions] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutDocPdfSecurityPermissions($v)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }
        if ($this->out_doc_pdf_security_permissions !== $v || $v === '') {
            $this->out_doc_pdf_security_permissions = $v;
        }
    } // setOutDocPdfSecurityPermissions()
    /**
     * Set the value of [out_doc_open_type] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setOutDocOpenType($v)
    {
        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }
        if ($this->out_doc_open_type !== $v || $v === 1) {
            $this->out_doc_open_type = $v;
        }
    } // setOutDocOpenType()
   
    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
        
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
    public function save($con = null)
    {
       
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
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }
    
}
