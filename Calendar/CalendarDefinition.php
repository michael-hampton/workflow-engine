<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarDefinition
 *
 * @author michael.hampton
 */
class CalendarDefinition extends BaseCalendarDefinition
{

    public $calendarLog = '';
    private $objMysql;

    public function __construct ()
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();
    }

    public function getCalendarList ($onlyActive = false, $arrayMode = false)
    {
        $sql = "SELECT CALENDAR_UID, 
                        CALENDAR_NAME, 
                        CALENDAR_CREATE_DATE, 
                        CALENDAR_UPDATE_DATE, 
                        CALENDAR_DESCRIPTION, 
                        CALENDAR_STATUS,
                CASE CALENDAR_UID
                    WHEN '00000000000000000000000000000001' THEN 'delete'
                    ELSE ''
                 END as 'DELETABLE'
               FROM calendar.calendar
               WHERE 1=1";

        // Note: This list doesn't show deleted items (STATUS = DELETED)
        if ( $onlyActive )
        {
            // Show only active. Used on assignment lists
            $sql .= " AND CALENDAR_STATUS = 'ACTIVE'";
        }
        else
        {
            $sql .= " AND CALENDAR_STATUS IN (" . implode (",", array("ACTIVE", "INACTIVE")) . ")";
            // Show Active and Inactive calendars. USed in main list
        }

        $sql .= " AND CALENDAR_UID != 'xx'";


        if ( !$arrayMode )
        {
            return $sql;
        }
        else
        {
            $results = $this->objMysql->_query ($sql);
            $return['criteria'] = $sql;
            $return['array'] = $results;
            return $return;
        }
    }

    public function getCalendarInfo ($CalendarUid)
    {
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid);
        $defaultCalendar['CALENDAR_UID'] = "00000000000000000000000000000001";
        $defaultCalendar['CALENDAR_NAME'] = 'DEFAULT_CALENDAR';
        $defaultCalendar['CALENDAR_CREATE_DATE'] = date ("Y-m-d");
        $defaultCalendar['CALENDAR_UPDATE_DATE'] = date ("Y-m-d");
        $defaultCalendar['CALENDAR_DESCRIPTION'] = "DEFAULT";
        $defaultCalendar['CALENDAR_STATUS'] = "ACTIVE";
        $defaultCalendar['CALENDAR_WORK_DAYS'] = "1|2|3|4|5";
        $defaultCalendar['CALENDAR_WORK_DAYS'] = explode ("|", "1|2|3|4|5");
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_DAY'] = 7;
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_START'] = "09:00";
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_END'] = "17:00";
        $defaultCalendar['HOLIDAY'] = array();
        if ( (is_object ($tr) && get_class ($tr) == 'CalendarDefinition' ) )
        {
            $fields['CALENDAR_UID'] = $tr->getCalendarUid ();
            $fields['CALENDAR_NAME'] = $tr->getCalendarName ();
            $fields['CALENDAR_CREATE_DATE'] = $tr->getCalendarCreateDate ();
            $fields['CALENDAR_UPDATE_DATE'] = $tr->getCalendarUpdateDate ();
            $fields['CALENDAR_DESCRIPTION'] = $tr->getCalendarDescription ();
            $fields['CALENDAR_STATUS'] = $tr->getCalendarStatus ();
            $fields['CALENDAR_WORK_DAYS'] = $tr->getCalendarWorkDays ();
            $fields['CALENDAR_WORK_DAYS_A'] = explode ("|", $tr->getCalendarWorkDays ());
        }
        else
        {
            $fields = $defaultCalendar;
            $this->saveCalendarInfo ($fields);
            $fields['CALENDAR_WORK_DAYS'] = "1|2|3|4|5";
            $fields['CALENDAR_WORK_DAYS_A'] = explode ("|", "1|2|3|4|5");
            $tr = $this->retrieveByPK ($CalendarUid);
        }
        $CalendarBusinessHoursObj = new CalendarBusinessHours();
        $CalendarBusinessHours = $CalendarBusinessHoursObj->getCalendarBusinessHours ($CalendarUid);
        $fields['BUSINESS_DAY'] = $CalendarBusinessHours;
        $CalendarHolidaysObj = new CalendarHolidays();
        $CalendarHolidays = $CalendarHolidaysObj->getCalendarHolidays ($CalendarUid);
        $fields['HOLIDAY'] = $CalendarHolidays;
        $fields = $this->validateCalendarInfo ($fields, $defaultCalendar);
        //********************
        return $fields;
    }

    //for edit
    public function getCalendarInfoE ($CalendarUid)
    {
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid);
        $defaultCalendar['CALENDAR_UID'] = "00000000000000000000000000000001";
        $defaultCalendar['CALENDAR_NAME'] = 'DEFAULT_CALENDAR';
        $defaultCalendar['CALENDAR_CREATE_DATE'] = date ("Y-m-d");
        $defaultCalendar['CALENDAR_UPDATE_DATE'] = date ("Y-m-d");
        $defaultCalendar['CALENDAR_DESCRIPTION'] = 'DEFAULT_CALENDAR';
        $defaultCalendar['CALENDAR_STATUS'] = "ACTIVE";
        $defaultCalendar['CALENDAR_WORK_DAYS'] = "1|2|3|4|5";
        $defaultCalendar['CALENDAR_WORK_DAYS'] = explode ("|", "1|2|3|4|5");
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_DAY'] = 7;
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_START'] = "09:00";
        $defaultCalendar['BUSINESS_DAY'][1]['CALENDAR_BUSINESS_END'] = "17:00";
        $defaultCalendar['HOLIDAY'] = array();
        if ( (is_object ($tr) && get_class ($tr) == 'CalendarDefinition' ) )
        {
            $fields['CALENDAR_UID'] = $tr->getCalendarUid ();
            $fields['CALENDAR_NAME'] = $tr->getCalendarName ();
            $fields['CALENDAR_CREATE_DATE'] = $tr->getCalendarCreateDate ();
            $fields['CALENDAR_UPDATE_DATE'] = $tr->getCalendarUpdateDate ();
            $fields['CALENDAR_DESCRIPTION'] = $tr->getCalendarDescription ();
            $fields['CALENDAR_STATUS'] = $tr->getCalendarStatus ();
            $fields['CALENDAR_WORK_DAYS'] = $tr->getCalendarWorkDays ();
            $fields['CALENDAR_WORK_DAYS_A'] = explode ("|", $tr->getCalendarWorkDays ());
        }
        else
        {
            $fields = $defaultCalendar;
            $this->saveCalendarInfo ($fields);
            $fields['CALENDAR_WORK_DAYS'] = "1|2|3|4|5";
            $fields['CALENDAR_WORK_DAYS_A'] = explode ("|", "1|2|3|4|5");
            $tr = $this->retrieveByPK ($CalendarUid);
        }
        $CalendarBusinessHoursObj = new CalendarBusinessHours();
        $CalendarBusinessHours = $CalendarBusinessHoursObj->getCalendarBusinessHours ($CalendarUid);
        $fields['BUSINESS_DAY'] = $CalendarBusinessHours;
        $CalendarHolidaysObj = new CalendarHolidays();
        $CalendarHolidays = $CalendarHolidaysObj->getCalendarHolidays ($CalendarUid);
        $fields['HOLIDAY'] = $CalendarHolidays;
        // $fields=$this->validateCalendarInfo($fields, $defaultCalendar); //********************
        return $fields;
    }

    //end for edit
    public function validateCalendarInfo ($fields, $defaultCalendar)
    {
        try {
            //Validate if Working days are Correct
            //Minimun 3 ?
            $workingDays = explode ("|", $fields['CALENDAR_WORK_DAYS']);
            if ( count ($workingDays) < 3 )
            {
                throw (new Exception ("You must define at least 3 Working Days!"));
            }
            //Validate that all Working Days have Bussines Hours
            if ( count ($fields['BUSINESS_DAY']) < 1 )
            {
                throw (new Exception ("You must define at least one Business Day for all days"));
            }
            $workingDaysOK = array();
            foreach ($workingDays as $day) {
                $workingDaysOK[$day] = false;
            }
            $sw_all = false;
            foreach ($fields['BUSINESS_DAY'] as $businessHours) {
                if ( ($businessHours['CALENDAR_BUSINESS_DAY'] == 7 ) )
                {
                    $sw_all = true;
                }
                elseif ( (in_array ($businessHours['CALENDAR_BUSINESS_DAY'], $workingDays) ) )
                {
                    $workingDaysOK[$businessHours['CALENDAR_BUSINESS_DAY']] = true;
                }
            }
            $sw_days = true;
            foreach ($workingDaysOK as $day => $sw_day) {
                $sw_days = $sw_days && $sw_day;
            }
            if ( !($sw_all || $sw_days) )
            {
                throw (new Exception ("Not all working days have their correspondent business day"));
            }
            //Validate Holidays
            return $fields;
        } catch (Exception $e) {
            //print $e->getMessage();
            $this->addCalendarLog ("!!!!!!! BAD CALENDAR DEFINITION. " . $e->getMessage ());
            $defaultCalendar['CALENDAR_WORK_DAYS'] = "1|2|3|4|5";
            $defaultCalendar['CALENDAR_WORK_DAYS_A'] = explode ("|", "1|2|3|4|5");
            return $defaultCalendar;
        }
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("calendar.calendar", [], ["CALENDAR_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objCalendarDefinition = new CalendarDefinition();
        $objCalendarDefinition->setCalendarCreateDate ($result[0]['CALENDAR_CREATE_DATE']);
        $objCalendarDefinition->setCalendarName ($result[0]['CALENDAR_NAME']);
        $objCalendarDefinition->setCalendarUpdateDate ($result[0]['CALENDAR_UPDATE_DATE']);

        if ( isset ($result[0]['CALENDAR_DESCRIPTION']) )
        {
            $objCalendarDefinition->setCalendarDescription ($result[0]['CALENDAR_DESCRIPTION']);
        }

        $objCalendarDefinition->setCalendarStatus ($result[0]['CALENDAR_STATUS']);
        $objCalendarDefinition->setCalendarWorkDays ($result[0]['CALENDAR_WORK_DAYS']);
        $objCalendarDefinition->setCalendarUid ($pk);

        return $objCalendarDefinition;
    }

    public function saveCalendarInfo ($aData)
    {

        $CalendarUid = $aData['CALENDAR_UID'];
        $CalendarName = $aData['CALENDAR_NAME'];
        $CalendarDescription = $aData['CALENDAR_DESCRIPTION'];
        $CalendarStatus = isset ($aData['CALENDAR_STATUS']) ? $aData['CALENDAR_STATUS'] : "INACTIVE";
        $defaultCalendars[] = '00000000000000000000000000000001';
        if ( in_array ($aData['CALENDAR_UID'], $defaultCalendars) )
        {
            $CalendarStatus = 'ACTIVE';
        }

        $CalendarWorkDays = isset ($aData['CALENDAR_WORK_DAYS']) ? implode ("|", $aData['CALENDAR_WORK_DAYS']) : "";

        //if exists the row in the database will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid);

        if ( !(is_object ($tr) && get_class ($tr) == 'CalendarDefinition') )
        {
            $tr = new CalendarDefinition();
            $tr->setCalendarCreateDate ('now');
        }

        $tr->setCalendarUid ($CalendarUid);
        $tr->setCalendarName ($CalendarName);
        $tr->setCalendarUpdateDate ('now');
        $tr->setCalendarDescription ($CalendarDescription);
        $tr->setCalendarStatus ($CalendarStatus);
        $tr->setCalendarWorkDays ($CalendarWorkDays);

        if ( $tr->validate () )
        {
            // we save it, since we get no validation errors, or do whatever else you like.
            $CalendarUid = $tr->save ();


            //Calendar Business Hours Save code.
            //First Delete all current records
            $CalendarBusinessHoursObj = new CalendarBusinessHours();
            $CalendarBusinessHoursObj->deleteAllCalendarBusinessHours ($CalendarUid);

            //Save all the sent records
            foreach ($aData['BUSINESS_DAY'] as $key => $objData) {
                $objData['CALENDAR_UID'] = $CalendarUid;
                $CalendarBusinessHoursObj->saveCalendarBusinessHours ($objData);
            }
            //Holiday Save code.
            //First Delete all current records
            $CalendarHolidayObj = new CalendarHolidays();
            $CalendarHolidayObj->deleteAllCalendarHolidays ($CalendarUid);
            //Save all the sent records
            foreach ($aData['HOLIDAY'] as $key => $objData) {
                if ( ($objData['CALENDAR_HOLIDAY_NAME'] != "") && ($objData['CALENDAR_HOLIDAY_START'] != "") && ($objData['CALENDAR_HOLIDAY_END'] != "") )
                {
                    $objData['CALENDAR_UID'] = $CalendarUid;
                    $CalendarHolidayObj->saveCalendarHolidays ($objData);
                }
            }
        }
        else
        {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $tr->getValidationFailures ();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage () . "<br/>";
            }
            //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
        }
    }

    public function deleteCalendar ($CalendarUid)
    {
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid);
        if ( !(is_object ($tr) && get_class ($tr) == 'CalendarDefinition') )
        {
            //
            return false;
        }
        $defaultCalendars[] = '00000000000000000000000000000001';
        if ( in_array ($tr->getCalendarUid (), $defaultCalendars) )
        {
            return false;
        }
        $tr->setCalendarStatus ('DELETED');
        $tr->setCalendarUpdateDate ('now');
        if ( $tr->validate () )
        {
            // we save it, since we get no validation errors, or do whatever else you like.
            $tr->save ();
        }
        else
        {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $tr->getValidationFailures ();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage () . "<br/>";
            }
            //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
        }
    }

    public function getCalendarFor ($userUid, $proUid, $tasUid, $sw_validate = true)
    {
        //Default Calendar
        $calendarUid = "00000000000000000000000000000001";
        $calendarOwner = "DEFAULT";
        //Load User,Task and Process calendars (if exist)

        $sql = "SELECT CALENDAR_UID, USER_UID, OBJECT_TYPE FROM calendar.calendar_assignees WHERE USER_UID IN (" . implode (",", array($userUid, $proUid, $tasUid)) . ")";
        $results = $this->objMysql->_query ($sql);
        
        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        $calendarArray = array();

        foreach ($results as $aRow) {
            if ( $aRow['USER_UID'] == $userUid )
            {
                $calendarArray['USER'] = $aRow['CALENDAR_UID'];
            }
            if ( $aRow['USER_UID'] == $proUid )
            {
                $calendarArray['PROCESS'] = $aRow['CALENDAR_UID'];
            }
            if ( $aRow['USER_UID'] == $tasUid )
            {
                $calendarArray['TASK'] = $aRow['CALENDAR_UID'];
            }
        }

        if ( isset ($calendarArray['USER']) )
        {
            $calendarUid = $calendarArray['USER'];
            $calendarOwner = "USER";
        }
        elseif ( isset ($calendarArray['PROCESS']) )
        {
            $calendarUid = $calendarArray['PROCESS'];
            $calendarOwner = "PROCESS";
        }
        elseif ( isset ($calendarArray['TASK']) )
        {
            $calendarUid = $calendarArray['TASK'];
            $calendarOwner = "TASK";
        }
        //print "<h1>$calendarUid</h1>";
        if ( $sw_validate )
        {
            $calendarDefinition = $this->getCalendarInfo ($calendarUid);
        }
        else
        {
            $calendarDefinition = $this->getCalendarInfoE ($calendarUid);
        }
        $calendarDefinition['CALENDAR_APPLIED'] = $calendarOwner;

        return $calendarDefinition;
    }

    public function assignCalendarTo ($objectUid, $calendarUid, $objectType)
    {
        $objCalendarAssignment = new CalendarAssignment();
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $objCalendarAssignment->retrieveByPk ($objectUid, $objectType);

        if ( $calendarUid != "" )
        {
            if ( !(is_object ($tr) && get_class ($tr) == 'CalendarAssignment') )
            {
                $tr = new CalendarAssignment();
            }
            $tr->setObjectUid ($objectUid);
            $tr->setCalendarUid ($calendarUid);
            $tr->setObjectType ($objectType);
            if ( $tr->validate () )
            {
                // we save it, since we get no validation errors, or do whatever else you like.
                $tr->save ();
            }
            else
            {
                // Something went wrong. We can now get the validationFailures and handle them.
                $msg = '';
                $validationFailuresArray = $tr->getValidationFailures ();
                foreach ($validationFailuresArray as $message) {
                    $msg .= $message . "<br/>";
                }
                //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
            }
        }
        else
        {
            //Delete record
            if ( (is_object ($tr) && get_class ($tr) == 'CalendarAssignment' ) )
            {
                $this->objMysql->_delete ("calendar.calendar_assignees", [], ["OBJECT_TYPE" => $objectType, "USER_UID" => $objectUid]);
            }
        }
    }

    //Added by Qennix
    //Counts all users,task,process by calendar
    public function getAllCounterByCalendar ($type)
    {
        $sql = "SELECT CALENDAR_UID, COUNT(*) AS CNT FROM calendar.calendar_assignees
                WHERE OBJECT_TYPE = ?
                GROUP BY CALENDAR_UID";

        $results = $this->objMysql->_query ($sql, [$type]);

        $aCounter = array();

        foreach ($results as $row) {
            $aCounter[$row['CALENDAR_UID']] = $row['CNT'];
        }
        return $aCounter;
    }

    public function calendarName ($calendarUid)
    {
        $tr = $this->retrieveByPK ($calendarUid);
        if ( (is_object ($tr) && get_class ($tr) == 'CalendarDefinition' ) )
        {
            return $tr->getCalendarName ();
        }
        return false;
    }

}
