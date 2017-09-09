<?php

class FieldConditions
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Quick get all records into a associative array
     */
    public function getAllBySyepUid ($StepUid)
    {
        $aRows = array();
        $results = $this->objMysql->_query ("SELECT dt.*, sf.field_conditions FROM workflow.step_fields sf 
                                            INNER JOIN workflow.data_types dt ON dt.field_id = sf.field_id
                                            WHERE sf.step_id = ?", [$StepUid]);

        foreach ($results as $result) {
            $aRows[$result['field_id']]['databaseOptions'] = $result['options'];
            $aRows[$result['field_id']]['conditions'] = $result['field_conditions'];
        }

        return $aRows;
    }

    /**
     * function to save conditions to db
     * @param type $aData
     * @throws type
     * @throws Exception
     */
    public function create ($aData)
    {
        try {

            if ( isset ($aData['database']) && !empty ($aData['database']['databaseName']) )
            {
                if ( !isset ($aData['fieldIdentifier']) || $aData['fieldIdentifier'] === "null" )
                {
                    throw new Exception ('Field Identifier is missing');
                }

                $databaseOptions = new DatabaseOptions ($aData['fieldIdentifier']);
                $databaseOptions->loadObject ($aData['database']);

                if ( $databaseOptions->validate () )
                {
                    $databaseOptions->save ();
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $databaseOptions->getValidationFailures ();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
                }
            }

            if ( isset ($aData['displayField']) && !empty ($aData['displayField']['event']) )
            {
                if ( !isset ($aData['fieldIdentifier']) || $aData['fieldIdentifier'] === "null" )
                {
                    throw new Exception ('Field Identifier is missing');
                }
                else
                {
                    $aData['displayField']['fieldIdentifier'] = $aData['fieldIdentifier'];
                }

                $oFieldCondition = new BaseFieldCondition ($aData['stepId']);
                $oFieldCondition->loadObject ($aData['displayField']);

                if ( $oFieldCondition->validate () )
                {
                    $oFieldCondition->save ();
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oFieldCondition->getValidationFailures ();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
                }
            }
            else
            {
                if ( $this->fieldConditionExists ($aData['fieldIdentifier'], $aData['stepId']) )
                {
                    $this->remove ($aData['fieldIdentifier'], $aData['stepId']);
                }
            }

            //$objFieldOptions = new FieldOptions();
            //$objFieldOptions->saveRequiredFields ($aData);
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * remove condition from field in db
     * @param type $fieldId
     * @param type $stepId
     * @return type
     * @throws type
     */
    public function remove ($fieldId, $stepId)
    {
        try {
            $baseConditions = new BaseFieldCondition ($stepId);
            $baseConditions->setFieldIdentifier ($fieldId);
            $iResult = $baseConditions->delete ();
            return $iResult;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * get conditions for a step field
     * @param type $fieldId
     * @param type $stepId
     * @return type
     */
    private function retrieveByPk ($fieldId, $stepId)
    {
        $result = $this->objMysql->_select ("workflow.step_fields", array("field_conditions"), array("field_id" => $fieldId, "step_id" => $stepId));

        if ( isset ($result[0]['field_conditions']) && !empty ($result[0]['field_conditions']) )
        {
            return $result[0]['field_conditions'];
        }

        return [];
    }

    /**
     * check if a field has a condition
     * @param type $aDynaform
     * @param type $sUid
     * @return type
     * @throws type
     */
    public function fieldConditionExists ($aDynaform, $sUid)
    {
        try {
            $found = false;
            $obj = $this->retrieveByPk ($aDynaform, $sUid);

            if ( !empty ($obj) )
            {
                $obj = json_decode ($obj, true);

                if ( isset ($obj['displayField']) )
                {
                    $found = true;
                }
            }
   
            return ($found);
        } catch (Exception $e) {
            throw ($e);
        }
    }
}
