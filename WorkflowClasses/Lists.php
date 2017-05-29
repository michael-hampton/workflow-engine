<?php

class Lists
{

    use Validator;

    private $paused = array();
    private $pausedCounter = 0;
    private $completed = array();
    private $completedCounter = 0;
    private $unassigned = array();
    private $unassignedCounter = 0;
    private $assigned = array();
    private $assignedCounter = 0;
    private $abandoned = array();
    private $abandonedCounter = 0;
    private $rejected = array();
    private $rejectedCounter = 0;
    private $participated = array();
    private $participatedCounter = 0;
    private $audit;
    private $projectId;
    private $lastStep;
    private $arrParents = array();
    private $objMysql;
    private $parentId;
    private $lastStepInProcess;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
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

                        if ( !in_array ($this->projectId, $this->participated) )
                        {
                            $this->participated[$this->parentId][] = $this->projectId;
                            $this->participatedCounter++;
                        }
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
                if ( !in_array ($this->projectId, $this->assigned) )
                {
                    $this->assigned[$this->parentId][] = $this->projectId;
                    $this->assignedCounter++;
                }
            }
        }
    }

    private function paused ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "HELD" )
            {
                if ( !in_array ($this->projectId, $this->paused) )
                {
                    $this->paused[$this->parentId][] = $this->projectId;
                    $this->pausedCounter++;
                }
            }
        }
    }

    private function rejected ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "REJECTED" )
            {
                if ( !in_array ($this->projectId, $this->rejected) )
                {
                    $this->rejected[$this->parentId][] = $this->projectId;
                    $this->rejectedCounter++;
                }
            }
        }
    }

    private function abandoned ()
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "ABANDONED" )
            {
                if ( !in_array ($this->projectId, $this->abandoned) )
                {
                    $this->abandoned[$this->parentId][] = $this->projectId;
                    $this->abandonedCounter++;
                }
            }
        }
    }

    private function completed ($lastStepInProcess)
    {
        if ( isset ($this->lastStep['status']) && trim ($this->lastStep['status']) != "" )
        {
            if ( $this->lastStep['status'] == "COMPLETE" && $lastStepInProcess == $this->lastStep['current_step'] )
            {
                if ( !in_array ($this->projectId, $this->completed) )
                {
                    $this->completed[$this->parentId][] = $this->projectId;
                    $this->completedCounter++;
                }
            }
        }
    }

    public function getCounters (Users $objUser = null)
    {
        $inboxCount = 0;
        $draftCount = 0;
        
        $this->abandoned();
        $this->assigned();
        $this->completed();
        $this->participated();
        $this->paused();
        $this->rejected();
        //$this->unassigned();


        if ( $objUser !== null && is_object ($objUser) )
        {
            $objNotificationsFactory = new NotificationsFactory();

            $inboxCount = $objNotificationsFactory->countNotifications (array("user" => $objUser->getUser_email (), "status" => 1), "ns.date_sent", "DESC");
            $draftCount = $objNotificationsFactory->countNotifications (array("user" => $objUser->getUser_email (), "status" => 2), "ns.date_sent", "DESC");
        }

        $response = array(
            array('count' => $inboxCount, 'item' => 'CASES_INBOX'),
            array('count' => $draftCount, 'item' => 'CASES_DRAFT'),
            array('count' => $this->abandonedCounter, 'item' => 'ABANDONED'),
            array('count' => $this->participatedCounter, 'item' => 'PARTICIPATED'),
            array('count' => $this->pausedCounter, 'item' => 'PAUSED'),
            array('count' => $this->completedCounter, 'item' => 'COMPLETED'),
            array('count' => $this->unassignedCounter, 'item' => 'UNASSIGNED'),
            array('count' => $this->assignedCounter, 'item' => 'ASSIGNED'),
            array('count' => $this->rejectedCounter, 'item' => 'REJECTED')
        );

        return $response;
    }

    private function getLastStep ($workflowId)
    {
        $result = $this->objMysql->_select ("workflow.status_mapping", array(), array("step_to" => 0, "workflow_id" => $workflowId));

        return $result[0]['id'];
    }

    public function loadList ($listName = '', $dataList = array(), $total = false)
    {
        if ( !isset ($dataList["userId"]) )
        {

            throw (new Exception ("ID_USER_NOT_EXIST"));
        }
        else
        {

            $this->validateUserId ($dataList["userId"]);
            $userUid = $dataList["userId"];

            $objUsers = new UsersFactory ($userUid);
            $arrUser = $objUsers->getUsers ();
        }

        if ( isset ($dataList['page']) && is_numeric ($dataList['page']) )
        {
            $page = $dataList['page'];
        }

        if ( isset ($dataList['page_limit']) && is_numeric ($dataList['page_limit']) )
        {
            $pageLimit = $dataList['page_limit'];
        }

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
                }
            }
        }

        if ( $listName !== "" )
        {
            $objNotificationsFactory = new NotificationsFactory();

            switch ($listName) {
                case "CASES_INBOX":
                case "inbox":
                    return $objNotificationsFactory->getNotifications (array("status" => 1, "user" => $arrUser[0]->getUser_email ()));
                    break;

                case "CASES_DRAFT":
                case "draft":
                    return $objNotificationsFactory->getNotifications (array("status" => 2, "user" => $arrUser[0]->getUser_email ()));
                    break;

                case "ABANDONED":
                    $this->abandoned ();
                    $list['list'] = $this->abandoned;
                    break;

                case "PARTICIPATED":
                    $this->participated ();
                    $list['list'] = $this->participated;
                    break;

                case "PAUSED":
                    $this->paused ();
                    $list['list'] = $this->paused;
                    break;

                case "COMPLETED":
                    $this->completed ();
                    $list['list'] = $this->completed;
                    break;

                case "UNASSIGNED":
                    $this->unassigned ();
                    $list['list'] = $this->unassigned;
                    break;

                case "ASSIGNED":
                    $this->assigned ();
                    $list['list'] = $this->assigned;
                    break;

                case "REJECTED":
                    $this->rejected ();
                    $list['list'] = $this->rejected;
                    break;
            }

            if ( isset ($list['list']) && !empty ($list['list']) )
            {
                $arrProjects = $this->loadProjects ($list['list']);

                if ( isset ($page) && isset ($pageLimit) )
                {
                    $arrrPaginated = $this->paginate ($arrProjects, $pageLimit, $page);

                    return $arrrPaginated;
                }

                return $arrProjects;
            }
        }
       
        return $response;
    }

    public function loadProjects ($list)
    {
        $arrProjects = [];

        foreach ($list as $parentId => $arrList) {
            foreach ($arrList as $id) {
                $objCases = new Cases();
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
