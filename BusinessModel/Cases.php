<?php

namespace BusinessModel;

class Cases
{

    use Validator;

    private $objMysql = null;

    private function getConnection ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Get list for Cases
     *
     * @access public
     * @param array $dataList, Data for list
     * @return array
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
            throw (new \Exception ("ID_INCORRECT_VALUE_ACTION"));
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
                    //if ( isset ($element['current_step']) && isset ($element['workflow_id']) )
                    //{
                    //$previousStep = $this->getPreviousStep ($element['current_step'], $element['workflow_id']);
                    //}

                    if ( isset ($auditData['elements'][$elementId]['steps']) )
                    {
                        foreach ($auditData['elements'][$elementId]['steps'] as $audit) {
                            $arrUsers[] = $audit['claimed'];
                        }
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
            throw new \Exception ($ex);
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

                $objCase = new \Elements ($arrCase['projectId'], $arrCase['elementId']);

                if ( isset ($arrCase['workflow_id']) )
                {

                    $objCase->setWorkflow_id ($element['workflow_id']);
                    $workflowName = $this->getWorkflowName ($arrCase['workflow_id']);

                    if ( $workflowName !== false )
                    {
                        $objCase->setWorkflowName ($workflowName);
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
    public function getCaseInfo ($projectId, $caseId = null)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $workflowObject = $this->objMysql->_select ("workflow.workflow_data", array(), array("object_id" => $projectId));

            if ( empty ($workflowObject) )
            {
                return false;
            }

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

                        $objElements = new \Elements ($projectId, $elementId);

                        if ( isset ($audit['due_date']) )
                        {
                            $objElements->setDueDate ($audit['due_date']);
                        }

                        if ( isset ($audit['dateCompleted']) )
                        {
                            $objElements->setDateCompleted ($audit['dateCompleted']);
                        }

                        if ( isset ($element['request_id']) && is_numeric ($element['request_id']) )
                        {
                            $objElements->setRequestId ($element['request_id']);
                        }

                        $workflowName = $this->getWorkflowName ($element['workflow_id']);

                        if ( $workflowName !== false )
                        {
                            $objElements->setWorkflowName ($workflowName);
                        }

                        $objElements->setWorkflow_id ($element['workflow_id']);


                        if ( isset ($audit['claimed']) )
                        {
                            $objElements->setCurrent_user ($audit['claimed']);
                        }

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
            throw new \Exception ($ex);
        }
    }

    /**
     * Get Case Notes
     *
     * @access public
     * @param string $app_uid, Uid for case
     * @return array
     */
    public function getCaseNotes ($app_uid, $usr_uid, $data_get = array())
    {
        try {
            $this->isArray ($data_get);

            if ( is_numeric ($usr_uid) )
            {
                $this->validateUserId ($usr_uid);
            }

            $start = isset ($data_get["start"]) ? $data_get["start"] : "0";
            $limit = isset ($data_get["limit"]) ? $data_get["limit"] : "";
            $sort = isset ($data_get["sort"]) ? $data_get["sort"] : "datetime";
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

            $objComments = new \Comments();
            $note_data = $objComments->getNotesList ($app_uid, $user, $start, $limit, $sort, $dir, $dateFrom, $dateTo, $search);
            $response = array();

            if ( !empty ($note_data) )
            {
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
            }

            return $response;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Save new case note
     *
     * @access public
     * @param string $app_uid, Uid for case
     * @param array $app_data, Data for case variables
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
            throw (new \Exception ("COMMENT CANNOT BE MORE THAN 500 CHARACTERS"));
        }

        $this->isBoolean ($send_mail);
        //$case = new \Cases();
        //$caseLoad = $case->loadCase ($app_uid);
        //$pro_uid = $caseLoad['PRO_UID'];
        $note_content = addslashes ($note_content);
        $comments = new \Comments();
        $comments->addCaseNote ($app_uid, $usr_uid, $note_content, intval ($send_mail));
    }

    public function startCase (\Workflow $objWorkflow)
    {
        $objWorkflow = new \Workflow ($objWorkflow->getWorkflowId (), null);

        $objStep = $objWorkflow->getNextStep ();

        $stepId = $objStep->getStepId ();

        $objForm = new \BusinessModel\Form (new \Task ($stepId), new \Workflow ($objWorkflow->getWorkflowId ()));
        $arrFields = $objForm->getFields ();

        $objFprmBuilder = new \BusinessModel\FormBuilder ("AddNewForm");
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

    public function addCase (\Workflow $objWorkflow, \Users $objUser, array $variables, $arrFiles = array(), $blSaveProject = true, $projectId = null, $blHasEvent = FALSE)
    {
        try {
            // Check For Parent

            $oProcesses = new \BusinessModel\Process();

            $pro = $oProcesses->processExists ($objWorkflow->getWorkflowId ());

            if ( !$pro )
            {
                throw new \Exception ("Process doesnt exist");
            }

            $arrData['form'] = array(
                "priority" => 1,
                "deptId" => 1,
                "workflow_id" => $objWorkflow->getWorkflowId (),
                "added_by" => $objUser->getUsername (),
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
            $arrData['form']['claimed'] = $objUser->getUsername ();

            if ( $blHasEvent === true )
            {
                $arrData['form']['hasEvent'] = true;
            }

            if ( $blSaveProject === true )
            {
                $projectId = $this->saveProject ($arrData, $objWorkflow, $objUser);

                if ( !$projectId )
                {
                    return false;
                }

                $this->projectUid ($projectId);
            }

            $errorCounter = 0;

            $objElements = new \Elements ($projectId);
            $objStep = $objWorkflow->getNextStep ();

            if ( isset ($arrFiles['fileUpload']) )
            {
                $arrFiles = $this->uploadCaseFiles ($arrFiles, $projectId, $objStep, $objUser);
            }

            if ( $errorCounter === 0 )
            {
                if ( isset ($arrFiles) && !empty ($arrFiles) )
                {
                    $variables['form']['file2'] = implode (",", $arrFiles);
                }

                $variables['form']['source_id'] = $projectId;

                $variables['form']['status'] = "NEW";
                $variables['form']['workflow_id'] = $objWorkflow->getWorkflowId ();
                $variables['form']['claimed'] = $objUser->getUsername ();
                $variables['form']['dateCompleted'] = date ("Y-m-d H:i:s");

                if ( $blHasEvent === true )
                {
                    $variables['form']['hasEvent'] = true;
                }
                
                if(isset($variables['form']['name']) && trim($variables['form']['name']) !== "") {
                     $objElements->setOriginalTitle($variables['form']['name']);
                }
                
                 if(isset($variables['form']['description']) && trim($variables['form']['description']) !== "") {
                     $objElements->setOriginalDescription($variables['form']['description']);
                }
      
                $validation = $objStep->save ($objElements, $variables['form'], $objUser);
                $caseId = $objElements->getId ();

                if ( $validation === false )
                {
                    $validate['validation'] = $objStep->getFieldValidation ();
                    echo json_encode ($validate);
                    return false;
                }
                
                $objElements->updateTitle($objUser, $objStep);

                return array("project_id" => $projectId, "case_id" => $caseId);
            }
        } catch (Exception $ex) {
            throw new \Exception ($ex);
        }
    }

    /**
     * Uploads files that were saved as part of the new case process
     * @see addCase
     */
    public function uploadCaseFiles ($arrFilesUploaded, $projectId, \WorkflowStep $objStep, \Users $objUser, $fileType = '')
    {
        if ( isset ($arrFilesUploaded['fileUpload']['name'][0]) && !empty ($arrFilesUploaded['fileUpload']['name'][0]) )
        {
            foreach ($arrFilesUploaded['fileUpload']['name'] as $key => $value) {

                $fileContent = file_get_contents ($arrFilesUploaded['fileUpload']['tmp_name'][$key]);
                $arrData = array(
                    "source_id" => $projectId,
                    "filename" => $value,
                    "date_uploaded" => date ("Y-m-d H:i:s"),
                    "uploaded_by" => $objUser->getUsername (),
                    "contents" => $fileContent,
                    "files" => $arrFilesUploaded,
                    "step" => $objStep
                );

                if ( trim ($fileType) !== "" )
                {
                    $arrData['file_type'] = $fileType;
                }

                $objAttachments = new \BusinessModel\Attachment();

                $id = $arrFiles = $objAttachments->loadObject ($arrData, $objUser);

                if ( $id === false )
                {
                    $messages = $objAttachments->getArrayValidation ();
                    $html = '';

                    foreach ($messages as $message) {
                        $html .= $message . "</br>";
                    }

                    throw new \Exception ("File could not be uploaded </br>" . $html);
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

    public function saveProject ($arrData, \Workflow $objWorkflow, \Users $objUser)
    {
        $objSave = new \Save();
        $arrWorkflow = $objWorkflow->getProcess ();
        $workflowId = isset ($arrWorkflow[0]['parent_id']) && $arrWorkflow[0]['parent_id'] !== '0' ? $arrWorkflow[0]['parent_id'] : $objWorkflow->getWorkflowId ();

        $objStep = (new \Workflow ($workflowId))->getNextStep ();
        $validation = $objStep->save ($objSave, $arrData['form'], $objUser);

        if ( $validation === false )
        {
            $validate['validation'] = $objStep->getFieldValidation ();
            echo json_encode ($validate);
            return false;
        }

        $projectId = $objSave->getId ();

        return $projectId;
    }

    /**
     * Moves a case to a specific task
     * @param Elements $objElement
     * @param type $stepTo
     * @return boolean
     */
    public function derivateCase (\Elements $objElement, $stepTo)
    {
        $workflowData = $this->objMysql->_select ("workflow.workflow_data", [], ["object_id" => $objElement->getParentId ()]);
        $workflowData = json_decode ($workflowData[0]['workflow_data'], true);

        if ( isset ($workflowData['elements'][$objElement->getId ()]) )
        {
            $workflowData['elements'][$objElement->getId ()]['current_step'] = $stepTo;
            $this->objMysql->_update ("workflow.workflow_data", ["workflow_data" => json_encode ($workflowData)], ["object_id" => $objElement->getParentId ()]);
        }

        return true;
    }

    /**
     * gets workflow name
     * @param type $workflowId
     * @return boolean
     */
    private function getWorkflowName ($workflowId)
    {
        $workflow = $this->objMysql->_select ("workflow.workflows", array(), array("workflow_id" => $workflowId));

        if ( isset ($workflow[0]['workflow_name']) && trim ($workflow[0]['workflow_name']) !== "" )
        {
            return $workflow[0]['workflow_name'];
        }

        return false;
    }

    /**
     * get step name of a task
     * @param type $stepName
     * @return boolean
     */
    private function getStepName ($stepName)
    {

        $step = $this->objMysql->_query ("SELECT t.step_name FROM workflow.status_mapping m
                                                    INNER JOIN workflow.task t ON t.TAS_UID = m.TAS_UID
                                                    WHERE m.id = ?", [$stepName]);

        if ( isset ($step[0]['step_name']) && trim ($step[0]['step_name']) !== "" )
        {
            return $step[0]['step_name'];
        }

        return false;
    }

    /**
     * gets previous task
     * @param type $currentStep
     * @param type $workflowId
     * @return boolean
     */
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
     * udates the status of a task
     * @param Elements $objElement
     * @param type $status
     * @param type $rejectionReason
     */
    public function updateStatus (\Elements $objElement, \Users $objUser, $status, $rejectionReason = '')
    {
        /** Todo
         * Pass in user object
         */
        $arrStepData = array(
            'claimed' => $objUser->getUsername (),
            "dateCompleted" => date ("Y-m-d H:i;s"),
            "status" => $status
        );

        if ( $rejectionReason !== "" )
        {
            $arrStepData['rejectionReason'] = $rejectionReason;
        }

        $objSteps = new \WorkflowStep (null, $objElement);

        if ( $status === "COMPLETE" )
        {
            $objSteps->complete ($objElement, $arrStepData, $objUser);
        }
        else
        {
            $objSteps->save ($objElement, $arrStepData, $objUser);
        }
    }

    /**
     * 
     * @param Elements $objElements
     */
    public function assignUsers (\Elements $objElements, \Users $objUser)
    {
        $arrStepData = array(
            'claimed' => $objUser->getUsername (),
            "dateCompleted" => date ("Y-m-d H:i;s"),
            "status" => "CLAIMED"
        );

        $objStep = new \WorkflowStep (null, $objElements);
        $objStep->assignUserToStep ($objElements, $objUser, $arrStepData);
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
    public function getCaseVariables ($app_uid, $pro_uid, $dynaFormUid = null, $act_uid = null, $app_index = null)
    {
        $this->isInteger ($app_uid, '$app_uid');

        $arrSystemVariables = getSystemVariables ();

        $objCase = $this->getCaseInfo ($pro_uid, $app_uid);

        if ( empty ($objCase) || !is_object ($objCase) )
        {
            return false;
        }

        $arrayCaseVariable = [];

        $arrFields = [];

        if ( !is_null ($dynaFormUid) )
        {
            $objForm = new \BusinessModel\Form (new \Task ($dynaFormUid));
            $arrAllFields = $objForm->getFields (true);



            if ( !empty ($arrAllFields) && is_array ($arrAllFields) )
            {
                $arrFields = array_keys ($arrAllFields);
            }
        }


        $arrayCaseVariable = $this->__getFieldsAndValuesByDynaFormAndAppData (
                $arrFields, $objCase, $arrSystemVariables
        );

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
    private function __getFieldsAndValuesByDynaFormAndAppData (array $form, \Elements $objElements, array $caseVariables)
    {
        try {
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
    public function addCasesOutputDocument ($projectId, $caseId, $stepId, $outputDocumentUid, $objUser)
    {
        try {
            $outputID = $outputDocumentUid;

            $oOutputDocument = new \OutputDocument();
            $aOD = $oOutputDocument->retrieveByPk ($outputID);
            $Fields = $this->getCaseVariables ($caseId, $projectId, $stepId);

            $sFilename = preg_replace ('[^A-Za-z0-9_]', '_', $this->replaceDataField ($aOD->getOutDocFilename (), $Fields));

            $objDocumentVersion = new \DocumentVersion (array());
            $lastDocVersion = $objDocumentVersion->getLastDocVersionByFilename ($sFilename);

            if ( ($aOD->getOutDocVersioning () ) )
            {
                $lastDocVersion ++;
                $objDocumentVersion->create (array("filename" => $sFilename, "document_id" => $aOD->getOutDocUid (), "document_type" => "OUTPUT"), $objUser);
            }

            $sFilename = $aOD->getOutDocUid () . "_" . $lastDocVersion;
            $pathOutput = OUTPUT_DOCUMENTS . $projectId . "/";
            $objFile = new \BusinessModel\FileUpload();
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

    public function replaceDataField ($sContent, $aFields)
    {
        $nrt = array("\n", "\r", "\t");
        $nrthtml = array("(n /)", "(r /)", "(t /)");

        $strContentAux = str_replace ($nrt, $nrthtml, $sContent);

        $iOcurrences = preg_match_all ('/\[([^\]]+)\]/', $sContent, $arrayMatch1, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        if ( $iOcurrences )
        {
            $arrayGrid = array();

            for ($i = 0; $i <= $iOcurrences - 1; $i++) {
                $arrayGrid[] = $arrayMatch1[1][$i][0];
            }

            $arrayGrid = array_unique ($arrayGrid);

            $strContentAux1 = null;
            $strContentAux1 = $strContentAux;

            foreach ($arrayGrid as $value) {
                if ( $value !== "" )
                {
                    $grdName = $value;

                    if ( isset ($aFields[$grdName]) && trim ($aFields[$grdName]) !== "" )
                    {

                        $newValue = str_replace ($nrt, $nrthtml, nl2br ($aFields[$grdName]));
                        $newValue = urlencode ($aFields[$grdName]);
                        $newValue = stripcslashes ($aFields[$grdName]);

                        $strContentAux1 = str_replace ("[" . $grdName . "]", $newValue, $strContentAux1);
                    }
                }
            }

            $strContentAux = $strContentAux1;
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

        $objUserFactory = new \BusinessModel\UsersFactory();
        $objUser = $objUserFactory->getUser ($sUserUID);

        $aObjectPermissions = $this->getAllObjectsFrom ($sProcessUID, $sApplicationUID, $sTasKUID, $objUser);
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

        $results = $this->objMysql->_select ("workflow.step", [], ["TAS_UID" => $sTasKUID]);

        $aOutputDocuments = array();


        foreach ($results as $aRow) {

            $oAppDocument = new \DocumentVersion();

            $lastVersion = $oAppDocument->getLastDocVersion ($aRow['STEP_UID_OBJ']);

            if ( $aRow['document_type'] == "OUTPUT_DOCUMENT" )
            {
                $aAux = $oAppDocument->load ($aRow['STEP_UID_OBJ'], $lastVersion);

                $oOutputDocument = new \OutputDocument();
                $aGields = $oOutputDocument->retrieveByPk ($aRow['STEP_UID_OBJ']);
                //OUTPUTDOCUMENT
                $outDocTitle = $aGields->getOutDocTitle ();
                switch ($aGields->getOutDocGenerate ()) {
                    case "PDF":
                        $fileDoc = 'javascript:alert("NO DOC")';
                        $fileDocLabel = " ";
                        $filePdf = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=pdf&random=' . rand ();
                        $filePdfLabel = ".pdf";
                        break;

                    case "DOC":
                        $fileDoc = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=doc&random=' . rand ();
                        $fileDocLabel = ".doc";
                        $filePdf = 'javascript:alert("NO PDF")';
                        $filePdfLabel = " ";
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
                                    $fileDocLabel = ".doc";
                                    $fileDoc = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        $filePdf = 'tasks/cases_ShowOutputDocument?a=' .
                                $aAux['id'] . '&v=' . $lastVersion . '&ext=pdf&random=' . rand ();
                        $filePdfLabel = ".pdf";

                        break;
                }

                try {
                    $oUser = new \BusinessModel\UsersFactory();
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
//                if ( in_array ($aRow['id'], $aObjectPermissions['OUTPUT_DOCUMENTS']) )
//                {
//                    if ( in_array ($aRow['id'], $aDelete['OUTPUT_DOCUMENTS']) )
//                    {
//                        $aFields['ID_DELETE'] = 'ID_DELETE';
//                    }
//                }
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

     * Obtain all user permits for Dynaforms, Input and output documents from some action [VIEW, BLOCK, etc...]

     * function getAllObjectsFrom ($PRO_UID, $APP_UID, $TAS_UID, $USR_UID, $ACTION)

     * @access public

     * @param  Project ID, Case ID, Step ID, User ID, Action

     * @return Array with user permissions for all objects types

     */
    public function getAllObjectsFrom ($PRO_UID, $APP_UID, $TAS_UID = "", \Users $objUser, $ACTION = "")
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $USR_UID = $objUser->getUserId ();

        $USER_PERMISSIONS = array();
        $INPUT = array();
        $OUTPUT = array();
        $GROUP_PERMISSIONS = array();

        $RESULT = array(
            "DYNAFORM" => array(),
            "Input" => array(),
            "Output" => array(),
            "CASES_NOTES" => 0,
            "MSGS_HISTORY" => array()

                /* ----------------------------------********--------------------------------- */
        );

        $objTask = new \Task();
        $objTask->setTasUid ($TAS_UID);

        $objPermissions = new \BusinessModel\StepPermission ($objTask);
        $arrPermissions = $objPermissions->getProcessPermissions ();

        if ( !empty ($arrPermissions) )
        {
            foreach ($arrPermissions as $objectType => $arrPermission) {
                foreach ($arrPermission as $permissionType => $permissions) {


                    if ( !empty ($permissions) )
                    {
                        $permission = explode (",", $permissions);

                        foreach ($permission as $perm) {

                            if ( trim ($perm) === trim ($USR_UID) )
                            {
                                $arrData = array("OP_ACTION" => $objectType, "OP_OBJ_TYPE" => $permissionType, "USR_UID" => $perm);

                                if ( in_array ($objectType, array("INPUT", "OUTPUT")) )
                                {
                                    if ( $objectType === "INPUT" )
                                    {
                                        array_push ($INPUT, $arrData);
                                    }
                                    else
                                    {
                                        array_push ($OUTPUT, $arrData);
                                    }
                                }
                                else
                                {
                                    if ( $permissionType == "user" )
                                    {
                                        array_push ($USER_PERMISSIONS, $arrData);
                                    }
                                    else
                                    {
                                        array_push ($GROUP_PERMISSIONS, $arrData);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $PERMISSIONS = array_merge ($USER_PERMISSIONS, $GROUP_PERMISSIONS, $INPUT, $OUTPUT);
        }

        if ( !empty ($PERMISSIONS) )
        {
            foreach ($PERMISSIONS as $row) {

                $ACTION = $row['OP_ACTION'];

                $sw_participate = false; // must be false for default
                $participants = $this->getUsersParticipatedInCase ($PRO_UID);

                if ( in_array ($objUser->getUsername (), $participants) && $ACTION == "master" )
                {
                    $sw_participate = true;
                }

                if ( $sw_participate === false )
                {
                    switch ($ACTION) {
                        case "RO":
                            if ( !in_array ($TAS_UID, $RESULT['DYNAFORM']) )
                            {

                                array_push ($RESULT['DYNAFORM'], $TAS_UID);
                            }
                            break;

                        case 'Input':
                        case 'Output':
                            if ( $ACTION == 'Input' )
                            {

                                $obj_type = "INPUT_DOCUMENT";
                            }
                            else
                            {

                                $obj_type = "OUTPUT_DOCUMENT";
                            }

                            $oDataset = $this->objMysql->_select ("workflow.step", [], ["TAS_UID" => $TAS_UID, "STEP_TYPE_OBJ" => $obj_type]);

                            if ( !empty ($oDataset) )
                            {
                                foreach ($oDataset as $aRow) {
                                    if ( !in_array ($aRow['STEP_UID_OBJ'], $RESULT[$ACTION]) )
                                    {
                                        array_push ($RESULT[$ACTION], $aRow['STEP_UID_OBJ']);
                                    }
                                }
                            }

                            break;
                    }

                    $RESULT['CASES_NOTES'] = 1;
                }
            }
        }

        $sw_participate = !isset ($sw_participate) ? false : true;

        return array(
            "DYNAFORMS" => $RESULT['DYNAFORM'],
            "INPUT_DOCUMENTS" => $RESULT['Input'],
            "OUTPUT_DOCUMENTS" => $RESULT['Output'],
            "CASES_NOTES" => $RESULT['CASES_NOTES'],
            "MSGS_HISTORY" => $RESULT['MSGS_HISTORY'],
            "MASTER_DYNAFORM" => (int) $sw_participate

                /* ----------------------------------********--------------------------------- */
        );
    }

    /**
     * Get Permissions, Participate, Access
     *
     * @param object $objUser
     * @param string $proUid
     * @param string $appUid
     * @return array Returns array with all access
     */
    public function userAuthorization (\Users $objUser, $proUid, $appUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $objCase = $this->getCaseInfo ($proUid, $appUid);

        $arrayAccess = array();

        //User has participated
        $aParticipated = $this->getUsersParticipatedInCase ($proUid);

        if ( !empty ($aParticipated) )
        {
            $arrayAccess['participated'] = in_array ($objUser->getUsername (), $aParticipated) ? true : false;
        }

        //User is supervisor
        if ( !empty ($objCase) && is_object ($objCase) )
        {
            $workflowId = $objCase->getWorkflow_id ();
            $supervisor = new \BusinessModel\ProcessSupervisor();
            $isSupervisor = $supervisor->isUserProcessSupervisor (new \Workflow ($workflowId), $objUser);

            $arrayAccess['supervisor'] = ($isSupervisor) ? true : false;


            $query = $this->objMysql->_select ("workflow.status_mapping", [], ["id" => $objCase->getCurrentStepId ()]);

            $stepId = $query[0]['TAS_UID'];

            $objectPermissions = $this->getAllObjectsFrom ($proUid, $appUid, $stepId, $objUser);

            //Object Permissions
            if ( count ($objectPermissions) > 0 )
            {
                foreach ($objectPermissions as $key => $value) {
                    $arrayAccess['objectPermissions'][$key] = $value;
                }
            }
        }

        return $arrayAccess;
    }

    public function hasPermission (\Users $objUser, $proUid, $appUid)
    {
        $userPermission = $this->userAuthorization ($objUser, $proUid, $appUid);

        if ( isset ($userPermission['objectPermissions']['DYNAFORMS']) && !empty ($userPermission['objectPermissions']['DYNAFORMS']) )
        {
            return true;
        }

        if ( isset ($userPermission['participated']) && (int) $userPermission['participated'] === 1 )
        {
            return true;
        }

        if ( isset ($userPermission['supervisor']) && (int) $userPermission['supervisor'] === 1 )
        {
            return true;
        }
        return true;
    }

    /**
     * Get Users to reassign
     *
     * @param string $userUid         Unique id of User (User logged)
     * @param string $taskUid         Unique id of Task
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * @return array Return Users to reassign
     */
    public function getUsersToReassign ($projectId, $caseId, \Users $objUser, \WorkflowStep $objStep, $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            $arrayUser = [];
            $numRecTotal = 0;
            //Set variables
            $stepId = $objStep->getStepId ();
            $workflowId = $objStep->getWorkflowId ();
            //Set variables
            $filterName = 'filter';

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter']) )
            {
                $filterName = isset ($arrayFilterData['filterOption']) ? $arrayFilterData['filterOption'] : '';
            }

            //Get data
            if ( !is_null ($limit) && $limit . '' == '0' )
            {
                //Return
                return [
                    'total' => $numRecTotal,
                    'start' => (int) ((!is_null ($start)) ? $start : 0),
                    'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                    $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter'])) ? $arrayFilterData['filter'] : '',
                    'data' => $arrayUser
                ];
            }
            //Set variables
            $processSupervisor = new \BusinessModel\ProcessSupervisor();
            $arrayResult = $processSupervisor->getProcessSupervisors ($workflowId, 'ASSIGNED', null, null, null, 'teams');

            $arrayGroupUid = array_merge (
                    array_map (function ($value) {
                        return $value['permission'];
                    }, $objStep->getGroupsOfTask ($stepId, 'team')), //Groups
                    array_map (function ($value) {
                        return $value['grp_uid'];
                    }, $arrayResult['data'])                 //ProcessSupervisor Groups
            );

            if ( empty ($arrayGroupUid) )
            {
                return false;
            }

            $arrayGroupUid = array_values (array_filter (array_unique ($arrayGroupUid)));

            $sqlTaskUser = "
            SELECT permission
            FROM   workflow.step_permission
            WHERE step_id = '%s' AND
             permission_type = 'user'";
            $sqlGroupUser = '
            SELECT usrid
            FROM   user_management.poms_users 
            WHERE  team_id IN (%s)
            ';
            $sqlProcessSupervisor = '
            SELECT user_id
            FROM   workflow.process_supervisors
            WHERE  workflow_id = \'%s\' AND
                   pu_type = \'%s\'
            ';
            $sqlUserToReassign = sprintf ($sqlTaskUser, $stepId);

            if ( !empty ($arrayGroupUid) )
            {
                $sqlUserToReassign .= ' UNION ' . sprintf ($sqlGroupUser, '\'' . implode ('\', \'', $arrayGroupUid) . '\'');
            }
            $sqlUserToReassign .= ' UNION ' . sprintf ($sqlProcessSupervisor, $workflowId, 'SUPERVISOR');

            $sql = "SELECT usrid, username, firstName, lastName 
                    FROM user_management.poms_users
                    WHERE usrid IN (" . $sqlUserToReassign . ")";

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter']) && trim ($arrayFilterData['filter']) != '' )
            {
                $search = (isset ($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : '';

                $sql .= "AND (username LIKE '%" . $search . "%' OR firstName LIKE '%" . $search . "%' OR lastName LIKE '%" . $search . "%' )";
            }

            $sql .= " AND status = 1";

            $result1 = $this->objMysql->_query ($sql);

            //Number records total
            $numRecTotal = count ($result1);
            //Query

            if ( !is_null ($sortField) && trim ($sortField) != '' )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "username";
            }

            if ( !is_null ($sortDir) && trim ($sortDir) != '' && strtoupper ($sortDir) == 'DESC' )
            {
                $sql .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $sql .= " ORDER BY " . $sortField . " ASC";
            }

            if ( !is_null ($limit) )
            {
                $sql .= " LIMIT " . ((int) ($limit));
            }

            if ( !is_null ($start) )
            {
                $sql .= " OFFSET " . ((int) ($start));
            }

            $results = $this->objMysql->_query ($sql);
            foreach ($results as $row) {
                $arrayUser[] = $row;
            }
            //Return
            return [
                'total' => $numRecTotal,
                'start' => (int) ((!is_null ($start)) ? $start : 0),
                'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                $filterName => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter'])) ? $arrayFilterData['filter'] : '',
                'data' => $arrayUser
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCasesForTask (\Flow $objFlow)
    {
        if ( trim ($objFlow->getId ()) === "" )
        {
            return false;
        }

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $rows = [];
        $dates = [];
        $total = 0;

        $workflowData = $this->objMysql->_select ("workflow.workflow_data");

        foreach ($workflowData as $WorkflowObject) {
            $obj = json_decode ($WorkflowObject['workflow_data'], true);
            $objAudit = json_decode ($WorkflowObject['audit_data'], true);

            if ( isset ($obj['elements']) )
            {
                foreach ($obj['elements'] as $elementId => $element) {

                    if ( isset ($element['current_step']) && $element['current_step'] === $objFlow->getId () )
                    {
                        $lastStep = end ($objAudit['elements'][$elementId]['steps']);


                        $rows[$WorkflowObject['object_id']] = $elementId;
                        $dates[$WorkflowObject['object_id']] = $lastStep['dateCompleted'];
                        $total++;
                    }
                }
            }
        }

        if ( !empty ($rows) )
        {
            return array(
                "total" => $total,
                "dates" => $dates,
                "rows" => $rows
            );
        }
    }

    public function doPostReassign (\Task $objTask, $data, $doReassign = true)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        if ( !is_array ($data) )
        {
            $isJson = is_string ($data) && is_array (json_decode ($data, true)) ? true : false;
            if ( $isJson )
            {
                $data = json_decode ($data, true);
            }
            else
            {
                return;
            }
        }

        $casesToReassign = $data['cases'];

        foreach ($casesToReassign as $val) {

            if ( $doReassign === true )
            {
                $appDelegation = $this->objMysql->_select ("workflow.workflow_data", [], ["object_id" => $val['parentId']]);
                $existDelegation = $this->validateReassignData ($objTask, $appDelegation, $val, 'DELEGATION_NOT_EXISTS');

                if ( !$existDelegation )
                {
                    return false;
                }

                //Will be not able reassign a case when is paused
                $flagPaused = $this->validateReassignData ($objTask, $appDelegation, $val, 'ID_REASSIGNMENT_PAUSED_ERROR');

                //Current users of OPEN DEL_INDEX thread
                $flagSameUser = $this->validateReassignData ($objTask, $appDelegation, $val, 'REASSIGNMENT_TO_THE_SAME_USER');


                if ( $flagPaused && $flagSameUser )
                {
                    return true;
                }

                return false;
            }


            if ( !isset ($val['user']) || !is_a ($val['user'], "Users") )
            {
                throw new \Exception ("No user provided");
            }

            //USER_NOT_ASSIGNED_TO_TASK
            $flagHasPermission = $this->validateReassignData ($objTask, array(), $val, 'USER_NOT_ASSIGNED_TO_TASK');

            return $flagHasPermission;
        }
    }

    /**
     * @param $appDelegation
     * @param $value
     * @param $data
     * @param string $type
     * @return bool
     */
    private function validateReassignData (\Task $objTask, $appDelegation = array(), $value, $type = 'DELEGATION_NOT_EXISTS')
    {
        $return = true;
        switch ($type) {
            case 'DELEGATION_NOT_EXISTS':

                $workflowData = json_decode ($appDelegation[0]['workflow_data'], true);

                if ( !isset ($workflowData['elements'][$value['elementId']]) )
                {
                    $this->messageResponse = [
                        'APP_UID' => $value['APP_UID'],
                        'DEL_INDEX' => $value['DEL_INDEX'],
                        'RESULT' => 0,
                        'STATUS' => $type
                    ];
                    $return = false;
                }

                break;


            case 'USER_NOT_ASSIGNED_TO_TASK':

                $stepResult = $this->objMysql->_select ("workflow.status_mapping", [], ["TAS_UID" => $objTask->getTasUid ()]);

                if ( !isset ($stepResult[0]) || empty ($stepResult[0]) )
                {
                    return false;
                }

                if ( !isset ($stepResult[0]['step_from']) )
                {
                    return false;
                }

                if ( !isset ($stepResult[0]['workflow_id']) )
                {
                    return false;
                }

                $permission = new StepPermission ($objTask);
                $supervisor = new ProcessSupervisor();
                $objUser = (new UsersFactory())->getUser ($value['user']->getUserId ());

                //$taskUid = $objFlow->getId ();
                $flagBoolean = $permission->checkUserOrGroupAssignedTask ($objUser);
                $flagps = $supervisor->isUserProcessSupervisor (new \Workflow ($stepResult[0]['workflow_id']), $objUser);

                if ( !$flagBoolean && !$flagps )
                {
                    $this->messageResponse = [
                        'APP_UID' => $value['elementId'],
                        'RESULT' => 0,
                        'STATUS' => 'USER_NOT_ASSIGNED_TO_TASK'
                    ];
                    $return = false;
                }
                break;
            case 'ID_REASSIGNMENT_PAUSED_ERROR':

                foreach ($appDelegation as $workflowObject) {
                    $audit = json_decode ($workflowObject['audit_data'], true);

                    if ( isset ($audit['elements'][$value['elementId']]['steps']) )
                    {
                        $lastEl = array_values (array_slice ($audit['elements'][$value['elementId']]['steps'], -1))[0];

                        if ( isset ($lastEl['status']) && in_array (trim ($lastEl['status']), array("HELD", "ABANDONED", "REJECT")) )
                        {
                            $this->messageResponse = [
                                'APP_UID' => $value['elementId'],
                                'RESULT' => 0,
                                'STATUS' => \G::LoadTranslation ('ID_REASSIGNMENT_PAUSED_ERROR')
                            ];
                            $return = false;
                        }
                    }
                }
                break;
            case 'REASSIGNMENT_TO_THE_SAME_USER':

                $audit = json_decode ($appDelegation[0]['audit_data'], true);
                $objUser = (new UsersFactory())->getUser ($value['user']->getUserId ());

                if ( isset ($audit['elements'][$value['elementId']]['steps'][$objTask->getStepId ()]) && isset ($audit['elements'][$value['elementId']]['steps'][$objTask->getStepId ()]['claimed']) )
                {
                    if ( trim ($objUser->getUsername ()) === trim ($audit['elements'][$value['elementId']]['steps'][$objTask->getStepId ()]['claimed']) )
                    {
                        $this->messageResponse = [
                            'APP_UID' => $value['elementId'],
                            'RESULT' => 1,
                            'STATUS' => 'SUCCESS'
                        ];
                        $return = false;
                    }
                }

                break;
        }
        return $return;
    }

}
