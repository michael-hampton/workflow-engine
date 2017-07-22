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

    private $dom = null;
    private $schemaFile = '';
    private $tableName;
    private $oldTableName = null;
    private $columns;
    private $primaryKey = array();
    private $baseDir = '';
    private $targetDir = '';
    private $configDir = '';
    private $dataDir = '';
    private $classesDir = '';
    private $className = '';
    private $dataSource = '';
    private $rootNode;
    private $dbConfig;
    private $db;
    private $alterTable = true;
    private $keepData = false;

    public function __construct ($tableName = null)
    {
        if ( isset ($tableName) )
        {
            $this->tableName = $tableName;
            $this->className = $this->toCamelCase ($tableName);
        }
        $this->dbConfig = new StdClass();
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
        echo "<pre>";
        print_r($this->columns);
        die;
    }
}
