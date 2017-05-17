<?php

class BPMNWorkflow extends BPMN
{

    private $objMysql;
    private $workflow;
    private $collectionId;
    private $eventTypes = array(
        "End" => array(
            "category" => "event",
            "item" => "end",
            "eventType" => 1,
            "eventDimension" => 8,
            "text" => "End"
        ),
        "reject" => array(
            "category" => "event",
            "item" => "Terminate",
            "eventType" => 13,
            "eventDimension" => 8,
            "text" => "Terminate",
            "conditionType" => "reject"
        ),
        "hold" => array(
            "category" => "event",
            "eventDimension" => 4,
            "item" => "Timer",
            "text" => "Hold",
            "eventType" => 3,
            "conditionType" => "hold"
        ),
        "sendNotification" => array(
            "category" => "event",
            "eventDimension" => 4,
            "item" => "Message",
            "text" => "Message",
            "eventType" => 2,
            "conditionType" => "sendNotification"
        ),
        "doAllocation" => array(
            "item" => "User task",
            "text" => "Message",
            "taskType" => 2,
            "conditionType" => "assignUser"
        ),
        "autoAssign" => array(
            "item" => "service task",
            "text" => "Auto Assign",
            "taskType" => 6,
            "conditionType" => "autoAssign"
        ),
        "gateway" => array(
            "item" => "",
            "category" => "gateway",
            "gatewayType" => 1 // 1 = parallel, 4 = exclusive
        )
    );

    /**
     * 
     * @param type $collectionId
     * @param type $workflow
     */
    public function __construct ($collectionId = null, $workflow = null)
    {
        $this->objMysql = new Mysql2();
        $this->workflow = $workflow;
        $this->collectionId = $collectionId;
    }

    /**
     * 
     * @return type
     */
    public function getProcess ()
    {
        $arrGateways = [];

        $objBPMN = new BPMN();
        $objTasks = new Task();
        $arrStepMapping = $objBPMN->getAllTasks ($this->workflow);

        $intNoOfSteps = count ($arrStepMapping);

        $arrData = array(
            "class" => "go.GraphLinksModel",
            "modelData" => array(
                "position" => "-5 -5"
            ),
            "nodeDataArray" => array()
        );

        $linkDataArray = array();

        $dimension = 50;

        $max = $this->objMysql->_query ("SELECT MAX(step_id) + 1 AS `MAX` FROM workflow.steps");
        $lastKey = $max[0]['MAX'] + $intNoOfSteps;

        foreach ($arrStepMapping as $key => $arrStep) {
            $item = "generic task";
            $taskType = 0;

            $conditions = $arrStep->getCondition ();

            $loc = !empty ($arrStep->getLoc ()) ? $arrStep->getLoc () : $dimension . " 100";
            $part1 = explode (" ", $loc)[0];
            $unset = false;

            if ( $arrStep->getFirstStep () == 1 )
            {
                $arrData['nodeDataArray'][$key] = array(
                    "category" => "event",
                    "item" => "start",
                    "key" => $arrStep->getStepFrom (),
                    "loc" => $loc,
                    "text" => "Start",
                    "eventType" => 1,
                    "eventDimension" => 1,
                    "taskType" => 6
                );

                $linkDataArray[$key]['from'] = $arrStep->getStepFrom ();
                $linkDataArray[$key]['to'] = $arrStep->getStepTo ();
            }
            else
            {
                if ( !empty ($conditions) )
                {
                    foreach ($conditions as $conditionKey => $condition) {

                        $arrTasks = array("doAllocation", "autoAssign");

                        if ( $condition == "Yes" )
                        {
                            if ( array_key_exists ($conditionKey, $this->eventTypes) )
                            {
                                if ( in_array ($conditionKey, $arrTasks) )
                                {
                                    $item = $this->eventTypes[$conditionKey]['item'];
                                    $taskType = $this->eventTypes[$conditionKey]['taskType'];
                                }
                                else
                                {
                                    if ( $conditionKey == "gateway" )
                                    {
                                        $unset = true;
                                    }

                                    if ( $conditionKey != "gateway" || !in_array ($arrStep->getStepFrom (), $arrGateways) )
                                    {
                                        $id = $conditionKey == "gateway" ? $arrStep->getStepFrom () : $lastKey + 1;
                                        $linkDataArray[$id]['from'] = $arrStep->getStepFrom ();
                                        $linkDataArray[$id]['to'] = $id;

                                        $arrData['nodeDataArray'][$id] = array(
                                            "category" => $this->eventTypes[$conditionKey]['category'],
                                            "eventDimension" => $this->eventTypes[$conditionKey]['eventDimension'],
                                            "item" => $this->eventTypes[$conditionKey]['item'],
                                            "key" => $id,
                                            "loc" => $part1 . " 300",
                                            "text" => $this->eventTypes[$conditionKey]['text']);

                                        if ( $conditionKey == "gateway" )
                                        {
                                            $arrData['nodeDataArray'][$id]['gatewayType'] = $this->eventTypes[$conditionKey]['gatewayType'];
                                        }
                                        else
                                        {
                                            $arrData['nodeDataArray'][$id]['eventType'] = $this->eventTypes[$conditionKey]['eventType'];
                                        }

                                        $lastKey++;

                                        if ( $conditionKey == "gateway" )
                                        {
                                            $arrGateways[] = $arrStep->getStepFrom ();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ( $unset !== true )
                {
                    $arrData['nodeDataArray'][$key] = array(
                        "category" => "activity",
                        "item" => $item,
                        "key" => $arrStep->getStepFrom (),
                        "loc" => $loc,
                        "text" => $arrStep->getStepName (),
                        "taskType" => $taskType
                    );
                }

                if ( $arrStep->getStepTo () == 0 )
                {
                    $id = $lastKey + 1;

                    $linkDataArray[$key]['from'] = $arrStep->getStepFrom ();
                    $linkDataArray[$key]['to'] = $id;

                    $arrData['nodeDataArray'][$id] = array(
                        "category" => $this->eventTypes['End']['category'],
                        "item" => $this->eventTypes['End']['item'],
                        "key" => $id,
                        "loc" => "730 128",
                        "text" => $this->eventTypes['End']['text'],
                        "eventType" => $this->eventTypes['End']['eventType'],
                        "eventDimension" => $this->eventTypes['End']['eventDimension']
                    );

                    $lastKey++;
                }
                else
                {
                    //if ( $unset !== true )
                    //{
                    $linkDataArray[$key]['from'] = $arrStep->getStepFrom ();
                    $linkDataArray[$key]['to'] = $arrStep->getStepTo ();
                    //}
                }
            }

            $dimension += 160;
        }

        $arrData['linkDataArray'] = $linkDataArray;

        ksort ($arrData['nodeDataArray']);
        ksort ($arrData['linkDataArray']);

        $arrData = $this->fix_keys ($arrData);

        return $arrData;
    }

    /**
     * 
     * @param type $arrData
     * @return boolean
     */
    public function buildProcess ($arrData)
    {
        $arrWorkflowData = json_decode ($arrData['data'], true);

        if ( empty ($arrWorkflowData) )
        {
            return false;
        }

        if ( !isset ($arrWorkflowData['nodeDataArray']) )
        {
            return false;
        }

        $arrSteps = array();

        $end = null;
        $arrEvents = array();
        $arrEventTypes = array();
        $arrStepFields = array();
        $arrNewMappings = [];
        $objBPMN = new BPMN();

        foreach ($arrWorkflowData['nodeDataArray'] as $key => $steps) {
            if ( $steps['category'] == "event" && $steps['item'] != "start" && $steps['item'] != "End" && $steps['item'] != "end" )
            {
                $step = $steps['key'];

                foreach ($arrWorkflowData['linkDataArray'] as $link) {
                    if ( $link['to'] == $step )
                    {
                        $arrEventTypes[$link['from']] = $steps['item'];
                        $arrEvents[] = $link['from'];
                    }
                }
            }
        }

        foreach ($arrWorkflowData['nodeDataArray'] as $key => $steps) {

            $objTask = new Task ($steps['key']);
            $objFlow = new Flow ($steps['key'], $this->workflow);

            if ( $steps['item'] == "End" || $steps['item'] == "end" )
            {
                $end = $steps['key'];
            }
            else
            {
                $check = $objTask->getTask ($steps['key']);
                $check2 = $objBPMN->getFlow ($steps['key'], $this->workflow);

                $arrStepFields[$steps['key']] = $this->objMysql->_select ("workflow.step_fields", array(), array("step_id" => $steps['key']));

                if ( !empty ($check) && $steps['category'] != "event" && $steps['category'] != "gateway" )
                {
//                     echo "DELETING STEP " . $steps['text'] . " ";
//
//                    $objFlow->removeFlow ();
//
//                    $objTask->removeTask ($steps['key']);
                }

                if ( $steps['category'] != "event" || $steps['item'] == "start" )
                {

                    echo "DELETING STEP " . $steps['text'] . " ";

                    $objFlow->removeFlow ();

                    $objTask->removeTask ($steps['key']);

                    $objTask->setStepName ($steps['text']);
                    $id = $objTask->save ();

                    $arrStepFields[$steps['key']]['step_id'] = $id;

                    echo "CREATED STEP " . $steps['text'] . " ";

                    $arrNewMappings[$steps['key']] = $id;

                    if ( !empty ($check2) && $check2->getTrigger () != "" )
                    {

                        $arrSteps[$steps['key']]['step_trigger'] = $check2->getTrigger ();
                    }
                    else
                    {
                        $arrSteps[$steps['key']]['step_trigger'] = array();
                    }
                }

                if ( $steps['category'] != "event" || $steps['item'] == "start" )
                {
                    $arrSteps[$steps['key']]['id'] = $id;
                    $arrSteps[$steps['key']]['first_step'] = $steps['item'] == "start" ? 1 : 0;
                    $arrSteps[$steps['key']]['loc'] = $steps['loc'];
                    $arrSteps[$steps['key']]['conditions']['gateway'] = $steps['category'] == "gateway" ? 1 : 0;

                    foreach ($this->eventTypes as $eventType) {

                        if ( !empty ($eventType['item']) && $eventType['item'] == $steps['item'] )
                        {

                            if ( isset ($eventType['conditionType']) )
                            {
                                $arrSteps[$steps['key']]['conditions'][$eventType['conditionType']] = 1;
                            }
                        }
                        else
                        {
                            if ( isset ($eventType['conditionType']) )
                            {
                                $arrSteps[$steps['key']]['conditions'][$eventType['conditionType']] = 0;
                            }
                        }
                    }
                }

                if ( in_array ($steps['key'], $arrEvents) )
                {
                    switch ($arrEventTypes[$steps['key']]) {
                        case "Terminate":
                            $arrSteps[$steps['key']]['conditions']['reject'] = 1;
                            break;

                        case "Timer":
                            $arrSteps[$steps['key']]['conditions']['hold'] = 1;
                            break;
                        case "Message":
                            $arrSteps[$steps['key']]['conditions']['sendNotification'] = 1;
                            break;
                    }
                }
            }
        }

        $this->objMysql->_delete ("workflow.status_mapping", array("workflow_id" => $this->workflow));

        foreach ($arrWorkflowData['linkDataArray'] as $key => $mapping) {

            $from = $arrSteps[$mapping['from']]['id'];

            if ( $mapping['to'] == $end )
            {
                $to = 0;
            }
            else
            {
                $to = $arrSteps[$mapping['to']]['id'];
            }

            $firstStep = $arrSteps[$mapping['from']]['first_step'];
            $arrConditions = array();

            foreach ($arrSteps[$mapping['from']]['conditions'] as $conditionKey => $condition) {

                if ( $condition == 1 )
                {
                    switch ($conditionKey) {
                        case "assignUser":
                            $arrConditions['doAllocation'] = "Yes";
                            break;

                        case "autoAssign":
                            $arrConditions['autoAssign'] = "Yes";
                            break;
                        case "reject":
                            $arrConditions['reject'] = "Yes";
                            break;
                        case "hold":
                            $arrConditions['hold'] = "Yes";
                            break;
                        case "sendNotification":
                            $arrConditions['sendNotification'] = "Yes";
                            break;

                        case "gateway":
                            $arrConditions['gateway'] = "Yes";
                            break;
                    }
                }
            }

            if ( $firstStep == 1 )
            {
                $arrConditions['autoAssign'] = "Yes";
            }

            if ( empty ($arrSteps[$mapping['from']]['step_trigger']) || !isset ($arrSteps[$mapping['from']]['step_trigger']) )
            {
                $arrSteps[$mapping['from']]['step_trigger'] = array();
            }

            if ( $from != "" && is_numeric ($from) && is_numeric ($to) )
            {
                $objBPMN = new BPMN();

                if ( $firstStep == 1 )
                {
                    $objBPMN->setStart ($from, $to, $this->workflow, $key, json_encode ($arrConditions), $arrSteps[$mapping['from']]['loc']);
                }
                elseif ( $to == 0 )
                {
                    $objBPMN->setEnd ($from, $this->workflow, $key, json_encode ($arrConditions), $arrSteps[$mapping['from']]['loc']);
                }
                else
                {
                    $objBPMN->saveFlow ($from, $to, $this->workflow, $key, json_encode ($arrConditions), $arrSteps[$mapping['from']]['loc']);
                }

                echo "CREATED MAPPING " . $from . " ";

                $objTrigger = new Trigger ($from);

                if ( !empty ($arrSteps[$mapping['from']]['step_trigger']) )
                {
                    $objTrigger->setTrigger ($arrSteps[$mapping['from']]['step_trigger']);
                }
            }
        }

        if ( isset ($arrStepFields) && !empty ($arrStepFields) )
        {

            foreach ($arrStepFields as $key => $arrSteps) {

                if ( !empty ($arrSteps) )
                {
                    $this->objMysql->_delete ("workflow.step_fields", array("step_id" => $key));

                    $stepId = $arrSteps['step_id'];

                    foreach ($arrSteps as $arrStepField) {
                        if ( isset ($arrStepField['field_id']) && isset ($arrStepField['order_id']) )
                        {

                            $this->objMysql->_insert ("workflow.step_fields", array("step_id" => $stepId, "field_id" => $arrStepField['field_id'], "is_disabled" => $arrStepField['is_disabled'], "order_id" => $arrStepField['order_id']));
                        }
                    }
                }
            }
        }
    }

    /**
     * 
     * @param type $array
     * @return type
     */
    function fix_keys ($array)
    {
        $numberCheck = false;
        foreach ($array as $k => $val) {
            if ( is_array ($val) )
                $array[$k] = $this->fix_keys ($val); //recurse
            if ( is_numeric ($k) )
                $numberCheck = true;
        }
        if ( $numberCheck === true )
        {
            return array_values ($array);
        }
        else
        {
            return $array;
        }
    }

}
