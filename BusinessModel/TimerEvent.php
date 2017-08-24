<?php

namespace BusinessModel;

class TimerEvent
{

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
     * Get year, month, day, hour, minute and second by datetime
     *
     * @param string $datetime Datetime (yyyy-mm-dd hh:ii:ss)
     *
     * return array Return data
     */
    public function getYearMonthDayHourMinuteSecondByDatetime ($datetime)
    {
        try {
            $arrayData = array();

            if ( preg_match ('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $datetime, $arrMatch) )
            {
                $arrayData[] = $arrMatch[1]; //Year
                $arrayData[] = $arrMatch[2]; //Month
                $arrayData[] = $arrMatch[3]; //Day
                $arrayData[] = (isset ($arrayMatch[4])) ? $arrayMatch[4] : "00"; //Hour
                $arrayData[] = (isset ($arrayMatch[5])) ? $arrayMatch[5] : "00"; //Minute
                $arrayData[] = (isset ($arrayMatch[6])) ? $arrayMatch[6] : "00"; //Second

                return $arrayData;
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
     * Get valid Next Run Date
     *
     * @param array  $arrayTimerEventData Timer-Event data
     * @param string $datetime            Datetime
     * @param bool   $flagIncludeDatetime Flag
     *
     * return string Return the valid Next Run Date
     */
    public function getValidNextRunDateByDataAndDatetime (array $arrayTimerEventData, $datetime, $flagIncludeDatetime = true)
    {
        try {
            $nextRunDate = $datetime;

            //Get Next Run Date
            list($year, $month, $day, $hour, $minute, $second) = $this->getYearMonthDayHourMinuteSecondByDatetime ($datetime);

            $arrayMonthsShort = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
            $arrayWeekdays = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
            switch (strtoupper ($arrayTimerEventData["TMREVN_OPTION"])) {
                case "HOURLY":
                    $hhmmss = "$hour:" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";
                    $nextRunDate = "$year-$month-$day $hhmmss";
                    if ( !$flagIncludeDatetime )
                    {
                        $nextRunDate = date ("Y-m-d H:i:s", strtotime ("$nextRunDate +1 hour"));
                    }
                    break;
                case "DAILY":
                    $hhmmss = $arrayTimerEventData["TMREVN_HOUR"] . ":" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";

                    $arrayWeekdaysData = $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"];
                    if ( !empty ($arrayWeekdaysData) )
                    {
                        sort ($arrayWeekdaysData);
                        $weekday = (int) (date ("w", strtotime ($datetime)));
                        $weekday = ($weekday == 0) ? 7 : $weekday;
                        $firstWeekday = $arrayWeekdaysData[0];
                        $nextWeekday = $firstWeekday;
                        $typeStatement = "this";


                        $flag = false;
                        foreach ($arrayWeekdaysData as $value) {
                            $d = $value;
                            if ( ($flagIncludeDatetime && $d >= $weekday) || (!$flagIncludeDatetime && $d > $weekday) )
                            {
                                $nextWeekday = $d;
                                $flag = true;
                                break;
                            }
                        }
                        if ( !$flag )
                        {
                            $typeStatement = "next";
                        }

                        $minusVal = $nextWeekday == 0 ? 0 : $nextWeekday - 1;

                        $nextRunDate = date ("Y-m-d", strtotime ("$year-$month-$day $typeStatement " . $arrayWeekdays[$minusVal])) . " $hhmmss";
                        //echo $nextRunDate;
                    }
                    else
                    {
                        $nextRunDate = "$year-$month-$day $hhmmss";
                        if ( !$flagIncludeDatetime )
                        {
                            $nextRunDate = date ("Y-m-d", strtotime ("$nextRunDate +1 day")) . " $hhmmss";
                        }
                    }
                    break;
                case "MONTHLY":
                    $hhmmss = $arrayTimerEventData["TMREVN_HOUR"] . ":" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";
                    $arrayMonthsData = $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"];

                    if ( !empty ($arrayMonthsData) )
                    {
                        sort ($arrayMonthsData);
                        $firstMonth = $arrayMonthsData[0];
                        $nextMonth = $firstMonth;

                        $flag = false;
                        foreach ($arrayMonthsData as $value) {
                            $m = $value;
                            if ( ($flagIncludeDatetime && $m >= $month) || (!$flagIncludeDatetime && $m > $month) )
                            {
                                $nextMonth = $m;
                                $flag = true;
                                break;
                            }
                        }
                        if ( !$flag )
                        {
                            $year++;
                        }
                        if ( checkdate ((int) ($nextMonth), (int) ($arrayTimerEventData["TMREVN_DAY"]), (int) ($year)) )
                        {
                            $nextRunDate = "$year-$nextMonth-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                        }
                        else
                        {
                            $minusVal = $nextMonth == 0 ? 0 : $nextMonth - 1;
                            $nextRunDate = date ("Y-m-d", strtotime ("last day of " . $arrayMonthsShort[$minusVal] . " $year")) . " $hhmmss";
                        }
                    }
                    else
                    {
                        if ( checkdate ((int) ($month), (int) ($arrayTimerEventData["TMREVN_DAY"]), (int) ($year)) )
                        {
                            $nextRunDate = "$year-$month-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                        }
                        else
                        {
                            $minusVal = $month == 0 ? 0 : $month - 1;
                            $nextRunDate = date ("Y-m-d", strtotime ("last day of " . $arrayMonthsShort[$minusVal] . " $year")) . " $hhmmss";
                        }
                        if ( !$flagIncludeDatetime )
                        {
                            list($yearAux, $monthAux) = $this->getYearMonthDayHourMinuteSecondByDatetime (date ("Y-m-d", strtotime ("$year-$month-01 next month")));
                            if ( checkdate ((int) ($monthAux), (int) ($arrayTimerEventData["TMREVN_DAY"]), (int) ($yearAux)) )
                            {
                                $nextRunDate = "$yearAux-$monthAux-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                            }
                            else
                            {
                                $nextRunDate = date ("Y-m-d", strtotime ("last day of " . $arrayMonthsShort[((int) ($monthAux)) - 1] . " $yearAux")) . " $hhmmss";
                            }
                        }
                    }

                    break;
                case "EVERY":
                    if ( $arrayTimerEventData["TMREVN_HOUR"] . "" != "" )
                    {
                        $nextRunDate = date ("Y-m-d H:i:s", strtotime ("$nextRunDate +" . ((int) ($arrayTimerEventData["TMREVN_HOUR"])) . " hours"));
                    }
                    if ( $arrayTimerEventData["TMREVN_MINUTE"] . "" != "" )
                    {
                        $nextRunDate = date ("Y-m-d H:i:s", strtotime ("$nextRunDate +" . ((int) ($arrayTimerEventData["TMREVN_MINUTE"])) . " minutes"));
                    }
                    break;
            }
            //Return

            return date ("Y-m-d H:i:s", strtotime ($nextRunDate));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Next Run Date
     *
     * @param array  $arrayTimerEventData Timer-Event data
     * @param string $datetime            Datetime
     * @param bool   $flagIncludeDatetime Flag
     *
     * return string Return the Next Run Date
     */
    public function getNextRunDateByDataAndDatetime (array $arrayTimerEventData, $datetime, $flagIncludeDatetime = true)
    {
        try {

            $nextRunDate = $datetime;

            //Get Next Run Date
            if ( !is_array ($arrayTimerEventData["TMREVN_CONFIGURATION_DATA"]) )
            {
                $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"] = unserialize ($arrayTimerEventData["TMREVN_CONFIGURATION_DATA"]);
            }
            $timeDatetime = strtotime ($datetime);
            $flagNextRunDate = true;
            switch (strtoupper ($arrayTimerEventData["TMREVN_OPTION"])) {
                case "HOURLY":
                case "DAILY":
                case "MONTHLY":
                    //case "EVERY":
                    $nextRunDate = $this->getValidNextRunDateByDataAndDatetime ($arrayTimerEventData, $arrayTimerEventData["TMREVN_START_DATE"], $flagIncludeDatetime);

                    $timeNextRunDate = strtotime ($nextRunDate);

                    if ( $timeNextRunDate > $timeDatetime )
                    {
                        $flagNextRunDate = false;
                    }
                    break;
            }


            if ( $flagNextRunDate )
            {
                switch (strtoupper ($arrayTimerEventData["TMREVN_OPTION"])) {
                    case "HOURLY":
                    case "DAILY":
                    case "MONTHLY":
                    case "EVERY":
                        $nextRunDate = $this->getValidNextRunDateByDataAndDatetime ($arrayTimerEventData, $datetime, $flagIncludeDatetime);

                        $timeNextRunDate = strtotime ($nextRunDate);
                        if ( $timeNextRunDate < $timeDatetime )
                        {

                            $nextRunDate = $this->getValidNextRunDateByDataAndDatetime ($arrayTimerEventData, $datetime, false);
                        }
                        break;
                }
            }
            //Return
            return $nextRunDate;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Unset fields
     *
     * @param array $arrayData Data with the fields
     *
     * return array Return data with the fields
     */
    public function unsetFields (array $arrayData)
    {
        try {
            unset ($arrayData["TMREVN_UID"]);
            unset ($arrayData["PRJ_UID"]);
            unset ($arrayData["TMREVN_LAST_RUN_DATE"]);
            unset ($arrayData["TMREVN_LAST_EXECUTION_DATE"]);
            //Return
            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Timer-Event
     *
     * @param string $timerEventUid Unique id of Timer-Event
     *
     * return bool Return true if exists the Timer-Event, false otherwise
     */
    public function exists ($timerEventUid)
    {
        try {
            $result = $this->objMysql->_select ("workflow.timer_event", [], ["TMREVN_UID" => $timerEventUid]);

            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Event of a Timer-Event
     *
     * @param string $projectUid             Unique id of Project
     * @param string $eventUid               Unique id of Event
     * @param string $timerEventUidToExclude Unique id of Timer-Event to exclude
     *
     * return bool Return true if exists the Event of a Timer-Event, false otherwise
     */
    public function existsEvent ($projectUid, $eventUid, $timerEventUidToExclude = "")
    {
        try {
            $sql = "SELECT * FROM workflow.status_mapping sm INNER JOIN workflow.timer_event te ON te.EVN_UID"
                    . " = sm.id WHERE sm.id = ?";
            if ( $timerEventUidToExclude != "" )
            {
                $sql .= " AND te.TMREVN_UID != ?";
            }
            $result = $this->objMysql__query ($sql, $arrParameters);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the Timer-Event
     *
     * @param string $timerEventUid         Unique id of Timer-Event
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exists the Timer-Event
     */
    public function throwExceptionIfNotExistsTimerEvent ($timerEventUid)
    {
        try {
            if ( !$this->exists ($timerEventUid) )
            {
                throw new \Exception ("ID_TIMER_EVENT_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if is registered the Event
     *
     * @param string $projectUid             Unique id of Project
     * @param string $eventUid               Unique id of Event
     * @param string $fieldNameForException  Field name for the exception
     * @param string $timerEventUidToExclude Unique id of Timer-Event to exclude
     *
     * return void Throw exception if is registered the Event
     */
    public function throwExceptionIfEventIsRegistered ($projectUid, $eventUid, $timerEventUidToExclude = "")
    {
        try {
            if ( $this->existsEvent ($projectUid, $eventUid, $timerEventUidToExclude) )
            {
                throw new \Exception ("ID_TIMER_EVENT_ALREADY_REGISTERED");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $timerEventUid Unique id of Timer-Event
     * @param string $projectUid    Unique id of Project
     * @param array  $arrayData     Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($timerEventUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayTimerEventData = ($timerEventUid == "") ? array() : $this->getTimerEvent ($timerEventUid, true);
            $flagInsert = ($timerEventUid == "") ? true : false;
            $arrayFinalData = array_merge ($arrayTimerEventData, $arrayData);
            //Verify data - Field definition
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfDataNotMetFieldDefinition($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            //Verify data
            if ( isset ($arrayData["EVN_UID"]) )
            {
                $arrayEventType = array("START", "INTERMEDIATE");
                $arrayEventMarker = array("TIMER");
                $bpmnEvent = \BpmnEventPeer::retrieveByPK ($arrayData["EVN_UID"]);
                if ( is_null ($bpmnEvent) )
                {
                    throw new \Exception ("ID_EVENT_NOT_EXIST");
                }
                if ( !in_array ($bpmnEvent->getEvnType (), $arrayEventType) || !in_array ($bpmnEvent->getEvnMarker (), $arrayEventMarker) )
                {
                    throw new \Exception ("ID_EVENT_NOT_IS_TIMER_EVENT");
                }
                if ( $bpmnEvent->getPrjUid () != $projectUid )
                {
                    throw new \Exception ("ID_EVENT_EVENT_NOT_BELONG_TO_PROJECT");
                }
                $this->throwExceptionIfEventIsRegistered ($projectUid, $arrayData["EVN_UID"], $timerEventUid);
            }
            //Verify data - Field definition
            $arrayFieldDefinition = array();
            $bpmnEvent = (new Event())->getEvent ($projectUid);

            if ( !isset ($bpmnEvent[0]) || empty ($bpmnEvent[0]) )
            {
                throw new \Exception ("Event doesnt exist");
            }
            $stepCondition = json_decode ($bpmnEvent[0]['step_condition'], true);

            switch ($stepCondition['params']['hold']['evn_type']) {
                case "START":
                    $arrayFieldDefinition = array(
                        "TMREVN_OPTION" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array("HOURLY", "DAILY", "MONTHLY", "EVERY", "ONE-DATE-TIME"), "fieldNameAux" => "timerEventOption")
                    );
                    break;
                case "INTERMEDIATE":
                    $arrayFieldDefinition = array(
                        "TMREVN_OPTION" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array("WAIT-FOR", "WAIT-UNTIL-SPECIFIED-DATE-TIME"), "fieldNameAux" => "timerEventOption")
                    );
                    break;
            }
            if ( !empty ($arrayFieldDefinition) )
            {
                //$process->throwExceptionIfDataNotMetFieldDefinition($arrayFinalData, $arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            }
            $arrayFieldDefinition = array();
            $arrayValidateData = array(
                "TMREVN_DAY" => array("/^(?:0[1-9]|[12][0-9]|3[01])$/"),
                "TMREVN_HOUR" => array("/^(?:[0-1]\d|2[0-3])$/"),
                "TMREVN_MINUTE" => array("/^(?:[0-5]\d)$/")
            );
            switch (strtoupper ($arrayFinalData["TMREVN_OPTION"])) {
                case "HOURLY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE" => array("type" => "date", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE" => array("type" => "date", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_MINUTE" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute")
                    );
                    break;
                case "DAILY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE" => array("type" => "date", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE" => array("type" => "date", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_HOUR" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute"),
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "array", "required" => false, "empty" => true, "defaultValues" => array(1, 2, 3, 4, 5, 6, 7), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
                case "MONTHLY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE" => array("type" => "date", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE" => array("type" => "date", "required" => false, "empty" => true, "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_DAY" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventDay"),
                        "TMREVN_HOUR" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute"),
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "array", "required" => false, "empty" => true, "defaultValues" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
                case "EVERY":
                    $arrayFieldDefinition = array(
                        "TMREVN_HOUR" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute")
                    );
                    $arrayValidateData["TMREVN_HOUR"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    $arrayValidateData["TMREVN_MINUTE"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    break;
                case "ONE-DATE-TIME":
                    $arrayFieldDefinition = array(
                        "TMREVN_NEXT_RUN_DATE" => array("type" => "datetime", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventNextRunDate")
                    );
                    break;
                case "WAIT-FOR":
                    //TMREVN_DAY
                    //TMREVN_HOUR
                    //TMREVN_MINUTE
                    $arrayValidateData["TMREVN_DAY"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    $arrayValidateData["TMREVN_HOUR"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    $arrayValidateData["TMREVN_MINUTE"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    break;
                case "WAIT-UNTIL-SPECIFIED-DATE-TIME":
                    $arrayFieldDefinition = array(
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
            }
            if ( !empty ($arrayFieldDefinition) )
            {
                //$process->throwExceptionIfDataNotMetFieldDefinition($arrayFinalData, $arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            }
            foreach ($arrayValidateData as $key => $value) {
                if ( isset ($arrayData[$key]) && !preg_match ($value[0], $arrayData[$key]) )
                {
                    throw new \Exception ("ID_INVALID_VALUE");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Timer-Event for a Project
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Timer-Event created
     */
    public function create ($projectUid, array $arrayData)
    {
        try {

            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Verify data
            //$process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);

            //Create
            $arrayData = $this->unsetFields ($arrayData);
            try {
                $timerEvent = new \TimerEvent();
                $bpmnEvent = (new Event())->getEvent ($projectUid);
                if ( !isset ($bpmnEvent[0]) || empty ($bpmnEvent[0]) )
                {
                    throw new Exception ("Event doesnt exist");
                }

                $stepCondition = json_decode ($bpmnEvent[0]['step_condition'], true);

                //$timerEventUid = \ProcessMaker\Util\Common::generateUID();
                $arrayData["TMREVN_START_DATE"] = (isset ($arrayData["TMREVN_START_DATE"]) && $arrayData["TMREVN_START_DATE"] . "" != "") ? $arrayData["TMREVN_START_DATE"] : null;
                $arrayData["TMREVN_END_DATE"] = (isset ($arrayData["TMREVN_END_DATE"]) && $arrayData["TMREVN_END_DATE"] . "" != "") ? $arrayData["TMREVN_END_DATE"] : null;
                $arrayData["TMREVN_NEXT_RUN_DATE"] = (isset ($arrayData["TMREVN_NEXT_RUN_DATE"]) && $arrayData["TMREVN_NEXT_RUN_DATE"] . "" != "") ? $arrayData["TMREVN_NEXT_RUN_DATE"] : null;
                $arrayData["TMREVN_CONFIGURATION_DATA"] = serialize ((isset ($arrayData["TMREVN_CONFIGURATION_DATA"])) ? $arrayData["TMREVN_CONFIGURATION_DATA"] : "");
                $arrayData['EVN_UID'] = $projectUid;
                $timerEvent->loadObject ($arrayData);

                //$timerEvent->setTmrevnUid ($projectUid);
                $timerEvent->setPrjUid ($arrayData['WORKFLOW_ID']);
                if ( trim ($stepCondition['params']['hold']['evn_type']) !== "" )
                {
                    switch (trim ($arrayData["TMREVN_OPTION"])) {
                        case "HOURLY":
                        case "DAILY":
                        case "MONTHLY":
                        case "EVERY":
                            $timerEvent->setTmrevnNextRunDate ($this->getNextRunDateByDataAndDatetime ($arrayData, date ("Y-m-d H:i:s")));
                            break;
                    }
                }
                if ( $timerEvent->validate () )
                {
                    $timerEventUid = $timerEvent->save ();

                    //Return
                    return $this->getTimerEvent ($timerEventUid);
                }
                else
                {
                    $msg = "";
                    foreach ($timerEvent->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "") ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Timer-Events
     *
     * @param string $projectUid Unique id of Project
     *
     * return array Return an array with all Timer-Events
     */
    public function getTimerEvents ($projectUid)
    {
        try {
            $arrayTimerEvent = array();
            //Verify data
            //Get data
            $results = $this->objMysql->_select ("workflow.timer_event", [], ["workflow_id" => $projectUid]);
            foreach ($results as $result) {
                $row = $rsCriteria->getRow ();
                $row["TMREVN_CONFIGURATION_DATA"] = unserialize ($row["TMREVN_CONFIGURATION_DATA"]);
                $arrayTimerEvent[] = $this->getTimerEventDataFromRecord ($row);
            }
            //Return
            return $arrayTimerEvent;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Timer-Event
     *
     * @param string $timerEventUid Unique id of Timer-Event
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Timer-Event
     */
    public function getTimerEvent ($timerEventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsTimerEvent ($timerEventUid);
            //Get data
            $result = $this->objMysql->_select ("workflow.timer_event", [], ["TMREVN_UID" => $timerEventUid]);
            $row = $result[0];
            $row["TMREVN_CONFIGURATION_DATA"] = unserialize ($row["TMREVN_CONFIGURATION_DATA"]);
            //Return
            return $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Timer-Event by unique id of Event
     *
     * @param string $projectUid    Unique id of Project
     * @param string $eventUid      Unique id of Event
     * @param bool   $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Timer-Event by unique id of Event
     */
    public function getTimerEventByEvent ($projectUid, $eventUid, $flagGetRecord = true)
    {
        try {
            //Verify data
            $bpmnEvent = (new Event())->getEvent ($eventUid);

            //$process->throwExceptionIfNotExistsProcess ($projectUid, $this->arrayFieldNameForException["projectUid"]);
            if ( !isset ($bpmnEvent[0]) || empty ($bpmnEvent[0]) )
            {
                throw new \Exception ("Event " . $eventUid . " doesnt exist");
            }

            $row = $this->objMysql->_select ("workflow.timer_event", [], ["EVN_UID" => $eventUid]);

            if ( !isset ($row[0]) || empty ($row[0]) )
            {
                return false;
            }

            $row[0]["TMREVN_CONFIGURATION_DATA"] = unserialize ($row[0]["TMREVN_CONFIGURATION_DATA"]);
            //Return
            return (!$flagGetRecord) ? $this->getTimerEventDataFromRecord ($row[0]) : $row[0];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Timer-Event from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Timer-Event
     */
    public function getTimerEventDataFromRecord (array $record)
    {
        try {
            $objTimerEvent = new \TimerEvent();
            $objTimerEvent->setTmrevnUid ($record['TMREVN_UID']);
            $objTimerEvent->setPrjUid ($record['workflow_id']);
            $objTimerEvent->setEvnUid ($record['EVN_UID']);
            $objTimerEvent->setTmrevnDay ($record['TMREVN_DAY']);
            $objTimerEvent->setTmrevnEndDate ($record['TMREVN_END_DATE']);
            $objTimerEvent->setTmrevnStartDate ($record['TMREVN_START_DATE']);
            $objTimerEvent->setTmrevnHour ($record['TMREVN_HOUR']);
            $objTimerEvent->setTmrevnMinute ($record['TMREVN_MINUTE']);
            $objTimerEvent->setTmrevnConfigurationData ($record['TMREVN_CONFIGURATION_DATA']);
            $objTimerEvent->setTmrevnNextRunDate ($record['TMREVN_NEXT_RUN_DATE']);
            $objTimerEvent->setTmrevnStatus ($record['TMREVN_STATUS']);
            $objTimerEvent->setTmrevnOption ($record['TMREVN_OPTION']);

            return $objTimerEvent;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update Timer-Event
     *
     * @param string $timerEventUid Unique id of Timer-Event
     * @param array  $arrayData     Data
     *
     * return int Return the number of rows affected by this update
     */
    public function update ($timerEventUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set variables
            $arrayTimerEventData = $this->getTimerEvent ($timerEventUid, true);
            $arrayFinalData = array_merge ($arrayTimerEventData, $arrayData);
            //Verify data
            $this->throwExceptionIfNotExistsTimerEvent ($timerEventUid);
            $this->throwExceptionIfDataIsInvalid ($timerEventUid, $arrayTimerEventData["workflow_id"], $arrayData);
            //Update
            $arrayData = $this->unsetFields ($arrayData);
            try {
                $timerEvent = new \TimerEvent();
                $bpmnEvent = (new \Event())->getEvent ($arrayFinalData["EVN_UID"]);

                if ( !isset ($bpmnEvent[0]) || empty ($bpmnEvent[0]) )
                {
                    throw new \Exception ("Event doesnt exist");
                }

                if ( isset ($arrayData["TMREVN_START_DATE"]) )
                {
                    $arrayData["TMREVN_START_DATE"] = ($arrayData["TMREVN_START_DATE"] . "" != "") ? $arrayData["TMREVN_START_DATE"] : null;
                }
                if ( isset ($arrayData["TMREVN_END_DATE"]) )
                {
                    $arrayData["TMREVN_END_DATE"] = ($arrayData["TMREVN_END_DATE"] . "" != "") ? $arrayData["TMREVN_END_DATE"] : null;
                }
                if ( isset ($arrayData["TMREVN_NEXT_RUN_DATE"]) )
                {
                    $arrayData["TMREVN_NEXT_RUN_DATE"] = ($arrayData["TMREVN_NEXT_RUN_DATE"] . "" != "") ? $arrayData["TMREVN_NEXT_RUN_DATE"] : null;
                }
                if ( isset ($arrayData["TMREVN_CONFIGURATION_DATA"]) )
                {
                    $arrayData["TMREVN_CONFIGURATION_DATA"] = serialize ($arrayData["TMREVN_CONFIGURATION_DATA"]);
                }
                $timerEvent->loadObject ($arrayData);
                $timerEvent->setTmrevnUid ($timerEventUid);
                $timerEvent->setEvnUid ($arrayFinalData["EVN_UID"]);
                $eventType = "START";

                if ( $eventType == "START" )
                {

                    switch (strtoupper ($arrayFinalData["TMREVN_OPTION"])) {
                        case "HOURLY":
                        case "DAILY":
                        case "MONTHLY":
                        case "EVERY":
                            $flagUpdateNextRunDate = false;
                            $arrayFieldsToCheck = array();
                            switch (strtoupper ($arrayFinalData["TMREVN_OPTION"])) {
                                case "HOURLY":
                                    $arrayFieldsToCheck = array("TMREVN_START_DATE", "TMREVN_END_DATE", "TMREVN_MINUTE");
                                    break;
                                case "DAILY":
                                    $arrayFieldsToCheck = array("TMREVN_START_DATE", "TMREVN_END_DATE", "TMREVN_HOUR", "TMREVN_MINUTE", "TMREVN_CONFIGURATION_DATA");
                                    break;
                                case "MONTHLY":
                                    $arrayFieldsToCheck = array("TMREVN_START_DATE", "TMREVN_END_DATE", "TMREVN_DAY", "TMREVN_HOUR", "TMREVN_MINUTE", "TMREVN_CONFIGURATION_DATA");
                                    break;
                                case "EVERY":
                                    $arrayFieldsToCheck = array("TMREVN_HOUR", "TMREVN_MINUTE");
                                    break;
                            }

                            foreach ($arrayFieldsToCheck as $value) {

                                if ( isset ($arrayData[$value]) )
                                {
                                    if ( $value == "TMREVN_CONFIGURATION_DATA" )
                                    {
                                        $arrayAux = unserialize ($arrayData[$value]);
                                        $array1 = array_diff ($arrayAux, $arrayTimerEventData[$value]);
                                        $array2 = array_diff ($arrayTimerEventData[$value], $arrayAux);
                                        $flagUpdateNextRunDate = !empty ($array1) || !empty ($array2);
                                    }
                                    else
                                    {
                                        $flagUpdateNextRunDate = $arrayData[$value] != $arrayTimerEventData[$value];
                                    }

                                    if ( $flagUpdateNextRunDate )
                                    {
                                        break;
                                    }
                                }
                            }

                            if ( $flagUpdateNextRunDate )
                            {
                                $timerEvent->setTmrevnNextRunDate ($this->getNextRunDateByDataAndDatetime ($arrayFinalData, date ("Y-m-d H:i:s")));
                            }

                            break;
                    }
                }
                if ( $timerEvent->validate () )
                {
                    $result = $timerEvent->save ();
                    //Return
                    return $result;
                }
                else
                {
                    $msg = "";
                    foreach ($timerEvent->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure->getMessage ();
                    }
                    throw new \Exception (\G::LoadTranslation ("ID_REGISTRY_CANNOT_BE_UPDATED") . (($msg != "") ? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Start/Continue case by Timer-Event
     *
     * @param string $datetime Datetime (yyyy-mm-dd hh:ii:ss)
     * @param bool   $frontEnd Flag to represent the terminal front-end
     *
     * return void
     */
    public function startContinueCaseByTimerEvent ($datetime)
    {
        try {
            //Set variables
            $case = new \BusinessModel\Cases();
            list($year, $month, $day, $hour, $minute) = $this->getYearMonthDayHourMinuteSecondByDatetime ($datetime);
            $dateIni = "$year-$month-$day 00:00:00";

            $this->log ("START-NEW-CASES", "Date \"$datetime (UTC +00:00)\": Start new cases");

            $results = $this->objMysql->_query ("SELECT * FROM workflow.`timer_event` te
                                                INNER JOIN workflow.status_mapping sm ON sm.id = te.EVN_UID");


            foreach ($results as $result) {

                $conditions = json_decode ($result['step_condition'], true);

                $params = $conditions['params']['hold'];

                if ( $params['evn_type'] !== "INTERMEDIATE" )
                {
                    $result["TMREVN_CONFIGURATION_DATA"] = unserialize ($result["TMREVN_CONFIGURATION_DATA"]);

                    //Set variables
                    $arrayTimerEventData = $result;

                    //Create the new case
                    $timerEventNextRunDate = $arrayTimerEventData["TMREVN_NEXT_RUN_DATE"];
                    $timerEventNextRunDateNew = "";

                    $flagCase = false;

                    if ( strtotime ($timerEventNextRunDate) < strtotime ($dateIni) )
                    {
                        $timerEventNextRunDateNew = $this->getNextRunDateByDataAndDatetime ($arrayTimerEventData, $datetime); //Generate new date for old TMREVN_NEXT_RUN_DATE

                        $flagCase = true; //Create the old case
                    }
                    else
                    {
                        list(,,, $hourCase, $minuteCase) = $this->getYearMonthDayHourMinuteSecondByDatetime ($timerEventNextRunDate);

                        if ( (int) ($hour . $minute) <= (int) ($hourCase . $minuteCase) )
                        {
                            $flagCase = $hourCase == $hour && $minuteCase == $minute;
                        }
                        else
                        {
                            $timerEventNextRunDateNew = $this->getNextRunDateByDataAndDatetime ($arrayTimerEventData, $datetime); //Generate new date for old TMREVN_NEXT_RUN_DATE

                            $flagCase = true; //Create the old case
                        }
                    }

                    if ( $flagCase )
                    {
                        
                        $objUser = (new UsersFactory())->getUser ($_SESSION['user']['usrid']);
                        $variables = array("name" => "NAME", "description" => "DESCRIPTION");
                        $arrCase = $case->addCase (new \Workflow ($arrayTimerEventData['workflow_id']), $objUser, array("form" => $variables), [], true, null, true);

                        $this->log ("CREATED-NEW-CASE", "Case #" . $arrCase['case_id'] . " created, PRO_UID: " . $arrCase['project_id']);

                        //Update Timer-Event
                        $arrayData = array();

                        switch ($arrayTimerEventData["TMREVN_OPTION"]) {
                            case "HOURLY":
                            case "DAILY":
                            case "MONTHLY":
                            case "EVERY":

                                if ( $timerEventNextRunDateNew == "" )
                                {
                                    $timerEventNextRunDateNew = $this->getNextRunDateByDataAndDatetime ($arrayTimerEventData, $timerEventNextRunDate, false);
                                }

                                if ( $arrayTimerEventData["TMREVN_OPTION"] != "EVERY" &&
                                        $arrayTimerEventData["TMREVN_END_DATE"] . "" != "" && strtotime ($timerEventNextRunDateNew) > strtotime ($arrayTimerEventData["TMREVN_END_DATE"] . " 23:59:59")
                                )
                                {
                                    $arrayData["TMREVN_STATUS"] = "PROCESSED";
                                }
                                else
                                {
                                    $arrayData["TMREVN_NEXT_RUN_DATE"] = $timerEventNextRunDateNew;
                                }
                                break;
                            case "ONE-DATE-TIME":
                                $arrayData["TMREVN_STATUS"] = "PROCESSED";
                                break;
                        }

                        $arrayData["TMREVN_LAST_RUN_DATE"] = $timerEventNextRunDate;
                        $arrayData["TMREVN_LAST_EXECUTION_DATE"] = date ("Y-m-d H:i:s");

                        $result = $this->singleUpdate ($arrayTimerEventData["TMREVN_UID"], $arrayData);


                        $flagRecord = true;
                    }
                }
                else
                {
                   $arrCases = $case->getCasesForTask(new \Flow($result['EVN_UID']));
                   
                   $total = $arrCases['total'];
                   $dates = $arrCases['dates'];
                   $rows = $arrCases['rows'];

                    $counter = 0;
                    $flagRecord = false;

                    do {
                        $flagNextRecord = false;
                        
                        foreach ($rows as $projectId => $elementId) {
                            if ( $counter + 1 > $total )
                            {
                                $flagNextRecord = false;
                                break;
                            }

                            $result["TMREVN_CONFIGURATION_DATA"] = unserialize ($result["TMREVN_CONFIGURATION_DATA"]);
                            $arrayTimerEventData = $result;
                            $arrayApplicationData = $case->getCaseInfo ($projectId, $elementId);

                            $continueCaseDate = str_replace (";", ":", $dates[$projectId]);
                            
                            switch ($arrayTimerEventData["TMREVN_OPTION"]) {
                                case "WAIT-FOR":
                                    if ( $arrayTimerEventData["TMREVN_DAY"] . "" != "" )
                                    {
                                        $continueCaseDate = date ("Y-m-d H:i:s", strtotime ("$continueCaseDate +" . ((int) ($arrayTimerEventData["TMREVN_DAY"])) . " days"));
                                    }

                                    if ( $arrayTimerEventData["TMREVN_HOUR"] . "" != "" )
                                    {
                                        $continueCaseDate = date ("Y-m-d H:i:s", strtotime ("$continueCaseDate +" . ((int) ($arrayTimerEventData["TMREVN_HOUR"])) . " hours"));
                                    }
                                    if ( $arrayTimerEventData["TMREVN_MINUTE"] . "" != "" )
                                    {
                                        $continueCaseDate = date ("Y-m-d H:i:s", strtotime ("$continueCaseDate +" . ((int) ($arrayTimerEventData["TMREVN_MINUTE"])) . " minutes"));
                                    }
                                    break;

                                case "WAIT-UNTIL-SPECIFIED-DATE-TIME":
                                    //$continueCaseDate = \G::replaceDataField ($arrayTimerEventData["TMREVN_CONFIGURATION_DATA"], $arrayApplicationData["APP_DATA"]);
                                    break;
                            }

                            $arrayContinueCaseDateData = $this->getYearMonthDayHourMinuteSecondByDatetime ($continueCaseDate);
                            
                            if ( !empty ($arrayContinueCaseDateData) )
                            {
                                $flagCase = false;
                                if ( strtotime ($continueCaseDate) < strtotime ($dateIni) )
                                {
                                    $flagCase = true; //Continue the old case
                                }
                                else
                                {
                                    $yearCase = $arrayContinueCaseDateData[0];
                                    $monthCase = $arrayContinueCaseDateData[1];
                                    $dayCase = $arrayContinueCaseDateData[2];
                                    $hourCase = $arrayContinueCaseDateData[3];
                                    $minuteCase = $arrayContinueCaseDateData[4];
                                    if ( "$yearCase-$monthCase-$dayCase" == "$year-$month-$day" )
                                    {
                                        if ( (int) ($hour . $minute) <= (int) ($hourCase . $minuteCase) )
                                        {
                                            $flagCase = $hourCase == $hour && $minuteCase == $minute;
                                        }
                                        else
                                        {
                                            $flagCase = true; //Continue the old case
                                        }
                                    }
                                }
                            }

                            if ( $flagCase )
                            {
                                $objUser = (new UsersFactory())->getUser ($_SESSION['user']['usrid']);
                                $result = $case->updateStatus ($arrayApplicationData, $objUser, "COMPLETE");
                                $flagRecord = true;
                            }
                            $counter++;
                        }
                    }
                    while ($flagNextRecord);
                }
            }


            //echo $date . " " . $dateIni . " " . $dateEnd;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Single update Timer-Event
     *
     * @param string $timerEventUid Unique id of Timer-Event
     * @param array  $arrayData     Data
     *
     * return int Return integer
     */
    public function singleUpdate ($timerEventUid, array $arrayData)
    {
        try {
            try {
                $record = $this->getTimerEvent ($timerEventUid);
                $arrayData = array_merge ($record, $arrayData);
                $timerEvent = new \TimerEvent();
                $timerEvent->setTmrevnUid ($timerEventUid);

                $arrayData['TMREVN_CONFIGURATION_DATA'] = serialize ($arrayData['TMREVN_CONFIGURATION_DATA']);

                $timerEvent->loadObject ($arrayData);

                if ( $timerEvent->validate () )
                {

                    $result = $timerEvent->save ();

                    //Return
                    return $result;
                }
                else
                {
                    $msg = "";

                    foreach ($timerEvent->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }

                    throw new \Exception ("ID_REGISTRY_CANNOT_BE_UPDATED") . (($msg != "") ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function setRecord ($record)
    {
        $timerEvents = new \TimerEvent();
        $timerEvents->setEvnUid ($record['EVN_UID']);
        $timerEvents->setPrjUid ($record['workflow_id']);
        $timerEvents->setTmrevnConfigurationData ($record['TMREVN_CONFIGURATION_DATA']);
        $timerEvents->setTmrevnDay ($record['TMREVN_DAY']);
        $timerEvents->setTmrevnEndDate ($record['TMREVN_END_DATE']);
        $timerEvents->setTmrevnStartDate ($record['TMREVN_START_DATE']);
        $timerEvents->setTmrevnHour ($record['TMREVN_HOUR']);
        $timerEvents->setTmrevnLastRunDate ($record['TMREVN_LAST_RUN_DATE']);
        $timerEvents->setTmrevnOption ($record['TMREVN_OPTION']);
        $timerEvents->setTmrevnMinute ($record['TMREVN_MINUTE']);
        $timerEvents->setTmrevnNextRunDate ($record['TMREVN_NEXT_RUN_DATE']);
        $timerEvents->setTmrevnStatus ($record['TMREVN_STATUS']);
        $timerEvents->setTmrevnLastExecutionDate ($record['TMREVN_LAST_EXECUTION_DATE']);

        return $timerEvents;
    }

    /**
     * Log
     *
     * @param string $action Action
     * @param string $value  Value
     *
     * return void
     */
    private function log ($action, $value = "")
    {
        try {
            $workspace = (defined ("SYS_SYS")) ? SYS_SYS : "Wokspace Undefined";
            $ipClient = $this->getIpAddress ();

            $username = "timereventcron";
            $fullname = "timereventcron";

            $this->writeLog ("|" . $workspace . "|" . $ipClient . "|" . $username . "|" . $fullname . "|" . $action . "|" . $value, "timerevent.log");
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
