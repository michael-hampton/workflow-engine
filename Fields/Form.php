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
    public function __construct ($stepId = null, $workflowId = null)
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
     * @param type $arrData
     * @param type $arrFormData
     * @param type $checked
     * @return type
     */
    public function save ($arrData, $arrFormData, $checked)
    {
        $arrFields = json_decode ($arrData, true);
        $arrErrors = array();

        $objFieldFactory = new FieldFactory();

        foreach ($arrFields as $arrField) {

            /********* Save Field **********************/
            $fieldId = $objFieldFactory->create ($arrField);

            if ( !is_numeric ($fieldId) )
            {
                throw new Exception ("Field could not be created");
            }

            $this->fieldId = $fieldId;
            $arrFieldIds[] = $fieldId;

            if ( isset ($arrField['required']) && $arrField['required'] == 'true' )
            {
                $arrRequired[] = $fieldId;
            }

            /************** Save option values *******************************/
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
                
                /****************** save database option values *************************/
                elseif ( !empty ($arrFormData['databaseName']) && !empty ($arrFormData['tableName']) )
                {
                    $objDatabaseOptions = new DatabaseOptions ($fieldId);
                    $objDatabaseOptions->setDatabaseName ($arrFormData['databaseName']);
                    $objDatabaseOptions->setIdColumn ($arrFormData['columnName']);
                    $objDatabaseOptions->setTableName ($arrFormData['tableName']);
                    $objDatabaseOptions->setValueColumn ($arrFormData['columnName']);

                    if ( $objDatabaseOptions->validate () )
                    {
                        $objDatabaseOptions->save ();
                    }
                    else
                    {
                        
                    }
                }
            }
            
            $objStepField = new StepField($this->stepId);

            if ( $checked == "true" )
            {
                $objStepField->delete();
                $this->objMysql->_delete ("workflow.required_fields", array("step_id" => $this->stepId));

                foreach ($arrFieldIds as $key => $fieldId) {
                    $objStepField->setFieldId($fieldId);
                    $objStepField->setOrderId($key);
                    $objStepField->save();
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
     */
    public function saveRequiredField ($fieldId)
    {
        $this->objMysql->_insert ("workflow.required_fields", array(
            "step_id" => $this->stepId,
            "field_id" => $fieldId,
            "workflow_id" => $this->workflowId));
    }

    public function checkRequiredField ($fieldId)
    {
        $result = $this->objMysql->_select ("workflow.required_fields", array(), array("workflow_id" => $this->workflowId, "step_id" => $this->stepId, "field_id" => $fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }

        return [];
    }

    public function removeRequiredField ($fieldId)
    {
        if ( !empty ($this->checkRequiredField ($fieldId)) )
        {
            throw new Exception ('row doesnt exist.');
        }

        $this->objMysql->_delete ("workflow.required_fields", array("step_id" => $this->stepId, "workflow_id" => $this->workflowId, "field_id" => $fieldId));
    }

    public function buildFormForStep (WorkflowStep $objWorkflowStep, $projectId, $elementId = null)
    {
        $objCases = new Cases();
        $objCase = $objCases->getCaseInfo ($projectId, $elementId);


        $currentStepId = $objCase->getCurrentStepId ();

        $stepId = $objWorkflowStep->getStepId ();
        $taskId = $objWorkflowStep->getWorkflowStepId();
        $objFormBuilder = new FormBuilder();
        $buildSummary = false;
        $html = '';

        $objAttachments = new Attachments();
        $arrAttachments = $objAttachments->getAllAttachments ($projectId);
        $attachmentHTML = $objFormBuilder->buildAttachments ($arrAttachments);

        /*         * ************** Fields ************************* */
        $arrFields = $objWorkflowStep->getFields ();

        if ( empty ($arrFields) )
        {
            // display summary based on starting step
            $firstStep = $objWorkflowStep->getFirstStepForWorkflow ();

            if ( !empty ($firstStep) )
            {
                $objWorkflowStep = new WorkflowStep ($firstStep[0]['id']);
                $arrFields = $objWorkflowStep->getFields ();
            }
        }


        // Permissions

        $objVariables = new StepVariable();

        foreach ($arrFields as $key => $objField) {

            // This eventually needs to be replaced so that everything comes from the variables
            $fieldId = $objField->getFieldId ();

            if ( $currentStepId !== $objWorkflowStep->getWorkflowStepId () )
            {
                $objField->setIsDisabled (1);
            }

            if ( isset ($objCase->objJobFields[$fieldId]) )
            {
                $accessor = $objCase->objJobFields[$fieldId]['accessor'];
                $value = call_user_func (array($objCase, $accessor));

                if ( trim ($value) !== "" )
                {
                    $objField->setValue (trim ($value));
                }
            }
            else
            {
                $objVariable = $objVariables->getVariableForField ($fieldId);

                if ( !empty ($objVariable) )
                {
                    $variable = $objVariable->getVariableName ();

                    if ( trim ($variable) !== "" && isset ($objCase->arrElement[$variable]) )
                    {
                        $objField->setValue ($objCase->arrElement[$variable]);
                    }
                }
            }
        }

        if ( !empty ($arrFields) )
        {
            $buildSummary = true;
            $objFormBuilder->buildForm ($arrFields);
        }

        $objStepDocument = new StepDocument ($taskId);
        $arrDocuments = $objStepDocument->getInputDocumentForStep ();

        if ( !empty ($arrDocuments) )
        {
            $objFormBuilder->buildDocHTML ($arrDocuments);
        }

        $html = $objFormBuilder->render ();

        return $html;
    }

}
