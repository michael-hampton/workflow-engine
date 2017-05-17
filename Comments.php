<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Mysql.php';

class Comments
{

    public $id;
    public $object = array();
    public $objFields = array(
        "source_id" => array("required" => "true", "type" => "int"),
        "comment" => array("required" => "true", "type" => "string"),
        "datetime" => array("required" => "true", "type" => "date"),
        "username" => array("required" => "true", "type" => "string"),
        "comment_type" => array("required" => "true", "type" => "int"),
    );
    public $table = "task_manager.comments";

    public function __construct ()
    {
        
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function loadObject ($arrData)
    {
        foreach ($arrData as $fieldName => $fieldValue) {
            if ( isset ($this->objFields[$fieldName]) && !empty ($fieldValue) )
            {
                $this->object[$fieldName] = $fieldValue;
            }
        }
    }

    public function save ()
    {
        $objMysql = new Mysql2();
        $id = $objMysql->_insert ($this->table, $this->object, false);
        return $id;
    }

    public function getAttachment ()
    {
        $objMysql = new Mysql2();
        return $objMysql->_select ($this->table, array(), array("id" => $this->id));
    }

    public function getAllComments ($sourceId)
    {
        $objMysql = new Mysql2();
        return $objMysql->_select ($this->table, array(), array("source_id" => $sourceId));
    }

}
