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
            $userUid = ($user == "CURRENT_USER") || $user == '' ? $userUid : $user;

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

    public function getUsersParticipatedInCase ($projectId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $workflowObject = $this->objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $projectId));

            if ( !isset ($workflowObject[0]) || empty ($workflowObject[0]) )
            {
                return false;
            }

            $workflowData = json_decode ($workflowObject[0]['workflow_data'], true);
            $auditData = json_decode ($workflowObject[0]['audit_data'], true);

            $arrUsers = array();

            if ( isset ($workflowData['elements']) && !empty ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {
                    //if ( $elementId == $caseId )
                    //{
                    $previousStep = $this->getPreviousStep ($element['current_step'], $element['workflow_id']);

                    foreach ($auditData['elements'][$elementId]['steps'] as $audit) {
                        $arrUsers[] = $audit['claimed'];
                    }
                    //}
                }
            }

            if ( !empty ($arrUsers) )
            {
                return array_unique ($arrUsers);
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
        $arrUsed = array();

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

                    if ( count ($auditData['elements'][$elementId]['steps']) == 1 )
                    {
                        reset ($auditData['elements'][$elementId]['steps']);
                        $first_key = key ($auditData['elements'][$elementId]['steps']);

                        $audit = $auditData['elements'][$elementId]['steps'][$first_key];
                    }
                    else
                    {
                        $audit = $auditData['elements'][$elementId]['steps'][$previousStep];
                    }

                    if ( trim ($audit['dateCompleted']) !== "" )
                    {
                        $dateCompleted = date ('Y-m-d', strtotime (str_replace ('.', '-', $audit['dateCompleted'])));
                    }


                    if ( $userUid !== null && $audit['claimed'] != $userUid )
                    {
                        $intSkip++;
                    }

                    if ( is_numeric ($process) && trim ($process) != trim ($workflowId) )
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

                    if ( $intSkip == 0 && trim ($arrResult['object_id']) !== trim ($elementId) )
                    {

                        $arrCase = array(
                            "elementId" => $elementId,
                            "projectId" => $arrResult['object_id'],
                            "workflow_id" => $workflowId,
                            "current_user" => $audit['claimed'],
                            "current_step" => $currentStep,
                            "status" => $currentStatus
                        );

                        if ( !in_array ($elementId, $arrUsed) )
                        {
                            $arrCases[] = $arrCase;
                        }
                    }
                }


                $arrUsed[] = $elementId;
            }

            $arrUsed = array();
        }

        if ( !empty ($arrCases) )
        {
            if ( $paged === true )
            {
                $arrCases = $this->paginate ($arrCases, $limit, $start);
            }

            $arrUsed = array();

            foreach ($arrCases['data'] as $key => $arrCase) {

                $objCase = new Elements ($arrCase['projectId'], $arrCase['elementId']);

                if ( isset ($arrCase['workflow_id']) )
                {

                    $objCase->setWorkflow_id ($workflow_id);
                    $workflowName = $this->getWorkflowName ($arrCase['workflow_id']);

                    if ( $workflowName !== false )
                    {
                        $objCase->setWorkflowName ($workflow[0]['workflow_name']);
                    }
                }

                if ( isset ($arrCase['current_user']) )
                {
                    $objCase->setCurrent_user ($arrCase['current_user']);
                }

                if ( isset ($arrCase['current_step']) )
                {

                    $stepName = $this->getStepName ($arrCase['current_step']);

                    if ( $stepName !== false )
                    {
                        $objCase->setCurrent_step ($stepName);
                    }
                }

                if ( isset ($arrCase['status']) )
                {
                    $objCase->setStatus ($arrCase['status']);
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

    /**
     * Get data of a Case
     *
     * @param string $applicationUid Unique id of Case
     * @param string $userUid Unique id of User
     *
     * return array Return an array with data of Case Info
     */
    public function getCaseInfo ($projectId, $caseId = null, $userUid = null)
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

                // if no case id specified use the last element in the array
                if ( $caseId === null )
                {
                    end ($workflowData['elements']);         // move the internal pointer to the end of the array
                    $caseId = key ($workflowData['elements']);  // fetches the key of the element pointed to by the internal pointer
                }

                foreach ($workflowData['elements'] as $elementId => $element) {
                    if ( $elementId == $caseId )
                    {
                        $previousStep = $this->getPreviousStep ($element['current_step'], $element['workflow_id']);

                        if ( isset ($auditData['elements'][$elementId]['steps'][$previousStep]) )
                        {
                            $audit = $auditData['elements'][$elementId]['steps'][$previousStep];
                        }
                        elseif ( count ($auditData['elements'][$elementId]['steps']) == 1 )
                        {
                            end ($auditData['elements'][$elementId]['steps']);         // move the internal pointer to the end of the array
                            $previousStep = key ($auditData['elements'][$elementId]['steps']);
                            $audit = $auditData['elements'][$elementId]['steps'][$previousStep];
                        }

                        $objElements = new Elements ($projectId, $elementId);

                        $workflowName = $this->getWorkflowName ($element['workflow_id']);

                        if ( $workflowName !== false )
                        {
                            $objElements->setWorkflowName ($workflowName);
                        }

                        $objElements->setWorkflow_id ($element['workflow_id']);
                        $objElements->setCurrent_user ($audit['claimed']);

                        $stepName = $this->getStepName ($element['current_step']);

                        if ( $stepName !== false )
                        {
                            $objElements->setCurrent_step ($stepName);
                            $objElements->setCurrentStepId ($element['current_step']);
                        }

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
        $note_data = $objComments->getNotesList ($app_uid, $user, $start, $limit, $sort, $dir, $dateFrom, $dateTo, $search);
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

                $response['data'][$con]['app_uid'] = $value['source_id'];
                $response['data'][$con]['usr_uid'] = $value['username'];
                $response['data'][$con]['note_date'] = $value['datetime'];
                $response['data'][$con]['note_content'] = $value['comment'];
                $con++;
            }
        }
        else
        {
            $con = 0;
            foreach ($note_data['array']['notes'] as $value) {
                $response[$con]['app_uid'] = $value['source_id'];
                $response[$con]['usr_uid'] = $value['username'];
                $response[$con]['note_date'] = $value['datetime'];
                $response[$con]['note_content'] = $value['comment'];
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
        $this->isString ($app_uid, '$app_uid');
        $this->projectUid ($app_uid);
        $this->isString ($usr_uid, '$usr_uid');
        $this->validateUserId ($usr_uid, '$usr_uid');
        $this->isString ($note_content, '$note_content');

        if ( strlen ($note_content) > 500 )
        {
            throw (new Exception ("COMMENT CANNOT BE MORE THAN 500 CHARACTERS"));
        }

        $this->isBoolean ($send_mail);
        //$case = new \Cases();
        //$caseLoad = $case->loadCase ($app_uid);
        //$pro_uid = $caseLoad['PRO_UID'];
        $note_content = addslashes ($note_content);
        $comments = new Comments();
        $comments->addCaseNote ($app_uid, $usr_uid, $note_content, intval ($send_mail));
    }

    public function startCase ($workflowId)
    {
        $objWorkflow = new Workflow ($workflowId, null);

        $objStep = $objWorkflow->getNextStep ();

        $stepId = $objStep->getStepId ();

        $objForm = new Form ($stepId, $workflowId);
        $arrFields = $objForm->getFields ();

        $objFprmBuilder = new FormBuilder ("AddNewForm");
        $objFprmBuilder->buildForm ($arrFields, array());
        $html = $objFprmBuilder->render ();

        return $html;
    }

    /* Add New Case
     *
     * @param string $processUid Unique id of Project
     * @param string $taskUid Unique id of Activity (task)
     * @param string $userUid Unique id of Case
     * @param array $variables
     *
     * return array Return an array with Task Case
     */

    public function addCase ($processUid, $userUid, $variables, $arrFiles = array(), $blSaveProject = true, $projectId = null)
    {
        try {
            // Check For Parent
            $objWorkflow = new Workflow ($processUid);
            $arrWorkflow = $objWorkflow->getProcess ();

            $workflowId = isset ($arrWorkflow[0]['parent_id']) && $arrWorkflow[0]['parent_id'] !== '0' ? $arrWorkflow[0]['parent_id'] : $processUid;

            $oProcesses = new Process();

            $pro = $oProcesses->processExists ($processUid);

            if ( !$pro )
            {
                throw new Exception ("Process doesnt exist");
            }

            $arrData['form'] = array(
                "priority" => 1,
                "deptId" => 1,
                "workflow_id" => $workflowId,
                "added_by" => $_SESSION['user']['username'],
                "date_created" => date ("Y-m-d"),
                "project_status" => 1,
                "dueDate" => date ("Y-m-d")
            );

            if ( isset ($variables['form']['description']) )
            {
                $arrData['form']['description'] = $variables['form']['description'];
            }

            if ( isset ($variables['form']['name']) )
            {
                $arrData['form']['name'] = $variables['form']['name'];
            }

            $arrData['form']['status'] = "NEW PROJECT";
            $arrData['form']['dateCompleted'] = date ("Y-m-d H:i:s");
            $arrData['form']['claimed'] = $_SESSION['user']['username'];

            if ( $blSaveProject === true )
            {
                $projectId = $this->saveProject ($arrData, $workflowId);

                $this->projectUid ($projectId);
            }

            $errorCounter = 0;

            $objElements = new Elements ($projectId);
            $objWorkflow = new Workflow ($processUid);
            $objStep = $objWorkflow->getNextStep ();

            if ( isset ($arrFiles['fileUpload']) )
            {
                $arrFiles = $this->uploadCaseFiles ($arrFiles, $projectId, $objStep);

//                if ( !$arrFiles )
//                {
//                    $errorCounter++;
//                }
            }

            if ( $errorCounter === 0 )
            {
                if ( isset ($arrFiles) && !empty ($arrFiles) )
                {
                    $_POST['form']['file2'] = implode (",", $arrFiles);
                }

                $variables['form']['source_id'] = $projectId;

                $variables['form']['status'] = "NEW";
                $variables['form']['workflow_id'] = $processUid;
                $variables['form']['claimed'] = $_SESSION["user"]["username"];
                $variables['form']['dateCompleted'] = date ("Y-m-d H:i:s");

                $validation = $objStep->save ($objElements, $variables['form']);

                if ( $validation === false )
                {
                    $validate['validation'] = $objStep->getFieldValidation ();
                    echo json_encode ($validate);
                    return false;
                }
            }
        } catch (Exception $ex) {
            throw new Exception ($ex);
        }
    }

    /**
     * Uploads files that were saved as part of the new case process
     * @see addCase
     */
    public function uploadCaseFiles ($arrFilesUploaded, $projectId, WorkflowStep $objStep, $fileType = '')
    {
        if ( isset ($arrFilesUploaded['fileUpload']['name'][0]) && !empty ($arrFilesUploaded['fileUpload']['name'][0]) )
        {
            foreach ($arrFilesUploaded['fileUpload']['name'] as $key => $value) {

                $fileContent = file_get_contents ($arrFilesUploaded['fileUpload']['tmp_name'][$key]);

                $arrData = array(
                    "source_id" => $projectId,
                    "filename" => $value,
                    "date_uploaded" => date ("Y-m-d H:i:s"),
                    "uploaded_by" => $_SESSION['user']['username'],
                    "contents" => $fileContent,
                    "files" => $arrFilesUploaded,
                    "step" => $objStep
                );

                if ( trim ($fileType) !== "" )
                {
                    $arrData['file_type'] = $fileType;
                }

                $objAttachments = new Attachments();
                $id = $arrFiles = $objAttachments->loadObject ($arrData);

                if ( $id === false )
                {
                    $messages = $objAttachments->getArrayValidation ();
                    $html = '';

                    foreach ($messages as $message) {
                        $html .= $message . "</br>";
                    }

                    throw new Exception ("File could not be uploaded </br>" . $html);
                }

                $arrFiles[] = $id;
            }
        }
        else
        {
            $arrErrors[] = "file";
        }

        if ( !empty ($arrErrors) )
        {
            return false;
        }

        return $arrFiles;
    }

    public function saveProject ($arrData, $workflowId)
    {
        $objSave = new Save();
        $objWorkflow = new Workflow ($workflowId);

        $objStep = $objWorkflow->getNextStep ();
        $validation = $objStep->save ($objSave, $arrData['form']);

        if ( $validation === false )
        {
            print_r ($objStep->getFieldValidation ());
        }

        $projectId = $objSave->getId ();

        return $projectId;
    }

    private function getWorkflowName ($workflowId)
    {
        $workflow = $this->objMysql->_select ("workflow.workflows", array(), array("workflow_id" => $workflowId));

        if ( isset ($workflow[0]['workflow_name']) && trim ($workflow[0]['workflow_name']) !== "" )
        {
            return $workflow[0]['workflow_name'];
        }

        return false;
    }

    private function getStepName ($stepName)
    {
        $step = $this->objMysql->_query ("SELECT s.step_name FROM workflow.status_mapping m
                                                    INNER JOIN workflow.steps s ON s.step_id = m.step_from
                                                    WHERE m.id = ?", [$stepName]);

        if ( isset ($step[0]['step_name']) && trim ($step[0]['step_name']) !== "" )
        {
            return $step[0]['step_name'];
        }

        return false;
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

    public function updateStatus (Elements $objElement, $status, $rejectionReason = '')
    {
        $arrStepData = array(
            'claimed' => $_SESSION["user"]["username"],
            "dateCompleted" => date ("Y-m-d H:i;s"),
            "status" => $status
        );

        if ( $rejectionReason !== "" )
        {
            $arrStepData['rejectionReason'] = $rejectionReason;
        }

        $objSteps = new WorkflowStep (null, $objElement);

        if ( $status === "COMPLETE" )
        {
            $objSteps->complete ($objElement, $arrStepData);
        }
        else
        {
            $objStep->save ($objElement, $arrStepData);
        }
    }

    public function assignUsers (Elements $objElements)
    {
        $arrStepData = array(
            'claimed' => $_SESSION["user"]["username"],
            "dateCompleted" => date ("Y-m-d H:i;s"),
            "status" => "CLAIMED"
        );

        $objStep = new WorkflowStep (null, $objElements);
        $objStep->assignUserToStep ($objElements, $arrStepData);
    }

    /**
     * Get Case Variables
     *
     * @access public
     * @param string $app_uid, Uid for case
     * @param string $usr_uid, Uid for user
     * @param string $dynaFormUid, Uid for step
     * @return array
     */
    public function getCaseVariables ($app_uid, $usr_uid, $pro_uid, $dynaFormUid = null, $act_uid = null, $app_index = null)
    {
        $this->isInteger ($app_uid, '$app_uid');
        //Validator::appUid($app_uid, '$app_uid');
        $this->isInteger ($usr_uid, '$usr_uid');
        $this->validateUserId ($usr_uid);

        $arrSystemVariables = array(
            "WORKFLOW_NAME" => "getWorkflowName",
            "STEP_NAME" => "getCurrentStep",
            "ASSIGNED" => "getCurrent_user",
            "DATE_COMPLETED" => "",
            "COMPLETED" => "",
            "id" => "getId",
            "USER" => "getCurrent_user",
            "PROJECT_ID" => "getParentId",
            "STEP_ID" => "getCurrentStepId",
            "WORKFLOW_ID" => "getWorkflow_id",
            "STATUS" => "getStatus"
        );

        $objCase = $this->getCaseInfo ($pro_uid, $app_uid);

        $arrayCaseVariable = [];

        if ( !is_null ($dynaFormUid) )
        {
            $objForm = new Form ($dynaFormUid);
            $arrAllFields = $objForm->getFields (true);
            $arrFields = [];


            foreach ($arrAllFields as $key => $arrField) {
                $arrFields[] = $key;
            }


            $arrayCaseVariable = $this->__getFieldsAndValuesByDynaFormAndAppData (
                    $arrFields, $objCase, $arrSystemVariables
            );
        }
        else
        {
            $arrayCaseVariable = $objCase;
        }

        return $arrayCaseVariable;
    }

    /**
     * Get fields and values by DynaForm
     *
     * @param array $form
     * @param array $appData
     * @param array $caseVariable
     *
     * return array Return array
     */
    private function __getFieldsAndValuesByDynaFormAndAppData (array $form, Elements $objElements, array $caseVariables)
    {
        try {
            $caseVariableAux = [];

            foreach ($form as $value) {
                $field = $value;

                if ( isset ($objElements->objJobFields[$field]) )
                {
                    if ( isset ($objElements->objJobFields[$field]['accessor']) )
                    {
                        if ( method_exists ($objElements, $objElements->objJobFields[$field]['accessor']) )
                        {
                            $caseVariable[$field] = call_user_func (array($objElements, $objElements->objJobFields[$field]['accessor']));
                        }
                    }
                }
            }

            if ( !empty ($caseVariables) )
            {
                foreach ($caseVariables as $variableName => $functionName) {
                    if ( trim ($functionName) !== "" )
                    {
                        $caseVariable[$variableName] = call_user_func (array($objElements, $functionName));
                    }
                }
            }


            return $caseVariable;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get data of Cases OutputDocument
     *
     * @param int $projectId
     * @param int $caseId
     * @param string $outputDocumentUid
     * @param string $userUid
     *
     * return object Return an object with data of an OutputDocument
     */
    public function addCasesOutputDocument ($projectId, $caseId, $stepId, $outputDocumentUid, $userUid)
    {
        try {
            $sApplication = $caseId;
            $sUserLogged = $userUid;
            $outputID = $outputDocumentUid;

            $oOutputDocument = new \OutputDocument();
            $aOD = $oOutputDocument->retrieveByPk ($outputID);
            $Fields = $this->getCaseVariables ($caseId, $userUid, $projectId, $stepId);

            $sFilename = preg_replace ('[^A-Za-z0-9_]', '_', $this->replaceDataField ($aOD->getOutDocFilename (), $Fields));

            $fileTags = $aOD->getOutDocTags ();

            $objDocumentVersion = new DocumentVersion (array());
            $lastDocVersion = $objDocumentVersion->getLastDocVersionByFilename ($sFilename);

            if ( ($aOD->getOutDocVersioning () ) )
            {
                $lastDocVersion ++;
                $objDocumentVersion->create (array("filename" => $sFilename, "document_id" => $aOD->getOutDocUid (), "document_type" => "OUTPUT"));
            }
            else
            {
                
            }

            $sFilename = $aOD->getOutDocUid () . "_" . $lastDocVersion;
            $pathOutput = $_SERVER['DOCUMENT_ROOT'] . "/FormBuilder/public/uploads/OutputDocuments/" . $projectId . "/";
            $objFile = new FileUpload();
            $objFile->mk_dir ($pathOutput);

            $aProperties = array();
            if ( trim ($aOD->getOutDocMedia ()) === "" )
            {
                $aOD->setOutDocMedia ('Letter');
            }
            if ( trim ($aOD->getOutDocLeftMargin ()) === "" )
            {
                $aOD->setOutDocLeftMargin (15);
            }
            if ( trim ($aOD->getOutDocRightMargin ()) === "" )
            {
                $aOD->setOutDocRightMargin (15);
            }
            if ( trim ($aOD->getOutDocTopMargin ()) === "" )
            {
                $aOD->setOutDocTopMargin (15);
            }
            if ( trim ($aOD->getOutDocBottomMargin ()) === "" )
            {
                $aOD->setOutDocBottomMargin (15);
            }
            $aProperties['media'] = $aOD->getOutDocMedia ();

            $aProperties['margins'] = array('left' => $aOD->getOutDocLeftMargin (), 'right' => $aOD->getOutDocRightMargin (), 'top' => $aOD->getOutDocTopMargin (), 'bottom' => $aOD->getOutDocBottomMargin ()
            );

            if ( trim ($aOD->getOutDocReportGenerator ()) !== "" )
            {
                $aProperties['report_generator'] = $aOD->getOutDocReportGenerator ();
            }

            $aOD->generate ($outputID, $Fields, $pathOutput, $sFilename, $aOD->getOutDocTemplate (), (boolean) $aOD->getOutDocLandscape (), $aOD->getOutDocGenerate (), $aProperties);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /* Returns content with [parameters] replaced with its values defined */

    public function replaceDataField ($sContent, $aFields, $DBEngine = 'mysql')
    {
        $nrt = array("\n", "\r", "\t");
        $nrthtml = array("(n /)", "(r /)", "(t /)");

        $strContentAux = str_replace ($nrt, $nrthtml, $sContent);

        $iOcurrences = preg_match_all ('/\[([^\]]+)\]/', $sContent, $arrayMatch1, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        $nl2brRecursive = true;

        if ( $iOcurrences )
        {
            $arrayGrid = array();

            for ($i = 0; $i <= $iOcurrences - 1; $i++) {
                $arrayGrid[] = $arrayMatch1[1][$i][0];
            }

            $arrayGrid = array_unique ($arrayGrid);

            foreach ($arrayGrid as $index => $value) {
                if ( $value !== "" )
                {
                    $grdName = $value;

                    $strContentAux1 = $strContentAux;
                    $strContentAux = null;

                    if ( isset ($aFields[$grdName]) && trim ($aFields[$grdName]) !== "" )
                    {
                        $newValue = str_replace ($nrt, $nrthtml, nl2br ($aFields[$grdName]));
                        $newValue = urlencode ($aFields[$grdName]);
                        $newValue = stripcslashes ($aFields[$grdName]);

                        $strContentAux .= str_replace ("[" . $grdName . "]", $newValue, $strContentAux1);
                    }
                }
            }
        }

        $strContentAux = str_replace ($nrthtml, $nrt, $strContentAux);
        $sContent = $strContentAux;

        return $sContent;
    }

    /*
     * get all generate document
     *
     * @name getAllGeneratedDocumentsCriteria
     * @param string $sProcessUID
     * @param string $sApplicationUID
     * @param string $sTasKUID
     * @param string $sUserUID
     * @return object
     */

    public function getAllGeneratedDocumentsCriteria ($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $listing = false;

        $aObjectPermissions = $this->getAllObjects ($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID);
        if ( !is_array ($aObjectPermissions) )
        {
            $aObjectPermissions = array('DYNAFORMS' => array(-1), 'INPUT_DOCUMENTS' => array(-1), 'OUTPUT_DOCUMENTS' => array(-1));
        }
        if ( !isset ($aObjectPermissions['DYNAFORMS']) )
        {
            $aObjectPermissions['DYNAFORMS'] = array(-1);
        }
        else
        {
            if ( !is_array ($aObjectPermissions['DYNAFORMS']) )
            {
                $aObjectPermissions['DYNAFORMS'] = array(-1);
            }
        }
        if ( !isset ($aObjectPermissions['INPUT_DOCUMENTS']) )
        {
            $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
        }
        else
        {
            if ( !is_array ($aObjectPermissions['INPUT_DOCUMENTS']) )
            {
                $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
            }
        }
        if ( !isset ($aObjectPermissions['OUTPUT_DOCUMENTS']) )
        {
            $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
        }
        else
        {
            if ( !is_array ($aObjectPermissions['OUTPUT_DOCUMENTS']) )
            {
                $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
            }
        }

        $results = $this->objMysql->_select ("workflow.step_document", [], ["step_id" => 23]);

        $aOutputDocuments = array();
        $aOutputDocuments[] = array(
            'APP_DOC_UID' => 'char',
            'DOC_UID' => 'char',
            'APP_DOC_COMMENT' => 'char',
            'APP_DOC_FILENAME' => 'char',
            'APP_DOC_INDEX' => 'integer'
        );

        foreach ($results as $aRow) {

            $oAppDocument = new DocumentVersion();

            $lastVersion = $oAppDocument->getLastDocVersion ($aRow['document_id']);

            if ( $aRow['document_type'] == 1 )
            {
                $aAux = $oAppDocument->load ($aRow['document_id'], $lastVersion);

                $oOutputDocument = new OutputDocument();
                $aGields = $oOutputDocument->retrieveByPk ($aRow['document_id']);
                //OUTPUTDOCUMENT
                $outDocTitle = $aGields->getOutDocTitle ();
                switch ($aGields->getOutDocGenerate ()) {
                    case "PDF":
                        $fileDoc = 'javascript:alert("NO DOC")';
                        $fileDocLabel = " ";
                        $filePdf = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=pdf&random=' . rand ();
                        $filePdfLabel = ".pdf";
                        if ( is_array ($listing) )
                        {
                            foreach ($listing as $folderitem) {
                                if ( ($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "PDF") )
                                {
                                    $filePdfLabel = \G::LoadTranslation ('ID_GET_EXTERNAL_FILE') . " .pdf";
                                    $filePdf = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;

                    case "DOC":
                        $fileDoc = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=doc&random=' . rand ();
                        $fileDocLabel = ".doc";
                        $filePdf = 'javascript:alert("NO PDF")';
                        $filePdfLabel = " ";
                        if ( is_array ($listing) )
                        {
                            foreach ($listing as $folderitem) {
                                if ( ($folderitem->filename == $aRow['id']) && ($folderitem->type == "DOC") )
                                {
                                    $fileDocLabel = \G::LoadTranslation ('ID_GET_EXTERNAL_FILE') . " .doc";
                                    $fileDoc = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;

                    case "BOTH":
                        $fileDoc = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=doc&random=' . rand ();
                        $fileDocLabel = ".doc";
                        if ( is_array ($listing) )
                        {
                            foreach ($listing as $folderitem) {
                                if ( ($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "DOC") )
                                {
                                    $fileDocLabel = G::LoadTranslation ('ID_GET_EXTERNAL_FILE') . " .doc";
                                    $fileDoc = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        $filePdf = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=pdf&random=' . rand ();
                        $filePdfLabel = ".pdf";

                        if ( is_array ($listing) )
                        {
                            foreach ($listing as $folderitem) {
                                if ( ($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "PDF") )
                                {
                                    $filePdfLabel = \G::LoadTranslation ('ID_GET_EXTERNAL_FILE') . " .pdf";
                                    $filePdf = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;
                }

                try {
                    $oUser = new UsersFactory();
                    $aAux1 = $oUser->getUser ($aAux['user_id']);

                    $sUser = $this->usersNameFormatBySetParameters ("@lastName, @firstName (@userName)", $aAux1->getUsername (), $aAux1->getFirstName (), $aAux1->getLastName ());
                } catch (\Exception $oException) {
                    $sUser = '(USER DELETED)';
                }

                //if both documents were generated, we choose the pdf one, only if doc was
                //generate then choose the doc file.
                $firstDocLink = $filePdf;
                $firstDocLabel = $filePdfLabel;
                if ( $aGields->getOutDocGenerate () == 'DOC' )
                {
                    $firstDocLink = $fileDoc;
                    $firstDocLabel = $fileDocLabel;
                }
                $aFields = array(
                    'APP_DOC_UID' => $aAux['id'],
                    'DOC_UID' => $aAux['document_id'],
                    // 'APP_DOC_COMMENT' => $aAux['APP_DOC_COMMENT'],
                    'APP_DOC_FILENAME' => $aAux['filename'],
                    // 'APP_DOC_INDEX' => $aAux['APP_DOC_INDEX'],
                    // 'ORIGIN' => $aTask['TAS_TITLE'],
                    'CREATE_DATE' => $aAux['date_created'],
                    'CREATED_BY' => $sUser,
                    'FILEDOC' => $fileDoc,
                    'FILEPDF' => $filePdf,
                    'OUTDOCTITLE' => $outDocTitle,
                    'DOC_VERSION' => $aAux['document_version'],
                    'TYPE' => $aAux['document_type'] . ' ' . $aGields->getOutDocGenerate (),
                    'DOWNLOAD_LINK' => $firstDocLink,
                    'DOWNLOAD_FILE' => $aAux['filename'] . $firstDocLabel
                );
                if ( trim ($fileDocLabel) != '' )
                {
                    $aFields['FILEDOCLABEL'] = $fileDocLabel;
                }
                if ( trim ($filePdfLabel) != '' )
                {
                    $aFields['FILEPDFLABEL'] = $filePdfLabel;
                }
                if ( $aFields['APP_DOC_FILENAME'] != '' )
                {
                    $aFields['TITLE'] = $aFields['APP_DOC_FILENAME'];
                }
                else
                {
                    $aFields['TITLE'] = $aFields['APP_DOC_COMMENT'];
                }

                //$aFields['POSITION'] = $_SESSION['STEP_POSITION'];
                $aFields['CONFIRM'] = 'ID_CONFIRM_DELETE_ELEMENT';
                if ( in_array ($aRow['id'], $aObjectPermissions['OUTPUT_DOCUMENTS']) )
                {
                    if ( in_array ($aRow['id'], $aDelete['OUTPUT_DOCUMENTS']) )
                    {
                        $aFields['ID_DELETE'] = 'ID_DELETE';
                    }
                }
                $aOutputDocuments[] = $aFields;
            }
        }

        return $aOutputDocuments;
    }

    public function usersNameFormatBySetParameters ($formatUserName, $userName, $firstName, $lastName)
    {
        $usersNameFormat = str_replace (array("@userName", "@firstName", "@lastName"), array($userName, $firstName, $lastName), $formatUserName);
        $usersNameFormat = trim ($usersNameFormat);

        return $usersNameFormat;
    }

    /**

     * Obtain all user permits for Dynaforms, Input and output documents

     * function getAllObjects ($PRO_UID, $APP_UID, $TAS_UID, $USR_UID)

     * @access public

     * @param  Process ID, Application ID, Task ID and User ID

     * @return Array within all user permissions all objects' types

     */
    public function getAllObjects ($PRO_UID, $APP_UID, $TAS_UID = '', $USR_UID = '', $delIndex = 0)
    {

        $ACTIONS = Array('VIEW', 'BLOCK', 'DELETE'); //TO COMPLETE

        $MAIN_OBJECTS = Array();

        $RESULT_OBJECTS = Array();

        foreach ($ACTIONS as $action) {

            $MAIN_OBJECTS[$action] = $this->getAllObjectsFrom ($PRO_UID, $APP_UID, $TAS_UID, $USR_UID, $action, $delIndex);
        }

        /* ADDITIONAL OPERATIONS */

        /*         * * BETWEN VIEW AND BLOCK** */

        $RESULT_OBJECTS['DYNAFORMS'] = $this->arrayDiff (
                $MAIN_OBJECTS['VIEW']['DYNAFORMS'], $MAIN_OBJECTS['BLOCK']['DYNAFORMS']
        );

        $RESULT_OBJECTS['INPUT_DOCUMENTS'] = $this->arrayDiff (
                $MAIN_OBJECTS['VIEW']['INPUT_DOCUMENTS'], $MAIN_OBJECTS['BLOCK']['INPUT_DOCUMENTS']
        );

        $RESULT_OBJECTS['OUTPUT_DOCUMENTS'] = array_merge_recursive (
                $this->arrayDiff ($MAIN_OBJECTS['VIEW']['OUTPUT_DOCUMENTS'], $MAIN_OBJECTS['BLOCK']['OUTPUT_DOCUMENTS']), $this->arrayDiff ($MAIN_OBJECTS['DELETE']['OUTPUT_DOCUMENTS'], $MAIN_OBJECTS['BLOCK']['OUTPUT_DOCUMENTS'])
        );

        $RESULT_OBJECTS['CASES_NOTES'] = $this->arrayDiff (
                $MAIN_OBJECTS['VIEW']['CASES_NOTES'], $MAIN_OBJECTS['BLOCK']['CASES_NOTES']
        );

        array_push ($RESULT_OBJECTS["DYNAFORMS"], -1, -2);

        array_push ($RESULT_OBJECTS['INPUT_DOCUMENTS'], -1);

        array_push ($RESULT_OBJECTS['OUTPUT_DOCUMENTS'], -1);

        array_push ($RESULT_OBJECTS['CASES_NOTES'], -1);

        return [];


        return $RESULT_OBJECTS;
    }

    public function arrayDiff ($array1, $array2)
    {
        if ( !is_array ($array1) )
        {
            $array1 = (array) $array1;
        }

        if ( !is_array ($array2) )
        {
            $array2 = (array) $array2;
        }

        // This wrapper for array_diff rekeys the array returned
        $valid_array = array_diff ($array1, $array2);

        // reinstantiate $array1 variable
        $array1 = array();

        // loop through the validated array and move elements to $array1
        // this is necessary because the array_diff function returns arrays that retain their original keys
        foreach ($valid_array as $valid) {
            $array1[] = $valid;
        }
        return $array1;
    }

    /**

     * Obtain all user permits for Dynaforms, Input and output documents from some action [VIEW, BLOCK, etc...]

     * function getAllObjectsFrom ($PRO_UID, $APP_UID, $TAS_UID, $USR_UID, $ACTION)

     * @author Erik Amaru Ortiz <erik@colosa.com>

     * @access public

     * @param  Process ID, Application ID, Task ID, User ID, Action, Delegation index

     * @return Array within all user permitions all objects' types

     */
    public function getAllObjectsFrom ($PRO_UID, $APP_UID, $TAS_UID = "", $USR_UID = "", $ACTION = "", $delIndex = 0)
    {

//        $aCase = $this->loadCase ($APP_UID);
//
//
//
//        if ( $delIndex != 0 )
//        {
//
//            $appDelay = new AppDelay();
//
//
//
//            if ( $appDelay->isPaused ($APP_UID, $delIndex) )
//            {
//
//                $aCase["APP_STATUS"] = "PAUSED";
//            }
//        }
//
//
//
//        $USER_PERMISSIONS = Array();
//
//        $GROUP_PERMISSIONS = Array();
//
//        $RESULT = Array(
//            "DYNAFORM" => Array(),
//            "INPUT" => Array(),
//            "OUTPUT" => Array(),
//            "CASES_NOTES" => 0,
//            "MSGS_HISTORY" => Array()
//
//                /* ----------------------------------********--------------------------------- */
//        );
//
//
//
//        //permissions per user
//
//        $oCriteria = new Criteria ('workflow');
//
//        $oCriteria->add (
//                $oCriteria->getNewCriterion (ObjectPermissionPeer::USR_UID, $USR_UID)->addOr (
//                        $oCriteria->getNewCriterion (ObjectPermissionPeer::USR_UID, '')->addOr (
//                                $oCriteria->getNewCriterion (ObjectPermissionPeer::USR_UID, '0')
//                        )
//                )
//        );
//
//        $oCriteria->add (ObjectPermissionPeer::PRO_UID, $PRO_UID);
//
//        $oCriteria->add (ObjectPermissionPeer::OP_ACTION, $ACTION);
//
//        $oCriteria->add (
//                $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, $TAS_UID)->addOr (
//                        $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, '')->addOr (
//                                $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, '0')
//                        )
//                )
//        );
//
//
//
//        $rs = ObjectPermissionPeer::doSelectRS ($oCriteria);
//
//        $rs->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//
//
//        while ($rs->next ()) {
//
//            $row = $rs->getRow ();
//
//
//
//            if ( $row["OP_CASE_STATUS"] == "ALL" || $row["OP_CASE_STATUS"] == "" || $row["OP_CASE_STATUS"] == "0" ||
//                    $row["OP_CASE_STATUS"] == $aCase["APP_STATUS"]
//            )
//            {
//
//                array_push ($USER_PERMISSIONS, $row);
//            }
//        }
//
//
//
//        //permissions per group
//
//        G::loadClass ('groups');
//
//
//
//        $gr = new Groups();
//
//        $records = $gr->getActiveGroupsForAnUser ($USR_UID);
//
//
//
//        foreach ($records as $group) {
//
//            $oCriteria = new Criteria ('workflow');
//
//            $oCriteria->add (ObjectPermissionPeer::USR_UID, $group);
//
//            $oCriteria->add (ObjectPermissionPeer::PRO_UID, $PRO_UID);
//
//            $oCriteria->add (ObjectPermissionPeer::OP_ACTION, $ACTION);
//
//            $oCriteria->add (
//                    $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, $TAS_UID)->addOr (
//                            $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, '')->addOr (
//                                    $oCriteria->getNewCriterion (ObjectPermissionPeer::TAS_UID, '0')
//                            )
//                    )
//            );
//
//
//
//            $rs = ObjectPermissionPeer::doSelectRS ($oCriteria);
//
//            $rs->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//            while ($rs->next ()) {
//
//                $row = $rs->getRow ();
//
//
//
//                if ( $row["OP_CASE_STATUS"] == "ALL" || $row["OP_CASE_STATUS"] == "" || $row["OP_CASE_STATUS"] == "0" ||
//                        $row["OP_CASE_STATUS"] == $aCase["APP_STATUS"]
//                )
//                {
//
//                    array_push ($GROUP_PERMISSIONS, $row);
//                }
//            }
//        }
//
//
//
//        $PERMISSIONS = array_merge ($USER_PERMISSIONS, $GROUP_PERMISSIONS);
//
//
//
//        foreach ($PERMISSIONS as $row) {
//
//            $USER = $row['USR_UID'];
//
//            $USER_RELATION = $row['OP_USER_RELATION'];
//
//            $TASK_SOURCE = $row['OP_TASK_SOURCE'];
//
//            $PARTICIPATE = $row['OP_PARTICIPATE'];
//
//            $O_TYPE = $row['OP_OBJ_TYPE'];
//
//            $O_UID = $row['OP_OBJ_UID'];
//
//            $ACTION = $row['OP_ACTION'];
//
//            $CASE_STATUS = $row['OP_CASE_STATUS'];
//
//
//
//            // here!,. we should verify $PARTICIPATE
//
//            $sw_participate = false; // must be false for default
//
//            if ( ($row['OP_CASE_STATUS'] != 'COMPLETED') && ($row['OP_CASE_STATUS'] != '') && ($row['OP_CASE_STATUS'] != '0') )
//            {
//
//                if ( $PARTICIPATE == 1 )
//                {
//
//                    $oCriteriax = new Criteria ('workflow');
//
//                    $oCriteriax->add (AppDelegationPeer::USR_UID, $USR_UID);
//
//                    $oCriteriax->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//
//
//                    if ( AppDelegationPeer::doCount ($oCriteriax) == 0 )
//                    {
//
//                        $sw_participate = true;
//                    }
//                }
//            }
//
//            if ( !$sw_participate )
//            {
//
//                switch ($O_TYPE) {
//
//                    case 'ANY':
//
//                        //for dynaforms
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        $oCriteria->add (ApplicationPeer::APP_UID, $APP_UID);
//
//                        $oCriteria->addJoin (ApplicationPeer::PRO_UID, StepPeer::PRO_UID);
//
//                        $oCriteria->addJoin (StepPeer::STEP_UID_OBJ, DynaformPeer::DYN_UID);
//
//                        if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                        {
//
//                            if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                            {
//
//                                $oCriteria->add (StepPeer::TAS_UID, $TASK_SOURCE);
//                            }
//                        }
//
//                        $oCriteria->add (StepPeer::STEP_TYPE_OBJ, 'DYNAFORM');
//
//                        $oCriteria->addAscendingOrderByColumn (StepPeer::STEP_POSITION);
//
//                        $oCriteria->setDistinct ();
//
//
//
//                        $oDataset = DynaformPeer::doSelectRS ($oCriteria);
//
//                        $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                        $oDataset->next ();
//
//
//
//                        while ($aRow = $oDataset->getRow ()) {
//
//                            if ( !in_array ($aRow['DYN_UID'], $RESULT['DYNAFORM']) )
//                            {
//
//                                array_push ($RESULT['DYNAFORM'], $aRow['DYN_UID']);
//                            }
//
//                            $oDataset->next ();
//                        }
//
//
//
//                        //InputDocuments and OutputDocuments
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_UID);
//
//                        $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_TYPE);
//
//
//
//                        $arrayCondition = array();
//
//                        $arrayCondition[] = array(AppDelegationPeer::APP_UID, AppDocumentPeer::APP_UID, Criteria::EQUAL);
//
//                        $arrayCondition[] = array(AppDelegationPeer::DEL_INDEX, AppDocumentPeer::DEL_INDEX, Criteria::EQUAL);
//
//                        $oCriteria->addJoinMC ($arrayCondition, Criteria::LEFT_JOIN);
//
//
//
//                        $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                        $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                        if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                        {
//
//                            if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                            {
//
//                                $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                            }
//                        }
//
//                        $oCriteria->add (
//                                $oCriteria->getNewCriterion (AppDocumentPeer::APP_DOC_TYPE, 'INPUT')->
//                                        addOr ($oCriteria->getNewCriterion (AppDocumentPeer::APP_DOC_TYPE, 'OUTPUT'))->
//                                        addOr ($oCriteria->
//                                                getNewCriterion (AppDocumentPeer::APP_DOC_TYPE, 'ATTACHED'))
//                        );
//
//
//
//                        $oDataset = AppDelegationPeer::doSelectRS ($oCriteria);
//
//                        $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//
//
//                        while ($oDataset->next ()) {
//
//                            $aRow = $oDataset->getRow ();
//
//
//
//                            if ( $aRow['APP_DOC_TYPE'] == "ATTACHED" )
//                            {
//
//                                $aRow['APP_DOC_TYPE'] = "INPUT";
//                            }
//
//                            if ( !in_array ($aRow['APP_DOC_UID'], $RESULT[$aRow['APP_DOC_TYPE']]) )
//                            {
//
//                                array_push ($RESULT[$aRow['APP_DOC_TYPE']], $aRow['APP_DOC_UID']);
//                            }
//                        }
//
//
//
//                        $RESULT['CASES_NOTES'] = 1;
//
//                        /* ----------------------------------********--------------------------------- */
//
//
//
//                        // Message History
//
//                        $RESULT['MSGS_HISTORY'] = array('PERMISSION' => $ACTION);
//
//
//
//                        $arrayDelIndex = array();
//
//
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        if ( $USER_RELATION == 1 )
//                        {
//
//                            //Users
//
//                            $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                            $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                            if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                            {
//
//                                if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                                {
//
//                                    $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                                }
//                            }
//
//                            $oCriteria->add (AppDelegationPeer::USR_UID, $USER);
//
//
//
//                            $oDataset = AppDelegationPeer::doSelectRS ($oCriteria);
//
//                            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                            $oDataset->next ();
//
//                            while ($aRow = $oDataset->getRow ()) {
//
//                                $arrayDelIndex[] = $aRow["DEL_INDEX"];
//
//                                $oDataset->next ();
//                            }
//                        }
//                        else
//                        {
//
//                            //Groups
//
//                            $oCriteria->addJoin (GroupUserPeer::USR_UID, AppDelegationPeer::USR_UID, Criteria::LEFT_JOIN);
//
//                            $oCriteria->add (GroupUserPeer::GRP_UID, $USER);
//
//                            $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                            $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                            if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                            {
//
//                                if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                                {
//
//                                    $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                                }
//                            }
//
//
//
//                            $oDataset = AppDelegationPeer::doSelectRS ($oCriteria);
//
//                            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                            $oDataset->next ();
//
//                            while ($aRow = $oDataset->getRow ()) {
//
//                                $arrayDelIndex[] = $aRow["DEL_INDEX"];
//
//                                $oDataset->next ();
//                            }
//                        }
//
//                        $RESULT["MSGS_HISTORY"] = array_merge (array("DEL_INDEX" => $arrayDelIndex), $RESULT["MSGS_HISTORY"]);
//
//                        break;
//
//                    case 'DYNAFORM':
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        $oCriteria->add (ApplicationPeer::APP_UID, $APP_UID);
//
//                        if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" )
//                        {
//
//                            $oCriteria->add (StepPeer::TAS_UID, $TASK_SOURCE);
//                        }
//
//                        if ( $O_UID != '' && $O_UID != '0' )
//                        {
//
//                            $oCriteria->add (DynaformPeer::DYN_UID, $O_UID);
//                        }
//
//                        $oCriteria->addJoin (ApplicationPeer::PRO_UID, StepPeer::PRO_UID);
//
//                        $oCriteria->addJoin (StepPeer::STEP_UID_OBJ, DynaformPeer::DYN_UID);
//
//                        $oCriteria->add (StepPeer::STEP_TYPE_OBJ, 'DYNAFORM');
//
//                        $oCriteria->addAscendingOrderByColumn (StepPeer::STEP_POSITION);
//
//                        $oCriteria->setDistinct ();
//
//
//
//                        $oDataset = DynaformPeer::doSelectRS ($oCriteria);
//
//                        $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                        $oDataset->next ();
//
//
//
//                        while ($aRow = $oDataset->getRow ()) {
//
//                            if ( !in_array ($aRow['DYN_UID'], $RESULT['DYNAFORM']) )
//                            {
//
//                                array_push ($RESULT['DYNAFORM'], $aRow['DYN_UID']);
//                            }
//
//                            $oDataset->next ();
//                        }
//
//                        break;
//
//                    case 'INPUT':
//
//                    case 'OUTPUT':
//
//                        if ( $row['OP_OBJ_TYPE'] == 'INPUT' )
//                        {
//
//                            $obj_type = 'INPUT';
//                        }
//                        else
//                        {
//
//                            $obj_type = 'OUTPUT';
//                        }
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_UID);
//
//                        $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_TYPE);
//
//                        $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                        $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                        if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                        {
//
//                            if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                            {
//
//                                $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                            }
//                        }
//
//                        if ( $O_UID != '' && $O_UID != '0' )
//                        {
//
//                            $oCriteria->add (AppDocumentPeer::DOC_UID, $O_UID);
//                        }
//
//                        if ( $obj_type == 'INPUT' )
//                        {
//
//                            $oCriteria->add (
//                                    $oCriteria->getNewCriterion (AppDocumentPeer::APP_DOC_TYPE, $obj_type)->
//                                            addOr ($oCriteria->getNewCriterion (AppDocumentPeer::APP_DOC_TYPE, 'ATTACHED'))
//                            );
//                        }
//                        else
//                        {
//
//                            $oCriteria->add (AppDocumentPeer::APP_DOC_TYPE, $obj_type);
//                        }
//
//
//
//                        $aConditions = Array();
//
//                        $aConditions[] = array(AppDelegationPeer::APP_UID, AppDocumentPeer::APP_UID);
//
//                        $aConditions[] = array(AppDelegationPeer::DEL_INDEX, AppDocumentPeer::DEL_INDEX);
//
//                        $oCriteria->addJoinMC ($aConditions, Criteria::LEFT_JOIN);
//
//
//
//                        $oDataset = AppDocumentPeer::doSelectRS ($oCriteria);
//
//                        $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                        $oDataset->next ();
//
//                        while ($aRow = $oDataset->getRow ()) {
//
//                            if ( !in_array ($aRow['APP_DOC_UID'], $RESULT[$obj_type]) )
//                            {
//
//                                array_push ($RESULT[$obj_type], $aRow['APP_DOC_UID']);
//                            }
//
//                            $oDataset->next ();
//                        }
//
//                        if ( $obj_type == 'INPUT' )
//                        {
//
//                            // For supervisor documents
//
//                            $oCriteria = new Criteria ('workflow');
//
//                            $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_UID);
//
//                            $oCriteria->addSelectColumn (AppDocumentPeer::APP_DOC_TYPE);
//
//                            $oCriteria->add (ApplicationPeer::APP_UID, $APP_UID);
//
//                            $oCriteria->add (ApplicationPeer::PRO_UID, $PRO_UID);
//
//                            if ( $O_UID != '' && $O_UID != '0' )
//                            {
//
//                                $oCriteria->add (AppDocumentPeer::DOC_UID, $O_UID);
//                            }
//
//                            $oCriteria->add (AppDocumentPeer::APP_DOC_TYPE, 'INPUT');
//
//                            $oCriteria->add (AppDocumentPeer::DEL_INDEX, 100000);
//
//
//
//                            $oCriteria->addJoin (ApplicationPeer::APP_UID, AppDocumentPeer::APP_UID, Criteria::LEFT_JOIN);
//
//
//
//                            $oDataset = AppDocumentPeer::doSelectRS ($oCriteria);
//
//                            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                            $oDataset->next ();
//
//                            while ($aRow = $oDataset->getRow ()) {
//
//                                if ( !in_array ($aRow['APP_DOC_UID'], $RESULT['INPUT']) )
//                                {
//
//                                    array_push ($RESULT['INPUT'], $aRow['APP_DOC_UID']);
//                                }
//
//                                $oDataset->next ();
//                            }
//                        }
//
//                        break;
//
//                    case 'CASES_NOTES':
//
//                        $RESULT['CASES_NOTES'] = 1;
//
//                        break;
//
//                    /* ----------------------------------********--------------------------------- */
//
//                    case 'MSGS_HISTORY':
//
//                        // Permission
//
//                        $RESULT['MSGS_HISTORY'] = array('PERMISSION' => $ACTION);
//
//                        $arrayDelIndex = array();
//
//                        $oCriteria = new Criteria ('workflow');
//
//                        if ( $USER_RELATION == 1 )
//                        {
//
//                            $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                            $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                            if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                            {
//
//                                if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                                {
//
//                                    $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                                }
//                            }
//
//                            $oCriteria->add (AppDelegationPeer::USR_UID, $USER);
//
//                            $oDataset = AppDelegationPeer::doSelectRS ($oCriteria);
//
//                            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                            $oDataset->next ();
//
//                            while ($aRow = $oDataset->getRow ()) {
//
//                                $arrayDelIndex[] = $aRow["DEL_INDEX"];
//
//                                $oDataset->next ();
//                            }
//                        }
//                        else
//                        {
//
//                            //Groups
//
//                            $oCriteria->addJoin (GroupUserPeer::USR_UID, AppDelegationPeer::USR_UID, Criteria::LEFT_JOIN);
//
//                            $oCriteria->add (GroupUserPeer::GRP_UID, $USER);
//
//                            $oCriteria->add (AppDelegationPeer::APP_UID, $APP_UID);
//
//                            $oCriteria->add (AppDelegationPeer::PRO_UID, $PRO_UID);
//
//                            if ( $aCase['APP_STATUS'] != 'COMPLETED' )
//                            {
//
//                                if ( $TASK_SOURCE != '' && $TASK_SOURCE != "0" && $TASK_SOURCE != 0 )
//                                {
//
//                                    $oCriteria->add (AppDelegationPeer::TAS_UID, $TASK_SOURCE);
//                                }
//                            }
//
//                            $oDataset = AppDelegationPeer::doSelectRS ($oCriteria);
//
//                            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//
//                            $oDataset->next ();
//
//                            while ($aRow = $oDataset->getRow ()) {
//
//                                $arrayDelIndex[] = $aRow["DEL_INDEX"];
//
//                                $oDataset->next ();
//                            }
//                        }
//
//                        $RESULT["MSGS_HISTORY"] = array_merge (array("DEL_INDEX" => $arrayDelIndex), $RESULT["MSGS_HISTORY"]);
//
//                        break;
//                }
//            }
//        }
//
//        return Array(
//            "DYNAFORMS" => $RESULT['DYNAFORM'],
//            "INPUT_DOCUMENTS" => $RESULT['INPUT'],
//            "OUTPUT_DOCUMENTS" => $RESULT['OUTPUT'],
//            "CASES_NOTES" => $RESULT['CASES_NOTES'],
//            "MSGS_HISTORY" => $RESULT['MSGS_HISTORY']
//
//                /* ----------------------------------********--------------------------------- */
//        );
    }

}
