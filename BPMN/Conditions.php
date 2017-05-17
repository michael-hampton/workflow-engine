<?php
class Conditions
{
    private $objMysql;
    private $step;
    
    public function __construct ($step)
    {
        $this->objMysql = new Mysql2();
        $this->step = $step;
    }
    
    public function getConditions()
    {
         $conditions = $this->objMysql->_select ("workflow.status_mapping", array(), array("id" => $this->step), array());
         $stepCondition = json_decode ($conditions[0]['step_condition'], true);
         
         return $stepCondition;
    }
}

