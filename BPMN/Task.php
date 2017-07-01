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

    public function getStepName ()
    {
        return $this->stepName;
    }

    public function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function setStepId ($stepId)
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
