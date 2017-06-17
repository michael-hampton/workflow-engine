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
        $objStepDocument = new InputDocument ($this->stepId);
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

            $fieldType = $this->objMysql->_select ("workflow.field_types", array(), array("field_type" => $arrField['type']));

            if ( !isset ($fieldType[0]) || empty ($fieldType[0]) )
            {
                throw new Exception ("Field type is unrecognized");
            }

            $arrField['type'] = $fieldType[0]['field_type_id'];

            /*             * ******* Save Field ********************* */
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

            /*             * ************ Save option values ****************************** */
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

            /*             * **************** save database option values ************************ */
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
                    $msg = "";
                    foreach ($objDatabaseOptions->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }
                    throw new Exception ("Failed to create database options " . $msg);
                }
            }

            $objStepField = new StepField ($this->stepId);

            /*             * ********************* Assign Field to step ********************** */
            if ( $checked == "true" )
            {
                $objStepField->delete ();
                $objRequiredField = new RequiredField ($this->stepId, $fieldId, $this->workflowId);
                $objRequiredField->removeAllRequiredFieldsFromStep ();

                foreach ($arrFieldIds as $key => $fieldId) {
                    $objStepField->setFieldId ($fieldId);
                    $objStepField->setOrderId ($key);

                    if ( $objStepField->validate () )
                    {
                        $objStepField->save ();
                    }
                    else
                    {

                        $msg = "";
                        foreach ($objStepField->getArrayValidation () as $validationFailure) {
                            $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                        }
                        throw new Exception ("Failed to assign field to step " . $msg);
                    }
                }
            }

            /*             * **************************** Save Required Fields **************************** */
            if ( !empty ($arrRequired) )
            {
                foreach ($arrRequired as $fieldId) {
                    $objRequiredField = new RequiredField ($this->stepId, $fieldId, $this->workflowId);

                    if ( $objRequiredField->validate () )
                    {
                        $objRequiredField->save ();
                    }
                    else
                    {
                        $msg = "";
                        foreach ($objRequiredField->getArrayValidation () as $validationFailure) {
                            $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                        }
                        throw new Exception ("Failed to save required field " . $msg);
                    }
                }
            }

            /*             * ******************** Save Variables ***************************** */
            if ( isset ($arrField['id']) && trim ($arrField['id']) !== "" )
            {
                $objVariable = new StepVariable ($fieldId);
                $variableName = strtolower (trim (str_replace (" ", "", $arrField['id'])));

                $objVariable->create ($fieldId, array("VAR_NAME" => $variableName, "VAR_FIELD_TYPE" => "string"));
            }
        }
    }

    public function buildFormForStep (WorkflowStep $objWorkflowStep, Users $objUser, $projectId, $elementId = null)
    {
        $objCases = new Cases();
        $objCase = $objCases->getCaseInfo ($projectId, $elementId);

        $currentStepId = $objCase->getCurrentStepId ();
        $workflowId = $objWorkflowStep->getWorkflowId ();
        $stepId = $objWorkflowStep->getStepId ();
        $taskId = $objWorkflowStep->getWorkflowStepId ();
        $objFormBuilder = new FormBuilder();
        $buildSummary = false;
        $html = '';

        $userPermissions = $objCases->getAllObjectsFrom ($projectId, $elementId, $stepId, $objUser);
        
        $objProcessSupervisor = new ProcessSupervisor();
        $blProcessSupervisor = $objProcessSupervisor->isUserProcessSupervisor ($workflowId, $objUser);

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

            if ( $blProcessSupervisor !== true )
            {
                if ( !in_array ($stepId, $userPermissions['DYNAFORMS']) && $userPermissions['MASTER_DYNAFORM'] !== 1 )
                {
                    $objField->setIsDisabled (1);
                }

                if ( $userPermissions['MASTER_DYNAFORM'] === 0 )
                {
                    $objField->setIsDisabled (1);
                }
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

        $objStepDocument = new InputDocument ($taskId);
        $arrDocuments = $objStepDocument->getInputDocumentForStep ();

        if ( !empty ($arrDocuments) )
        {
            foreach ($arrDocuments as $key => $arrDocument) {
                if ( !in_array ($arrDocument->getId (), $userPermissions['INPUT_DOCUMENTS']) && $blProcessSupervisor !== true )
                {
                    unset ($arrDocuments[$key]);
                }
            }
            $objFormBuilder->buildDocHTML ($arrDocuments);
        }

        $outPutDocuments = $objCases->getAllGeneratedDocumentsCriteria ($projectId, $elementId, $stepId, $_SESSION['user']['usrid']);

        if ( !empty ($outPutDocuments) )
        {
            foreach ($outPutDocuments as $key => $outPutDocument) {

                if ( !in_array ($outPutDocument['DOC_UID'], $userPermissions['OUTPUT_DOCUMENTS']) && $blProcessSupervisor !== true )
                {
                    unset ($outPutDocument[$key]);
                }
            }

            $objFormBuilder->buildOutputDocumentList ($outPutDocuments);
        }

        $html = $objFormBuilder->render ();

        return $html;
    }

}
