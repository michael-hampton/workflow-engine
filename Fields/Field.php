<?php

class Field
{

    private $fieldId;

    /**
     * The value for the fieldType field.
     * @var        string
     */
    private $fieldType;
    private $isReadOnly;

    /**
     * The value for the fieldName field.
     * @var        string
     */
    private $fieldName;
    private $label;
    private $id;
    private $dataType;
    private $options;
    private $required_field;
    private $defaultValue;
    private $fieldClass;
    private $placeholder;
    private $maxLength;
    private $fieldConditions;
    private $customJavascript;
    private $objMysql;
    private $validation;
    private $type;
    private $stepId;
    private $value;
    private $isDisabled;
    private $ValidationFailures;
    private $helpText;
    private $arrayFieldDefinition = array(
        "type" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFieldType", "mutator" => "setFieldType"),
        "required" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getTableName", "mutator" => "setTableName"),
        "label" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getLabel", "mutator" => "setLabel"),
        "placeholder" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getPlaceholder", "mutator" => "setPlaceholder"),
        "className" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFieldClass", "mutator" => "setFieldClass"),
        "name" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFieldName", "mutator" => "setFieldName"),
        "description" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getHelpText", "mutator" => "setHelpText"),
        "id" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getFieldId", "mutator" => "setFieldId"),
        "validation" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getValidation", "mutator" => "setValidation"),
        "maxlength" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getMaxLength", "mutator" => "setMaxLength"),
        "value" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getDefaultValue", "mutator" => "setDefaultValue"),
    );

    /**
     *
     * @param type $fieldId
     * @param type $stepId
     */
    public function __construct ($fieldId = null, $stepId = null)
    {
        if ( $fieldId !== null )
        {
            $this->fieldId = $fieldId;
        }

        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }

        $this->objMysql = new Mysql2();
    }

    public function loadObject ($arrField)
    {
        foreach ($arrField as $formField => $formValue) {

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

    public function validate ()
    {
        $errorCount = 0;

        foreach ($this->arrayFieldDefinition as $fieldName => $arrField) {
            if ( $arrField['required'] === true )
            {
                $accessor = $this->arrayFieldDefinition[$fieldName]['accessor'];

                if ( trim ($this->$accessor ()) == "" )
                {
                    $this->ValidationFailures[] = $fieldName . " Is empty. It is a required field";
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

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getValidationFailures ()
    {
        return $this->ValidationFailures;
    }

    /**
     * 
     * @param type $ValidationFailures
     */
    public function setValidationFailures ($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }

    /**
     * Get the fieldType column value.
     * 
     * @return     string
     */
    public function getFieldType ()
    {
        return $this->fieldType;
    }

    /**
     * @param mixed $fieldType
     */
    public function setFieldType ($fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * Get the isReadOnly column value.
     * 
     * @return     string
     */
    public function getIsReadOnly ()
    {
        return $this->isReadOnly;
    }

    /**
     * @param mixed $isReadOnly
     */
    public function setIsReadOnly ($isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;
    }

    /**
     * Get the fieldName column value.
     * 
     * @return     string
     */
    public function getFieldName ()
    {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     */
    public function setFieldName ($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Get the label column value.
     * 
     * @return     string
     */
    public function getLabel ()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel ($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getFieldId ()
    {
        return $this->fieldId;
    }

    /**
     * @param mixed $fieldId
     */
    public function setFieldId ($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    function getDataType ()
    {
        return $this->dataType;
    }

    function setDataType ($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * Get the options column value.
     * 
     * @return     string
     */
    function getOptions ()
    {
        return $this->options;
    }

    function setOptions ($options)
    {
        $this->options = $options;
    }

    function getRequired_field ()
    {
        return $this->required_field;
    }

    function setRequired_field ($required_field)
    {
        $this->required_field = $required_field;
    }

    /**
     * Get the defaultValue column value.
     * 
     * @return     string
     */
    function getDefaultValue ()
    {
        return $this->defaultValue;
    }

    /**
     * Get the fieldClass column value.
     * 
     * @return     string
     */
    function getFieldClass ()
    {
        return $this->fieldClass;
    }

    /**
     * Get the placeholder column value.
     * 
     * @return     string
     */
    function getPlaceholder ()
    {
        return $this->placeholder;
    }

    /**
     * Get the maxLength column value.
     * 
     * @return     int
     */
    function getMaxLength ()
    {
        return $this->maxLength;
    }

    /**
     * Get the defaultValue column value.
     * 
     * @return     string
     */
    public function setDefaultValue ($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public function setFieldClass ($fieldClass)
    {
        $this->fieldClass = $fieldClass;
    }

    public function setPlaceholder ($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function setMaxLength ($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function getFieldConditions ()
    {
        return $this->fieldConditions;
    }

    public function setFieldConditions ($fieldConditions)
    {
        $this->fieldConditions = $fieldConditions;
    }

    /**
     * Get the customJavascript column value.
     * 
     * @return     string
     */
    public function getCustomJavascript ()
    {
        return $this->customJavascript;
    }

    public function setCustomJavascript ($customJavascript)
    {
        $this->customJavascript = $customJavascript;
    }

    public function getValidation ()
    {
        return $this->validation;
    }

    public function getType ()
    {
        return $this->type;
    }

    public function setValidation ($validation)
    {
        $this->validation = $validation;
    }

    public function setType ($type)
    {
        $this->type = $type;
    }

    /**
     * Get the value column value.
     * 
     * @return     string
     */
    public function getValue ()
    {
        return $this->value;
    }

    /**
     * Get the helpText column value.
     * 
     * @return     string
     */
    public function getHelpText ()
    {
        return $this->helpText;
    }

    public function setHelpText ($helpText)
    {
        $this->helpText = $helpText;
    }

    /**
     *
     * @param type $value
     */
    public function setValue ($value)
    {
        $this->value = $value;
    }

    public function getIsDisabled ()
    {
        return $this->isDisabled;
    }

    /**
     *
     * @param type $isDisabled
     */
    public function setIsDisabled ($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    public function save ()
    {
        if ( is_numeric ($this->id) )
        {
            $id = $this->id;

            $this->objMysql->_update (
                    "workflow.fields", array(
                "field_type" => $this->fieldType,
                "field_name" => !empty ($this->fieldName) ? $this->fieldName : "",
                "field_identifier" => !empty ($this->fieldId) ? strtolower (str_replace (" ", "", $this->fieldId)) : $this->fieldName,
                "label" => $this->label,
                "field_class" => $this->fieldClass,
                "default_value" => !empty ($this->defaultValue) ? $this->defaultValue : "",
                "placeholder" => !empty ($this->placeholder) ? $this->placeholder : "",
                "maxlength" => !empty ($this->maxLength) ? $this->maxLength : null,
                "type" => !empty ($this->type) ? $this->type : '',
                "validation" => !empty ($this->validation) ? $this->validation : '',
                    ), array(
                "field_id" => $this->id
                    )
            );
        }
        else
        {
            $id = $this->objMysql->_insert (
                    "workflow.fields", array(
                "field_type" => $this->fieldType,
                "field_name" => !empty ($this->fieldName) ? $this->fieldName : "",
                "field_identifier" => !empty ($this->fieldName) ? strtolower (str_replace (" ", "", $this->fieldName)) : "",
                "label" => $this->label,
                "field_class" => $this->fieldClass,
                "default_value" => !empty ($this->defaultValue) ? $this->defaultValue : "",
                "placeholder" => !empty ($this->placeholder) ? $this->placeholder : "",
                "maxlength" => !empty ($this->maxLength) ? $this->maxLength : null,
                    )
            );
        }



        return $id;
    }

    /**
     *
     */
    public function delete ()
    {
        $this->objMysql->_delete ("workflow.step_fields", array("field_id" => $this->fieldId, "step_id" => $this->stepId));
    }

}
