<?php

class Cases
{

    use Validator;

    private $objMysql = null;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Get list for Cases
     *
     * @access public
     * @param array $dataList, Data for list
     * @return array
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     */
    public function getList ($dataList = array())
    {
        $this->isArray ($dataList);

        if ( isset ($dataList['userId']) )
        {
            $this->validateUsername ($dataList['userId']);
            $userUid = $dataList["userId"];
        }

        $dir = isset ($dataList["dir"]) ? $dataList["dir"] : "DESC";
        $sort = isset ($dataList["sort"]) ? $dataList["sort"] : "element_id";
        $start = isset ($dataList["start"]) ? $dataList["start"] : "0";
        $limit = isset ($dataList["limit"]) ? $dataList["limit"] : "";
        $filter = isset ($dataList["filter"]) ? $dataList["filter"] : "";
        $process = isset ($dataList["process"]) ? $dataList["process"] : "";
        $category = isset ($dataList["category"]) ? $dataList["category"] : "";
        $status = isset ($dataList["status"]) ? strtoupper ($dataList["status"]) : "";
        $user = isset ($dataList["user"]) ? $dataList["user"] : "";
        $search = isset ($dataList["search"]) ? $dataList["search"] : "";
        $action = isset ($dataList["action"]) ? $dataList["action"] : "todo";
        $paged = isset ($dataList["paged"]) ? $dataList["paged"] : true;

        $dateFrom = (!empty ($dataList["dateFrom"])) ? substr ($dataList["dateFrom"], 0, 10) : "";
        $dateTo = (!empty ($dataList["dateTo"])) ? substr ($dataList["dateTo"], 0, 10) : "";

        $valuesCorrect = array('todo', 'draft', 'paused', 'sent', 'selfservice', 'unassigned', 'search');
        if ( !in_array ($action, $valuesCorrect) )
        {
            throw (new \Exception (\G::LoadTranslation ("ID_INCORRECT_VALUE_ACTION")));
        }
        $start = (int) $start;
        $start = abs ($start);
        if ( $start != 0 )
        {
            $start--;
        }
        $limit = (int) $limit;
        $limit = abs ($limit);

        if ( $limit == 0 )
        {
            $limit = 25;
        }

        if ( !($dir == 'DESC' || $dir == 'ASC') )
        {
            $dir = 'ASC';
        }

        if ( $process != '' )
        {
            $this->proUid ($process);
        }

        if ( $category != '' )
        {
            $this->catUid ($category);
        }

        if ( $action == 'search' || $action == 'to_reassign' )
        {
            $userUid = ($user == "CURRENT_USER") ? $userUid : $user;
            if ( $first )
            {
                $result = array();
                $result['totalCount'] = 0;
                $result['data'] = array();
                return $result;
            }
        }

        $result = $this->getAll (
                $userUid, $start, $limit, $action, $filter, $search, $process, $status, $dateFrom, $dateTo, $dir, (strpos ($sort, ".") !== false) ? $sort : "APP_CACHE_VIEW." . $sort, $category, $paged
        );

        if ( $paged == false )
        {
            $response = $result['data'];
        }
        else
        {
            $response['total'] = $result['totalCount'];
            $response['start'] = $start + 1;
            $response['limit'] = $limit;
            $response['sort'] = strtolower ($sort);
            $response['dir'] = strtolower ($dir);
            $response['cat_uid'] = $category;
            $response['pro_uid'] = $process;
            $response['search'] = $search;

            if ( $action == 'search' )
            {
                $response['app_status'] = strtolower ($status);
                $response['usr_uid'] = $user;
                $response['date_from'] = $dateFrom;
                $response['date_to'] = $dateTo;
            }
            $response['data'] = $result['data'];
        }

        return $response;
    }

    public function getUsersParticipatedInCase ($projectId, $caseId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $workflowObject = $this->objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $projectId));

            $workflowData = json_decode ($workflowObject[0]['workflow_data'], true);
            $auditData = json_decode ($workflowObject[0]['audit_data'], true);

            $arrUsers = array();

            if ( isset ($workflowData['elements']) && !empty ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {
                    if ( $elementId == $caseId )
                    {
                        $previousStep = $this->getPreviousStep ($element['current_step'], $element['workflow_id']);

                        foreach ($auditData['elements'][$elementId]['steps'] as $audit) {
                            $arrUsers[] = $audit['claimed'];
                        }
                    }
                }
            }

            if ( !empty ($arrUsers) )
            {
                return $arrUsers;
            }

            return [];
        } catch (Exception $ex) {
            throw new Exception ($ex);
        }
    }

    public function getAll ($userUid = null, $start = 0, $limit = 25, $action, $filter, $search, $process = null, $status = null, $dateFrom = null, $dateTo = null, $dir = null, $sort = null, $category = null, $paged)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $arrCases = [];

        $arrResults = $this->objMysql->_select ("workflow.workflow_data", array(), array());

        foreach ($arrResults as $arrResult) {
            $workflowData = json_decode ($arrResult['workflow_data'], true);
            $auditData = json_decode ($arrResult['audit_data'], true);

            if ( isset ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {

                    $intSkip = 0;
                    $dateCompleted = '';

                    $currentStep = $element['current_step'];
                    $workflowId = $element['workflow_id'];
                    $requestId = $element['request_id'];
                    $previousStep = $this->getPreviousStep ($currentStep, $workflowId);
                    $currentStatus = $element['status'];

                    $audit = $auditData['elements'][$elementId]['steps'][$previousStep];

                    if ( trim ($audit['dateCompleted']) !== "" )
                    {
                        $dateCompleted = date ('Y-m-d', strtotime (str_replace ('.', '-', $audit['dateCompleted'])));
                    }


                    if ( $userUid !== null && $audit['claimed'] != $userUid )
                    {
                        $intSkip++;
                    }

                    if ( is_numeric ($process) && $process != $workflowId )
                    {
                        $intSkip++;
                    }

                    if ( is_numeric ($category) && $category != $requestId )
                    {
                        $intSkip++;
                    }

                    if ( trim ($status) !== "" && trim (strtolower ($status)) !== trim (strtolower ($currentStatus)) )
                    {
                        $intSkip++;
                    }

                    if ( trim ($dateFrom) !== "" && trim ($dateTo) !== "" )
                    {
                        if ( !$this->dateIsBetween ($dateFrom, $dateTo, $dateCompleted) )
                        {
                            //echo $dateFrom . " " . $dateTo . "  " . $dateCompleted;
                            $intSkip++;
                        }
                    }


                    if ( $intSkip == 0 )
                    {
                        $arrCase = array(
                            "elementId" => $elementId,
                            "projectId" => $arrResult['object_id'],
                            "workflow_id" => $workflowId,
                            "current_user" => $audit['claimed']
                        );
                        $arrCases[] = $arrCase;
                    }
                }
            }
        }

        if ( !empty ($arrCases) )
        {
            if ( $paged === true )
            {
                $arrCases = $this->paginate ($arrCases, $limit, $start);
            }

            foreach ($arrCases['data'] as $key => $arrCase) {

                $objCase = new Elements ($arrCase['projectId'], $arrCase['elementId']);

                if ( isset ($arrCase['workflow_id']) )
                {
                    $objCase->setWorkflow_id ($workflow_id);
                }

                if ( isset ($arrCase['current_user']) )
                {
                    $objCase->setCurrent_user ($arrCase['current_user']);
                }

                if ( is_object ($objCase) )
                {
                    $arrCases['data'][$key] = $objCase;
                }
            }

            return $arrCases;
        }

        return [];
    }

    private function paginate ($array, $intPageLimit, $page = 1)
    {
        $intPageLimit = (int) $intPageLimit;
        $page = (int) $page;
        $totalRows = (int) count ($array);
        $_SESSION["pagination"]["current_page"] = $page;

        $page = $page < 1 ? 1 : $page + 1;

        $start = ($page - 1) * $intPageLimit;

        $_SESSION["pagination"]["total_pages"] = (int) ceil (($totalRows / $intPageLimit));
        $_SESSION["pagination"]["total_counter"] = $totalRows;

        $arrData['totalCount'] = $totalRows;

        $arrData['data'] = array_slice ($array, $start, $intPageLimit);

        return $arrData;
    }

    private function getPreviousStep ($currentStep, $workflowId)
    {
        $result1 = $this->objMysql->_select ("workflow.status_mapping", array("step_from"), array("id" => $currentStep, "workflow_id" => $workflowId));
        $result = $this->objMysql->_select ("workflow.status_mapping", array("id"), array("step_to" => $result1[0]['step_from'], "workflow_id" => $workflowId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result[0]['id'];
        }

        return false;
    }

    /**
     * Get data of a Case
     *
     * @param string $applicationUid Unique id of Case
     * @param string $userUid Unique id of User
     *
     * return array Return an array with data of Case Info
     */
    public function getCaseInfo ($projectId, $caseId, $userUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $workflowObject = $this->objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $projectId));

            $workflowData = json_decode ($workflowObject[0]['workflow_data'], true);
            $auditData = json_decode ($workflowObject[0]['audit_data'], true);

            if ( isset ($workflowData['elements']) && !empty ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {
                    if ( $elementId == $caseId )
                    {
                        $previousStep = $this->getPreviousStep ($element['current_step'], $element['workflow_id']);

                        $audit = $auditData['elements'][$elementId]['steps'][$previousStep];

                        $objElements = new Elements ($projectId, $elementId);
                        $objElements->setWorkflow_id ($element['workflow_id']);
                        $objElements->setCurrent_user ($audit['claimed']);
                        $objElements->setCurrent_step ($element['current_step']);

                        return $objElements;
                    }
                }
            }

            return [];
        } catch (Exception $ex) {
            throw new Exception ($ex);
        }
    }

    /**
     * Get Case Notes
     *
     * @access public
     * @param string $app_uid, Uid for case
     * @return array
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     */
    public function getCaseNotes ($app_uid, $usr_uid, $data_get = array())
    {
        $this->isArray ($data_get);

        if ( is_numeric ($usr_uid) )
        {
            $this->validateUserId ($usr_uid);
        }

        $start = isset ($data_get["start"]) ? $data_get["start"] : "0";
        $limit = isset ($data_get["limit"]) ? $data_get["limit"] : "";
        $sort = isset ($data_get["sort"]) ? $data_get["sort"] : "APP_NOTES.NOTE_DATE";
        $dir = isset ($data_get["dir"]) ? $data_get["dir"] : "DESC";
        $user = isset ($data_get["user"]) ? $data_get["user"] : "";
        $dateFrom = (!empty ($data_get["dateFrom"])) ? substr ($data_get["dateFrom"], 0, 10) : "";
        $dateTo = (!empty ($data_get["dateTo"])) ? substr ($data_get["dateTo"], 0, 10) : "";
        $search = isset ($data_get["search"]) ? $data_get["search"] : "";
        $paged = isset ($data_get["paged"]) ? $data_get["paged"] : true;

        if ( (int) $start == 1 || (int) $start == 0 )
        {
            $start = 0;
        }
        $dir = strtoupper ($dir);

        if ( !($dir == 'DESC' || $dir == 'ASC') )
        {
            $dir = 'DESC';
        }

        if ( $dateFrom != '' )
        {
            $this->isDate ($dateFrom, 'Y-m-d', '$date_from');
        }
        if ( $dateTo != '' )
        {
            $this->isDate ($dateTo, 'Y-m-d', '$date_to');
        }

        $objComments = new Comments();
        $note_data = $appNote->getNotesList ($app_uid, $user, $start, $limit, $sort, $dir, $dateFrom, $dateTo, $search);
        $response = array();
        if ( $paged === true )
        {
            $response['total'] = $note_data['array']['totalCount'];
            $response['start'] = $start;
            $response['limit'] = $limit;
            $response['sort'] = $sort;
            $response['dir'] = $dir;
            $response['usr_uid'] = $user;
            $response['date_to'] = $dateTo;
            $response['date_from'] = $dateFrom;
            $response['search'] = $search;
            $response['data'] = array();
            $con = 0;
            foreach ($note_data['array']['notes'] as $value) {
                $response['data'][$con]['app_uid'] = $value['APP_UID'];
                $response['data'][$con]['usr_uid'] = $value['USR_UID'];
                $response['data'][$con]['note_date'] = $value['NOTE_DATE'];
                $response['data'][$con]['note_content'] = $value['NOTE_CONTENT'];
                $con++;
            }
        }
        else
        {
            $con = 0;
            foreach ($note_data['array']['notes'] as $value) {
                $response[$con]['app_uid'] = $value['APP_UID'];
                $response[$con]['usr_uid'] = $value['USR_UID'];
                $response[$con]['note_date'] = $value['NOTE_DATE'];
                $response[$con]['note_content'] = $value['NOTE_CONTENT'];
                $con++;
            }
        }
        return $response;
    }

    /**
     * Save new case note
     *
     * @access public
     * @param string $app_uid, Uid for case
     * @param array $app_data, Data for case variables
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     */
    public function saveCaseNote ($app_uid, $usr_uid, $note_content, $send_mail = false)
    {
        Validator::isString ($app_uid, '$app_uid');
        Validator::appUid ($app_uid, '$app_uid');
        Validator::isString ($usr_uid, '$usr_uid');
        Validator::usrUid ($usr_uid, '$usr_uid');
        Validator::isString ($note_content, '$note_content');
        if ( strlen ($note_content) > 500 )
        {
            throw (new \Exception (\G::LoadTranslation ("ID_INVALID_MAX_PERMITTED", array($note_content, '500'))));
        }
        Validator::isBoolean ($send_mail, '$send_mail');
        $case = new \Cases();
        $caseLoad = $case->loadCase ($app_uid);
        $pro_uid = $caseLoad['PRO_UID'];
        $tas_uid = \AppDelegation::getCurrentTask ($app_uid);
        $respView = $case->getAllObjectsFrom ($pro_uid, $app_uid, $tas_uid, $usr_uid, 'VIEW');
        $respBlock = $case->getAllObjectsFrom ($pro_uid, $app_uid, $tas_uid, $usr_uid, 'BLOCK');
        if ( $respView['CASES_NOTES'] == 0 && $respBlock['CASES_NOTES'] == 0 )
        {
            throw (new \Exception (\G::LoadTranslation ("ID_CASES_NOTES_NO_PERMISSIONS")));
        }
        $note_content = addslashes ($note_content);
        $appNote = new \AppNotes();
        $appNote->addCaseNote ($app_uid, $usr_uid, $note_content, intval ($send_mail));
    }

    public function startCase ($workflowId)
    {
        $objWorkflow = new Workflow ($workflowId, null);
        $objStep = $objWorkflow->getNextStep ();
        $stepId = $objStep->getStepId();
        
        $objForm = new Form($stepId, $workflowId);
        $arrFields = $objForm->getFields();

        $objFprmBuilder = new FormBuilder ("AddNewForm");
        $html = $objFprmBuilder->buildForm($arrFields, array());
        
        return $html;
    }

}
