<?php

abstract class BaseProcessRoute implements Persistent
{

    private $objMysql;
    private $from;
    private $to;
    private $firstWorkflow;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getFrom ()
    {
        return $this->from;
    }

    public function getTo ()
    {
        return $this->to;
    }

    public function getFirstWorkflow ()
    {
        return $this->firstWorkflow;
    }

    /**
     * 
     * @param type $from
     */
    public function setFrom ($from)
    {
        $this->from = $from;
    }

    /**
     * 
     * @param type $to
     */
    public function setTo ($to)
    {
        $this->to = $to;
    }

    /**
     * 
     * @param type $firstWorkflow
     */
    public function setFirstWorkflow ($firstWorkflow)
    {
        $this->firstWorkflow = $firstWorkflow;
    }

    public function validate ()
    {
        $errorCount = 0;

        if ( trim ($this->from) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->to) === "" )
        {
            $errorCount++;
        }

        if ( trim ($this->firstWorkflow) === "" )
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
        $this->objMysql->_insert (
                "workflow.workflow_mapping", array(
            "workflow_from" => $this->from,
            "workflow_to" => $this->to,
            "first_workflow" => $this->firstWorkflow
                )
        );
    }

    public function loadObject (array $arrData)
    {
        ;
    }

    public function updateMapping ($from, $firstWorkflow = 0)
    {
        $this->objMysql ("workflow.workflow_mapping", array("workflow_to" => $this->id), array("workflow_from" => $from));
    }

}
