<?php

class DatabaseOptions
{

    private $databaseName;
    private $tableName;
    private $idColumn;
    private $valueColumn;
    private $whereColumn;
    private $orderBy;
    private $stepId;
    private $objMysql;
    private $fieldId;
    private $validationFailures = array();
    private $arrayFieldDefinition = array(
        "databaseName" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDatabaseName", "mutator" => "setDatabaseName"),
        "tableName" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getTableName", "mutator" => "setTableName"),
        "idColumn" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getIdColumn", "mutator" => "setIdColumn"),
        "valueColumn" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getValueColumn", "mutator" => "setValueColumn"),
        "whereColumn" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getWhereColumn", "mutator" => "setWhereColumn"),
        "orderBy" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getOrderBy", "mutator" => "setOrderBy"),
    );

    public function __construct ($fieldId)
    {
        $this->fieldId = $fieldId;

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
        $arrDatabaseOptions = array(
            "databaseName" => $this->databaseName,
            "tableName" => $this->tableName,
            "idColumn" => $this->idColumn,
            "valueColumn" => $this->valueColumn,
            "whereColumn" => $this->whereColumn,
            "orderBy" => $this->orderBy
        );

        $strOptions = json_encode ($arrDatabaseOptions);

        $id = $this->objMysql->_insert ("workflow.data_types", array(
            "options" => $strOptions,
            "field_id" => $this->fieldId,
            "data_object_type" => 2
                )
        );

        $this->objMysql->_update ("workflow.fields", array("data_type" => $id), array("field_id" => $this->fieldId));
    }

    public function getDatabaseName ()
    {
        return $this->databaseName;
    }

    public function getIdColumn ()
    {
        return $this->idColumn;
    }

    public function getValueColumn ()
    {
        return $this->valueColumn;
    }

    public function getWhereColumn ()
    {
        return $this->whereColumn;
    }

    public function getOrderBy ()
    {
        return $this->orderBy;
    }

    public function setDatabaseName ($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    public function setIdColumn ($idColumn)
    {
        $this->idColumn = $idColumn;
    }

    public function setValueColumn ($valueColumn)
    {
        $this->valueColumn = $valueColumn;
    }

    public function setWhereColumn ($whereColumn)
    {
        $this->whereColumn = $whereColumn;
    }

    public function setOrderBy ($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function getTableName ()
    {
        return $this->tableName;
    }

    public function setTableName ($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getValidationFailures ()
    {
        return $this->validationFailures;
    }

    public function setValidationFailures ($validationFailures)
    {
        $this->validationFailures = $validationFailures;
    }

}
