<?php

namespace BusinessModel;

class StepVariable
{

    use Validator;

    private $objMysql;
    private $fieldId;

    /**
     * 
     * @param type $fieldId
     */
    public function __construct (\Field $objField = null)
    {
        $this->objMysql = new \Mysql2();

        if ( $objField !== null )
        {
            $this->fieldId = $objField->getFieldId ();
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
            $this->existsName ($fieldId, $arrayData["VAR_NAME"], "");

            $this->throwExceptionFieldDefinition ($arrayData);

            if ( isset ($arrayData['VAR_SQL']) && trim ($arrayData['VAR_SQL']) !== "" )
            {
                $this->throwExceptionIfSomeRequiredVariableSqlIsMissingInVariables ($arrayData["VAR_SQL"], array());

                $arrSql = $this->buildSql ($arrayData["VAR_SQL"]);

                if ( !empty ($arrSql) )
                {
                    $objDatabase = new \DatabaseOptions ($fieldId);
                    $objDatabase->setDatabaseName ($arrayData['VAR_DBCONNECTION']);
                    $objDatabase->setTableName ($arrSql['from']);
                    $objDatabase->setIdColumn ($arrSql['select']);
                    $objDatabase->setWhereColumn ($arrSql['where']);

                    if ( isset ($arrSql['order']) )
                    {
                        $objDatabase->setOrderBy ($arrSql['order']);
                    }

                    if ( $objDatabase->validate () )
                    {
                        $objDatabase->save ();
                    }
                    else
                    {
                        $msg = '';

                        foreach ($objDatabase->getValidationFailures () as $message) {
                            $msg .= $message . "</br>";
                        }

                        throw new Exception ("Failed to save database options " . $msg);
                    }
                }
            }

            $variable = new \Variable ($fieldId);

            if ( isset ($arrayData["VAR_NAME"]) && trim ($arrayData['VAR_NAME']) !== "" )
            {
                $variable->setVariableName ($arrayData["VAR_NAME"]);
            }
            else
            {
                throw new \Exception ("Variable Name cant be empty");
            }

            if ( isset ($arrayData["VAR_FIELD_TYPE"]) )
            {
                $variable->setValidationType ($arrayData["VAR_FIELD_TYPE"]);
            }
            else
            {
                throw new \Exception ("Validation type cannot be empty");
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
                throw new \Exception ("Cannot create variable " . $msg);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function buildSql ($sqlString)
    {
        $re = '/^(select|SELECT) (?P<select>(.+?)) (from|FROM) (?P<from>(.+)) ((where|WHERE) (?P<where>(.+?)))( (order by|ORDER BY) (?P<order>(.+?)))?( (limit|LIMIT) (?P<limit>(.+)))?$/';

        preg_match ($re, $sqlString, $matches, PREG_OFFSET_CAPTURE, 0);

        $arraySql = array(
            "select" => $matches['select'][0],
            "from" => $matches['from'][0],
            "where" => $matches['where'][0],
        );

        if ( isset ($matches['order'][0]) )
        {
            $arraySql['order'] = $matches['order'][0];
        }

        if ( isset ($matches['limit'][0]) )
        {
            $arraySql['limit'] = $matches['limit'][0];
        }

        return $arraySql;
    }

    /**
     * Get required variables in the SQL
     *
     * @param string $sql SQL
     *
     * return array Return an array with required variables in the SQL
     */
    public function sqlGetRequiredVariables ($sql)
    {
        try {
            $arrayVariableRequired = array();
            preg_match_all ("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", $sql, $arrayMatch, PREG_SET_ORDER);
            foreach ($arrayMatch as $value) {
                $arrayVariableRequired[] = $value[1];
            }
            return $arrayVariableRequired;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if some required variable in the SQL is missing in the variables
     *
     * @param string $variableName  Variable name
     * @param string $variableSql   SQL
     * @param array  $arrayVariable The variables
     *
     * return void Throw exception if some required variable in the SQL is missing in the variables
     */
    public function throwExceptionIfSomeRequiredVariableSqlIsMissingInVariables ($variableSql, array $arrayVariable)
    {
        try {
            $arrayResult = array_diff (array_unique ($this->sqlGetRequiredVariables ($variableSql)), array_keys ($arrayVariable));

            if ( count ($arrayResult) > 0 )
            {
                throw new \Exception ("ID_PROCESS_VARIABLE_REQUIRED_VARIABLES_FOR_QUERY");
            }
        } catch (\Exception $e) {
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
    public function existsName ($fieldId, $variableName)
    {
        try {
            $result = $this->objMysql->_query ("SELECT * FROM workflow.workflow_variables WHERE variable_name = ? AND field_id != ?", [$variableName, $fieldId]);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                if ( $variableName == $result[0]["variable_name"] )
                {
                    throw new \Exception ("Field already exists");
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

            $isUsed = $this->isUsed ($processUid);

            if ( $isUsed !== false )
            {
                throw new \Exception ("Variable is assigned to other fields");
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


                $arrVariables = new \Variable ($result[0]['field_id']);
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
    public function getVariable ($variableUid)
    {
        try {
            $result = $this->objMysql->_select ("workflow.workflow_variables", array(), array("id" => $variableUid));

            if ( !empty ($result) )
            {
                $arrVariables = [];


                $arrVariables = new \Variable ($result[0]['field_id']);
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

            foreach ($results as $result) {
                $arrVariables[$result['field_id']] = new \Variable ($result['field_id']);
                $arrVariables[$result['field_id']]->setValidationType ($result['validation_type']);
                $arrVariables[$result['field_id']]->setVariableName ($result['variable_name']);
                $arrVariables[$result['field_id']]->setId ($result['id']);
                $arrVariables[$result['field_id']]->setVarSql ($result['variation_sql']);
                $arrVariables[$result['field_id']]->setVarDbconnection ($result['db_connection']);
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
                throw new \Exception ("Could not find Variable");
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
                    throw new \Exception ('ID_DOES_NOT_EXIST');
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

    /**
     * Verify field definition
     *
     * @param array $aData Unique id of Variable to exclude
     *
     */
    public function throwExceptionFieldDefinition ($aData)
    {

        try {
            if ( isset ($aData["VAR_NAME"]) )
            {
                $this->isString ($aData['VAR_NAME'], '$var_name');
                $this->isNotEmpty ($aData['VAR_NAME'], '$var_name');
            }
            if ( isset ($aData["VAR_FIELD_TYPE"]) )
            {
                $this->isString ($aData['VAR_FIELD_TYPE'], '$var_field_type');
                $this->isNotEmpty ($aData['VAR_FIELD_TYPE'], '$var_field_type');
            }
            if ( isset ($aData["VAR_FIELD_SIZE"]) )
            {
                $this->isInteger ($aData["VAR_FIELD_SIZE"], '$var_field_size');
            }
            if ( isset ($aData["VAR_LABEL"]) )
            {
                $this->isString ($aData['VAR_LABEL'], '$var_label');
                $this->isNotEmpty ($aData['VAR_LABEL'], '$var_label');
            }
            if ( isset ($aData["VAR_DBCONNECTION"]) )
            {
                $this->isString ($aData['VAR_DBCONNECTION'], '$var_dbconnection');
            }
            if ( isset ($aData["VAR_SQL"]) )
            {
                $this->isString ($aData['VAR_SQL'], '$var_sql');
            }
            if ( isset ($aData["VAR_NULL"]) )
            {
                $this->isInteger ($aData['VAR_NULL'], '$var_null');
                if ( $aData["VAR_NULL"] != 0 && $aData["VAR_NULL"] != 1 )
                {
                    throw new \Exception ("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getVariableTypeByName ($variableName)
    {
        try {
            $sql = "SELECT id, variable_name, validation_type, db_connection, variation_sql, accepted_values FROM workflow.workflow_variables WHERE variable_name = ?";
            $results = $this->objMysql->_query ($sql, [$variableName]);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                return false;
            }

            return sizeof ($results[0]) ? $results[0] : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
