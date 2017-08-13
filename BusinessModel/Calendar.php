<?php

namespace BusinessModel;

class Calendar
{

    public $pmCalendarUid = '';
    public $pmCalendarData = array();

    use Validator;

    private $objMysql;

    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the name of a Calendar
     *
     * @param string $calendarName       Name
     * @param string $calendarUidExclude Unique id of Calendar to exclude
     *
     * return bool Return true if exists the name of a Calendar, false otherwise
     */
    public function existsName ($calendarName, $calendarUidExclude = "")
    {
        try {
            $arrParameters = array();

            $sql = "SELECT CALENDAR_UID FROM calendar.calendar WHERE CALENDAR_STATUS != 'DELETED'";

            if ( $calendarUidExclude != "" )
            {
                $sql .= " AND CALENDAR_UID != ?";
                $arrParameters[] = $calendarUidExclude;
            }

            $sql .= " AND CALENDAR_NAME = ?";
            $arrParameters[] = $calendarName;

            $results = $this->objMysql->_query ($sql, $arrParameters);

            if ( isset ($results[0]) && !empty ($results[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Transform Work Days
     *
     * @param mixed $workDays Work days
     * @param bool  $toDb     If is true transform data to represent it according to database, do the reverse otherwise
     *
     * return mixed Return Work days
     */
    public function workDaysTransformData ($workDays, $toDb = true)
    {
        try {
            $arrayDayName = array("SUN", "ALL");
            $arrayDay = array(7, 0);
            $arrayDayDb = array(0, 7);
            $data = (is_string ($workDays) && preg_match ("/\|/", $workDays)) ? explode ("|", $workDays) : $workDays;
            $type = "int";
            if ( is_array ($data) )
            {
                $data = implode ("|", $data);
                $type = "array";
            }
            if ( $toDb )
            {
                $data = str_replace ($arrayDay, $arrayDayName, $data);
                $data = str_replace ($arrayDayName, $arrayDayDb, $data);
            }
            else
            {
                $data = str_replace ($arrayDayDb, $arrayDayName, $data);
                $data = str_replace ($arrayDayName, $arrayDay, $data);
            }
            switch ($type) {
                case "int":
                    $data = (int) ($data);
                    break;
                case "array":
                    $data = explode ("|", $data);
                    foreach ($data as $key => $value) {
                        $data[$key] = (int) ($value);
                    }
                    break;
            }
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if doesn't exists the Calendar in table CALENDAR_DEFINITION
     *
     * @param string $calendarUid           Unique id of Calendar
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if doesn't exists the Calendar in table CALENDAR_DEFINITION
     */
    public function throwExceptionIfNotExistsCalendar ($calendarUid)
    {
        try {
            $obj = (new \CalendarDefinition())->retrieveByPK ($calendarUid);
            if ( !(is_object ($obj) && get_class ($obj) == "CalendarDefinition") )
            {
                throw new Exception ("ID_CALENDAR_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the name of a Calendar
     *
     * @param string $calendarName          Name
     * @param string $fieldNameForException Field name for the exception
     * @param string $calendarUidExclude    Unique id of Calendar to exclude
     *
     * return void Throw exception if exists the name of a Calendar
     */
    public function throwExceptionIfExistsName ($calendarName, $calendarUidExclude = "")
    {
        try {
            if ( $this->existsName ($calendarName, $calendarUidExclude) )
            {
                throw new \Exception ("ID_CALENDAR_NAME_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Calendar
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new Calendar created
     */
    public function create ($arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            unset ($arrayData["CAL_UID"]);

            //Verify data
            $this->throwExceptionIfExistsName ($arrayData["CALENDAR_NAME"]);
            if ( isset ($arrayData["CALENDAR_WORK_DAYS"]) && count ($arrayData["CALENDAR_WORK_DAYS"]) < 3 )
            {
                throw (new \Exception ("ID_MOST_AT_LEAST_3_DAY"));
            }

            //Set variables
            $arrayCalendarWorkHour = array();
            if ( isset ($arrayData["BUSINESS_DAY"]) )
            {
                foreach ($arrayData["BUSINESS_DAY"] as $value) {

                    if ( isset ($value['CALENDAR_BUSINESS_DAY']) )
                    {
                        if ( $value["CALENDAR_BUSINESS_DAY"] != 0 && !in_array ($value["CALENDAR_BUSINESS_DAY"], $arrayData["CALENDAR_WORK_DAYS"], true) )
                        {
                            throw new \Exception ("ID_VALUE_SPECIFIED_DOES_NOT_EXIST " . $value["CALENDAR_BUSINESS_DAY"]);
                        }
                        $arrayCalendarWorkHour[] = array(
                            "CALENDAR_BUSINESS_DAY" => $this->workDaysTransformData ($value["CALENDAR_BUSINESS_DAY"]),
                            "CALENDAR_BUSINESS_START" => $value["CALENDAR_BUSINESS_START"],
                            "CALENDAR_BUSINESS_END" => $value["CALENDAR_BUSINESS_END"]
                        );
                    }
                }
            }
            $arrayCalendarHoliday = array();
            if ( isset ($arrayData["HOLIDAY"]) )
            {
                foreach ($arrayData["HOLIDAY"] as $value) {
                    $arrayCalendarHoliday[] = array(
                        "CALENDAR_HOLIDAY_NAME" => $value["CALENDAR_HOLIDAY_NAME"],
                        "CALENDAR_HOLIDAY_START" => $value["CALENDAR_HOLIDAY_START"],
                        "CALENDAR_HOLIDAY_END" => $value["CALENDAR_HOLIDAY_END"]
                    );
                }
            }
            $arrayDataAux = array();
            $arrayDataAux["CALENDAR_NAME"] = $arrayData["CALENDAR_NAME"];
            $arrayDataAux["CALENDAR_DESCRIPTION"] = (isset ($arrayData["CALENDAR_DESCRIPTION"])) ? $arrayData["CALENDAR_DESCRIPTION"] : "";
            $arrayDataAux["CALENDAR_WORK_DAYS"] = $this->workDaysTransformData ($arrayData["CALENDAR_WORK_DAYS"]);
            $arrayDataAux["CALENDAR_STATUS"] = (isset ($arrayData["CALENDAR_STATUS"])) ? $arrayData["CALENDAR_STATUS"] : "ACTIVE";
            $arrayDataAux["BUSINESS_DAY"] = $arrayCalendarWorkHour;
            $arrayDataAux["HOLIDAY"] = $arrayCalendarHoliday;
            $arrayDataAux['CALENDAR_UID'] = '';
            //Create
            $calendarDefinition = new \CalendarDefinition();
            $calendarDefinition->saveCalendarInfo ($arrayDataAux);
            //Return
            //$arrayData = array_merge (array("CAL_UID" => $arrayDataAux["CALENDAR_UID"]), $arrayData);
            //return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Calendar
     *
     * @param string $calendarUid Unique id of Calendar
     * @param array  $arrayData   Data
     *
     * return array Return data of the Calendar updated
     */
    public function update ($calendarUid, $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            //Verify data
            $this->throwExceptionIfNotExistsCalendar ($calendarUid);

            if ( isset ($arrayData["CALENDAR_NAME"]) )
            {
                $this->throwExceptionIfExistsName ($arrayData["CALENDAR_NAME"], $calendarUid);
            }
            if ( isset ($arrayData["CALENDAR_WORK_DAYS"]) && count ($arrayData["CALENDAR_WORK_DAYS"]) < 3 )
            {
                throw (new \Exception ("ID_MOST_AT_LEAST_3_DAY"));
            }

            //Set variables
            $arrayCalendarData = $this->getCalendar ($calendarUid);
            $calendarWorkDays = (isset ($arrayData["CALENDAR_WORK_DAYS"])) ? $arrayData["CALENDAR_WORK_DAYS"] : $arrayCalendarData["CALENDAR_WORK_DAYS"];
            $arrayCalendarWorkHour = array();

            $arrayAux = (isset ($arrayData["BUSINESS_DAY"])) ? $arrayData["BUSINESS_DAY"] : $arrayData["BUSINESS_DAY"];

            foreach ($arrayAux as $value) {
                if ( isset ($arrayData["CALENDAR_WORK_HOUR"]) && $value["CALENDAR_BUSINESS_DAY"] != 0 && !in_array ($value["CALENDAR_BUSINESS_DAY"], $calendarWorkDays, true) )
                {
                    throw new \Exception ("ID_VALUE_SPECIFIED_DOES_NOT_EXIST");
                }

                $arrayCalendarWorkHour[] = array(
                    "CALENDAR_BUSINESS_DAY" => $this->workDaysTransformData ($value["CALENDAR_BUSINESS_DAY"]),
                    "CALENDAR_BUSINESS_START" => $value["CALENDAR_BUSINESS_START"],
                    "CALENDAR_BUSINESS_END" => $value["CALENDAR_BUSINESS_END"]
                );
            }

            $arrayCalendarHoliday = array();
            $arrayAux = (isset ($arrayData["HOLIDAY"])) ? $arrayData["HOLIDAY"] : $arrayCalendarData["HOLIDAY"];

            foreach ($arrayAux as $value) {
                $arrayCalendarHoliday[] = array(
                    "CALENDAR_HOLIDAY_NAME" => $value["CALENDAR_HOLIDAY_NAME"],
                    "CALENDAR_HOLIDAY_START" => $value["CALENDAR_HOLIDAY_START"],
                    "CALENDAR_HOLIDAY_END" => $value["CALENDAR_HOLIDAY_END"]
                );
            }


            $arrayDataAux = array();
            $arrayDataAux["CALENDAR_UID"] = $calendarUid;
            $arrayDataAux["CALENDAR_NAME"] = (isset ($arrayData["CALENDAR_NAME"])) ? $arrayData["CALENDAR_NAME"] : $arrayCalendarData["CALENDAR_NAME"];
            $arrayDataAux["CALENDAR_DESCRIPTION"] = (isset ($arrayData["CALENDAR_DESCRIPTION"])) ? $arrayData["CALENDAR_DESCRIPTION"] : $arrayCalendarData["CALENDAR_DESCRIPTION"];
            $arrayDataAux["CALENDAR_WORK_DAYS"] = $this->workDaysTransformData ($calendarWorkDays);
            $arrayDataAux["CALENDAR_STATUS"] = (isset ($arrayData["CALENDAR_STATUS"])) ? $arrayData["CALENDAR_STATUS"] : $arrayCalendarData["CALENDAR_STATUS"];
            $arrayDataAux["BUSINESS_DAY"] = $arrayCalendarWorkHour;
            $arrayDataAux["HOLIDAY"] = $arrayCalendarHoliday;
            //Update
            $calendarDefinition = new \CalendarDefinition();
            $calendarDefinition->saveCalendarInfo ($arrayDataAux);
            //Return

            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Calendar
     *
     * @param string $calendarUid Unique id of Calendar
     *
     * return void
     */
    public function delete ($calendarUid)
    {
        try {
            //Verify data
            $calendarDefinition = new \CalendarDefinition();
            $this->throwExceptionIfNotExistsCalendar ($calendarUid);
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("USER");
            $nU = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("TASK");
            $nT = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("PROCESS");
            $nP = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            if ( $nU + $nT + $nP > 0 )
            {
                throw (new \Exception ("ID_MSG_CANNOT_DELETE_CALENDAR"));
            }
            //Delete
            $calendarDefinition->deleteCalendar ($calendarUid);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Calendar
     *
     * return object
     */
    public function getCalendarCriteria ()
    {
        try {

            $sql = "SELECT CALENDAR_UID, CALENDAR_NAME, CALENDAR_DESCRIPTION, CALENDAR_WORK_DAYS, CALENDAR_STATUS, CALENDAR_CREATE_DATE, CALENDAR_UPDATE_DATE FROM calendar.calendar";
            $sql .= " WHERE CALENDAR_STATUS != 'DELETED'";

            return $sql;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    
    /**
     * Get data of a Calendar from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Calendar
     */
    public function getCalendarDataFromRecord ($record)
    {

        try {

            $calendarHolidays = new \CalendarHolidays();
            $arrayCalendarWorkHour = array();
            $calendarBusinessHours = new \CalendarBusinessHours();
            $arrayData = $calendarBusinessHours->getCalendarBusinessHours ($record["CALENDAR_UID"]);


            foreach ($arrayData as $value) {

                $calendarBusinessHours = new \CalendarBusinessHours();
                $calendarBusinessHours->setCalendarBusinessDay ($this->workDaysTransformData ($value["CALENDAR_BUSINESS_DAY"] . "", false));
                $calendarBusinessHours->setCalendarBusinessStart ($value["CALENDAR_BUSINESS_START"] . "");
                $calendarBusinessHours->setCalendarBusinessEnd ($value["CALENDAR_BUSINESS_END"] . "");

                $arrayCalendarWorkHour[] = $calendarBusinessHours;
            }

            $arrayCalendarHoliday = array();
            $arrayData = $calendarHolidays->getCalendarHolidays ($record["CALENDAR_UID"]);


            foreach ($arrayData as $value) {

                $calendarHolidays = new \CalendarHolidays();
                $calendarHolidays->setCalendarHolidayName ($value["CALENDAR_HOLIDAY_NAME"] . "");
                $calendarHolidays->setCalendarHolidayStart ($value["CALENDAR_HOLIDAY_START"] . "");
                $calendarHolidays->setCalendarHolidayEnd ($value["CALENDAR_HOLIDAY_END"] . "");

                $arrayCalendarHoliday[] = $calendarHolidays;
            }

            $dateTime = new \DateTime ($record["CALENDAR_CREATE_DATE"]);
            $dateCreate = $dateTime->format ("Y-m-d H:i:s");
            $dateTime = new \DateTime ($record["CALENDAR_UPDATE_DATE"]);
            $dateUpdate = $dateTime->format ("Y-m-d H:i:s");

            $arrayCalendarWorkDays = array();
            foreach ($this->workDaysTransformData ($record["CALENDAR_WORK_DAYS"] . "", false) as $value) {
                $arrayCalendarWorkDays[$value] = "ID_WEEKDAY_" . (($value != 7) ? $value : 0);
            }

            $objCalendarDefinition = new \CalendarDefinition();

            $objCalendarDefinition->setCalendarUid ($record["CALENDAR_UID"]);
            $objCalendarDefinition->setCalendarName ($record["CALENDAR_NAME"]);
            $objCalendarDefinition->setCalendarDescription ($record["CALENDAR_DESCRIPTION"] . "");
            $objCalendarDefinition->setCalendarWorkDays ($arrayCalendarWorkDays);
            $objCalendarDefinition->setCalendarStatus ($record["CALENDAR_STATUS"]);
            //$objCalendarDefinition->setWorkHours ($arrayCalendarWorkHour);
            //$objCalendarDefinition->setHolidays ($arrayCalendarHoliday);
            $objCalendarDefinition->setCalendarCreateDate ($dateCreate);
            $objCalendarDefinition->setCalendarUpdateDate ($dateUpdate);
            $objCalendarDefinition->setTotalUsers ((int) ($record["CALENDAR_TOTAL_USERS"]));
            $objCalendarDefinition->setTotalProcesses ((int) ($record["CALENDAR_TOTAL_PROCESSES"]));

            $arrTest = array(array("definition" => $objCalendarDefinition, "holidays" => $arrayCalendarHoliday, "work_hours" => $arrayCalendarWorkHour));

            return $arrTest;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Calendars
     *
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Calendars
     */
    public function getCalendars ($arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayCalendar = array();
            //Verify data
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayCalendar;
            }
            //Set variables
            $calendar = new \CalendarDefinition();
            $arrayTotalUsersByCalendar = $calendar->getAllCounterByCalendar ("USER");
            $arrayTotalProcessesByCalendar = $calendar->getAllCounterByCalendar ("PROCESS");
            $arrayTotalTasksByCalendar = $calendar->getAllCounterByCalendar ("TASK");
            //SQL
            $criteria = $this->getCalendarCriteria ();

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {
                $criteria .= " AND (CALENDAR_NAME LIKE '%" . $arrayFilterData["filter"] . "%' OR CALENDAR_DESCRIPTION LIKE '%" . $arrayFilterData["filter"] . "%') ";
            }
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                //$sortField = strtoupper ($sortField);
                //$sortField = (isset ($this->arrayFieldDefinition[$sortField]["fieldName"])) ? $this->arrayFieldDefinition[$sortField]["fieldName"] : $sortField;
                switch ($sortField) {
                    case "CALENDAR_UID":
                    case "CALENDAR_NAME":
                    case "CALENDAR_DESCRIPTION":
                    case "CALENDAR_WORK_DAYS":
                    case "CALENDAR_STATUS":
                    case "CALENDAR_CREATE_DATE":
                    case "CALENDAR_UPDATE_DATE":
                        $sortField = $sortField;
                        break;
                    default:
                        $sortField = "CALENDAR_NAME";
                        break;
                }
            }
            else
            {
                $sortField = "CALENDAR_NAME";
            }
            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $criteria .= " ORDER BY " . $sortField . " ASC";
            }

            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . (int) $limit;
            }

            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . (int) $start;
            }

            $results = $this->objMysql->_query ($criteria);

            foreach ($results as $row) {
                $row["CALENDAR_TOTAL_USERS"] = (isset ($arrayTotalUsersByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalUsersByCalendar[$row["CALENDAR_UID"]] : 0;
                $row["CALENDAR_TOTAL_PROCESSES"] = (isset ($arrayTotalProcessesByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalProcessesByCalendar[$row["CALENDAR_UID"]] : 0;
                $row["CALENDAR_TOTAL_TASKS"] = (isset ($arrayTotalTasksByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalTasksByCalendar[$row["CALENDAR_UID"]] : 0;

                $calendarObj = $this->getCalendarDataFromRecord ($row);

                $arrayCalendar[] = $calendarObj[0];
            }

            //Return
            return $arrayCalendar;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Calendar
     *
     * @param string $calendarUid Unique id of Calendar
     *
     * return array Return an array with data of a Calendar
     */
    public function getCalendar ($calendarUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsCalendar ($calendarUid);
            //Get data
            //Set variables
            $calendar = new \CalendarDefinition();
            $arrayTotalUsersByCalendar = $calendar->getAllCounterByCalendar ("USER");
            $arrayTotalProcessesByCalendar = $calendar->getAllCounterByCalendar ("PROCESS");
            $arrayTotalTasksByCalendar = $calendar->getAllCounterByCalendar ("TASK");

            //SQL
            $criteria = $this->getCalendarCriteria ();
            $criteria .= " AND CALENDAR_UID = ?";
            $arrParameters = array($calendarUid);
            $results = $this->objMysql->_query ($criteria, $arrParameters);
            $row = $results[0];

            $row["CALENDAR_TOTAL_USERS"] = (isset ($arrayTotalUsersByCalendar[$calendarUid])) ? $arrayTotalUsersByCalendar[$calendarUid] : 0;
            $row["CALENDAR_TOTAL_PROCESSES"] = (isset ($arrayTotalProcessesByCalendar[$calendarUid])) ? $arrayTotalProcessesByCalendar[$calendarUid] : 0;
            $row["CALENDAR_TOTAL_TASKS"] = (isset ($arrayTotalTasksByCalendar[$calendarUid])) ? $arrayTotalTasksByCalendar[$calendarUid] : 0;

            //Return
            return $this->getCalendarDataFromRecord ($row);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function validateCalendarInfo ($fields, $defaultCalendar)
    {

        try {
            //Validate if Working days are Correct
            //Minimun 3 ?
            $workingDays = explode ('|', $fields['CALENDAR_WORK_DAYS']);
            if ( count ($workingDays) < 3 )
            {
                throw (new \Exception ('You must define at least 3 Working Days!'));
            }
            //Validate that all Working Days have Bussines Hours
            if ( count ($fields ['BUSINESS_DAY']) < 1 )
            {
                throw (new \Exception ('You must define at least one Business Day for all days'));
            }
            $workingDaysOK = array();
            foreach ($workingDays as $day) {
                $workingDaysOK[$day] = false;
            }
            $sw_all = false;
            foreach ($fields ['BUSINESS_DAY'] as $businessHours) {
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
                throw (new \Exception ('Not all working days have their correspondent business day'));
            }
            //Validate Holidays
            return $fields;
        } catch (Exception $e) {
            //print $e->getMessage();
            //$this->addCalendarLog('!!!!!!! BAD CALENDAR DEFINITION. '.$e->getMessage());
            $defaultCalendar ['CALENDAR_WORK_DAYS'] = '1|2|3|4|5';
            $defaultCalendar ['CALENDAR_WORK_DAYS_A'] = explode ('|', '1|2|3|4|5');
            return $defaultCalendar;
        }
    }
}
