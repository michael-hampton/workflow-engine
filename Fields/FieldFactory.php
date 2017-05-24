<?php

class FieldFactory
{

    private $objMysql;

    private function createConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getFieldByIdentifier ($fieldIdentifier)
    {

        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $result = $this->objMysql->_select ("workflow.fields", array(), array("field_identifier" => $fieldIdentifier));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result[0]['field_id'];
        }

        return [];
    }

    /**
     * 
     * @return \StepField|boolean
     */
    public function getAllFields ()
    {
        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $arrResult = $this->objMysql->_query ("SELECT
                                                            f.*, 
                                                            ft.field_type
                                                        FROM workflow.fields f
                                                        INNER JOIN workflow.field_types ft ON ft.field_type_id = f.field_type
                                                       ");

        foreach ($arrResult as $intKey => $arrField) {
            $arrFields[$arrField['field_id']] = new StepField ($arrField['field_id']);
            $arrFields[$arrField['field_id']]->setFieldType ($arrField['field_type']);
            $arrFields[$arrField['field_id']]->setLabel ($arrField['label']);
            $arrFields[$arrField['field_id']]->setFieldName ($arrField['field_name']);
            $arrFields[$arrField['field_id']]->setIsReadOnly ($arrField['is_readonly']);
            $arrFields[$arrField['field_id']]->setFieldId ($arrField['field_identifier']);
            $arrFields[$arrField['field_id']]->setDataType ($arrField['data_type']);
            $arrFields[$arrField['field_id']]->setFieldClass ($arrField['field_class']);
            $arrFields[$arrField['field_id']]->setDefaultValue ($arrField['default_value']);
            $arrFields[$arrField['field_id']]->setPlaceholder ($arrField['placeholder']);
            $arrFields[$arrField['field_id']]->setMaxLength ($arrField['maxlength']);
            $arrFields[$arrField['field_id']]->setId ($arrField['field_id']);
        }

        if ( empty ($arrFields) )
        {
            return false;
        }

        return $arrFields;
    }

    /**
     * 
     * @param type $stepId
     * @return \StepField|boolean
     */
    public function getFieldsForStep ($stepId)
    {
        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $query = "SELECT sf.*, f.*, ft.field_type, dt.options, dt.data_object_type,
                IF(rf.field_id IS NOT NULL, 1, 0) as required_field, sf.field_conditions 
                FROM workflow.step_fields sf 
                INNER JOIN workflow.fields f ON f.field_id = sf.field_id 
                INNER JOIN workflow.field_types ft ON ft.field_type_id = f.field_type 
                LEFT JOIN workflow.data_types dt ON dt.id = f.data_type 
                LEFT JOIN workflow.required_fields rf on rf.field_id = f.field_id
                WHERE sf.step_id = ? 
                GROUP BY f.field_id
                ORDER BY sf.order_id";

        $arrParameters = array($stepId);
        $arrResult = $this->objMysql->_query ($query, $arrParameters);

        foreach ($arrResult as $intKey => $arrField) {
            $arrFields[$arrField['field_id']] = new StepField ($arrField['field_id']);
            $arrFields[$arrField['field_id']]->setFieldType ($arrField['field_type']);
            $arrFields[$arrField['field_id']]->setLabel ($arrField['label']);
            $arrFields[$arrField['field_id']]->setFieldName ($arrField['field_name']);
            $arrFields[$arrField['field_id']]->setIsReadOnly ($arrField['is_readonly']);
            $arrFields[$arrField['field_id']]->setFieldId ($arrField['field_identifier']);
            $arrFields[$arrField['field_id']]->setDataType ($arrField['data_type']);
            $arrFields[$arrField['field_id']]->setId ($arrField['field_id']);

            if ( !empty ($arrField['data_type']) )
            {
                if ( $arrField['data_object_type'] == 2 )
                {
                    $arrDatabaseOptions = json_decode ($arrField['options'], true);

                    $sql = "SELECT * FROM " . $arrDatabaseOptions['databaseName'] . "." . $arrDatabaseOptions['tableName'];

                    if ( !empty ($arrDatabaseOptions['whereColumn']) )
                    {
                        $sql .= " WHERE " . $arrDatabaseOptions['whereColumn'];
                    }

                    if ( !empty ($arrDatabaseOptions['orderBy']) )
                    {
                        $sql .= " ORDER BY " . $arrDatabaseOptions['orderBy'] . " ASC ";
                    }

                    $arrQuery = $this->objMysql->_query ($sql);
                    $arrOptions = array();

                    if ( !empty ($arrQuery) )
                    {
                        foreach ($arrQuery as $resultKey => $result) {
                            $arrOptions[$result[$arrDatabaseOptions['idColumn']]] = $result[$arrDatabaseOptions['valueColumn']];
                        }
                    }
                }
                else
                {
                    $arrOptions = json_decode ($arrField['options'], true);

                    if ( !empty ($arrOptions) )
                    {
                        foreach ($arrOptions as $resultKey => $result) {
                            $arrOptions[$resultKey] = $result;
                        }
                    }
                }
            }

            if ( isset ($arrOptions) && !empty ($arrOptions) )
            {
                $arrFields[$arrField['field_id']]->setOptions (json_encode ($arrOptions));
            }

            if ( !empty ($arrField['field_conditions']) )
            {
                $arrFields[$arrField['field_id']]->setFieldConditions ($arrField['field_conditions']);
            }

            if ( isset ($arrField['custom_javascript']) && !empty ($arrField['custom_javascript']) )
            {
                $arrFields[$arrField['field_id']]->setCustomJavascript ($arrField['custom_javascript']);
            }

            $arrFields[$arrField['field_id']]->setRequired_field ($arrField['required_field']);

            $arrFields[$arrField['field_id']]->setFieldClass ($arrField['field_class']);
            $arrFields[$arrField['field_id']]->setDefaultValue ($arrField['default_value']);
            $arrFields[$arrField['field_id']]->setPlaceholder ($arrField['placeholder']);
            $arrFields[$arrField['field_id']]->setMaxLength ($arrField['maxlength']);
        }

        if ( empty ($arrFields) )
        {
            return false;
        }

        return $arrFields;
    }

    /**
     * 
     * @param type $step
     * @return type
     */
    public function getRequiredFields ($step)
    {
        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $query = "SELECT r.*, f.* FROM workflow.required_fields r 
                    INNER JOIN workflow.fields f ON f.field_id = r.field_id
                    WHERE r.step_id = ?";
        $arrParameters = array($step);
        $arrResult = $this->objMysql->_query ($query, $arrParameters);

        return $arrResult;
    }

    public function create ($aData)
    {
        try {
            $oFields = new StepField();
            $field = $this->getFieldByIdentifier ($aData['id']);

            if ( !empty ($field) && is_numeric ($field) )
            {
                $oFields->setId ($field);
            }

            $oFields->loadObject ($aData);

            if ( $oFields->validate () )
            {
                $id = $oFields->save ();
                return $id;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oFields->getValidationFailures ();
                foreach ($aValidationFailures as $strMessage) {
                    $sMessage .= $strMessage . '<br />';
                }
                throw(new Exception ('The field cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $ex) {
            throw($ex);
        }
    }

    /**
     * get field by id
     * @param type $fieldId
     * @return type
     */
    private function retrieveByPK ($fieldId)
    {
        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $result = $this->objMysql->_select ("workflow.fields", array(), array("field_id" => $fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result[0];
        }

        return [];
    }

    /**
     * delete a field from the fields table
     * @param type $fieldId
     * @return boolean
     */
    private function deleteField ($fieldId)
    {
        if ( $this->objMysql == null )
        {
            $this->createConnection ();
        }

        $this->objMysql->_delete ("workflow.fields", array("field_id" => $fieldId));

        return true;
    }

    /**
     * checks if a field is assigned to a step
     * @param type $fieldId
     * @return type
     */
    private function fieldIsAssigned ($fieldId)
    {
        if ( $this->objMysql === null )
        {
            $this->createConnection ();
        }

        $result = $this->objMysql->_select ("workflow.step_fields", array(), array("field_id" => $fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }

        return [];
    }

    /**
     * remove field from database
     * @param type $fieldId
     * @param type $deleteFull 
     * if true it will delete from both step fields and fields false will only delete from step fields
     * @return type
     * @throws type
     */
    public function remove ($fieldId, $stepId, $deleteFull = false)
    {
        try {

            if ( empty ($fieldId) || !is_numeric ($fieldId) )
            {
                throw new Exception ('Incorrect field id.');
            }

            if ( empty ($stepId) || !is_numeric ($stepId) )
            {
                throw new Exception ('Incorrect step id.');
            }

            $oFields = $this->retrieveByPK ($fieldId);
            if ( !empty ($oFields) )
            {
                $oFields = new StepField ($fieldId, $stepId);
                $iResult = $oFields->delete ();

                if ( $deleteFull === true )
                {
                    if ( !empty ($this->fieldIsAssigned ($fieldId)) )
                    {
                        throw new Exception ('Field is assigned to a step.');
                    }
                    $this->deleteField ($fieldId);
                }

                return $iResult;
            }
            else
            {
                throw(new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

}
