<?php

class StepVariable extends Variable
{

    private $objMysql;
    private $fieldId;

    /**
     * 
     * @param type $fieldId
     */
    public function __construct ($fieldId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $fieldId !== null )
        {
            $this->fieldId = $fieldId;
        }
    }

    /**
     * Create Variable for a Process
     *
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Variable created
     */
    public function create ($fieldId, array $arrayData)
    {
        try {
            //Verify data
            //$this->existsName ($fieldId, $arrayData["VAR_NAME"], "");

            $variable = new Variable ($fieldId);

            if ( isset ($arrayData["VAR_NAME"]) && trim ($arrayData['VAR_NAME']) !== "" )
            {
                $variable->setVariableName ($arrayData["VAR_NAME"]);
            }
            else
            {
                throw new Exception ("Variable Name cant be empty");
            }

            if ( isset ($arrayData["VAR_FIELD_TYPE"]) )
            {
                $variable->setValidationType ($arrayData["VAR_FIELD_TYPE"]);
            }
            else
            {
                throw new Exception ("Validation type cannot be empty");
            }

            if ( isset ($arrayData["VAR_DBCONNECTION"]) )
            {
                $variable->setVarDbconnection ($arrayData["VAR_DBCONNECTION"]);
            }
            else
            {
                $variable->setVarDbconnection ("");
            }

            if ( isset ($arrayData["VAR_SQL"]) )
            {
                $variable->setVarSql ($arrayData["VAR_SQL"]);
            }
            else
            {
                $variable->setVarSql ("");
            }

            if ( $variable->validate () )
            {
                if ( $this->checkFieldHasVariable ($fieldId) === true )
                {
                    $variable->update ();
                }
                else
                {
                    $variable->save ();
                }
            }
            else
            {
                $msg = "";
                foreach ($variable->getValidationErrors () as $validationFailure) {
                    $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                }
                throw new Exception ("Cannot create variable " . $msg);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the name of a variable
     *
     * @param string $processUid         Unique id of Process
     * @param string $variableName       Name
     *
     */
    public function existsName ($fieldId, $variableName, $variableUidToExclude = "")
    {
        try {

            $result = $this->objMysql->_select ("workflow.workflow_variables", array("variable_name"), array("variable_name" => $variableName));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                if ( $variableName == $result[0]["variable_name"] )
                {
                    throw new Exception ("Field already exists");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Variable
     *
     * @param string $processUid Unique id of Process
     * @param string $variableUid Unique id of Variable
     *
     * return void
     */
    public function delete ($processUid, $variableUid)
    {
        try {

            $this->throwExceptionIfNotExistsVariable ($variableUid);
            //Verify variable

            $isUsed = $this->isUsed ($processUid, $variable);

            if ( $isUsed !== false )
            {
                throw new Exception ("Variable is assigned to other fields");
            }

            $this->objMysql->_delete ("workflow.workflow_variables", array("field_id" => $this->fieldId));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $fieldId
     * @return \Variable
     * @throws \Exception
     */
    public function getVariableForField ($fieldId)
    {
        try {
            $result = $this->objMysql->_query ("SELECT var.* FROM workflow.workflow_variables var
                                                INNER JOIN workflow.fields f ON f.field_id = var.field_id
                                                WHERE f.field_identifier = ?", [$fieldId]);

            if ( !empty ($result) )
            {
                $arrVariables = [];


                $arrVariables = new Variable ($result[0]['field_id']);
                $arrVariables->setValidationType ($result[0]['validation_type']);
                $arrVariables->setVariableName ($result[0]['variable_name']);
                $arrVariables->setId ($result[0]['id']);

                //Return
                return $arrVariables;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Variable
     * @param string $processUid Unique id of Process
     * @param string $variableUid Unique id of Variable
     *
     * return array Return an array with data of a Variable
     */
    public function getVariable ($fieldId, $variableUid)
    {
        try {
            $result = $this->objMysql->_select ("workflow.workflow_variables", array(), array("id" => $variableUid));

            if ( !empty ($result) )
            {
                $arrVariables = [];


                $arrVariables = new Variable ($result[0]['field_id']);
                $arrVariables->setValidationType ($result[0]['validation_type']);
                $arrVariables->setVariableName ($result[0]['variable_name']);
                $arrVariables->setId ($result[0]['id']);

                //Return
                return $arrVariables;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of Variables
     *
     * @param string $processUid Unique id of Process
     *
     * return array Return an array with data of a DynaForm
     */
    public function getVariables ($processUid)
    {
        try {
            $results = $this->objMysql->_query ("SELECT wv.* FROM workflow.`step_fields` sf
                                    INNER JOIN workflow.workflow_variables wv ON wv.field_id = sf.field_id
                                    WHERE sf.step_id = ?", [$processUid]);

            $arrVariables = [];

            foreach ($results as $key => $result) {
                $arrVariables[$result['field_id']] = new Variable ($result['field_id']);
                $arrVariables[$result['field_id']]->setValidationType ($result['validation_type']);
                $arrVariables[$result['field_id']]->setVariableName ($result['variable_name']);
                $arrVariables[$result['field_id']]->setId ($result['id']);
            }


            //Return
            return $arrVariables;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $fieldId
     * @return boolean
     */
    public function checkFieldHasVariable ($fieldId)
    {
        $result = $this->objMysql->_select ("workflow.workflow_variables", array(), array("field_id" => $fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return false;
    }

    /**
     * Check if the variable exists
     *
     * @param string $fieldId           Unique id of field
     *
     * return void Throw exception if it doesnt exist
     */
    public function throwExceptionIfNotExistsVariable ($fieldId)
    {
        try {
            if ( $this->checkFieldHasVariable ($fieldId) === false )
            {
                throw new Exception ("Could not find Variable");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Variable record by name
     *
     * @param string $variableName                  Variable name

     * @return array Returns an array with Variable record, ThrowTheException/FALSE otherwise
     */
    public function getVariableRecordByName ($variableName, $throwException = true)
    {
        try {
            $result = $this->objMysql->_select ("workflow.workflow_variables", array(), array("variable_name" => $variableName));
            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                $arrayVariableData = $result[0];
            }
            else
            {
                if ( $throwException )
                {
                    throw new Exception ('ID_DOES_NOT_EXIST');
                }
                else
                {
                    return false;
                }
            }
            //Return
            return $arrayVariableData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
