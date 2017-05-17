<?php

class Task
{

    private $stepName;
    private $objMysql;
    private $stepId;

    public function __construct ($stepId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }
    }

    function getStepName ()
    {
        return $this->stepName;
    }

    function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    function getStepId ()
    {
        return $this->stepId;
    }

    function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    public function save ()
    {
        $id = $this->objMysql->_insert ("workflow.steps", array("step_name" => $this->stepName));
        return $id;
    }

    public function removeTask ()
    {
        $this->objMysql->_delete ("workflow.steps", array("step_id" => $this->stepId));
    }

    public function getTask ($step)
    {
        $check = $this->objMysql->_select ("workflow.steps", array(), array("step_id" => $step));

        return $check;
    }

}
