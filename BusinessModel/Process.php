<?php

namespace BusinessModel;

class Process
{

    private $objMysql;

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the title of a Process
     *
     * @param string $processTitle      Title
     * return bool Return true if exists the title of a Process, false otherwise
     */
    public function existsTitle ($processTitle)
    {
        try {
            $result = $this->objMysql->_select ("workflow.workflows", array(), array("workflow_name" => $processTitle));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $processId
     * @return boolean
     */
    public function processExists ($processId)
    {
        $result = $this->objMysql->_select ("workflow.workflows", array(), array("workflow_id" => $processId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Verify if doesn't exists the Process in table PROCESS
     *
     * @param string $processUid            Unique id of Process
     * return void Throw exception if doesn't exists the Process in table PROCESS
     */
    public function throwExceptionIfNotExistsProcess ($processUid)
    {
        try {

            if ( !$this->processExists ($processUid) )
            {
                throw new \Exception ("ID_PROJECT_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a Process
     *
     * @param string $processTitle          Title
     * return void Throw exception if exists the title of a Process
     */
    public function throwExceptionIfExistsTitle ($processTitle)
    {
        try {
            if ( $this->existsTitle ($processTitle) )
            {
                throw new \Exception ("ID_PROJECT_TITLE_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * @param type $userId
     * @throws Exception
     */
    public function throwExceptionIfNotExistsUser ($userId)
    {
        $result = $this->objMysql->_select ("user_management.poms_users", array(), array("usrid" => $userId));

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            throw new \Exception ("CREATE_USER_DOES_NOT_EXIST");
        }
    }

    /**
     * Verify if doesn't exists the Process Category in table PROCESS_CATEGORY
     *
     * @param string $processCategoryUid    Unique id of Process Category
     *
     * return void Throw exception if doesn't exists the Process Category in table PROCESS_CATEGORY
     */
    public function throwExceptionIfNotExistsProcessCategory ($processCategoryUid)
    {
        try {
            $result = $this->objMysql->_select ("workflow.request_types", array(), array("request_id" => $processCategoryUid));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                
            }
            else
            {
                throw new \Exception ("ID_PROJECT_CATEGORY_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create/Update Process
     *
     * @param string $option
     * @param array  $arrayDefineProcessData
     *
     * return array  Return data array with new UID for each element
     *
     * @access public
     */
    public function defineProcess ($option, $arrayDefineProcessData)
    {
        if ( !isset ($arrayDefineProcessData["process"]) || count ($arrayDefineProcessData["process"]) == 0 )
        {
            throw (new \Exception ("Process data do not exist"));
        }

        //Process
        $arrayProcessData = $arrayDefineProcessData["process"];

        switch ($option) {
            case "CREATE":
                if ( !isset ($arrayProcessData["USR_UID"]) || trim ($arrayProcessData["USR_UID"]) == "" )
                {
                    throw (new \Exception ("User data do not exist"));
                }
                if ( !isset ($arrayProcessData["PRO_TITLE"]) || trim ($arrayProcessData["PRO_TITLE"]) == "" )
                {
                    throw (new \Exception ("Process title data do not exist"));
                }
                if ( !isset ($arrayProcessData["PRO_DESCRIPTION"]) )
                {
                    throw (new \Exception ("Process description data do not exist"));
                }
                if ( !isset ($arrayProcessData["PRO_CATEGORY"]) )
                {
                    throw (new \Exception ("Process category data do not exist"));
                }

                if ( isset ($arrayProcessData["PRO_TITLE"]) && $this->existsTitle ($arrayProcessData["PRO_TITLE"]) )
                {
                    throw (new \Exception ("ID_PROCESSTITLE_ALREADY_EXISTS"));
                }

                $arrayProcessData['PRO_DATE_CREATED'] = date ("Y-m-d H:i:s");
                break;
            case "UPDATE":
                //Verify data

                $this->throwExceptionIfNotExistsProcess ($arrayProcessData['PRO_UID']);
                break;
        }

        if ( isset ($arrayProcessData["PRO_TITLE"]) )
        {
            $arrayProcessData["PRO_TITLE"] = trim ($arrayProcessData["PRO_TITLE"]);
        }
        if ( isset ($arrayProcessData["PRO_DESCRIPTION"]) )
        {
            $arrayProcessData["PRO_DESCRIPTION"] = trim ($arrayProcessData["PRO_DESCRIPTION"]);
        }

        if ( isset ($arrayProcessData["PRO_CATEGORY"]) && $arrayProcessData["PRO_CATEGORY"] . "" != "" )
        {
            $this->throwExceptionIfNotExistsProcessCategory ($arrayProcessData["PRO_CATEGORY"]);
        }

        $trigger = new \BusinessModel\StepTrigger();

        /**
         * Try catch block is added to escape the exception and continue editing
         * the properties of the process, otherwise there is no way to edit
         * the properties that the exception is thrown: trigger nonexistent.
         * The same goes for the similar blocks.
         */
        if ( isset ($arrayProcessData["PRO_TRI_DELETED"]) && $arrayProcessData["PRO_TRI_DELETED"] . "" != "" )
        {
            try {
                $trigger->throwExceptionIfNotExistsTrigger ($arrayProcessData["PRO_TRI_DELETED"], $arrayProcessData['PRO_UID']);
            } catch (\Exception $e) {
                
            }
        }
        if ( isset ($arrayProcessData["PRO_TRI_CANCELED"]) && $arrayProcessData["PRO_TRI_CANCELED"] . "" != "" )
        {
            try {
                $trigger->throwExceptionIfNotExistsTrigger ($arrayProcessData["PRO_TRI_CANCELED"], $arrayProcessData['PRO_UID']);
            } catch (\Exception $e) {
                
            }
        }
        if ( isset ($arrayProcessData["PRO_TRI_PAUSED"]) && $arrayProcessData["PRO_TRI_PAUSED"] . "" != "" )
        {
            try {
                $trigger->throwExceptionIfNotExistsTrigger ($arrayProcessData["PRO_TRI_PAUSED"], $arrayProcessData['PRO_UID']);
            } catch (\Exception $e) {
                
            }
        }
        if ( isset ($arrayProcessData["PRO_TRI_UNPAUSED"]) && $arrayProcessData["PRO_TRI_UNPAUSED"] . "" != "" )
        {
            try {
                $trigger->throwExceptionIfNotExistsTrigger ($arrayProcessData["PRO_TRI_UNPAUSED"], $arrayProcessData['PRO_UID']);
            } catch (\Exception $e) {
                
            }
        }
        if ( isset ($arrayProcessData["PRO_TRI_REASSIGNED"]) && $arrayProcessData["PRO_TRI_REASSIGNED"] . "" != "" )
        {
            try {
                $trigger->throwExceptionIfNotExistsTrigger ($arrayProcessData["PRO_TRI_REASSIGNED"], $arrayProcessData['PRO_UID']);
            } catch (\Exception $e) {
                
            }
        }
        if ( isset ($arrayProcessData["PRO_PARENT"]) )
        {
            $this->throwExceptionIfNotExistsProcess ($arrayProcessData["parent_id"]);
        }

        if ( isset ($arrayProcessData["PRO_CREATE_USER"]) && $arrayProcessData["PRO_CREATE_USER"] . "" != "" )
        {
            $this->throwExceptionIfNotExistsUser ($arrayProcessData["PRO_CREATE_USER"]);
        }

        if ( isset ($arrayProcessData['PRO_SUBPROCESS']) && trim ($arrayProcessData['PRO_SUBPROCESS']) !== "" )
        {
            $arrayProcessData['PRO_SUBPROCESS'] = (int) $arrayProcessData['PRO_SUBPROCESS'];
        }
        
        $process = new \Workflow();

        switch ($option) {
            case "CREATE":
                $processUid = $process->create ($arrayProcessData);

                break;
            case "UPDATE":
                $processUid = $arrayProcessData["PRO_UID"];
                $process->setId ($processUid);
                $result = $process->update ($arrayProcessData);
                break;
        }

        /*         * ***************************** Routes ********************************************************************************* */

        $id = $processUid;

        if ( isset ($arrayDefineProcessData['routes']) )
        {
            $objProcessRoute = new \ProcessRoute();

            if ( $arrayDefineProcessData['routes']['position'] == "last" )
            {
                $result = $this->objMysql->_query ("SELECT m.workflow_from FROM workflow.workflow_mapping m
                                                        INNER JOIN workflow.workflows w ON w.workflow_id = m.workflow_from
                                                        WHERE m.workflow_to = 0
                                                        AND w.request_id = ?", [0 => $arrayProcessData["PRO_CATEGORY"]]);

                $from = $result[0]['workflow_from'];

                $arrRoutes = array('to' => array("workflow_to" => $id), "where" => array("workflow_from" => $from));
                $objProcessRoute->updateRoute ($arrRoutes);

                $objProcessRoute->defineRoute ($id, 0, 0);
            }
            elseif ( $arrayDefineProcessData['routes']['position'] == "first" )
            {
                // Remove first workflow flag from previous
                $query2 = $this->objMysql->_query ("SELECT DISTINCT
                                     		m.*
                                                FROM
                                                    workflow.workflow_mapping m
                                                INNER JOIN workflow.workflows w ON w.workflow_id = m.workflow_from
                                                OR w.workflow_id = m.workflow_to
                                                WHERE
                                                    w.request_id = ?
                                              AND m.first_workflow = 1", [0 => $arrayProcessData["PRO_CATEGORY"]]);

                if ( !empty ($query2) )
                {

                    $arrRoutes = array('to' => array("first_workflow" => 0), "where" => array("id" => $query2[0]['id']));
                    $objProcessRoute->updateRoute ($arrRoutes);

                    $objProcessRoute->defineRoute ($id, $query2[0]['workflow_from'], 1);
                }
                else
                {

                    $objProcessRoute->defineRoute ($id, 0, 1);
                }
            }
            else
            {
                $arrTo = $this->objMysql->_select ("workflow.workflow_mapping", array("workflow_to"), array("workflow_from" => $arrayDefineProcessData['routes']['position']));
                $to = $arrTo[0]['workflow_to'];

                $arrMapping[$arrayDefineProcessData['routes']['position']] = $id;
                $arrMapping[$id] = $to;

                $arrLast = $this->objMysql->_select ("workflow.workflow_mapping", array("workflow_to"), array("workflow_from" => $to));

                if ( !empty ($arrLast) )
                {
                    $last = $arrLast[0]['workflow_to'];
                    $arrMapping[$to] = $last;
                }

                if ( isset ($arrMapping) && !empty ($arrMapping) )
                {
                    foreach ($arrMapping as $key => $value) {
                        if ( $key != $id )
                        {

                            $arrRoutes = array('to' => array("workflow_to" => $value), "where" => array("workflow_from" => $key));
                            $objProcessRoute->updateRoute ($arrRoutes);
                        }
                        else
                        {

                            $objProcessRoute->defineRoute ($key, $value, 0);
                        }
                    }
                }
            }
        }

        return $arrayDefineProcessData;
    }

    /**
     * Create Process
     *
     * @param string $userUid
     * @param array  $arrayDefineProcessData
     *
     * return array  Return data array with new UID for each element
     *
     * @access public
     */
    public function createProcess ($userUid, $arrayDefineProcessData)
    {
        $arrayDefineProcessData["process"]["USR_UID"] = $userUid;

        return $this->defineProcess ("CREATE", $arrayDefineProcessData);
    }

    /**
     * Update Process
     *
     * @param string $processUid
     * @param string $userUid
     * @param array  $arrayDefineProcessData
     *
     * return array
     *
     * @access public
     */
    public function updateProcess (\Workflow $objWorkflow, \Users $objUser, $arrayDefineProcessData)
    {

        $arrayDefineProcessData["process"]["PRO_UID"] = $objWorkflow->getWorkflowId ();
        $arrayDefineProcessData["process"]["USR_UID"] = $objUser->getUserId ();

        return $this->defineProcess ("UPDATE", $arrayDefineProcessData);
    }

}
