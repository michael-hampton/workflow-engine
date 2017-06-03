<?php

class BaseFieldCondition
{

    /**
     * The value for the event field.
     * @var        string
     */
    private $event;

    /**
     * The value for the action field.
     * @var        string
     */
    private $action;

    /**
     * The value for the field field.
     * @var        string
     */
    private $field;
    private $fieldIdentifier;

    /**
     * The value for the step id field.
     * @var        int
     */
    private $stepId;
    private $objMysql;
    private $validationFailures = array();
    private $arrayFieldDefinition = array(
        "event" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getEvent", "mutator" => "setEvent"),
        "action" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getAction", "mutator" => "setAction"),
        "fieldIdentifier" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getFieldIdentifier", "mutator" => "setFieldIdentifier"),
        "field" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getField", "mutator" => "setField"),
    );

    public function __construct ($stepId = null)
    {
        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }

        $this->objMysql = new Mysql2();
    }

    public function loadObject ($arrDocument)
    {
        foreach ($arrDocument as $formField => $formValue) {

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
                    $this->validationFailures[] = $fieldName . " Is empty. It is a required field";
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

    public function save ()
    {
        $arrConditions = array(
            "displayField" => array(
                "event" => $this->event,
                "action" => $this->action,
                "field" => $this->field
            )
        );

        $strConditions = json_encode ($arrConditions);

        $this->objMysql->_update ("workflow.step_fields", array("field_conditions" => $strConditions), array("step_id" => $this->stepId, "field_id" => $this->fieldIdentifier)
        );
    }

    public function getEvent ()
    {
        return $this->event;
    }

    public function getAction ()
    {
        return $this->action;
    }

    public function getField ()
    {
        return $this->field;
    }

    public function getFieldIdentifier ()
    {
        return $this->fieldIdentifier;
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function setEvent ($event)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $event !== null && !is_string ($event) )
        {
            $event = (string) $event;
        }
        if ( $this->event !== $event )
        {
            $this->event = $event;
        }
    }

    public function setAction ($action)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $action !== null && !is_string ($action) )
        {
            $action = (string) $action;
        }
        if ( $this->action !== $action )
        {
            $this->action = $action;
        }
    }

    public function setField ($field)
    {
        $this->field = $field;
    }

    public function setFieldIdentifier ($fieldIdentifier)
    {
        $this->fieldIdentifier = $fieldIdentifier;
    }

    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    public function setValidationFailures ($validationFailures)
    {
        $this->validationFailures = $validationFailures;
    }

    public function delete ()
    {
        if ( trim ($this->fieldIdentifier) == "" || !is_numeric ($this->fieldIdentifier) )
        {
            throw new Exception ("Invalid field id given");
        }

        if ( trim ($this->stepId) == "" || !is_numeric ($this->stepId) )
        {
            throw new Exception ("Invalid step id given");
        }

        $this->objMysql->_update ("workflow.step_fields", array("field_conditions" => ""), array("field_id" => $this->fieldIdentifier, "step_id" => $this->stepId));
    }

}
