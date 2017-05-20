<?php

class BaseComments
{

    private $id;
    private $object = array();
    private $objFields = array(
        "source_id" => array("required" => "true", "type" => "int"),
        "comment" => array("required" => "true", "type" => "string"),
        "datetime" => array("required" => "true", "type" => "date"),
        "username" => array("required" => "true", "type" => "string"),
        "comment_type" => array("required" => "true", "type" => "int"),
    );
    private $table = "task_manager.comments";
    private $AppUid;
    private $UserUid;
    private $NoteDate;
    private $NoteContent;
    private $NoteType;
    private $Recipients;
    private $objMysql;

    public function getAppUid ()
    {
        return $this->AppUid;
    }

    public function getUserUid ()
    {
        return $this->UserUid;
    }

    public function getNoteDate ()
    {
        return $this->NoteDate;
    }

    public function getNoteContent ()
    {
        return $this->NoteContent;
    }

    public function getNoteType ()
    {
        return $this->NoteType;
    }

    public function getRecipients ()
    {
        return $this->Recipients;
    }

    public function setAppUid ($AppUid)
    {
        $this->AppUid = $AppUid;
    }

    public function setUserUid ($UserUid)
    {
        $this->UserUid = $UserUid;
    }

    public function setNoteDate ($NoteDate)
    {
        $this->NoteDate = $NoteDate;
    }

    public function setNoteContent ($NoteContent)
    {
        $this->NoteContent = $NoteContent;
    }

    public function setNoteType ($NoteType)
    {
        $this->NoteType = $NoteType;
    }

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function setRecipients ($Recipients)
    {
        $this->Recipients = $Recipients;
    }

    public function validate ()
    {
        
    }

    public function save ()
    {
        $this->objMysql->_insert ($this->table, array(
            "source_id" => $this->AppUid,
            "comment" => $this->NoteContent,
            "datetime" => $this->NoteDate,
            "username" => $this->UserUid,
            "comment_type" => $this->NoteType,
            "recipients" => $this->Recipients
                )
        );
    }

    public function loadObject ()
    {
        
    }

}
