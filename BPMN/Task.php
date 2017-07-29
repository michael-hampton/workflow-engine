<?php

class Task extends BaseTask
{

    private $stepName;
    private $objMysql;
    private $stepId;

    public function __construct ($stepId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $stepId !== null )
        {
            $this->setTasUid ($stepId);
            $this->stepId = $stepId;
        }
    }

    public function getStepName ()
    {
        return $this->stepName;
    }

    public function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    public function removeTask ()
    {
        $this->objMysql->_delete ("workflow.task", array("TAS_UID" => $this->stepId));
    }

    public function getTask ($step)
    {
        $check = $this->objMysql->_select ("workflow.task", array(), array("TAS_UID" => $step));

        return $check;
    }

    /**
     * create a new Task
     *
     * @param      array $aData with new values
     * @return     string
     */
    public function create ($aData)
    {

        try {

            if ( isset ($aData['TAS_UID']) )
            {
                $sTaskUID = $aData['TAS_UID'];
                $this->setTasUid ($sTaskUID);
            }



            $this->setProUid ($aData['PRO_UID']);

            $this->setTasTitle ((isset ($aData['TAS_TITLE']) ? $aData['TAS_TITLE'] : ''));
            $this->setTasDescription ((isset ($aData['TAS_DESCRIPTION']) ? $aData['TAS_DESCRIPTION'] : ''));
            $this->setTasDefTitle ("");
            $this->setTasDefMessage ("");
            $this->setTasDefSubjectMessage ("");
            $this->setTasType ("NORMAL");
            $this->setTasDuration ("1");
            $this->setTasDelayType ("");
            $this->setTasTypeDay ("");
            $this->setTasTimeunit ("DAYS");
            $this->setTasPriorityVariable ("");
            $this->setTasAssignType ("BALANCED");
            $this->setTasAssignVariable ("@@SYS_NEXT_USER_TO_BE_ASSIGNED");
            $this->setTasAssignLocation ("FALSE");
            $this->setTasAssignLocationAdhoc ("FALSE");
            $this->setTasLastAssigned ("0");
            $this->setTasUser ("0");
            $this->setTasCanUpload ("FALSE");
            $this->setTasViewUpload ("FALSE");
            $this->setTasViewAdditionalDocumentation ("FALSE");
            $this->setTasCanCancel ("FALSE");
            $this->setTasOwnerApp ("FALSE");
            $this->setTasCanPause ("FALSE");
            $this->setTasCanSendMessage ("TRUE");
            $this->setTasCanDeleteDocs ("FALSE");
            $this->setTasSelfService ("FALSE");
            $this->setTasSendLastEmail ("FALSE");

            $this->setTasGroupVariable ("");

            if ( isset ($aData['TAS_ID']) )
            {
                $this->setTasId ($aData['TAS_ID']);
            }

            $this->loadObject ($aData);

            if ( $this->validate () )
            {
                //$this->setTasTitleContent ((isset ($aData['TAS_TITLE']) ? $aData['TAS_TITLE'] : ''));
                //$this->setTasDescriptionContent ((isset ($aData['TAS_DESCRIPTION']) ? $aData['TAS_DESCRIPTION'] : ''));

                $sTaskUID = $this->save ();

                return $sTaskUID;
            }
            else
            {
                $e = new Exception ("Failed Validation in class " . get_class ($this) . ".");
                $e->aValidationFailures = $this->getValidationFailures ();

                throw ($e);
            }
        } catch (Exception $e) {


            throw ($e);
        }
    }

    /**
     * Get the tas_title column value.
     * @return     string
     */
    public function getTasTitleContent ()
    {
        if ( $this->getTasUid () == "" )
        {
            throw (new Exception ("Error in getTasTitle, the getTasUid() can't be blank"));
        }


        return $this->tas_title_content;
    }

    /**
     * Set the tas_title column value.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setTasTitleContent ($v)
    {
        if ( $this->getTasUid () == "" )
        {
            throw (new Exception ("Error in setTasTitle, the getTasUid() can't be blank"));
        }

        $v = isset ($v) ? ((string) $v) : '';


        if ( $v === "" )
        {
            $this->tas_title_content = $v;
        }
    }

    /**
     * This value goes in the content table
     * @var        string
     */
    protected $tas_description_content = '';

    /**
     * Get the tas_description column value.
     * @return     string
     */
    public function getTasDescriptionContent ()
    {
        if ( $this->getTasUid () == "" )
        {
            throw (new Exception ("Error in getTasDescription, the getTasUid() can't be blank"));
        }


        return $this->tas_description_content;
    }

    /**
     * Set the tas_description column value.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setTasDescriptionContent ($v)
    {
        if ( $this->getTasUid () == "" )
        {
            throw (new Exception ("Error in setTasDescription, the getTasUid() can't be blank"));
        }

        $v = isset ($v) ? ((string) $v) : '';


        if ( $v === "" )
        {
            $this->tas_description_content = $v;
        }
    }

    public function load ($TasUid)
    {
        try {
            $oRow = $this->retrieveByPK ($TasUid);
            if ( $oRow !== false )
            {
                return $oRow;
                /* ----------------------------------********--------------------------------- */
                ///////
                return $this;
            }
            else
            {
                throw (new Exception ("The row '" . $TasUid . "' in table TASK doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function updateTaskProperties ($fields)
    {

        try {

            $objFlow = $this->retrieveByPk ($fields['TAS_UID']);

            if ( array_key_exists ("TAS_DESCRIPTION", $fields) )
            {

                $contentResult += $this->setTasDescription ($fields["TAS_DESCRIPTION"]);
            }

            if ( array_key_exists ("TAS_DEF_TITLE", $fields) )
            {

                $contentResult += $this->setTasDefTitle ($fields["TAS_DEF_TITLE"]);
            }

            if ( array_key_exists ("TAS_DEF_DESCRIPTION", $fields) )
            {

                $contentResult += $this->setTasDefDescription ($fields["TAS_DEF_DESCRIPTION"]);
            }

            if ( array_key_exists ("TAS_DEF_PROC_CODE", $fields) )
            {

                $contentResult += $this->setTasDefProcCode ($fields["TAS_DEF_PROC_CODE"]);
            }

            if ( array_key_exists ("TAS_DEF_MESSAGE", $fields) )
            {
                $contentResult += $this->setTasDefMessage (trim ($fields["TAS_DEF_MESSAGE"]));
            }

            if ( array_key_exists ("TAS_DEF_SUBJECT_MESSAGE", $fields) )
            {
                $contentResult += $this->setTasDefSubjectMessage (trim ($fields["TAS_DEF_SUBJECT_MESSAGE"]));
            }

            if ( array_key_exists ("TAS_CALENDAR", $fields) )
            {
                $this->setTasCalendar ($fields['TAS_UID'], $fields["TAS_CALENDAR"]);
            }

            if ( !isset ($fields['TAS_UID']) || trim ($fields['TAS_UID']) === "" || !is_numeric ($fields['TAS_UID']) )
            {
                return false;
            }

            $objFlow->loadObject ($fields);

            if ( $objFlow->validate () )
            {
                $objFlow->save ();
            }
            else
            {
                $msg = '';
                foreach ($objFlow->getValidationFailures () as $strMessage) {
                    $msg .= $strMessage . "<br/>";
                }
                throw (new Exception ('The row cannot be created! ' . $msg));
            }

            //$this->objMysql->_update ("workflow.status_mapping", ["step_condition" => json_encode ($conditions)], ["id" => $fields['TAS_UID']]);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function setTasCalendar ($taskUid, $calendarUid)
    {

        //Save Calendar ID for this process

        $calendarObj = new CalendarDefinition();

        $calendarObj->assignCalendarTo ($taskUid, $calendarUid, 'TASK');
    }

    public function retrieveByPk ($pk)
    {

        $result = $this->objMysql->_select ("workflow.task", [], ["TAS_UID" => $pk]);
        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objFlow = new Task();
        $objFlow->setTasTimeunit ($result[0]['TAS_TIMEUNIT']);
        $objFlow->setTasDuration ($result[0]['TAS_DURATION']);

        $objCalendar = new CalendarAssignment();
        $objAssignment = $objCalendar->retrieveByPk ($result[0]['TAS_UID'], "TASK");

        if ( is_object ($objAssignment) )
        {
            $objFlow->setCalendarUid ($objAssignment->getCalendarUid ());
        }

        $objFlow->setTasTypeDay ($result[0]['TAS_TYPE_DAY']);
        $objFlow->setTasUid ($pk);
        $objFlow->setTasAssignType ($result[0]['TAS_ASSIGN_TYPE']);
        $objFlow->setTasSelfserviceTime ($result[0]['TAS_SELFSERVICE_TIME']);
        $objFlow->setTasSelfserviceTimeUnit ($result[0]['TAS_SELFSERVICE_TIME_UNIT']);
        $objFlow->setTasSelfserviceTriggerUid ($result[0]['TAS_SELFSERVICE_TRIGGER_UID']);
        $objFlow->setTasSelfserviceExecution ($result[0]['TAS_SELFSERVICE_EXECUTION']);

        return $objFlow;
    }

    /**
     * Get the assigned groups of a task
     *
     * @param string $sTaskUID
     * @param integer $iType
     * @return array
     */
    public function getGroupsOfTask ($sTaskUID, $iType)
    {
        try {
            $aGroups = array();
            $sql = "SELECT * FROM workflow.TASK_USER tu LEFT JOIN user_management.poms_users u ON u.team_id = tu.USR_UID WHERE tu.TAS_UID = ? AND TU_TYPE = ? AND TU_RELATION = 2 AND u.status = 1";
            $arrParameters = array($sTaskUID, $iType);
            $results = $this->objMysql->_query ($sql, $arrParameters);

            foreach ($results as $aRow) {
                $aGroups[] = $aRow;
                $oDataset->next ();
            }

            return $aGroups;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Get the assigned users of a task
     *
     * @param string $sTaskUID
     * @param integer $iType
     * @return array
     */
    public function getUsersOfTask ($sTaskUID, $iType)
    {
        try {
            $aUsers = array();
            $sql = "SELECT * FROM workflow.TASK_USER tu LEFT JOIN user_management.poms_users u ON u.usrid = tu.USR_UID WHERE tu.TAS_UID = ? AND TU_TYPE = ? AND TU_RELATION = 1 AND u.status = 1";
            $arrParameters = array($sTaskUID, $iType);
            $results = $this->objMysql->_query ($sql, $arrParameters);

            foreach ($results as $aRow) {
                $aUsers[] = $aRow;
            }
            return $aUsers;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
