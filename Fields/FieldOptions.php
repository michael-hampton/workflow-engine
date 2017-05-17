<?php

class FieldOptions
{

    private $objMysql;
    private $_strOptions;
    private $dataType;
    private $fieldId;
    private $dataTypeId;
    private $stepId;
    private $workflowId;

    /**
     * 
     * @param type $fieldId
     * @param type $stepId
     * @param type $workflowId
     */
    public function __construct ($fieldId = null, $stepId = null, $workflowId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $fieldId !== null )
        {
            $this->fieldId = $fieldId;
        }

        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }

        if ( $workflowId !== null )
        {
            $this->workflowId = $workflowId;
        }
    }

    /**
     * 
     * @param type $arrData
     */
    public function loadObject ($arrData)
    {
        if ( isset ($arrData['database']) && !empty ($arrData['database']) )
        {
            $this->_strOptions = json_encode ($arrData['database']);
            $this->setDataType (2);
            $this->dataTypeId = $this->saveDataType ();
            $this->updateField ();
        }

        if ( isset ($arrData['displayField']) && !empty ($arrData['displayField']['event']) )
        {
            $this->_strOptions = json_encode (array("displayField" => $arrData['displayField']));
            $this->save ();
        }

        $this->saveRequiredFields ($arrData);
    }

    /**
     * 
     * @return type
     */
    public function getDataType ()
    {
        return $this->dataType;
    }

    /**
     * 
     * @param type $dataType
     */
    public function setDataType ($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * 
     * @return type
     */
    public function get_strOptions ()
    {
        return $this->_strOptions;
    }

    /**
     * 
     * @param type $_strOptions
     */
    public function set_strOptions ($_strOptions)
    {
        if ( is_array ($_strOptions) )
        {
            $_strOptions = json_encode ($_strOptions);
        }

        $this->_strOptions = $_strOptions;
    }

    /**
     * 
     */
    private function updateField ()
    {
        $this->objMysql->_update ("workflow.fields", array("data_type" => $this->dataTypeId), array("field_id" => $this->fieldId));
    }

    /**
     * 
     * @return type
     */
    public function saveDataType ()
    {
        $id = $this->objMysql->_insert ("workflow.data_types", array("options" => $this->_strOptions, "field_id" => $this->fieldId, "data_object_type" => $this->dataType));
        $this->dataTypeId = $id;
        $this->updateField ();
        return $id;
    }

    private function save ()
    {
        $this->_model->_update ("workflow.step_fields", array("field_conditions" => $this->_strOptions), array("field_id" => $this->fieldId, "step_id" => $this->stepId));
    }

    /**
     * 
     * @param type $arrData
     */
    public function saveRequiredFields ($arrData)
    {
        $conditions = json_encode ($arrData['conditions']);

        $check = $this->objMysql->_select ("workflow.required_fields", array(), array("field_id" => $this->fieldId, "step_id" => $this->stepId));

        if ( !empty ($check) )
        {
            $this->objMysql->_update ("workflow.required_fields", array("expected_output" => $arrData['expectedOutput'], "field_condition" => $conditions), array("id" => $check[0]['id']));
        }
        else
        {
            $this->objMysql->_insert ("workflow.required_fields", array(
                "field_id" => $this->fieldId,
                "step_id" => $this->stepId,
                "expected_output" => $arrData['expectedOutput'],
                "field_condition" => $conditions
            ));
        }
    }

}
