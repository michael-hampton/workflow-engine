<?php
namespace BusinessModel;

class Lists
{

    use Validator;


    private $audit;
    private $projectId;
    private $lastStep;
    private $arrParents = array();
    private $objMysql;
    private $parentId;
    private $lastStepInProcess;

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
                    if ( $step['claimed'] == $_SESSION['user']['username'] )
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
            if ( $this->lastStep['claimed'] == $_SESSION['user']['username'] )
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
            if ( $this->lastStep['status'] == "REJECTED" )
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
            $list = $this->loadLists($allFunction);
            $response[$count]['count'] = count($list);
            $response[$count]['item'] = strtoupper($allFunction);
            
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

    public function loadList ($listName = '', $dataList = array())
    {
        if ( !isset ($dataList["userId"]) )
        {

            throw (new \Exception ("ID_USER_NOT_EXIST"));
        }
        else
        {

            $this->validateUserId ($dataList["userId"]);
            $userUid = $dataList["userId"];

            $objUsers = new \BusinessModel\UsersFactory();
            $arrUser = $objUsers->getUser($userUid);
        }

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
                return $objNotificationsFactory->getNotifications (array("status" => 1, "user" => $arrUser->getUser_email ()));
                break;

            case "CASES_DRAFT":
            case "draft":
                return $objNotificationsFactory->getNotifications (array("status" => 2, "user" => $arrUser[0]->getUser_email ()));
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
                $arrrPaginated = $this->paginate ($arrProjects, $pageLimit, $page);

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

                    $currentStep = $workflow['elements'][$elementId];
                    $this->lastStepInProcess = $this->getLastStep ($currentStep);

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

    public function last ($arrData)
    {
        end ($arrData['steps']);
        $key = key ($arrData['steps']);
        return $key;
    }

    function paginate ($array, $intPageLimit, $page = 1)
    {
        $intPageLimit = (int) $intPageLimit;
        $page = (int) $page;
        $totalRows = (int) count ($array);
        $_SESSION["pagination"]["current_page"] = $page;

        $page = $page < 1 ? 1 : $page + 1;

        $start = ($page - 1) * $intPageLimit;

        $_SESSION["pagination"]["total_pages"] = (int) ceil (($totalRows / $intPageLimit));
        $_SESSION["pagination"]["total_counter"] = $totalRows;

        return array_slice ($array, $start, $intPageLimit);
    }

}
