<?php

class Workflow
{

    private $intWorkflowId;
    private $objMike;
    private $intCollectionId;
    private $objMysql;

    public function __construct ($workflowId = null, $objMike = null)
    {
        $this->intWorkflowId = $workflowId;
        $this->objMike = $objMike;
        $this->objMysql = new Mysql2();

        if ( $objMike !== null )
        {
            $this->getWorkflowObject ();
        }
    }

    public function getNextStep ()
    {
        if ( $this->objMike !== null )
        {
            $workflowCollection = $this->getWorkflowObject ();

            return new WorkflowStep ($workflowCollection['current_step']);
        }
        else
        {
            $result = $this->objMysql->_select ("workflow.status_mapping", array("id"), array("workflow_id" => $this->intWorkflowId, "first_step" => 1));

            if ( !empty ($result) )
            {
                $intStartingWorkflowStepId = $result[0]['id'];
            }
            else
            {
                return false;
            }

            return new WorkflowStep ($intStartingWorkflowStepId);
        }
    }

    public function getWorkflowObject ()
    {
        $parentId = $this->objMike->getId ();
        $id = $this->objMike->getId ();


        if ( method_exists ($this->objMike, "getParentId") && $this->objMike->getParentId () != "" )
        {
            $parentId = $this->objMike->getSource_id ();
        }

        $result = $this->objMysql->_select ("workflow.workflow_data", array("workflow_data"), array("object_id" => $parentId));

        if ( !isset ($result[0]) )
        {
            //return false;
        }

        $workflowData = json_decode ($result[0]['workflow_data'], true);

        if ( is_numeric ($id) )
        {
            if ( isset ($workflowData['elements'][$id]) )
            {
                $workflowData = $workflowData['elements'][$id];
            }
        }


        if ( empty ($workflowData) )
        {
            return FALSE;
        }

        $this->intWorkflowId = $workflowData['workflow_id'];
        $this->intCollectionId = $workflowData['request_id'];

        return $workflowData;
    }

    public function getStepsForWorkflow ()
    {
        $arrResult = $this->objMysql->_query ("SELECT s.*, m.step_condition, m.first_step, m.step_from, m.step_to, m.id FROM workflow.status_mapping m
                                                INNER JOIN workflow.steps s ON s.step_id = m.step_from
                                                WHERE m.workflow_id = ?
                                                ORDER BY m.order_id ASC", [0 => $this->intWorkflowId]);

        $arrSteps = array();

        foreach ($arrResult as $result) {
            $arrSteps[$result['id']] = $result;
        }

        return $arrSteps;
    }

    public function getWorkflowId ()
    {
        return $this->intWorkflowId;
    }

    public function getPreviousStatus ()
    {
        $arrResult = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => $this->status, "workflow_id" => $this->workflow));
        if ( !empty ($arrResult) )
        {
            return $arrResult;
        }
        else
        {
            $arrResult2 = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => $this->status));
        }
    }

    public function deleteWorkflow ()
    {
        $arrResult = $this->objMysql->_select ("workflow.status_mapping", array("workflow_id"), array("workflow_id" => $this->intWorkflowId));

        if ( empty ($arrResult) )
        {
            $this->objMysql->_delete ("workflow.status_mapping", array("workflow_id" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflows", array("workflow_id" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflow_mapping", array("workflow.from" => $this->intWorkflowId));
            $this->objMysql->_delete ("workflow.workflow_mapping", array("workflow_to" => $this->intWorkflowId));
        }
    }

    /**
     * Load all Process
     *
     * @param array $arrayFilterData
     * @param int $start
     * @param int $limit
     *
     * return array Return data array with the Process
     *
     * @access public
     */
    public function loadAllProcess ($arrayFilterData = array(), $start = 0, $limit = 25)
    {
        //Copy of processmaker/workflow/engine/methods/processes/processesList.php
        $process = new \Process();

        $totalCount = 0;
        if ( isset ($arrayFilterData["category"]) && $arrayFilterData["category"] !== "<reset>" )
        {
            if ( isset ($arrayFilterData["processName"]) )
            {
                $proData = $process->getAllProcesses ($start, $limit, $arrayFilterData["category"], $arrayFilterData["processName"]);
            }
            else
            {
                $proData = $process->getAllProcesses ($start, $limit, $arrayFilterData["category"]);
            }
        }
        else
        {
            if ( isset ($arrayFilterData["processName"]) )
            {
                $proData = $process->getAllProcesses ($start, $limit, null, $arrayFilterData["processName"]);
            }
            else
            {
                $proData = $process->getAllProcesses ($start, $limit);
                $totalCount = $process->getAllProcessesCount ();
            }
        }
        $arrayData = array(
            "data" => $proData,
            "totalCount" => $totalCount
        );
        return $arrayData;
    }

    public function getAllProcesses ($start, $limit, $category = null, $processName = null, $counters = true, $reviewSubProcess = false, $userLogged = "")
    {

        $aProcesses = Array();
        $categories = Array();
        $arrParameters = array();

        $sql = "SELECT workflow_id, status, request_id, created_date, create_user, usrid, username, firstName, lastName, request_type";

        $sql .= "LEFT JOIN user_management.poms_users u ON u.username = create_user";
        $sql .= " LEFT JOIN request_types r ON r.request_id = w.request_id";

        $SQL .= "where STATUS != 0";

        if ( isset ($category) )
        {
            $SQL .= " AND request_id = ?";
            $arrParameters[] = $category;
        }

        if ( $userLogged != "" )
        {
            $sql .= " AND create_user = ?";
            $arrParameters[] = $userLogged;
        }

        /* if ($this->sort == "PRO_CREATE_DATE") {
          if ($this->dir == "DESC") {
          $sql .= " DESC";
          } else {
          $sql .= " ASC";
          }
          } */

        //execute a query to obtain numbers, how many cases there are by process
        if ( $counters )
        {
            $casesCnt = $this->getCasesCountInAllProcesses ();
        }

        //execute the query
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $processes = Array();
        $uids = array();

        foreach ($results as $row) {
            $processes[] = $row;
            $uids[] = $processes[sizeof ($processes) - 1]['PRO_UID'];
        }

        //process details will have the info about the processes
        $processesDetails = Array();

        foreach ($processes as $process) {

            // if not, then load the record to generate content for current language
            $proData = $this->load ($process['PRO_UID']);
            $proTitle = $proData['PRO_TITLE'];
            $proDescription = $proData['PRO_DESCRIPTION'];

            //filtering by $processName
            if ( isset ($processName) && $processName != '' && stripos ($proTitle, $processName) === false )
            {
                continue;
            }
            if ( $counters )
            {
                $casesCountTotal = 0;
                if ( isset ($casesCnt[$process['PRO_UID']]) )
                {
                    foreach ($casesCnt[$process['PRO_UID']] as $item) {
                        $casesCountTotal += $item;
                    }
                }
            }
            //get user format from configuration
            $userOwner = $process['firstName'] . ' ' . $process['lastName'];

            //get date format from configuration
            if ( $creationDateMask != '' )
            {
                list ($date, $time) = explode (' ', $process['PRO_CREATE_DATE']);
                list ($y, $m, $d) = explode ('-', $date);
                list ($h, $i, $s) = explode (':', $time);
                $process['PRO_CREATE_DATE'] = date ($creationDateMask, mktime ($h, $i, $s, $m, $d, $y));
            }
            $process['PRO_CATEGORY_LABEL'] = trim ($process['PRO_CATEGORY']) != '' ? $process['CATEGORY_NAME'] : '- ' . 'ID_PROCESS_NO_CATEGORY' . ' -';
            $process['PRO_TITLE'] = $proTitle;
            $process['PRO_DESCRIPTION'] = $proDescription;
            $process['PRO_STATUS_LABEL'] = $process['PRO_STATUS'] == 1 ? 'ACTIVE' : 'INACTIVE';
            $process['PRO_CREATE_USER_LABEL'] = $userOwner;

            if ( $counters )
            {
                $process['CASES_COUNT_TO_DO'] = (isset ($casesCnt[$process['PRO_UID']]['TO_DO']) ? $casesCnt[$process['PRO_UID']]['TO_DO'] : 0);
                $process['CASES_COUNT_COMPLETED'] = (isset ($casesCnt[$process['PRO_UID']]['COMPLETED']) ? $casesCnt[$process['PRO_UID']]['COMPLETED'] : 0);
                $process['CASES_COUNT_DRAFT'] = (isset ($casesCnt[$process['PRO_UID']]['DRAFT']) ? $casesCnt[$process['PRO_UID']]['DRAFT'] : 0);
                $process['CASES_COUNT_CANCELLED'] = (isset ($casesCnt[$process['PRO_UID']]['CANCELLED']) ? $casesCnt[$process['PRO_UID']]['CANCELLED'] : 0);
                $process['CASES_COUNT'] = $casesCountTotal;
            }

            unset ($process['PRO_CREATE_USER']);
            $aProcesses[] = $process;
        }

        if ( $limit == '' )
        {
            $limit = count ($aProcesses);
        }
        if ( $this->sort != "PRO_CREATE_DATE" )
        {
            if ( $this->dir == "ASC" )
            {
                usort ($aProcesses, array($this, "ordProcessAsc"));
            }
            else
            {
                usort ($aProcesses, array($this, "ordProcessDesc"));
            }
        }

        return $aProcesses;
    }

    public function getCasesCountInAllProcesses ()
    {
        /* SELECT PRO_UID, APP_STATUS, COUNT( * )
          FROM APPLICATION
          GROUP BY PRO_UID, APP_STATUS */
        require_once 'classes/model/Application.php';
        $memcache = &PMmemcached::getSingleton (SYS_SYS);
        $memkey = 'getCasesCountInAllProcesses';
        if ( ($aProcesses = $memcache->get ($memkey)) === false )
        {
            $oCriteria = new Criteria ('workflow');
            $oCriteria->addSelectColumn (ApplicationPeer::PRO_UID);
            $oCriteria->addSelectColumn (ApplicationPeer::APP_STATUS);
            $oCriteria->addSelectColumn ('COUNT(*) AS CNT');
            $oCriteria->addGroupByColumn (ApplicationPeer::PRO_UID);
            $oCriteria->addGroupByColumn (ApplicationPeer::APP_STATUS);
            $oDataset = ProcessPeer::doSelectRS ($oCriteria, Propel::getDbConnection ('workflow_ro'));
            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
            $aProcesses = Array();
            while ($oDataset->next ()) {
                $row = $oDataset->getRow ();
                $aProcesses[$row['PRO_UID']][$row['APP_STATUS']] = $row['CNT'];
            }
            $memcache->set ($memkey, $aProcesses, PMmemcached::ONE_HOUR);
        }
        return $aProcesses;
    }

    public function getCasesCountForProcess ($pro_uid)
    {
        $oCriteria = new Criteria ('workflow');
        $oCriteria->addSelectColumn ('COUNT(*) AS TOTAL_CASES');
        $oCriteria->add (ApplicationPeer::PRO_UID, $pro_uid);
        $oDataset = ApplicationPeer::doSelectRS ($oCriteria, Propel::getDbConnection ('workflow_ro'));
        $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
        $oDataset->next ();
        $cases = $oDataset->getRow ();
        return (int) $cases['TOTAL_CASES'];
    }

    public function getAllProcessesByCategory ()
    {
        $sql = "SELECT workflow_name, request_id, COUNT(*) AS CNT FROM workflow GROUP BY request_id";

        $aProc = Array();
        while ($oDataSet->next ()) {
            $row = $oDataSet->getRow ();
            $aProc[$row['PRO_CATEGORY']] = $row['CNT'];
        }
        return $aProc;
    }

}
