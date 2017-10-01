<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pmTable
 *
 * @author michael.hampton
 */
class pmTable
{
    private $tableName;
    private $oldTableName = null;
    private $columns;
    private $className = '';
    private $dataSource = '';
    private $dbConfig;
    private $alterTable = true;
    private $keepData = false;
    private $sPrefix = "RPT_";
    private $objMysql;

    public function __construct ($tableName = null)
    {
        if ( isset ($tableName) )
        {
            $this->tableName = $tableName;
            $this->className = $this->toCamelCase ($tableName);
        }
        $this->dbConfig = new StdClass();
        $this->objMysql = new Mysql2();
    }

    /**
     * Set oldTableName to pmTable
     * 
     * @param string $oldTableName
     */
    public function setOldTableName ($oldTableName)
    {
        $this->oldTableName = $oldTableName;
    }

    /**
     * Set columns to pmTable
     *
     * @param array $columns contains a array of abjects
     * array(StdClass->field_name, field_type, field_size, field_null, field_key, field_autoincrement,...)
     */
    public function setColumns ($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Set a data source
     *
     * @param string $dbsUid DBS_UID to relate the pmTable to phisical table
     */
    public function setDataSource ($dbsUid)
    {
        $this->dataSource = self::resolveDbSource ($dbsUid);
        switch (strtolower ($this->dataSource)) {
            case 'workflow':
                $this->dbConfig->adapter = "test";
                $this->dbConfig->host = "test";
                $this->dbConfig->name = "DB_NAME";
                $this->dbConfig->user = "DB_USER";
                $this->dbConfig->passwd = "DB_PASS";
                $this->dbConfig->port = 3306; //FIXME update this when port for workflow dsn will be available
                break;
        }
    }

    /**
     * Backward compatibility function
     * Resolve a propel data source
     *
     * @param string $dbsUid corresponding to DBS_UID key
     * @return string contains resolved DBS_UID
     */
    public static function resolveDbSource ($dbsUid)
    {
        switch ($dbsUid) {
            case 'workflow':
            case 'wf':
            case '0':
            case '':
            case null:
                $dbsUid = 'workflow';
                break;
            case 'rp':
            case 'report':
                $dbsUid = 'rp';
                break;
        }
        return $dbsUid;
    }

    public function getDataSource ()
    {
        return $this->dataSource;
    }

    /**
     * get Data base config object
     *
     * @return object containing dbConfig var
     */
    public function getDbConfig ()
    {
        return $this->dbConfig;
    }

    public function setAlterTable ($value)
    {
        $this->alterTable = $value;
    }

    public function setKeepData ($value)
    {
        $this->keepData = $value;
    }

    /**
     *
     * @param string $name any string witha name separated by underscore
     * @return string contains a camelcase expresion for $name
     */
    public function toCamelCase ($name)
    {
        $tmp = explode ('_', trim ($name));
        foreach ($tmp as $i => $part) {
            $tmp[$i] = ucFirst (strtolower ($part));
        }
        return implode ('', $tmp);
    }

    /**
     * Build the pmTable with all dependencies
     */
    public function build ()
    {
        $array = json_decode (json_encode ($this->columns), true);

        $this->createTable ($this->tableName, 'report', 'NORMAL', $array);
    }

    /**
     * Function createTable
     * This Function creates the table
     *
     * @access public
     * @param string $sTableName Table name
     * @param string $sConnection Connection name
     * @param string $sType
     * @param array $aFields
     * @param string $bDefaultFields
     * @return void
     */
    public function createTable ($sTableName, $sConnection = 'report', $sType = 'NORMAL', $aFields = array(), $bDefaultFields = true)
    {
        $sTableName = $this->sPrefix . $sTableName;
        //we have to do the propel connection

        $dbAdapter = "mysql";

        try {
            switch ($dbAdapter) {
                case 'mysql':
                    $sQuery = 'CREATE TABLE IF NOT EXISTS `' . strtolower($sTableName) . '` (';
                    if ( $bDefaultFields )
                    {
                        $sQuery .= "`id` INT(11) NOT NULL AUTO_INCREMENT,`PRO_UID` INT(11) NOT NULL, `APP_UID` INT(11) NOT NULL,";

                        if ( $sType == 'GRID' )
                        {
                            $sQuery .= "`ROW` INT NOT NULL,";
                        }
                    }
                    foreach ($aFields as $aField) {
       
                        $aField['column_size'] = isset($aField['column_size']) ? $aField['column_size'] : $aField['field_size'];
                        $aField['column_name'] = isset($aField['column_name']) ? $aField['column_name'] : $aField['field_name'];
                        $aField['column_type'] = isset($aField['column_type']) ? $aField['column_type'] : $aField['field_type'];

                        if ( !in_array ($aField['column_name'], array("PRO_UID", "APP_UID")) )
                        {
                            switch ($aField['column_type']) {
                                case 'number':
                                case 'INTEGER':
                                    $sQuery .= '`' . $aField['column_name'] . '` INT(' . $aField['column_size'] . ') ' . " NOT NULL DEFAULT '0',";
                                    break;
                                case 'char':
                                case "VARCHAR":
                                    $sQuery .= '`' . $aField['column_name'] . '` VARCHAR(' . $aField['column_size'] . ')' . " NOT NULL DEFAULT '',";
                                    break;
                                case 'text':
                                    $sQuery .= '`' . $aField['column_name'] . '` TEXT' . " ,";
                                    break;
                                case 'date':
                                    $sQuery .= '`' . $aField['column_name'] . '` DATE' . " NULL,";
                                    break;
                            }
                        }
                    }
                    if ( $bDefaultFields )
                    {
                        $sQuery .= 'PRIMARY KEY (id' . ($sType == 'GRID' ? ',ROW' : '') . ')) ';
                    }
                    $sQuery .= ' DEFAULT CHARSET=utf8;';

                    break;
                case 'mssql':
                    $sQuery = 'CREATE TABLE [' . $sTableName . '] (';
                    if ( $bDefaultFields )
                    {
                        $sQuery .= "[APP_UID] VARCHAR(32) NOT NULL DEFAULT '', [APP_NUMBER] INT NOT NULL,";
                        if ( $sType == 'GRID' )
                        {
                            $sQuery .= "[ROW] INT NOT NULL,";
                        }
                    }
                    foreach ($aFields as $aField) {
                        switch ($aField['sType']) {
                            case 'number':
                                $sQuery .= '[' . $aField['sFieldName'] . '] ' . $this->aDef['mssql'][$aField['sType']] . " NOT NULL DEFAULT '0',";
                                break;
                            case 'char':
                                $sQuery .= '[' . $aField['sFieldName'] . '] ' . $this->aDef['mssql'][$aField['sType']] . " NOT NULL DEFAULT '',";
                                break;
                            case 'text':
                                $sQuery .= '[' . $aField['sFieldName'] . '] ' . $this->aDef['mssql'][$aField['sType']] . " NOT NULL DEFAULT '',";
                                break;
                            case 'date':
                                $sQuery .= '[' . $aField['sFieldName'] . '] ' . $this->aDef['mssql'][$aField['sType']] . " NULL,";
                                break;
                        }
                    }
                    if ( $bDefaultFields )
                    {
                        $sQuery .= 'PRIMARY KEY (id' . ($sType == 'GRID' ? ',ROW' : '') . ')) ';
                    }
                    else
                    {
                        $sQuery .= ' ';
                    }


                    break;
            }

            $this->objMysql->_query ($sQuery);
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
