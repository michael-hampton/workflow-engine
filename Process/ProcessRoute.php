<?php

class ProcessRoute extends BaseProcessRoute
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrUpdate
     * @throws Exception
     */
    public function updateRoute ($arrUpdate)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( !isset ($arrUpdate['to']) || empty ($arrUpdate['to']) )
        {
            throw new Exception ("UPDATE ARRAY IS INCOMPLETE");
        }

        if ( !isset ($arrUpdate['where']) || empty ($arrUpdate['where']) )
        {
            throw new Exception ("UPDATE ARRAY IS INCOMPLETE");
        }

        $this->objMysql->_update ("workflow.workflow_mapping", $arrUpdate['to'], $arrUpdate['where']);
    }

    /**
     * Create Route
     *
     * @param string $processUid
     * @param string $taskUid
     * @param string $nextTaskUid
     * @param string $type
     * @param bool   $delete
     *
     * return string Return UID of new Route
     *
     * @access public
     */
    public function defineRoute ($from, $to, $firstWorkflow)
    {
        $this->setFrom ($from);
        $this->setTo ($to);
        $this->setFirstWorkflow ($firstWorkflow);

        $this->saveMapping ();
    }

}
