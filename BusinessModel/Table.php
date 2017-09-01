<?php

namespace BusinessModel;

class Table
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * List of Tables in process
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     * @return array
     */
    public function getTables ($pro_uid = '', $reportFlag = false)
    {
        //VALIDATION
        if ( $reportFlag )
        {
            $pro_uid = $this->validateProUid ($pro_uid);
        }
        $reportTables = array();

        $results = $this->objMysql->_select ('report_tables.additional_tables', [], ["PRO_UID" => $pro_uid]);
        foreach ($results as $row) {
            $reportTables[] = $this->getTable ($row['ADD_TAB_UID'], $pro_uid, $reportFlag, false);
        }
        return $reportTables;
    }

    /**
     * Get data for Table
     * @var string $tab_uid. Uid for table
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     * @var string $validate. Flag for validate
     *
     *
     * @return array
     */
    public function getTable ($tab_uid, $pro_uid = '', $reportFlag = false, $validate = true)
    {
        //VALIDATION
        if ( $validate )
        {
            if ( $reportFlag )
            {
                $pro_uid = $this->validateProUid ($pro_uid);
                $tabData['PRO_UID'] = $pro_uid;
            }
            $tab_uid = $this->validateTabUid ($tab_uid, $reportFlag);
        }
        $tabData = array();
        $additionalTables = new \AdditionalTables();
        // TABLE PROPERTIES
        $table = $additionalTables->load ($tab_uid, true);

        $table['DBS_UID'] = !isset ($table['DBS_UID']) || $table['DBS_UID'] == '' ? 'workflow' : $table['DBS_UID'];
        // TABLE NUM ROWS DATA
        $tableData = $additionalTables->getAllData ($tab_uid, 0, 20);
        if ( $reportFlag )
        {
            $tabData['REP_UID'] = $tab_uid;
            $tabData['REP_TAB_NAME'] = $table['ADD_TAB_CLASS_NAME'];
            $tabData['REP_TAB_DESCRIPTION'] = $table['ADD_TAB_DESCRIPTION'];
            $tabData['REP_TAB_CLASS_NAME'] = $table['ADD_TAB_CLASS_NAME'];
            $tabData['REP_TAB_CONNECTION'] = $table['DBS_UID'];
            $tabData['REP_TAB_TYPE'] = $table['ADD_TAB_TYPE'];
            $tabData['REP_TAB_GRID'] = isset ($table['ADD_TAB_GRID']) ? $table['ADD_TAB_GRID'] : 0;
            $tabData['REP_NUM_ROWS'] = $tableData['count'];
        }
        else
        {
            $tabData['PMT_UID'] = $tab_uid;
            $tabData['PMT_TAB_NAME'] = $table['ADD_TAB_CLASS_NAME'];
            $tabData['PMT_TAB_DESCRIPTION'] = $table['ADD_TAB_DESCRIPTION'];
            $tabData['PMT_TAB_CLASS_NAME'] = $table['ADD_TAB_CLASS_NAME'];
            $tabData['PMT_NUM_ROWS'] = $tableData['count'];
        }

        // TABLE FIELDS
        foreach ($table['FIELDS'] as $valField) {

            $tabData['FIELDS'][] = $valField;
        }

        return $tabData;
    }

    /**
     * Generate Data for Report Table
     * @var string $pro_uid. Uid for process
     * @var string $rep_uid. Uid for report table
     * @var string $validate. Flag for validate
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return void
     */
    public function generateDataReport ($pro_uid, $rep_uid, $validate = true)
    {
        if ( $validate )
        {
            $pro_uid = $this->validateProUid ($pro_uid);
            $rep_uid = $this->validateTabUid ($rep_uid);
        }

        $additionalTables = new \AdditionalTables();
        $additionalTables->populateReportTable ($pro_uid);
    }

    /**
     * Get data for Table
     * @var string $tab_uid. Uid for table
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     *     *
     * @return array
     */
    public function getTableData ($tab_uid, $pro_uid = '', $filter = null, $reportFlag = false, $search = '')
    {
        //Validation
        $inputFilter = new \InputFilter();
        $filter = $inputFilter->sanitizeInputValue ($filter, 'nosql');
        //VALIDATION
        if ( $reportFlag )
        {
            $pro_uid = $this->validateProUid ($pro_uid);
        }
        $tab_uid = $this->validateTabUid ($tab_uid, $reportFlag);
        $additionalTables = new AdditionalTables();
        $result = $additionalTables->getAllData ($tab_uid, null, null, null, $filter, false, $search);
        $primaryKeys = $additionalTables->getPrimaryKeys ();
        if ( is_array ($result['rows']) )
        {
            foreach ($result['rows'] as $i => $row) {
                $result['rows'][$i] = array_change_key_case ($result['rows'][$i], CASE_LOWER);
                $primaryKeysValues = array();
                foreach ($primaryKeys as $key) {
                    $primaryKeysValues[] = isset ($row[$key['FLD_NAME']]) ? $row[$key['FLD_NAME']] : '';
                }
                //$result['rows'][$i]['__index__'] = G::encrypt (implode (',', $primaryKeysValues), 'pmtable');
            }
        }
        else
        {
            $result['rows'] = array();
        }
        return $result;
    }

    public function dropTable ($tableName)
    {
        $tableName = "rpt_" . strtolower ($tableName);

        $this->objMysql->_query ('DROP TABLE IF EXISTS ' . $tableName . ' ');
    }

    /**
     * Save Data for Table
     * @var string $tab_data. Data for table
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     * @var string $createRep. Flag for create table
     *
     * @return array
     */
    public function saveTable ($tab_data, $pro_uid = '', $reportFlag = false, $createRep = true)
    {
        // CHANGE CASE UPPER TABLE
        $fieldsValidate = array();
        $tableName = '';
        $tableCon = 'workflow';
        $dataValidate = $tab_data;
        $oAdditionalTables = new \AdditionalTables();
        // VALIDATION TABLE DATA
        if ( $reportFlag )
        {

            $pro_uid = $this->validateProUid ($pro_uid);
            $dataValidate['TAB_UID'] = (isset ($dataValidate['REP_UID'])) ? $dataValidate['REP_UID'] : '';
            $dataValidate['REP_TAB_CONNECTION'] = "WORKFLOW";
            $dataValidate['PRO_UID'] = $pro_uid;
            $dataValidate['REP_TAB_NAME'] = $this->validateTabName ($dataValidate['reportTableName'], $reportFlag);
            $tempRepTabName = $dataValidate['REP_TAB_CONNECTION'];
            $dataValidate['REP_TAB_GRID'] = 0;

            $dataValidate['REP_TAB_CONNECTION'] = $this->validateRepConnection ($tempRepTabName);
            if ( $dataValidate['reportTableType'] == 'GRID' )
            {
                $dataValidate['REP_TAB_GRID'] = $this->validateRepGrid ($dataValidate['REP_TAB_GRID'], $pro_uid);
            }
            $fieldsValidate = $this->getDynafields ($pro_uid, $dataValidate['reportTableType'], $dataValidate['REP_TAB_GRID']);
            if ( empty ($fieldsValidate) )
            {
                $fieldsValidate['NAMES'] = array();
                $fieldsValidate['INDEXS'] = array();
                $fieldsValidate['UIDS'] = array();
            }
            $repTabClassName = $oAdditionalTables->getPHPName ($dataValidate['reportTableName']);
            $tableName = $dataValidate['reportTableName'];
            $tableCon = $dataValidate['REP_TAB_CONNECTION'];
        }
        else
        {
            $dataValidate['TAB_UID'] = (isset ($dataValidate['PMT_UID'])) ? $dataValidate['PMT_UID'] : '';
            $dataValidate['PMT_TAB_NAME'] = $this->validateTabName ($dataValidate['PMT_TAB_NAME']);
            $dataValidate['PMT_TAB_CONNECTION'] = 'workflow';
            $repTabClassName = $oAdditionalTables->getPHPName ($dataValidate['PMT_TAB_NAME']);
            $tableName = $dataValidate['PMT_TAB_NAME'];
            $tableCon = $dataValidate['PMT_TAB_CONNECTION'];
        }

        $columns = [];
        foreach ($dataValidate['reportFields'] as $key => $reportField) {
            foreach ($reportField as $key2 => $value) {
                $columns[$key2][$key] = $value;
            }
        }

        // Reserved Words Table, Field, Sql
        $reservedWords = array('ALTER', 'CLOSE', 'COMMIT', 'CREATE', 'DECLARE', 'DELETE',
            'DROP', 'FETCH', 'FUNCTION', 'GRANT', 'INDEX', 'INSERT', 'OPEN', 'REVOKE', 'ROLLBACK',
            'SELECT', 'SYNONYM', 'TABLE', 'UPDATE', 'VIEW', 'APP_UID', 'ROW', 'PMTABLE');
        $reservedWordsPhp = array('case', 'catch', 'cfunction', 'class', 'clone', 'const', 'continue',
            'declare', 'default', 'do', 'else', 'elseif', 'enddeclare', 'endfor', 'endforeach', 'endif',
            'endswitch', 'endwhile', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto',
            'if', 'implements', 'interface', 'instanceof', 'private', 'namespace', 'new', 'old_function',
            'or', 'throw', 'protected', 'public', 'static', 'switch', 'xor', 'try', 'use', 'var', 'while');
        $reservedWordsSql = $this->reservedWordsSql ();

        if ( $reportFlag )
        {
            $defaultColumns = $this->getReportTableDefaultColumns ($dataValidate['reportTableType']);
            $columns = array_merge ($defaultColumns, $columns);
        }
        // validations
        if ( $createRep )
        {
            $data = $oAdditionalTables->loadByName ($tableName);
            if ( is_array ($data) )
            {
                $this->dropTable ($tableName);
                $this->deleteTable ($data[0]["ADD_TAB_UID"]);
            }
        }
        if ( in_array (strtoupper ($tableName), $reservedWords) ||
                in_array (strtoupper ($tableName), $reservedWordsSql) )
        {
            throw (new \Exception ("ID_PMTABLE_INVALID_NAME"));
        }
        //backward compatility
        $flagKey = false;
        $columnsStd = array();

        foreach ($columns as $i => $column) {

            $columns[$i]['field_dyn'] = $columns[$i]['field_name'];


            if ( isset ($columns[$i]['column_name']) )
            {
                $columns[$i]['column_name'] = $columns[$i]['column_name'];
            }
            if ( isset ($columns[$i]['label']) )
            {
                $columns[$i]['label'] = $columns[$i]['label'];
            }
            if ( isset ($columns[$i]['fld_type']) )
            {
                $columns[$i]['column_type'] = $columns[$i]['column_type'];
            }

            if ( isset ($columns[$i]['column_size']) )
            {
                $columns[$i]['column_size'] = $columns[$i]['column_size'];
                if ( !is_numeric ($columns[$i]['column_size']) )
                {
                    throw (new \Exception ("The property fld_size: '" . $columns[$i]['column_size'] . "' is incorrect numeric value."));
                }
                else
                {
                    $columns[$i]['column_size'] = (int) $columns[$i]['column_size'];
                }
            }
            if ( isset ($columns[$i]['fld_key']) )
            {
                $columns[$i]['field_key'] = $columns[$i]['fld_key'];
                unset ($columns[$i]['fld_key']);
            }
            if ( isset ($columns[$i]['fld_null']) )
            {
                $columns[$i]['field_null'] = $columns[$i]['fld_null'];
                unset ($columns[$i]['fld_null']);
            }
            if ( isset ($columns[$i]['auto_increment']) )
            {
                $columns[$i]['auto_increment'] = $columns[$i]['auto_increment'];
                unset ($columns[$i]['auto_increment']);
            }
            // VALIDATIONS            

            if ( in_array (strtoupper ($columns[$i]['column_name']), $reservedWordsSql) ||
                    in_array (strtolower ($columns[$i]['column_name']), $reservedWordsPhp) ||
                    $columns[$i]['column_name'] == '' )
            {
                throw (new \Exception ("The property fld_name: '" . $columns[$i]['column_name'] . "' is incorrect value."));
            }
            if ( $columns[$i]['label'] == '' )
            {
                throw (new \Exception ("The property fld_label: '" . $columns[$i]['label'] . "' is incorrect value."));
            }
            $columns[$i]['column_type'] = $this->validateFldType ($columns[$i]['column_type']);
            if ( isset ($columns[$i]['auto_increment']) && $columns[$i]['auto_increment'] )
            {
                $typeCol = $columns[$i]['column_type'];
                if ( !($typeCol === 'INTEGER' || $typeCol === 'TINYINT' || $typeCol === 'SMALLINT' || $typeCol === 'BIGINT') )
                {
                    $columns[$i]['column_type'] = false;
                }
            }

            $temp = new \stdClass();
            foreach ($columns[$i] as $key => $valCol) {
                eval ('$temp->' . str_replace ('fld', 'field', $key) . " = '" . $valCol . "';");
            }

            $temp->uid = (isset ($temp->uid)) ? $temp->uid : '';
            $temp->_index = (isset ($temp->_index)) ? $temp->_index : '';
            $temp->field_uid = (isset ($temp->field_uid)) ? $temp->field_uid : '';
            $temp->field_dyn = (isset ($temp->field_dyn)) ? $temp->field_dyn : '';
            $temp->field_key = (isset ($temp->field_key)) ? $temp->field_key : 0;
            $temp->field_null = (isset ($temp->field_null)) ? $temp->field_null : 1;
            $temp->field_dyn = (isset ($temp->field_dyn)) ? $temp->field_dyn : '';
            $temp->field_filter = (isset ($temp->field_filter)) ? $temp->field_filter : 0;
            $temp->field_autoincrement = (isset ($temp->field_autoincrement)) ? $temp->field_autoincrement : 0;
            if ( !$reportFlag )
            {
                unset ($temp->_index);
                unset ($temp->field_filter);
            }
            if ( $temp->field_key == 1 || $temp->field_key == true )
            {
                $flagKey = true;
            }
            $columnsStd[$i] = $temp;
        }

        if ( !$flagKey )
        {
            throw (new \Exception ("The fields must have a key 'fld_key'"));
        }

        $pmTable = new \pmTable ($repTabClassName);
        $pmTable->setDataSource ($tableCon);
        $pmTable->setColumns ($columnsStd);
        $pmTable->setAlterTable (true);
        if ( !$createRep )
        {
            $pmTable->setKeepData (true);
        }
        $pmTable->build ();
        $buildResult = ob_get_contents ();
        ob_end_clean ();
        unset ($buildResult);
        // Updating additional table struture information
        if ( $reportFlag )
        {
            $addTabData = array(
                'ADD_TAB_UID' => $dataValidate['TAB_UID'],
                'ADD_TAB_NAME' => $dataValidate['REP_TAB_NAME'],
                'ADD_TAB_CLASS_NAME' => $repTabClassName,
                'ADD_TAB_DESCRIPTION' => $dataValidate['reportTableDescription'],
                'ADD_TAB_PLG_UID' => '',
                'DBS_UID' => ($dataValidate['REP_TAB_CONNECTION'] ? $dataValidate['REP_TAB_CONNECTION'] : 'workflow'),
                'PRO_UID' => $dataValidate['PRO_UID'],
                'ADD_TAB_TYPE' => $dataValidate['reportTableType'],
                'ADD_TAB_GRID' => $dataValidate['REP_TAB_GRID']
            );
        }
        else
        {
            $addTabData = array(
                'ADD_TAB_UID' => $dataValidate['TAB_UID'],
                'ADD_TAB_NAME' => $dataValidate['PMT_TAB_NAME'],
                'ADD_TAB_CLASS_NAME' => $repTabClassName,
                'ADD_TAB_DESCRIPTION' => $dataValidate['PMT_TAB_DSC'],
                'ADD_TAB_PLG_UID' => '',
                'DBS_UID' => ($dataValidate['PMT_TAB_CONNECTION'] ? $dataValidate['PMT_TAB_CONNECTION'] : 'workflow'),
                'PRO_UID' => '',
                'ADD_TAB_TYPE' => '',
                'ADD_TAB_GRID' => ''
            );
        }
        if ( $createRep )
        {
            //new report table
            //create record
            $addTabUid = $oAdditionalTables->create ($addTabData);
        }
        else
        {
            //editing report table
            //updating record
            $addTabUid = $dataValidate['TAB_UID'];
            $oAdditionalTables->update ($addTabData);
        }

        $oFields = new \ReportField();

        // Updating pmtable fields
        foreach ($columnsStd as $i => $column) {

            $column = (array) $column;
            $field = array(
                'FLD_UID' => $column['uid'],
                'FLD_INDEX' => $i,
                'ADD_TAB_UID' => $addTabUid,
                'FLD_NAME' => $column['column_name'],
                'FLD_DESCRIPTION' => $column['label'],
                'FLD_TYPE' => $column['column_type'],
                'FLD_SIZE' => (!isset ($column['column_size']) || $column['column_size'] == '') ? null : $column['column_size'],
                'FLD_NULL' => $column['field_null'] ? 1 : 0,
                'FLD_AUTO_INCREMENT' => $column['field_autoincrement'] ? 1 : 0,
                'FLD_KEY' => $column['field_key'] ? 1 : 0,
                'FLD_FOREIGN_KEY' => 0,
                'FLD_FOREIGN_KEY_TABLE' => '',
                'FLD_DYN_NAME' => $column['field_dyn'],
                'FLD_DYN_UID' => $column['field_dyn'],
                'FLD_FILTER' => (isset ($column['field_filter']) && $column['field_filter']) ? 1 : 0
            );
            $oFields->create ($field);
        }
        if ( $reportFlag )
        {
            $rep_uid = $addTabUid;
            $this->generateDataReport ($pro_uid, $rep_uid, false);
        }
        if ( $createRep )
        {
            $tab_uid = $addTabUid;
            return $this->getTable ($tab_uid, $pro_uid, $reportFlag, false);
        }
    }

    public function reservedWordsSql ()
    {
        //Reserved words SQL
        $reservedWordsSql = array("ACCESSIBLE", "ACTION", "ADD", "ALL", "ALTER", "ANALYZE", "AND", "ANY", "AS", "ASC", "ASENSITIVE", "AUTHORIZATION", "BACKUP", "BEFORE", "BEGIN", "BETWEEN", "BIGINT", "BINARY", "BIT", "BLOB", "BOTH", "BREAK", "BROWSE", "BULK", "BY", "CALL", "CASCADE", "CASE", "CHANGE", "CHAR", "CHARACTER", "CHECK", "CHECKPOINT", "CLOSE", "CLUSTERED", "COALESCE", "COLLATE", "COLUMN", "COMMIT", "COMPUTE", "CONDITION", "CONSTRAINT", "CONTAINS", "CONTAINSTABLE", "CONTINUE", "CONVERT", "CREATE", "CROSS", "CURRENT", "CURRENT_DATE", "CURRENT_TIME", "CURRENT_TIMESTAMP", "CURRENT_USER", "CURSOR", "DATABASE", "DATABASES", "DATE", "DAY_HOUR", "DAY_MICROSECOND", "DAY_MINUTE", "DAY_SECOND", "DBCC", "DEALLOCATE", "DEC", "DECIMAL", "DECLARE", "DEFAULT", "DELAYED", "DELETE", "DENY", "DESC", "DESCRIBE", "DETERMINISTIC", "DISK", "DISTINCT", "DISTINCTROW",
            "DISTRIBUTED", "DIV", "DOUBLE", "DROP", "DUAL", "DUMMY", "DUMP", "EACH", "ELSE", "ELSEIF", "ENCLOSED", "END", "ENUM", "ERRLVL", "ESCAPE", "ESCAPED", "EXCEPT", "EXEC", "EXECUTE", "EXISTS", "EXIT", "EXPLAIN", "FALSE", "FETCH", "FILE", "FILLFACTOR", "FLOAT", "FLOAT4", "FLOAT8", "FOR", "FORCE", "FOREIGN", "FREETEXT", "FREETEXTTABLE", "FROM", "FULL", "FULLTEXT", "FUNCTION", "GENERAL", "GOTO", "GRANT", "GROUP", "HAVING", "HIGH_PRIORITY", "HOLDLOCK", "HOUR_MICROSECOND", "HOUR_MINUTE", "HOUR_SECOND", "IDENTITY", "IDENTITYCOL", "IDENTITY_INSERT", "IF", "IGNORE", "IGNORE_SERVER_IDS", "IN", "INDEX", "INFILE", "INNER", "INOUT", "INSENSITIVE", "INSERT", "INT", "INT1", "INT2", "INT3", "INT4", "INT8", "INTEGER", "INTERSECT", "INTERVAL", "INTO", "IS", "ITERATE", "JOIN", "KEY", "KEYS", "KILL", "LEADING", "LEAVE", "LEFT", "LIKE", "LIMIT", "LINEAR", "LINENO", "LINES",
            "LOAD", "LOCALTIME", "LOCALTIMESTAMP", "LOCK", "LONG", "LONGBLOB", "LONGTEXT", "LOOP", "LOW_PRIORITY", "MASTER_HEARTBEAT_PERIOD", "MASTER_SSL_VERIFY_SERVER_CERT", "MATCH", "MAXVALUE", "MEDIUMBLOB", "MEDIUMINT", "MEDIUMTEXT", "MIDDLEINT", "MINUTE_MICROSECOND", "MINUTE_SECOND", "MOD", "MODIFIES", "NATIONAL", "NATURAL", "NO", "NOCHECK", "NONCLUSTERED", "NOT", "NO_WRITE_TO_BINLOG", "NULL", "NULLIF", "NUMERIC", "OF", "OFF", "OFFSETS", "ON", "OPEN", "OPENDATASOURCE", "OPENQUERY", "OPENROWSET", "OPENXML", "OPTIMIZE", "OPTION", "OPTIONALLY", "OR", "ORDER", "OUT", "OUTER", "OUTFILE", "OVER", "PERCENT", "PLAN", "PRECISION", "PRIMARY", "PRINT", "PROC", "PROCEDURE", "PUBLIC", "PURGE", "RAISERROR", "RANGE", "READ", "READS", "READTEXT", "READ_WRITE", "REAL", "RECONFIGURE", "REFERENCES", "REGEXP", "RELEASE", "RENAME", "REPEAT", "REPLACE",
            "REPLICATION", "REQUIRE", "RESIGNAL", "RESTORE", "RESTRICT", "RETURN", "REVOKE", "RIGHT", "RLIKE", "ROLLBACK", "ROWCOUNT", "ROWGUIDCOL", "RULE", "SAVE", "SCHEMA", "SCHEMAS", "SECOND_MICROSECOND", "SELECT", "SENSITIVE", "SEPARATOR", "SESSION_USER", "SET", "SETUSER", "SHOW", "SHUTDOWN", "SIGNAL", "SLOW", "SMALLINT", "SOME", "SPATIAL", "SPECIFIC", "SQL", "SQLEXCEPTION", "SQLSTATE", "SQLWARNING", "SQL_BIG_RESULT", "SQL_CALC_FOUND_ROWS", "SQL_SMALL_RESULT", "SSL", "STARTING", "STATISTICS", "STRAIGHT_JOIN", "SYSTEM_USER", "TABLE", "TERMINATED", "TEXT", "TEXTSIZE", "THEN", "TIME", "TIMESTAMP", "TINYBLOB", "TINYINT", "TINYTEXT", "TO", "TOP", "TRAILING", "TRAN", "TRANSACTION", "TRIGGER", "TRUE", "TRUNCATE", "TSEQUAL", "UNDO", "UNION", "UNIQUE", "UNLOCK", "UNSIGNED", "UPDATE", "UPDATETEXT", "USAGE", "USE", "USER", "USING", "UTC_DATE", "UTC_TIME",
            "UTC_TIMESTAMP", "VALUES", "VARBINARY", "VARCHAR", "VARCHARACTER", "VARYING", "VIEW", "WAITFOR", "WHEN", "WHERE", "WHILE", "WITH", "WRITE", "WRITETEXT", "XOR", "YEAR_MONTH", "ZEROFILL");
        return $reservedWordsSql;
    }

    /**
     * Delete Table
     * @var string $tab_uid. Uid for table
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     *
     *
     * @return void
     */
    public function deleteTable ($tab_uid, $pro_uid = '', $reportFlag = false)
    {
        if ( $reportFlag )
        {
            $pro_uid = $this->validateProUid ($pro_uid);
        }
        $tab_uid = $this->validateTabUid ($tab_uid, $reportFlag);
        $at = new \AdditionalTables();

        $at->deleteAll ($tab_uid);
    }

    /**
     * Get Fields of Dynaforms
     * @var string $pro_uid. Uid for Process
     * @var string $rep_tab_type. Type the Report Table
     * @var string $rep_tab_grid. Uid for Grid
     *     *
     * @return array
     */
    public function getDynafields ($pro_uid, $rep_tab_type, $rep_tab_grid = '')
    {
        $dynFields = array();
        $aFields = array();
        $aFields['FIELDS'] = array();
        $aFields['PRO_UID'] = $pro_uid;
        if ( isset ($rep_tab_type) && $rep_tab_type == 'GRID' )
        {
            list ($gridName, $gridId) = explode ('-', $rep_tab_grid);
            $dynFields = $this->_getDynafields ($pro_uid, 'grid', $gridId);
        }
        else
        {
            $dynFields = $this->_getDynafields ($pro_uid, 'xmlform');
        }
        $fieldReturn = array();
        foreach ($dynFields as $value) {
            $fieldReturn['NAMES'][] = $value['FIELD_NAME'];
            $fieldReturn['UIDS'][] = $value['FIELD_UID'];
            $fieldReturn['INDEXS'][] = $value['_index'];
        }

        return $fieldReturn;
    }

    /**
     * Get Fields of Dynaforms in xmlform
     * @var string $pro_uid. Uid for Process
     * @var string $type. Type the form
     * @var string $rep_tab_grid. Uid for Grid
     *     *
     * @return array
     */
    public function _getDynafields ($pro_uid, $type = 'xmlform', $rep_tab_grid = '')
    {
        $results = (new Form (new \Task (10)))->getFields (true);

        $fields = array();
        $fieldsNames = array();
        $labelFieldsTypeList = array('dropdown', 'radiogroup');
        $excludeFieldsList = array(
            'title',
            'subtitle',
            'link',
            'file',
            'button',
            'reset',
            'submit',
            'listbox',
            'checkgroup',
            'grid',
            'javascript',
            ''
        );
        $index = 0;
        foreach ($results as $aRow) {

            $fieldName = $aRow['name'];
            $fieldType = isset ($aRow['field_type']) ? $aRow['field_type'] : '';

            if ( !in_array ($fieldType, $excludeFieldsList) && !in_array ($fieldName, $fieldsNames) )
            {
                $fields[] = array(
                    'FIELD_UID' => $fieldName . '-' . $fieldType,
                    'FIELD_NAME' => $fieldName,
                    '_index' => $index++
                );
                $fieldsNames[] = $fieldName;
                if ( in_array ($fieldType, $labelFieldsTypeList) && !in_array ($fieldName . '_label', $fieldsNames) )
                {
                    $fields[] = array(
                        'FIELD_UID' => $fieldName . '_label' . '-' . $fieldType,
                        'FIELD_NAME' => $fieldName . '_label',
                        '_index' => $index++
                    );
                    $fieldsNames[] = $fieldName;
                }
            }
        }

        sort ($fields);
        return $fields;
    }

    /**
     * Get Default Columns of Report Table
     * @var string $type. Type of Report Table
     *
     *
     * @return array
     */
    public function getReportTableDefaultColumns ($type = 'NORMAL')
    {
        $defaultColumns = array();
        $application = array(
            'uid' => '',
            'field_name' => '',
            'field_uid' => '',
            'column_name' => 'APP_UID',
            'label' => 'APP_UID',
            'column_type' => 'INTEGER',
            'column_size' => 11,
            'field_dyn' => '',
            'field_key' => 1,
            'field_null' => 0,
            'field_filter' => false,
            'auto_increment' => false
        ); //APPLICATION KEY
        array_push ($defaultColumns, $application);
        $application = array(
            'uid' => '',
            'field_name' => '',
            'field_uid' => '',
            'column_name' => 'PRO_UID',
            'label' => 'PRO_UID',
            'column_type' => 'INTEGER',
            'column_size' => 11,
            'field_dyn' => '',
            'field_key' => 0,
            'field_null' => 0,
            'field_filter' => false,
            'auto_increment' => false
        ); //APP_NUMBER
        array_push ($defaultColumns, $application);
        $application = array(
            'uid' => '',
            'field_dyn' => '',
            'field_uid' => '',
            'column_name' => 'APP_STATUS',
            'label' => 'APP_STATUS',
            'column_type' => 'VARCHAR',
            'column_size' => 10,
            'field_dyn' => '',
            'field_key' => 0,
            'field_null' => 0,
            'field_filter' => false,
            'auto_increment' => false
        ); //APP_STATUS
        array_push ($defaultColumns, $application);
        //if it is a grid report table
        if ( $type == 'GRID' )
        {
            //GRID INDEX
            $gridIndex = array(
                'uid' => '',
                'field_dyn' => '',
                'field_uid' => '',
                'column_name' => 'ROW',
                'label' => 'ROW',
                'column_type' => 'INTEGER',
                'column_size' => '11',
                'field_dyn' => '',
                'field_key' => 1,
                'field_null' => 0,
                'field_filter' => false,
                'auto_increment' => false
            );
            array_push ($defaultColumns, $gridIndex);
        }

        return $defaultColumns;
    }

    /**
     * Validate Process Uid
     * @var string $pro_uid. Uid for process
     *
     *
     * @return string
     */
    public function validateProUid ($pro_uid)
    {
        $pro_uid = trim ($pro_uid);
        if ( $pro_uid == '' )
        {
            throw (new \Exception ("The project with prj_uid: '' does not exist."));
        }
        $oProcess = new Process();
        if ( !($oProcess->processExists ($pro_uid)) )
        {
            throw (new \Exception ("The project with prj_uid: '$pro_uid' does not exist."));
        }
        return $pro_uid;
    }

    /**
     * Validate Table Uid
     * @var string $tab_uid. Uid for table
     *
     *
     * @return string
     */
    public function validateTabUid ($tab_uid, $reportFlag = true)
    {
        if ( $reportFlag )
        {
            $label = 'The report table with rep_uid:';
        }
        else
        {
            $label = 'The pm table with pmt_uid:';
        }
        $tab_uid = trim ($tab_uid);
        if ( $tab_uid == '' )
        {
            throw (new \Exception ($label . "'' does not exist."));
        }
        $oAdditionalTables = new \AdditionalTables();
        if ( !($oAdditionalTables->exists ($tab_uid)) )
        {
            throw (new \Exception ($label . "'$tab_uid' does not exist."));
        }
        return $tab_uid;
    }

    /**
     * Validate Table Name
     * @var string $rep_tab_name. Name for report table
     *
     *
     * @return string
     */
    public function validateTabName ($rep_tab_name, $reportFlag = false)
    {
        $rep_tab_name = trim ($rep_tab_name);

        $nametype = ($reportFlag == false) ? 'pmt_tab_name' : 'rep_tab_name';
        if ( (strpos ($rep_tab_name, ' ')) || (strlen ($rep_tab_name) < 4) )
        {
            throw (new \Exception ("The property $nametype: '$rep_tab_name' is incorrect."));
        }
        $rep_tab_name = strtoupper ($rep_tab_name);
        if ( substr ($rep_tab_name, 0, 4) != 'PMT_' )
        {
            $rep_tab_name = 'PMT_' . $rep_tab_name;
        }
        return $rep_tab_name;
    }

    /**
     * Validate Report Table Connection
     * @var string $rep_tab_connection. Connection for report table
     * @var string $pro_uid. Uid for process
     *
     *
     * @return string
     */
    public function validateRepConnection ($rep_tab_connection)
    {
        $rep_tab_connection = trim ($rep_tab_connection);
        if ( $rep_tab_connection == '' )
        {
            throw (new \Exception ("The property rep_tab_connection: '$rep_tab_connection' is incorrect."));
        }

        $connections = ["WORKFLOW"];

        if ( !in_array ($rep_tab_connection, $connections) )
        {
            throw (new \Exception ("The property rep_tab_connection: '$rep_tab_connection' is incorrect."));
        }
        return $rep_tab_connection;
    }

    /**
     * Validate Field Type
     * @var string $fld_type. Type for field
     *
     *
     * @return string
     */
    public function validateFldType ($fld_type)
    {
        $fld_type = trim ($fld_type);
        if ( $fld_type == '' )
        {
            throw (new \Exception ("The property fld_type: '$fld_type' is incorrect."));
        }
        switch ($fld_type) {
            case 'INT':
                $fld_type = 'INTEGER';
                break;
            case 'TEXT':
                $fld_type = 'LONGVARCHAR';
                break;
            case 'DATETIME':
                $fld_type = 'TIMESTAMP';
                break;
        }

        return $fld_type;
    }

}
