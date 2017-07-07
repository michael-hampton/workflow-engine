<?php

class Flow
{

    private $workflowId;
    private $stepFrom;
    private $stepTo;
    private $isActive;
    private $condition;
    private $firstStep;
    private $orderId;
    private $objMysql;
    private $id;
    private $loc;
    private $stepName;


    /**
     * 
     * @param type $id
     * @param type $workflowId
     */
    public function __construct ($id = null, $workflowId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $id !== null )
        {
            $this->id = $id;
        }

        if ( $workflowId !== null )
        {
            $this->workflowId = $workflowId;
        }
    }

    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

        /**
     * 
     * @return type
     */
    function getWorkflowId ()
    {
        return $this->workflowId;
    }

    /**
     * 
     * @return type
     */
    function getStepFrom ()
    {
        return $this->stepFrom;
    }

    /**
     * 
     * @return type
     */
    function getStepTo ()
    {
        return $this->stepTo;
    }

    /**
     * 
     * @return type
     */
    function getIsActive ()
    {
        return $this->isActive;
    }

    /**
     * 
     * @return type
     */
    function getCondition ()
    {

        if ( !is_array ($this->condition) )
        {
            return json_decode ($this->condition, true);
        }
        return $this->condition;
    }

    function getFirstStep ()
    {
        return $this->firstStep;
    }

    function getOrderId ()
    {
        return $this->orderId;
    }

    /**
     * 
     * @param type $workflowId
     */
    function setWorkflowId ($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     * 
     * @param type $stepFrom
     */
    function setStepFrom ($stepFrom)
    {
        $this->stepFrom = $stepFrom;
    }

    /**
     * 
     * @param type $stepTo
     */
    function setStepTo ($stepTo)
    {
        $this->stepTo = $stepTo;
    }

    /**
     * 
     * @param type $isActive
     */
    function setIsActive ($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * 
     * @param type $condition
     */
    function setCondition ($condition)
    {
        if ( is_array ($condition) )
        {
            $condition = json_encode ($condition);
        }

        $this->condition = $condition;
    }

    /**
     * 
     * @param type $firstStep
     */
    function setFirstStep ($firstStep)
    {
        $this->firstStep = $firstStep;
    }

    /**
     * 
     * @param type $orderId
     */
    function setOrderId ($orderId)
    {
        $this->orderId = $orderId;
    }

    function getLoc ()
    {
        return $this->loc;
    }

    /**
     * 
     * @param type $loc
     */
    function setLoc ($loc)
    {
        $this->loc = $loc;
    }

    public function getStepName ()
    {
        return $this->stepName;
    }

    /**
     * 
     * @param type $stepName
     */
    public function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    /**
     * 
     * @return boolean
     */
    public function save ()
    {
        $this->objMysql->_insert ("workflow.status_mapping", array(
            "workflow_id" => $this->workflowId,
            "step_from" => $this->stepFrom,
            "step_to" => $this->stepTo,
            "step_condition" => $this->condition,
            "first_step" => $this->firstStep,
            "order_id" => $this->orderId,
            "loc" => $this->loc
                )
        );

        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function removeFlow ()
    {
        $this->objMysql->_delete ("workflow.status_mapping", array("step_from" => $this->id, "workflow_id" => $this->workflowId));

        return true;
    }

    public function retrieveByPk ($pk)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", [], ["id" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objFlow = new Flow();
        $objFlow->setFirstStep ($result[0]['first_step']);
        $objFlow->setCondition ($result[0]['step_condition']);
        $objFlow->setIsActive ($result[0]['is_active']);
        $objFlow->setOrderId ($result[0]['order_id']);
        $objFlow->setStepFrom ($result[0]['step_from']);
        $objFlow->setStepTo ($result[0]['step_to']);
        $objFlow->setWorkflowId ($result[0]['workflow_id']);

        return $objFlow;
    }

    /**

     * Verify if doesn't exists the Task

     *

     * @param string $processUid            Unique id of Process

     * @param string $taskUid               Unique id of Task

     * @param string $fieldNameForException Field name for the exception

     *

     * return void Throw exception if doesn't exists the Task

     */
    public function throwExceptionIfNotExistsTask ($taskUid, $processUid = '', $fieldNameForException = '')
    {
        try {

            $sql = "SELECT * FROM workflow.status_mapping WHERE id = ?";
            $arrParameters = array($taskUid);

            if ( $processUid != "" )
            {

                $sql .= " AND workflow_id = ?";
                $arrParameters[] = $processUid;
            }

            $result = $this->objMysql->_query ($sql, $arrParameters);

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                throw new Exception ("TASK DOES NOT EXIST");
            }
        } catch (\Exception $e) {

            throw $e;
        }
    }

}
