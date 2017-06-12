<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseMessageType
 *
 * @author michael.hampton
 */
abstract class BaseMessageType
{

    private $PrjUid;
    private $id;
    private $title;
    private $description;
    private $variables;
    private $objMysql;
    private $blUpdate;
    private $ValidationFailures;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle ($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getValidationFailures ()
    {
        return $this->ValidationFailures;
    }

    /**
     * @param mixed $ValidationFailures
     */
    public function setValidationFailures ($ValidationFailures)
    {
        $this->ValidationFailures = $ValidationFailures;
    }

    /**
     * @return mixed
     */
    public function getBlUpdate ()
    {
        return $this->blUpdate;
    }

    /**
     * @param mixed $blUpdate
     */
    public function setBlUpdate ($blUpdate)
    {
        $this->blUpdate = $blUpdate;
    }

    /**
     * @return mixed
     */
    public function getVariables ()
    {
        return $this->variables;
    }

    /**
     * @param mixed $variables
     */
    public function setVariables ($variables)
    {
        $this->variables = $variables;
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( $this->title === "" )
        {
            $errorCount++;
        }

        if ( $errorCount > 0 )
        {
            return false;
        }

        return true;
    }

    public function save ()
    {
        if ( $this->blUpdate === true )
        {
            $this->objMysql->_update ("workflow.message_type", array("title" => $this->title, "description" => $this->description, "variables" => $this->variables, "workflow_id" => $this->PrjUid), array($this->id));
        }
        else
        {
            $this->objMysql->_insert ("workflow.message_type", array("title" => $this->title, "description" => $this->description, "variables" => $this->variables, "workflow_id" => $this->PrjUid));
        }
    }

    public function delete ()
    {
        $this->objMysql->_delete ("workflow.message_type", ["id" => $this->id]);
    }

    public function getPrjUid ()
    {
        return $this->PrjUid;
    }

    public function setPrjUid ($PrjUid)
    {
        $this->PrjUid = $PrjUid;
    }

    //put your code here
}
