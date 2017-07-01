<?php

namespace BusinessModel;

class Calendar
{

    public $pmCalendarUid = '';
    public $pmCalendarData = array();

    use Validator;

    private $arrayFieldDefinition = array(
        "CAL_UID" => array("fieldName" => "CALENDAR_UID", "type" => "string", "required" => false, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "calendarUid"),
        "CAL_NAME" => array("fieldName" => "CALENDAR_NAME", "type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "calendarName"),
        "CAL_DESCRIPTION" => array("fieldName" => "CALENDAR_DESCRIPTION", "type" => "string", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "calendarDescription"),
        "CAL_WORK_DAYS" => array("fieldName" => "CALENDAR_WORK_DAYS", "type" => "array", "required" => true, "empty" => false, "defaultValues" => array(1, 2, 3, 4, 5, 6, 7), "fieldNameAux" => "calendarWorkDays"),
        "CAL_STATUS" => array("fieldName" => "CALENDAR_STATUS", "type" => "string", "required" => false, "empty" => false, "defaultValues" => array("ACTIVE", "INACTIVE"), "fieldNameAux" => "calendarStatus"),
        "CAL_WORK_HOUR" => array("fieldName" => "CALENDAR_WORK_HOUR", "type" => "array", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "calendarWorkHour"),
        "CAL_HOLIDAY" => array("fieldName" => "CALENDAR_HOLIDAY", "type" => "array", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "calendarHoliday"),
        "CAL_CREATE_DATE" => array("fieldName" => "CALENDAR_CREATE_DATE", "type" => "datetime", "required" => false, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "calendarCreateDate"),
        "CAL_UPDATE_DATE" => array("fieldName" => "CALENDAR_UPDATE_DATE", "type" => "datetime", "required" => false, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "calendarUpdateDate")
    );
    private $arrayWorkHourFieldDefinition = array(
        "DAY" => array("type" => "int", "required" => true, "empty" => false, "defaultValues" => array(0, 1, 2, 3, 4, 5, 6, 7), "fieldNameAux" => "day"),
        'HOUR_START' => array('type' => 'hour', 'required' => true, 'empty' => false, 'defaultValues' => array(), 'fieldNameAux' => 'hourStart', 'regexp' => '/^(?:[0-1]\d|2[0-3])\:[0-5]\d$/'),
        'HOUR_END' => array('type' => 'hour', 'required' => true, 'empty' => false, 'defaultValues' => array(), 'fieldNameAux' => 'hourEnd', 'regexp' => '/^(?:[0-1]\d|2[0-3])\:[0-5]\d$/')
    );
    private $arrayHolidayFieldDefinition = array(
        "NAME" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "name"),
        "DATE_START" => array("type" => "date", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "dateStart"),
        "DATE_END" => array("type" => "date", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "dateEnd")
    );
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
                    if ( $value["CALENDAR_BUSINESS_DAY"] != 0 && !in_array ($value["CALENDAR_BUSINESS_DAY"], $arrayData["CALENDAR_WORK_DAYS"], true) )
                    {
                        throw new \Exception ("ID_VALUE_SPECIFIED_DOES_NOT_EXIST");
                    }
                    $arrayCalendarWorkHour[] = array(
                        "CALENDAR_BUSINESS_DAY" => $this->workDaysTransformData ($value["CALENDAR_BUSINESS_DAY"]),
                        "CALENDAR_BUSINESS_START" => $value["CALENDAR_BUSINESS_START"],
                        "CALENDAR_BUSINESS_END" => $value["CALENDAR_BUSINESS_END"]
                    );
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
            $arrayDataAux['CALENDAR_UID'] = rand (5, 15);
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
            $process = new \ProcessMaker\BusinessModel\Process();
            $validator = new \ProcessMaker\BusinessModel\Validator();
            $validator->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $validator->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            $arrayData = \G::array_change_key_case2 ($arrayData, CASE_UPPER);
            //Verify data
            $this->throwExceptionIfNotExistsCalendar ($calendarUid);
            $process->throwExceptionIfDataNotMetFieldDefinition ($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, false);
            if ( isset ($arrayData["CAL_NAME"]) )
            {
                $this->throwExceptionIfExistsName ($arrayData["CAL_NAME"], $this->arrayFieldNameForException["calendarName"], $calendarUid);
            }
            if ( isset ($arrayData["CAL_WORK_DAYS"]) && count ($arrayData["CAL_WORK_DAYS"]) < 3 )
            {
                throw (new \Exception (\G::LoadTranslation ("ID_MOST_AT_LEAST_3_DAY")));
            }
            if ( isset ($arrayData["CAL_WORK_HOUR"]) )
            {
                foreach ($arrayData["CAL_WORK_HOUR"] as $value) {
                    $process->throwExceptionIfDataNotMetFieldDefinition ($value, $this->arrayWorkHourFieldDefinition, $this->arrayWorkHourFieldNameForException, true);
                }
            }
            if ( isset ($arrayData["CAL_HOLIDAY"]) )
            {
                foreach ($arrayData["CAL_HOLIDAY"] as $value) {
                    $process->throwExceptionIfDataNotMetFieldDefinition ($value, $this->arrayHolidayFieldDefinition, $this->arrayHolidayFieldNameForException, true);
                }
            }
            //Set variables
            $arrayCalendarData = \G::array_change_key_case2 ($this->getCalendar ($calendarUid), CASE_UPPER);
            $calendarWorkDays = (isset ($arrayData["CAL_WORK_DAYS"])) ? $arrayData["CAL_WORK_DAYS"] : array_keys ($arrayCalendarData["CAL_WORK_DAYS"]);
            $arrayCalendarWorkHour = array();
            $arrayAux = (isset ($arrayData["CAL_WORK_HOUR"])) ? $arrayData["CAL_WORK_HOUR"] : $arrayCalendarData["CAL_WORK_HOUR"];
            foreach ($arrayAux as $value) {
                if ( isset ($arrayData["CAL_WORK_HOUR"]) && $value["DAY"] != 0 && !in_array ($value["DAY"], $calendarWorkDays, true) )
                {
                    throw new \Exception (\G::LoadTranslation ("ID_VALUE_SPECIFIED_DOES_NOT_EXIST", array($this->arrayWorkHourFieldNameForException["day"], $this->arrayFieldNameForException["calendarWorkDays"])));
                }
                $arrayCalendarWorkHour[] = array(
                    "CALENDAR_BUSINESS_DAY" => $this->workDaysTransformData ($value["DAY"]),
                    "CALENDAR_BUSINESS_START" => $value["HOUR_START"],
                    "CALENDAR_BUSINESS_END" => $value["HOUR_END"]
                );
            }
            $arrayCalendarHoliday = array();
            $arrayAux = (isset ($arrayData["CAL_HOLIDAY"])) ? $arrayData["CAL_HOLIDAY"] : $arrayCalendarData["CAL_HOLIDAY"];
            foreach ($arrayAux as $value) {
                $arrayCalendarHoliday[] = array(
                    "CALENDAR_HOLIDAY_NAME" => $value["NAME"],
                    "CALENDAR_HOLIDAY_START" => $value["DATE_START"],
                    "CALENDAR_HOLIDAY_END" => $value["DATE_END"]
                );
            }
            $arrayDataAux = array();
            $arrayDataAux["CALENDAR_UID"] = $calendarUid;
            $arrayDataAux["CALENDAR_NAME"] = (isset ($arrayData["CAL_NAME"])) ? $arrayData["CAL_NAME"] : $arrayCalendarData["CAL_NAME"];
            $arrayDataAux["CALENDAR_DESCRIPTION"] = (isset ($arrayData["CAL_DESCRIPTION"])) ? $arrayData["CAL_DESCRIPTION"] : $arrayCalendarData["CAL_DESCRIPTION"];
            $arrayDataAux["CALENDAR_WORK_DAYS"] = $this->workDaysTransformData ($calendarWorkDays);
            $arrayDataAux["CALENDAR_STATUS"] = (isset ($arrayData["CAL_STATUS"])) ? $arrayData["CAL_STATUS"] : $arrayCalendarData["CAL_STATUS"];
            $arrayDataAux["BUSINESS_DAY"] = $arrayCalendarWorkHour;
            $arrayDataAux["HOLIDAY"] = $arrayCalendarHoliday;
            //Update
            $calendarDefinition = new \CalendarDefinition();
            $calendarDefinition->saveCalendarInfo ($arrayDataAux);
            //Return
            if ( !$this->formatFieldNameInUppercase )
            {
                $arrayData = \G::array_change_key_case2 ($arrayData, CASE_LOWER);
            }
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
            $this->throwExceptionIfNotExistsCalendar ($calendarUid, $this->arrayFieldNameForException["calendarUid"]);
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("USER");
            $nU = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("TASK");
            $nT = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            $arrayAux = $calendarDefinition->getAllCounterByCalendar ("PROCESS");
            $nP = (isset ($arrayAux[$calendarUid])) ? $arrayAux[$calendarUid] : 0;
            if ( $nU + $nT + $nP > 0 )
            {
                throw (new \Exception (\G::LoadTranslation ("ID_MSG_CANNOT_DELETE_CALENDAR")));
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

    /*     * ************SLA classes************** */

    public function dashCalculateDate ($iniDate, $duration, $formatDuration, $calendarData = array())
    {

        if ( strtoupper ($formatDuration) == 'DAYS' )
        {
            $duration = $duration * $calendarData['HOURS_FOR_DAY'];
        }
        if ( strtoupper ($formatDuration) == 'MINUTES' )
        {
            $duration = $duration / 60;
        }
        $hoursDuration = (float) $duration;
        $newDate = $iniDate;

        while ($hoursDuration > 0) {
            $newDate = $this->dashGetIniDate ($newDate, $calendarData);

            $rangeWorkHour = $this->dashGetRangeWorkHours ($newDate, $calendarData['BUSINESS_DAY']);
            $onlyDate = (date ('Y-m-d', strtotime ($newDate))) . ' ' . $rangeWorkHour['END'];

            if ( (((float) $hoursDuration) >= ((float) $rangeWorkHour['TOTAL'])) ||
                    ((strtotime ($onlyDate) - strtotime ($newDate)) < (((float) $hoursDuration) * 3600))
            )
            {
                $secondRes = (float) (strtotime ($onlyDate) - strtotime ($newDate));
                $newDate = $onlyDate;
                $hoursDuration -= (float) ($secondRes / 3600);
            }
            else
            {
                $newDate = date ('Y-m-d H:i:s', strtotime ('+' . (((float) $hoursDuration) * 3600) . ' seconds', strtotime ($newDate)));
                $hoursDuration = 0;
            }
        }

        return $newDate;
    }

    public function dashGetRangeWorkHours ($date, $workHours)
    {
        $auxIniDate = explode (' ', $date);
        $timeDate = $auxIniDate['1'];
        $timeDate = (float) str_replace (':', '', ((strlen ($timeDate) == 8) ? $timeDate : $timeDate . ':00'));
        $weekDay = date ('w', strtotime ($date));

        $workHoursDay = array();
        $tempWorkHoursDay = array();

        foreach ($workHours as $value) {
            if ( $value['CALENDAR_BUSINESS_DAY'] == $weekDay )
            {
                $rangeWorkHour = array();
                $timeStart = $value['CALENDAR_BUSINESS_START'];
                $timeEnd = $value['CALENDAR_BUSINESS_END'];
                $rangeWorkHour['START'] = ((strlen ($timeStart) == 8) ? $timeStart : $timeStart . ':00');
                $rangeWorkHour['END'] = ((strlen ($timeEnd) == 8) ? $timeEnd : $timeEnd . ':00');

                $workHoursDay[] = $rangeWorkHour;
            }

            if ( $value['CALENDAR_BUSINESS_DAY'] == '7' )
            {
                $rangeWorkHour = array();
                $timeStart = $value['CALENDAR_BUSINESS_START'];
                $timeEnd = $value['CALENDAR_BUSINESS_END'];
                $rangeWorkHour['START'] = ((strlen ($timeStart) == 8) ? $timeStart : $timeStart . ':00');
                $rangeWorkHour['END'] = ((strlen ($timeEnd) == 8) ? $timeEnd : $timeEnd . ':00');

                $tempWorkHoursDay[] = $rangeWorkHour;
            }
        }

        if ( !(count ($workHoursDay)) )
        {
            $workHoursDay = $tempWorkHoursDay;
        }

        foreach ($workHoursDay as $value) {
            $iniTime = (float) str_replace (':', '', $value['START']);
            $finTime = (float) str_replace (':', '', $value['END']);

            if ( ($iniTime <= $timeDate) && ($timeDate <= $finTime) )
            {
                //pr($finTime .' menos '.$iniTime .' = '.($finTime-$iniTime));
                $value['TOTAL'] = (($finTime - $iniTime) / 10000);
                return $value;
            }
        }
        return false;
    }

    public function dashNextWorkHours ($date, $weekDay, $workHours = array())
    {
        $auxIniDate = explode (' ', $date);

        $timeDate = $auxIniDate['1'];
        $timeDate = (float) str_replace (':', '', ((strlen ($timeDate) == 8) ? $timeDate : $timeDate . ':00'));
        $nextWorkHours = array();

        $workHoursDay = array();
        $tempWorkHoursDay = array();

        foreach ($workHours as $value) {
            if ( $value['CALENDAR_BUSINESS_DAY'] == $weekDay )
            {
                $rangeWorkHour = array();
                $timeStart = $value['CALENDAR_BUSINESS_START'];
                $timeEnd = $value['CALENDAR_BUSINESS_END'];
                $rangeWorkHour['START'] = ((strlen ($timeStart) == 8) ? $timeStart : $timeStart . ':00');
                $rangeWorkHour['END'] = ((strlen ($timeEnd) == 8) ? $timeEnd : $timeEnd . ':00');

                $workHoursDay[] = $rangeWorkHour;
            }

            if ( $value['CALENDAR_BUSINESS_DAY'] == '7' )
            {
                $rangeWorkHour = array();
                $timeStart = $value['CALENDAR_BUSINESS_START'];
                $timeEnd = $value['CALENDAR_BUSINESS_END'];
                $rangeWorkHour['START'] = ((strlen ($timeStart) == 8) ? $timeStart : $timeStart . ':00');
                $rangeWorkHour['END'] = ((strlen ($timeEnd) == 8) ? $timeEnd : $timeEnd . ':00');

                $tempWorkHoursDay[] = $rangeWorkHour;
            }
        }

        if ( !(count ($workHoursDay)) )
        {
            $workHoursDay = $tempWorkHoursDay;
        }

        $countHours = count ($workHoursDay);
        if ( $countHours )
        {
            for ($i = 1; $i < $countHours; $i++) {
                for ($j = 0; $j < $countHours - $i; $j++) {
                    $dataft = (float) str_replace (':', '', $workHoursDay[$j]['START']);
                    $datasc = (float) str_replace (':', '', $workHoursDay[$j + 1]['END']);
                    if ( $dataft > $datasc )
                    {
                        $aux = $workHoursDay[$j + 1];
                        $workHoursDay[$j + 1] = $workHoursDay[$j];
                        $workHoursDay[$j] = $aux;
                    }
                }
            }

            foreach ($workHoursDay as $value) {
                $iniTime = (float) str_replace (':', '', ((strlen ($value['START']) == 8) ? $value['START'] : $value['START'] . ':00'));
                $finTime = (float) str_replace (':', '', ((strlen ($value['END']) == 8) ? $value['END'] : $value['END'] . ':00'));

                if ( $timeDate <= $iniTime )
                {
                    $nextWorkHours['STATUS'] = true;
                    $nextWorkHours['DATE'] = $auxIniDate['0'] . ' ' . ((strlen ($value['START']) == 8) ? $value['START'] : $value['START'] . ':00');
                    return $nextWorkHours;
                }
                elseif ( ($iniTime <= $timeDate) && ($timeDate < $finTime) )
                {
                    $nextWorkHours['STATUS'] = true;
                    $nextWorkHours['DATE'] = $date;
                    return $nextWorkHours;
                }
            }
        }

        $nextWorkHours['STATUS'] = false;
        return $nextWorkHours;
    }

    public function dashIs_holiday ($date, $holidays = array())
    {
        $auxIniDate = explode (' ', $date);
        $iniDate = $auxIniDate['0'];
        $iniDate = strtotime ($iniDate);

        foreach ($holidays as $value) {
            $holidayStartDate = strtotime (date ('Y-m-d', strtotime ($value['CALENDAR_HOLIDAY_START'])));
            $holidayEndDate = strtotime (date ('Y-m-d', strtotime ($value['CALENDAR_HOLIDAY_END'])));

            if ( ($holidayStartDate <= $iniDate) && ($iniDate <= $holidayEndDate) )
            {
                return true;
            }
        }
        return false;
    }

    public function dashGetIniDate ($iniDate, $calendarData = array())
    {
        $flagIniDate = true;

        while ($flagIniDate) {
            // 1 if it's a work day
            $weekDay = date ('w', strtotime ($iniDate));
            if ( !(in_array ($weekDay, $calendarData['CALENDAR_WORK_DAYS_A'])) )
            {
                $iniDate = date ('Y-m-d' . ' 00:00:00', strtotime ('+1 day', strtotime ($iniDate)));
                continue;
            }

            // 2 if it's a holiday
            $iniDateHolidayDay = $this->dashIs_holiday ($iniDate, $calendarData['HOLIDAY']);
            if ( $iniDateHolidayDay )
            {
                $iniDate = date ('Y-m-d' . ' 00:00:00', strtotime ('+1 day', strtotime ($iniDate)));
                continue;
            }

            // 3 if it's work time
            $workHours = $this->dashNextWorkHours ($iniDate, $weekDay, $calendarData['BUSINESS_DAY']);
            if ( !($workHours['STATUS']) )
            {
                $iniDate = date ('Y-m-d' . ' 00:00:00', strtotime ('+1 day', strtotime ($iniDate)));
                continue;
            }
            else
            {
                $iniDate = $workHours['DATE'];
            }
            $flagIniDate = false;
        }

        return $iniDate;
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

                $arrayCalendarWorkHour[] = array($calendarBusinessHours);
            }

            $arrayCalendarHoliday = array();
            $arrayData = $calendarHolidays->getCalendarHolidays ($record["CALENDAR_UID"]);


            foreach ($arrayData as $value) {

                $calendarHolidays = new \CalendarHolidays();
                $calendarHolidays->setCalendarHolidayName ($value["CALENDAR_HOLIDAY_NAME"] . "");
                $calendarHolidays->setCalendarHolidayStart ($value["CALENDAR_HOLIDAY_START"] . "");
                $calendarHolidays->setCalendarHolidayEnd ($value["CALENDAR_HOLIDAY_END"] . "");

                $arrayCalendarHoliday[] = array(
                    $calendarHolidays
                );
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
            $objCalendarDefinition->setWorkHours ($arrayCalendarWorkHour);
            $objCalendarDefinition->setHolidays ($arrayCalendarHoliday);
            $objCalendarDefinition->setCalendarCreateDate ($dateCreate);
            $objCalendarDefinition->setCalendarUpdateDate ($dateUpdate);
            $objCalendarDefinition->setTotalUsers ((int) ($record["CALENDAR_TOTAL_USERS"]));
            $objCalendarDefinition->setTotalProcesses ((int) ($record["CALENDAR_TOTAL_PROCESSES"]));

            $arrTest = array($objCalendarDefinition);

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
            $process = new \ProcessMaker\BusinessModel\Process();
            $process->throwExceptionIfDataNotMetPagerVarDefinition (array("start" => $start, "limit" => $limit), $this->arrayFieldNameForException);
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
                $criteria->add (
                        $criteria->getNewCriterion (\CalendarDefinitionPeer::CALENDAR_NAME, "%" . $arrayFilterData["filter"] . "%", \Criteria::LIKE)->addOr (
                                $criteria->getNewCriterion (\CalendarDefinitionPeer::CALENDAR_DESCRIPTION, "%" . $arrayFilterData["filter"] . "%", \Criteria::LIKE))
                );
            }
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = strtoupper ($sortField);
                $sortField = (isset ($this->arrayFieldDefinition[$sortField]["fieldName"])) ? $this->arrayFieldDefinition[$sortField]["fieldName"] : $sortField;
                switch ($sortField) {
                    case "CALENDAR_UID":
                    case "CALENDAR_NAME":
                    case "CALENDAR_DESCRIPTION":
                    case "CALENDAR_WORK_DAYS":
                    case "CALENDAR_STATUS":
                    case "CALENDAR_CREATE_DATE":
                    case "CALENDAR_UPDATE_DATE":
                        $sortField = \CalendarDefinitionPeer::TABLE_NAME . "." . $sortField;
                        break;
                    default:
                        $sortField = \CalendarDefinitionPeer::CALENDAR_NAME;
                        break;
                }
            }
            else
            {
                $sortField = \CalendarDefinitionPeer::CALENDAR_NAME;
            }
            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria->addDescendingOrderByColumn ($sortField);
            }
            else
            {
                $criteria->addAscendingOrderByColumn ($sortField);
            }
            if ( !is_null ($start) )
            {
                $criteria->setOffset ((int) ($start));
            }
            if ( !is_null ($limit) )
            {
                $criteria->setLimit ((int) ($limit));
            }
            $rsCriteria = \CalendarDefinitionPeer::doSelectRS ($criteria);
            $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);
            while ($rsCriteria->next ()) {
                $row = $rsCriteria->getRow ();
                $row["CALENDAR_TOTAL_USERS"] = (isset ($arrayTotalUsersByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalUsersByCalendar[$row["CALENDAR_UID"]] : 0;
                $row["CALENDAR_TOTAL_PROCESSES"] = (isset ($arrayTotalProcessesByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalProcessesByCalendar[$row["CALENDAR_UID"]] : 0;
                $row["CALENDAR_TOTAL_TASKS"] = (isset ($arrayTotalTasksByCalendar[$row["CALENDAR_UID"]])) ? $arrayTotalTasksByCalendar[$row["CALENDAR_UID"]] : 0;
                $arrayCalendar[] = $this->getCalendarDataFromRecord ($row);
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

    public function getCalendarData ($calendarUid = null)
    {

        $calendarUid = (is_null ($calendarUid)) ? $this->pmCalendarUid : $calendarUid;
        $this->pmCalendarUid = $calendarUid;
        //if exists the row in the database propel will update it, otherwise will insert.

        $tr = (new \CalendarDefinition())->retrieveByPK ($calendarUid);
        $defaultCalendar ['CALENDAR_UID'] = '00000000000000000000000000000001';
        $defaultCalendar ['CALENDAR_NAME'] = 'Default';
        $defaultCalendar ['CALENDAR_CREATE_DATE'] = date ('Y-m-d');
        $defaultCalendar ['CALENDAR_UPDATE_DATE'] = date ('Y-m-d');
        $defaultCalendar ['CALENDAR_DESCRIPTION'] = 'Default';
        $defaultCalendar ['CALENDAR_STATUS'] = 'ACTIVE';
        $defaultCalendar ['CALENDAR_WORK_DAYS'] = '1|2|3|4|5';
        $defaultCalendar ['CALENDAR_WORK_DAYS'] = explode ('|', '1|2|3|4|5');
        $defaultCalendar ['BUSINESS_DAY'] [1] ['CALENDAR_BUSINESS_DAY'] = 7;
        $defaultCalendar ['BUSINESS_DAY'] [1] ['CALENDAR_BUSINESS_START'] = '09:00';
        $defaultCalendar ['BUSINESS_DAY'] [1] ['CALENDAR_BUSINESS_END'] = '17:00';
        $defaultCalendar ['BUSINESS_DAY'] [1] ['DIFF_HOURS'] = '8';
        $defaultCalendar ['HOURS_FOR_DAY'] = '8';
        $defaultCalendar ['HOLIDAY'] = array();
        if ( (is_object ($tr) && get_class ($tr) == 'CalendarDefinition' ) )
        {
            $fields ['CALENDAR_UID'] = $tr->getCalendarUid ();
            $fields ['CALENDAR_NAME'] = $tr->getCalendarName ();
            $fields ['CALENDAR_CREATE_DATE'] = $tr->getCalendarCreateDate ();
            $fields ['CALENDAR_UPDATE_DATE'] = $tr->getCalendarUpdateDate ();
            $fields ['CALENDAR_DESCRIPTION'] = $tr->getCalendarDescription ();
            $fields ['CALENDAR_STATUS'] = $tr->getCalendarStatus ();
            $fields ['CALENDAR_WORK_DAYS'] = $tr->getCalendarWorkDays ();
            $fields ['CALENDAR_WORK_DAYS_A'] = explode ('|', $tr->getCalendarWorkDays ());
        }
        else
        {
            $fields = $defaultCalendar;
            //$this->saveCalendarInfo ( $fields );
            $fields ['CALENDAR_WORK_DAYS'] = '1|2|3|4|5';
            $fields ['CALENDAR_WORK_DAYS_A'] = explode ('|', '1|2|3|4|5');
            //$tr = CalendarDefinitionPeer::retrieveByPK ( $calendarUid );
        }

        $CalendarBusinessHoursObj = new \CalendarBusinessHours();
        $CalendarBusinessHours = $this->getCalendarBusinessHours ($calendarUid);

        $numDay = 8;
        $daysHours = array();
        $hoursCant = array();
        $modaHours = 0;
        $keyModa = 0;
        foreach ($CalendarBusinessHours as $value) {
            if ( $value['CALENDAR_BUSINESS_DAY'] != $numDay )
            {
                $numDay = $value['CALENDAR_BUSINESS_DAY'];
                $daysHours[$numDay] = 0;
            }
            $daysHours[$numDay] += $value['DIFF_HOURS'];
        }
        foreach ($daysHours as $value) {
            if ( isset ($hoursCant[$value]) )
            {
                $hoursCant[$value] ++;
            }
            else
            {
                $hoursCant[$value] = 1;
            }
        }
        foreach ($hoursCant as $key => $value) {
            if ( $value > $modaHours )
            {
                $modaHours = $value;
                $keyModa = $key;
            }
        }
        $fields ['HOURS_FOR_DAY'] = $keyModa;
        $fields ['BUSINESS_DAY'] = $CalendarBusinessHours;

        $CalendarHolidaysObj = new \CalendarHolidays ( );
        $CalendarHolidays = $this->getCalendarHolidays ($calendarUid);

        $fields ['HOLIDAY'] = $CalendarHolidays;
        $fields = $this->validateCalendarInfo ($fields, $defaultCalendar);

        $this->pmCalendarData = $fields;
        return $this->pmCalendarData;
    }

    public function getCalendarBusinessHours ($calendarUid = null)
    {
        $calendarUid = (is_null ($calendarUid)) ? $this->pmCalendarUid : $calendarUid;
        $this->pmCalendarUid = $calendarUid;

        $sql = "SELECT CALENDAR_UID, CALENDAR_BUSINESS_DAY, CALENDAR_BUSINESS_START, CALENDAR_BUSINESS_END FROM calendar.calendar_business_hours WHERE CALENDAR_UID = ?";
        $sql .= " ORDER BY CALENDAR_BUSINESS_DAY DESC, CALENDAR_BUSINESS_START ASC";
        $arrParameters = array($calendarUid);
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $fields = array();
        $count = 0;
        foreach ($results as $row) {

            $iniTime = (float) str_replace (':', '', $row['CALENDAR_BUSINESS_START']);
            $finTime = (float) str_replace (':', '', $row['CALENDAR_BUSINESS_END']);
            $row['DIFF_HOURS'] = (($finTime - $iniTime) / 100);
            $fields[$count] = $row;
            $count++;
        }

        return $fields;
    }

    public function getCalendarHolidays ($calendarUid = null)
    {
        $calendarUid = (is_null ($calendarUid)) ? $this->pmCalendarUid : $calendarUid;
        $this->pmCalendarUid = $calendarUid;

        $sql = "SELECT CALENDAR_UID, CALENDAR_HOLIDAY_NAME, CALENDAR_HOLIDAY_START, CALENDAR_HOLIDAY_END FROM calendar.calendar_holidays WHERE CALENDAR_UID = ?";
        $arrParameters = array($calendarUid);
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $fields = array();

        $count = 0;
        foreach ($results as $row) {
            $a = explode (' ', $row['CALENDAR_HOLIDAY_START']);
            $row['CALENDAR_HOLIDAY_START'] = $a[0];
            $a = explode (' ', $row['CALENDAR_HOLIDAY_END']);
            $row['CALENDAR_HOLIDAY_END'] = $a[0];
            $fields[$count] = $row;

            $count++;
        }
        return $fields;
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
            foreach ($workingDays as $key => $day) {
                $workingDaysOK[$day] = false;
            }
            $sw_all = false;
            foreach ($fields ['BUSINESS_DAY'] as $keyB => $businessHours) {
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

     //Calculate the duration betwen two dates with a calendar
    public function dashCalculateDurationWithCalendar ($iniDate, $finDate = null, $calendarData = array())
    {
    	if ((is_null($finDate)) || ($finDate == '')) {
    		$finDate = date('Y-m-d H:i:s');
    	}
    

        if ((strtotime($finDate)) <= (strtotime($iniDate))) {
            return 0.00;
        }
	
        $secondDuration = 0.00;
    
    	$finDate = $this->dashGetIniDate($finDate, $calendarData);
    	$newDate = $iniDate;

		$timeIniDate = strtotime($iniDate);
		$timeFinDate = strtotime($finDate);

    	while ($timeIniDate < $timeFinDate) {
    		$newDate = $this->dashGetIniDate($newDate, $calendarData);
    
    		$rangeWorkHour = $this->dashGetRangeWorkHours($newDate, $calendarData['BUSINESS_DAY']);
    		$onlyDate = (date('Y-m-d',strtotime($newDate))) . ' ' . $rangeWorkHour['END'];
    
    		if ( (strtotime($finDate)) < (strtotime($onlyDate)) ) {
    			$secondRes = ( ((float)strtotime($finDate)) - ((float)strtotime($newDate)) );
    			$timeIniDate = strtotime($finDate);
    			$secondDuration += (float)$secondRes;
    		} else {
    			$secondRes = ( ((float)strtotime($onlyDate)) - ((float)strtotime($newDate)) );
    			$newDate = $onlyDate;
    			$timeIniDate = strtotime($onlyDate);
    			$secondDuration += (float)$secondRes;
    		}
    	}
    	return $secondDuration;
    }

}