<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StepGateway
 *
 * @author michael.hampton
 */
class StepGateway
{
    private $objMysql;
    private $stepId;
    
    public function __construct ($stepId)
    {
        $this->objMysql = new Mysql2();
        $this->stepId = $stepId;
    }

    public function getGateways()
    {
        $result = $this->objMysql->_select("workflow.gateways", [], ["step_id" => $this->stepId]);
        
        foreach ($result as $key => $res) {
            $result[$key]['trigger_type'] = "gateway";
        }
        
        return $result;
    }
}
