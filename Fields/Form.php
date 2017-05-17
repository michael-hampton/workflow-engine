<?php

class Form extends FieldFactory
{

    private $stepId;
    private $objMysql;
    private $workflowId;
    private $fieldId;

    /**
     * 
     * @param type $stepId
     * @param type $workflowId
     */
    public function __construct ($stepId, $workflowId = null)
    {
        $this->stepId = $stepId;
        $this->objMysql = new Mysql2();

        if ( $workflowId !== null )
        {
            $this->workflowId = $workflowId;
        }
    }

    /**
     * 
     * @param type $blReturnArray
     * @return type
     */
    public function getFields ($blReturnArray = false)
    {

        // Fields
        $arrStepFields = $this->getFieldsForStep ($this->stepId);

        $arrFields = [];

        if ( !empty ($arrStepFields) && $blReturnArray === true )
        {
            foreach ($arrStepFields as $arrStepField) {
                $arrFields[$arrStepField->getFieldId ()] = array(
                    "type" => $arrStepField->getFieldType (),
                    "label" => $arrStepField->getLabel (),
                    "name" => $arrStepField->getFieldName (),
                    "id" => $arrStepField->getFieldId (),
                    "default_value" => $arrStepField->getDefaultValue (),
                    "max_length" => $arrStepField->getMaxLength (),
                    "field_id" => $arrStepField->getId (),
                    "field_identifier" => $arrStepField->getFieldId (),
                    "required_field" => $arrStepField->getRequired_field (),
                    "field_type" => $arrStepField->getFieldType ()
                );
            }

            return $arrFields;
        }

        return $arrStepFields;
    }

    /**
     * 
     * @return type
     */
    public function getInputDocuments ()
    {
        // Input Documents
        $objStepDocument = new StepDocument ($this->stepId);
        $arrInputDocuments = $objStepDocument->getInputDocumentForStep ();

        if ( !empty ($arrInputDocuments) )
        {
            return $arrInputDocuments;
        }
    }

    /**
     * 
     * @param type $name
     * @return boolean
     */
    public function checkNameExists ($name)
    {
        $arrResult = $this->objMysql->_select ("workflow.fields", array("field_id"), array("field_name" => $name));

        if ( isset ($arrResult[0]) && !empty ($arrResult[0]) )
        {
            return $arrResult[0]['field_id'];
        }

        return false;
    }

    /**
     * 
     * @param type $arrData
     * @param type $arrFormData
     * @param type $checked
     * @return type
     */
    public function save ($arrData, $arrFormData, $checked)
    {
        $arrFields = json_decode ($arrData, true);
        $arrErrors = array();

        foreach ($arrFields as $arrField) {

            if ( $arrField['type'] != "paragraph" && empty ($arrField['name']) )
            {
                $arrErrors[] = "fieldNameWarning";
            }

            if ( empty ($arrField['label']) )
            {
                $arrErrors[] = "labelWarning";
            }

            if ( empty ($arrField['type']) )
            {
                $arrErrors[] = "field_typeWarning";
            }

            $check = $this->checkNameExists ($arrField['name']);


            if ( count ($arrErrors) > 0 )
            {
                return array("errors" => $arrErrors);
            }

            $fieldType = $this->objMysql->_select ("workflow.field_types", array(), array("field_type" => $arrField['type']));
            $fieldType = $fieldType[0]['field_type_id'];

            $objStepField = new StepField();
            $objStepField->setFieldType ($fieldType);
            $objStepField->setFieldName ($arrField['name']);
            $objStepField->setFieldId ($arrField['name']);
            $objStepField->setLabel ($arrField['label']);
            $objStepField->setFieldClass ($arrField['className']);

            if ( isset ($arrField['value']) )
            {
                $objStepField->setDefaultValue ($arrField['value']);
            }

            if ( isset ($arrField['maxlength']) )
            {
                $objStepField->setMaxLength ($arrField['maxlength']);
            }

            if ( isset ($arrField['placeholder']) )
            {
                $objStepField->setPlaceholder ($arrField['placeholder']);
            }

            if ( isset ($arrField['value_type']) )
            {
                $objStepField->setType ($arrField['value_type']);
            }

            if ( isset ($arrField['validation']) )
            {
                $objStepField->setValidation ($arrField['validation']);
            }

            if ( isset ($check) && is_numeric ($check) )
            {
                $objStepField->setId ($check);
            }

            $fieldId = $objStepField->save ();
            $this->fieldId = $fieldId;
            $arrFieldIds[] = $fieldId;

            if ( isset ($arrField['required']) && $arrField['required'] == 'true' )
            {
                $arrRequired[] = $fieldId;
            }

            if ( $fieldType == 3 )
            {

                if ( isset ($arrField['values']) && !empty ($arrField['values']) )
                {
                    $optionCount = 0;
                    $arrOptions = array();
                    foreach ($arrField['values'] as $intKey => $arrValue) {

                        $value = $arrValue['label'];

                        $arrOptions[$optionCount]['id'] = $arrValue['value'];
                        $arrOptions[$optionCount]['value'] = $value;

                        $optionCount++;
                    }

                    $objFieldOptions = new FieldOptions ($fieldId);
                    $objFieldOptions->setDataType (3);
                    $objFieldOptions->set_strOptions ($arrOptions);
                    $objFieldOptions->saveDataType ();
                }
                elseif ( !empty ($arrFormData['databaseName']) && !empty ($arrFormData['tableName']) )
                {
                    $arrOptions = array(
                        "databaseName" => $_REQUEST['databaseName'],
                        "tableName" => $_REQUEST['tableName'],
                        "idColumn" => $_REQUEST['columnName'],
                        "valueColumn" => $_REQUEST['columnName'],
                    );

                    $objFieldOptions = new FieldOptions ($fieldId);
                    $objFieldOptions->setDataType (2);
                    $objFieldOptions->set_strOptions ($arrOptions);
                    $objFieldOptions->saveDataType ();
                }
            }

            if ( $checked == "true" )
            {
                $this->objMysql->_delete ("workflow.step_fields", array("step_id" => $this->stepId));
                $this->objMysql->_delete ("workflow.required_fields", array("step_id" => $this->stepId));

                foreach ($arrFieldIds as $key => $fieldId) {
                    $this->saveFormField ($fieldId, $key);
                }
            }

            if ( !empty ($arrRequired) )
            {
                foreach ($arrRequired as $fieldId) {
                    $this->saveRequiredField ($fieldId);
                }
            }
        }
    }

    /**
     * 
     * @param type $fieldId
     * @param type $orderId#
     */
    public function saveFormField ($fieldId, $orderId)
    {
        $this->objMysql->_insert (
                "workflow.step_fields", array(
            "field_id" => $fieldId,
            "step_id" => $this->stepId,
            "is_disabled" => 0,
            "order_id" => $orderId
                )
        );
    }

    /**
     * 
     * @param type $fieldId
     */
    public function saveRequiredField ($fieldId)
    {
        $this->objMysql->_insert ("workflow.required_fields", array(
            "step_id" => $this->stepId,
            "field_id" => $fieldId,
            "workflow_id" => $this->workflowId));
    }
    
    public function checkRequiredField($fieldId)
    {
        $result = $this->objMysql->_select("workflow.required_fields", array(), array("workflow_id" => $this->workflowId, "step_id" => $this->stepId, "field_id" => $fieldId));
        
        if(isset($result[0]) && !empty($result[0])) {
            return $result;
        }
        
        return [];
    }
    
    public function removeRequiredField($fieldId)
    {
        if(!empty($this->checkRequiredField ($fieldId))) {
            throw new Exception('row doesnt exist.');
        }
        
        $this->objMysql->_delete("workflow.required_fields", array("step_id" => $this->stepId, "workflow_id" => $this->workflowId, "field_id" => $fieldId));
    }
}
