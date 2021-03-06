<?php

namespace BusinessModel;

class Lists
{

    use Validator;

    private $audit;
    private $projectId;
    private $lastStep;
    private $objMysql;
    private $parentId;
    private $lastStepInProcess;
    private $username;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    private function participated ()
    {

        if ( isset ($this->audit['steps']) && !empty ($this->audit['steps']) )
        {
            foreach ($this->audit['steps'] as $step) {
                if ( isset ($step['claimed']) )
                {
                    if ( $step['claimed'] == $this->username )
                    {
                        return array("parentId" => $this->parentId, "projectId" => $this->projectId);
                    }
                }
            }
        }
    }

    private function assigned ()
    {
        if ( isset ($this->lastStep['claimed']) && trim ($this->lastStep['claimed']) != "" )
        {
            if ( $this->lastStep['claimed'] == $this->username )
            {
                return array("parentId" => $this->parentId, "projectId" => $this->projectId);
            }
        }
    }

    private function paused ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "HELD" )
            {
                return array("parentId" => $this->parentId, "projectId" => $this->projectId);
            }
        }
    }

    private function rejected ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "REJECTED" || $this->lastStep['status'] == "REJECT" )
            {
                return array("parentId" => $this->parentId, "projectId" => $this->projectId);
            }
        }
    }

    private function abandoned ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "ABANDONED" )
            {
                return array("parentId" => $this->parentId, "projectId" => $this->projectId);
            }
        }
    }

    private function completed ($lastStepInProcess)
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "COMPLETE" && $lastStepInProcess == $this->lastStep['current_step'] )
            {
                return array("parentId" => $this->parentId, "projectId" => $this->projectId);
            }
        }
    }

    public function getCounters (Users $objUser = null)
    {
        $inboxCount = 0;
        $draftCount = 0;

        $response = array();

        if ( $objUser !== null && is_object ($objUser) )
        {
            $objNotificationsFactory = new NotificationsFactory();

            $inboxCount = $objNotificationsFactory->countNotifications (array("user" => $objUser->getUser_email (), "status" => 1), "ns.date_sent", "DESC");
            $draftCount = $objNotificationsFactory->countNotifications (array("user" => $objUser->getUser_email (), "status" => 2), "ns.date_sent", "DESC");
        }

        $allFunctions = array('abandoned', 'assigned', 'rejected', 'paused', 'completed', 'participated');
        $count = 0;
        foreach ($allFunctions as $allFunction) {
            $list = $this->loadLists ($allFunction);
            $response[$count]['count'] = count ($list);
            $response[$count]['item'] = strtoupper ($allFunction);

            $count++;
        }

        $count++;

        $response[$count]['count'] = $inboxCount;
        $response[$count]['item'] = "CASES_INBOX";

        $count++;

        $response[$count]['count'] = $draftCount;
        $response[$count]['item'] = "CASES_DRAFT";

        return $response;
    }

    private function getLastStep ($workflowId)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => 0, "workflow_id" => $workflowId));

        return $result[0]['id'];
    }

    public function loadList ($listName = '', \Users $objUser, $dataList = array())
    {
        if ( trim ($objUser->getUserId ()) === "" )
        {

            throw (new \Exception ("ID_USER_NOT_EXIST"));
        }

        $this->validateUserId ($objUser->getUserId ());
        $this->username = $objUser->getUsername ();

        if ( isset ($dataList['page']) && is_numeric ($dataList['page']) )
        {
            $page = $dataList['page'];
        }

        if ( isset ($dataList['page_limit']) && is_numeric ($dataList['page_limit']) )
        {
            $pageLimit = $dataList['page_limit'];
        }

        //error_reporting (0);

        $objNotificationsFactory = new \BusinessModel\NotificationsFactory();

        switch ($listName) {
            case "CASES_INBOX":
            case "inbox":
                return $objNotificationsFactory->getNotifications (array("status" => 1, "user" => $objUser->getUser_email ()));
                break;

            case "CASES_DRAFT":
            case "draft":
                return $objNotificationsFactory->getNotifications (array("status" => 2, "user" => $objUser->getUser_email ()));
                break;

            case "ABANDONED":
                $function = "abandoned";
                break;

            case "PARTICIPATED":
                $function = "participated";
                break;

            case "PAUSED":
                $function = "paused";
                break;

            case "COMPLETED":
                $function = "completed";
                break;

            case "UNASSIGNED":
                $function = "unassigned";
                break;

            case "ASSIGNED":
                $function = "assigned";
                break;

            case "REJECTED":
                $function = "rejected";
                break;
        }

        $arrLists = $this->loadLists ($function);

        if ( !empty ($arrLists) )
        {
            $arrProjects = $this->loadProjects ($arrLists);

            if ( isset ($page) && isset ($pageLimit) )
            {
                $arrrPaginated = $this->paginate (array_filter ($arrProjects), $pageLimit, $page);

                return $arrrPaginated;
            }

            return $arrProjects;
        }
    }

    public function loadLists ($function)
    {
        $arrLists = array();

        $results = $this->objMysql->_select ("workflow.workflow_data", array(), array());

        foreach ($results as $result) {
            $audit = json_decode ($result['audit_data'], true);
            $workflow = json_decode ($result['workflow_data'], true);

            $this->parentId = $result['object_id'];

            if ( !empty ($audit['elements']) && $audit['elements'] !== false )
            {
                foreach ($audit['elements'] as $elementId => $element) {

                    $this->projectId = $elementId;
                    $this->audit = $element;

                    $lastKey = $this->last ($this->audit);
                    $this->lastStep = $this->audit['steps'][$lastKey];

                    $currentStep = isset ($workflow['elements'][$elementId]) ? $workflow['elements'][$elementId] : [];

                    $this->lastStepInProcess = isset ($currentStep['workflow_id']) ? $this->getLastStep ($currentStep['workflow_id']) : '';

                    $list = $this->$function ();

                    if ( $list['parentId'] != $list['projectId'] )
                    {
                        $arrLists[$list['parentId']][] = $list['projectId'];
                    }
                }
            }
        }

        return $arrLists;
    }

    public function loadProjects ($list)
    {
        $arrProjects = [];

        foreach ($list as $parentId => $arrList) {
            foreach ($arrList as $id) {

                $objCases = new \BusinessModel\Cases();
                $objElement = $objCases->getCaseInfo ($parentId, $id);

                $arrProjects[] = $objElement;
            }
        }

        return $arrProjects;
    }

    private function last ($arrData)
    {
        end ($arrData['steps']);
        $key = key ($arrData['steps']);
        return $key;
    }

    private function paginate ($array, $intPageLimit, $page = 1)
    {
        $arrData = [];
        $intPageLimit = (int) $intPageLimit;
        $totalRows = (int) count ($array);

        $arrData['count']['page'] = $page;
        $arrData['count']['total'] = $totalRows;
        $arrData['count']['total_pages'] = (int) ceil ($totalRows / $intPageLimit);

        $page = (int) $page < 1 ? 1 : $page + 1;
        $start = ($page - 1) * $intPageLimit;
        $arrData['data'] = array_slice ($array, $start, $intPageLimit);

        return $arrData;
    }

}
