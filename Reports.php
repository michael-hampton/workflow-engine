<?php

class Reports
{

    private $objMysql;
    private $arrProjects = array();
    private $arrJson = array();
    private $arrUsersWithProjects = array();
    private $arrLateProjects = array();
    private $arrOnTime = array();
    private $arrCompletedProjects = array();
    private $incompleteProjects = array();
    private $arrProjectsForUser = array();
    private $arrAllProjects = array();

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function loadProjects ($arrProjects)
    {
        $this->arrProjects = $arrProjects;

        foreach ($arrProjects as $arrProject) {

            $this->arrAllProjects[$arrProject['id']] = $arrProject;
        }
    }
    
    public function getAllProjects()
    {
        return $this->arrAllProjects;
    }

    public function getProjectsForUser ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            if ( isset ($arrJSON['workflow_data']['elements']) )
            {
                foreach ($arrJSON['workflow_data']['elements'] as $key => $arrElement) {
                    if ( isset ($arrJSON['audit_data']['elements'][$key]['steps']) )
                    {
                        foreach ($arrJSON['audit_data']['elements'][$key]['steps'] as $step) {

                            if ( isset ($step['claimed']) && !empty ($step['claimed']) )
                            {
                                $claimed = $step['claimed'];

                                if ( $statusId != 4 && $statusId != 3 )
                                {
                                    if ( $claimed == $_SESSION['user']['username'] )
                                    {
                                        $this->arrProjectsForUser[] = $arrProject['id'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $this->arrProjectsForUser;
    }

    public function getCompletedProjects ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            if ( isset ($arrJSON['workflow_data']['elements']) )
            {
                foreach ($arrJSON['workflow_data']['elements'] as $key => $arrElement) {

                    if ( isset ($arrJSON['job']['completed_by']) )
                    {
                        $this->arrCompletedProjects[] = $arrProject['id'];
                    }
                }
            }
        }
        
        return $this->arrCompletedProjects;
    }

    public function getIncompleteProjects ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            if ( isset ($arrJSON['workflow_data']['elements']) )
            {
                foreach ($arrJSON['workflow_data']['elements'] as $key => $arrElement) {

                    if ( isset ($arrJSON['audit_data']['elements'][$key]['steps']) )
                    {
                        $this->incompleteProjects[] = $arrProject['id'];
                    }
                }
            }
        }
        
        return $this->incompleteProjects;
    }

    public function getLateProjects ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            $curdate = strtotime (date ("d-m-Y"));
            $mydate = strtotime (date ("d-m-Y", strtotime ($arrJSON['job']['dueDate'])));

            if ( $curdate > $mydate )
            {
                $this->arrLateProjects[] = $arrProject['id'];
            }
        }
        
        return $this->arrLateProjects;
    }

    public function getUsersWithProjects ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            if ( !isset ($this->arrUsersWithProjects[$arrJSON['job']['added_by']]) )
            {
                $this->arrUsersWithProjects[$arrJSON['job']['added_by']] = 1;
            }
            else
            {
                $this->arrUsersWithProjects[$arrJSON['job']['added_by']] = $this->arrUsersWithProjects[$arrJSON['job']['added_by']] + 1;
            }
        }
        
        return $this->arrUsersWithProjects;
    }

    public function getOnTimeProjects ()
    {
        foreach ($this->arrProjects as $arrProject) {

            $arrJSON = json_decode ($arrProject['step_data'], true);

            $curdate = strtotime (date ("d-m-Y"));
            $mydate = strtotime (date ("d-m-Y", strtotime ($arrJSON['job']['dueDate'])));

            if ( $curdate < $mydate )
            {
                $this->arrOnTime[] = $arrProject['id'];
            }
        }
        
        return $this->arrOnTime;
    }

}
