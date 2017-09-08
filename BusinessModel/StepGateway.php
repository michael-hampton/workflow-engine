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
namespace BusinessModel;

class StepGateway
{

    private $objMysql;
    private $stepId;

    public function __construct (\Task $objTask)
    {
        $this->objMysql = new \Mysql2();
        $this->stepId = $objTask->getStepId ();
    }

    public function getGateways ()
    {
        
        $result = $this->objMysql->_select ("workflow.gateways", [], ["step_id" => $this->stepId]);
        $keys = array_keys($result);
        
        foreach ($keys as $key) {
            $result[$key]['trigger_type'] = "gateway";
        }

        return $result;
    }

    public function updateStep ($arrTrigger, $arrWorkflowObject, $objMike)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $this->elementId = $objMike->getId ();

        $arrField = $this->objMysql->_select ("workflow.fields", array(), array("field_identifier" => trim ($arrTrigger['field_name'])));
        if ( empty ($arrField) )
        {
            throw new \Exception ("Field cannot be found");
        }
        $strField = $arrField[0]['field_identifier'];
        $strValue = $objMike->arrElement[$strField];
        $conditionalValue = $arrTrigger['conditionValue'];
        $trueField = $arrTrigger['step_to'];
        $falseField = $arrTrigger['else_step'];
        switch ($arrTrigger['condition_type']) {
            case "=":
                if ( trim ($strValue) == trim ($conditionalValue) )
                {
                    if ( isset ($arrWorkflowObject['elements'][$this->elementId]) )
                    {
                        $arrWorkflowObject['elements'][$this->elementId]['current_step'] = $trueField;
                    }
                }
                else
                {
                    if ( isset ($arrWorkflowObject['elements'][$this->elementId]) )
                    {
                        $arrWorkflowObject['elements'][$this->elementId]['current_step'] = $falseField;
                    }
                }
                break;
        }

        return $arrWorkflowObject;
    }

}
