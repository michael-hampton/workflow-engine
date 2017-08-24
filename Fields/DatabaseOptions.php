<?php

class DatabaseOptions
{

    private $databaseName;
    private $tableName;
    private $idColumn;
    private $valueColumn;
    private $whereColumn;
    private $orderBy;
    private $objMysql;
    private $fieldId;
    private $id;
    private $validationFailures = array();
    private $arrayFieldDefinition = array(
        "databaseName" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getDatabaseName", "mutator" => "setDatabaseName"),
        "tableName" => array("type" => "string", "required" => true, "empty" => false, "accessor" => "getTableName", "mutator" => "setTableName"),
        "idColumn" => array("type" => "string", "required" => true, "empty" => true, "accessor" => "getIdColumn", "mutator" => "setIdColumn"),
        "valueColumn" => array("type" => "string", "required" => false, "empty" => false, "accessor" => "getValueColumn", "mutator" => "setValueColumn"),
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

    public function checkOptionExists ()
    {
        $result = $this->objMysql->_select ("workflow.data_types", array(), array("field_id" => $this->fieldId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            $this->id = $result[0]['id'];
            return true;
        }
        else
        {
            return false;
        }
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

        if ( $this->checkOptionExists () )
        {
            $this->objMysql->_update ("workflow.data_types", array(
                "options" => $strOptions,
                "data_object_type" => 2
                    ), array("field_id" => $this->fieldId)
            );

            $this->objMysql->_update ("workflow.fields", array("data_type" => $this->id), array("field_id" => $this->fieldId));
        }
        else
        {
            $id = $this->objMysql->_insert ("workflow.data_types", array(
                "options" => $strOptions,
                "field_id" => $this->fieldId,
                "data_object_type" => 2
                    )
            );

            $this->objMysql->_update ("workflow.fields", array("data_type" => $id), array("field_id" => $this->fieldId));
        }
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
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $databaseName !== null && !is_string ($databaseName) )
        {
            $databaseName = (string) $databaseName;
        }
        if ( $this->databaseName !== $databaseName )
        {
            $this->databaseName = $databaseName;
        }
    }

    public function setIdColumn ($idColumn)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $idColumn !== null && !is_string ($idColumn) )
        {
            $idColumn = (string) $idColumn;
        }
        if ( $this->idColumn !== $idColumn )
        {
            $this->idColumn = $idColumn;
        }
    }

    public function setValueColumn ($valueColumn)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $valueColumn !== null && !is_string ($valueColumn) )
        {
            $valueColumn = (string) $valueColumn;
        }
        if ( $this->valueColumn !== $valueColumn )
        {
            $this->valueColumn = $valueColumn;
        }
    }

    public function setWhereColumn ($whereColumn)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $whereColumn !== null && !is_string ($whereColumn) )
        {
            $whereColumn = (string) $whereColumn;
        }
        if ( $this->whereColumn !== $whereColumn )
        {
            $this->whereColumn = $whereColumn;
        }
    }

    public function setOrderBy ($orderBy)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $orderBy !== null && !is_string ($orderBy) )
        {
            $orderBy = (string) $orderBy;
        }
        if ( $this->orderBy !== $orderBy )
        {
            $this->orderBy = $orderBy;
        }
    }

    public function getTableName ()
    {
        return $this->tableName;
    }

    public function setTableName ($tableName)
    {
        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ( $tableName !== null && !is_string ($tableName) )
        {
            $tableName = (string) $tableName;
        }
        if ( $this->tableName !== $tableName )
        {
            $this->tableName = $tableName;
        }
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
