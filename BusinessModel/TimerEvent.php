<?php
class TimerEvent
{
    /**
     * Constructor of the class
     *
     * return void
     */
    public function __construct()
    {
       
    }
   
    /**
     * Get year, month, day, hour, minute and second by datetime
     *
     * @param string $datetime Datetime (yyyy-mm-dd hh:ii:ss)
     *
     * return array Return data
     */
    public function getYearMonthDayHourMinuteSecondByDatetime($datetime)
    {
        try {
            $arrayData = array();
            if (preg_match("/^([1-9]\d{3})\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])(?:\s([0-1]\d|2[0-3])\:([0-5]\d)\:([0-5]\d))?$/", $datetime, $arrayMatch)) {
                $arrayData[] = $arrayMatch[1]; //Year
                $arrayData[] = $arrayMatch[2]; //Month
                $arrayData[] = $arrayMatch[3]; //Day
                $arrayData[] = (isset($arrayMatch[4]))? $arrayMatch[4] : "00"; //Hour
                $arrayData[] = (isset($arrayMatch[5]))? $arrayMatch[5] : "00"; //Minute
                $arrayData[] = (isset($arrayMatch[6]))? $arrayMatch[6] : "00"; //Second
            }
            //Return
            return $arrayData;
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
    public function getValidNextRunDateByDataAndDatetime(array $arrayTimerEventData, $datetime, $flagIncludeDatetime = true)
    {
        try {
            $nextRunDate = $datetime;
            //Get Next Run Date
            list($year, $month, $day, $hour, $minute, $second) = $this->getYearMonthDayHourMinuteSecondByDatetime($datetime);
            $arrayMonthsShort = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
            $arrayWeekdays    = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
            switch ($arrayTimerEventData["TMREVN_OPTION"]) {
                case "HOURLY":
                    $hhmmss = "$hour:" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";
                    $nextRunDate = "$year-$month-$day $hhmmss";
                    if (!$flagIncludeDatetime) {
                        $nextRunDate = date("Y-m-d H:i:s", strtotime("$nextRunDate +1 hour"));
                    }
                    break;
                case "DAILY":
                    $hhmmss = $arrayTimerEventData["TMREVN_HOUR"] . ":" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";
                    $arrayWeekdaysData = $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"];
                    if (!empty($arrayWeekdaysData)) {
                        sort($arrayWeekdaysData);
                        $weekday = (int)(date("w", strtotime($datetime)));
                        $weekday = ($weekday == 0)? 7 : $weekday;
                        $firstWeekday = $arrayWeekdaysData[0];
                        $nextWeekday   = $firstWeekday;
                        $typeStatement = "this";
                        $flag = false;
                        foreach ($arrayWeekdaysData as $value) {
                            $d = $value;
                            if (($flagIncludeDatetime && $d >= $weekday) || (!$flagIncludeDatetime && $d > $weekday)) {
                                $nextWeekday = $d;
                                $flag = true;
                                break;
                            }
                        }
                        if (!$flag) {
                            $typeStatement = "next";
                        }
                        $nextRunDate = date("Y-m-d", strtotime("$year-$month-$day $typeStatement " . $arrayWeekdays[$nextWeekday - 1])) . " $hhmmss";
                    } else {
                        $nextRunDate = "$year-$month-$day $hhmmss";
                        if (!$flagIncludeDatetime) {
                            $nextRunDate = date("Y-m-d", strtotime("$nextRunDate +1 day")) . " $hhmmss";
                        }
                    }
                    break;
                case "MONTHLY":
                    $hhmmss = $arrayTimerEventData["TMREVN_HOUR"] . ":" . $arrayTimerEventData["TMREVN_MINUTE"] . ":00";
                    $arrayMonthsData = $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"];
                    if (!empty($arrayMonthsData)) {
                        sort($arrayMonthsData);
                        $firstMonth = $arrayMonthsData[0];
                        $nextMonth = $firstMonth;
                        $flag = false;
                        foreach ($arrayMonthsData as $value) {
                            $m = $value;
                            if (($flagIncludeDatetime && $m >= $month) || (!$flagIncludeDatetime && $m > $month)) {
                                $nextMonth = $m;
                                $flag = true;
                                break;
                            }
                        }
                        if (!$flag) {
                            $year++;
                        }
                        if (checkdate((int)($nextMonth), (int)($arrayTimerEventData["TMREVN_DAY"]), (int)($year))) {
                            $nextRunDate = "$year-$nextMonth-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                        } else {
                            $nextRunDate = date("Y-m-d", strtotime("last day of " . $arrayMonthsShort[((int)($nextMonth)) - 1] . " $year")) . " $hhmmss";
                        }
                    } else {
                        if (checkdate((int)($month), (int)($arrayTimerEventData["TMREVN_DAY"]), (int)($year))) {
                            $nextRunDate = "$year-$month-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                        } else {
                            $nextRunDate = date("Y-m-d", strtotime("last day of " . $arrayMonthsShort[((int)($month)) - 1] . " $year")) . " $hhmmss";
                        }
                        if (!$flagIncludeDatetime) {
                            list($yearAux, $monthAux) = $this->getYearMonthDayHourMinuteSecondByDatetime(date("Y-m-d", strtotime("$year-$month-01 next month")));
                            if (checkdate((int)($monthAux), (int)($arrayTimerEventData["TMREVN_DAY"]), (int)($yearAux))) {
                                $nextRunDate = "$yearAux-$monthAux-" . $arrayTimerEventData["TMREVN_DAY"] . " $hhmmss";
                            } else {
                                $nextRunDate = date("Y-m-d", strtotime("last day of " . $arrayMonthsShort[((int)($monthAux)) - 1] . " $yearAux")) . " $hhmmss";
                            }
                        }
                    }
                    break;
                case "EVERY":
                    if ($arrayTimerEventData["TMREVN_HOUR"] . "" != "") {
                        $nextRunDate = date("Y-m-d H:i:s", strtotime("$nextRunDate +" . ((int)($arrayTimerEventData["TMREVN_HOUR"])) . " hours"));
                    }
                    if ($arrayTimerEventData["TMREVN_MINUTE"] . "" != "") {
                        $nextRunDate = date("Y-m-d H:i:s", strtotime("$nextRunDate +" . ((int)($arrayTimerEventData["TMREVN_MINUTE"])) . " minutes"));
                    }
                    break;
            }
            //Return
            return date("Y-m-d H:i:s", strtotime($nextRunDate));
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
    public function getNextRunDateByDataAndDatetime(array $arrayTimerEventData, $datetime, $flagIncludeDatetime = true)
    {
        try {
            $nextRunDate = $datetime;
            //Get Next Run Date
            if (!is_array($arrayTimerEventData["TMREVN_CONFIGURATION_DATA"])) {
                $arrayTimerEventData["TMREVN_CONFIGURATION_DATA"] = unserialize($arrayTimerEventData["TMREVN_CONFIGURATION_DATA"]);
            }
            $timeDatetime = strtotime($datetime);
            $flagNextRunDate = true;
            switch ($arrayTimerEventData["TMREVN_OPTION"]) {
                case "HOURLY":
                case "DAILY":
                case "MONTHLY":
                //case "EVERY":
                    $nextRunDate = $this->getValidNextRunDateByDataAndDatetime($arrayTimerEventData, $arrayTimerEventData["TMREVN_START_DATE"] . " 00:00:00", $flagIncludeDatetime);
                    $timeNextRunDate = strtotime($nextRunDate);
                    if ($timeNextRunDate > $timeDatetime) {
                        $flagNextRunDate = false;
                    }
                    break;
            }
            if ($flagNextRunDate) {
                switch ($arrayTimerEventData["TMREVN_OPTION"]) {
                    case "HOURLY":
                    case "DAILY":
                    case "MONTHLY":
                    case "EVERY":
                        $nextRunDate = $this->getValidNextRunDateByDataAndDatetime($arrayTimerEventData, $datetime, $flagIncludeDatetime);
                        $timeNextRunDate = strtotime($nextRunDate);
                        if ($timeNextRunDate < $timeDatetime) {
                            $nextRunDate = $this->getValidNextRunDateByDataAndDatetime($arrayTimerEventData, $datetime, false);
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
    public function unsetFields(array $arrayData)
    {
        try {
            unset($arrayData["TMREVN_UID"]);
            unset($arrayData["PRJ_UID"]);
            unset($arrayData["TMREVN_LAST_RUN_DATE"]);
            unset($arrayData["TMREVN_LAST_EXECUTION_DATE"]);
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
    public function exists($timerEventUid)
    {
        try {
            $result = $this-objMysql->_select("workflow.event_timer, [], ["id" => $timerEventUid]);
            return (isset($result[0]) && !empty($result[0]) ? true : false;
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
    public function existsEvent($projectUid, $eventUid, $timerEventUidToExclude = "")
    {
        try {
        
        $sql = "SELECT * FROM workflow.status_mapping sm INNER JOIN workflow.timer_event te ON te.task_id = sm.id WHERE sm.id = ?";
          
            if ($timerEventUidToExclude != "") {
               $sql .= " AND te.id != ?";
            }
          $result = $this->objMysql__query($sql, $arrParameters);
          
            return isset($result[0]) && !empty($result[0]) ? true : false;
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
    public function throwExceptionIfNotExistsTimerEvent($timerEventUid, $fieldNameForException)
    {
        try {
            if (!$this->exists($timerEventUid)) {
                throw new \Exception("ID_TIMER_EVENT_DOES_NOT_EXIST");
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
    public function throwExceptionIfEventIsRegistered($projectUid, $eventUid, $fieldNameForException, $timerEventUidToExclude = "")
    {
        try {
            if ($this->existsEvent($projectUid, $eventUid, $timerEventUidToExclude)) {
                throw new \Exception("ID_TIMER_EVENT_ALREADY_REGISTERED");
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
    public function throwExceptionIfDataIsInvalid($timerEventUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayTimerEventData = ($timerEventUid == "")? array() : $this->getTimerEvent($timerEventUid, true);
            $flagInsert = ($timerEventUid == "")? true : false;
            $arrayFinalData = array_merge($arrayTimerEventData, $arrayData);
            //Verify data - Field definition
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfDataNotMetFieldDefinition($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            //Verify data
            if (isset($arrayData["EVN_UID"])) {
                $arrayEventType   = array("START", "INTERMEDIATE");
                $arrayEventMarker = array("TIMER");
                $bpmnEvent = \BpmnEventPeer::retrieveByPK($arrayData["EVN_UID"]);
                if (is_null($bpmnEvent)) {
                    throw new \Exception("ID_EVENT_NOT_EXIST");
                }
                if (!in_array($bpmnEvent->getEvnType(), $arrayEventType) || !in_array($bpmnEvent->getEvnMarker(), $arrayEventMarker)) {
                    throw new \Exception("ID_EVENT_NOT_IS_TIMER_EVENT");
                }
                if ($bpmnEvent->getPrjUid() != $projectUid) {
                    throw new \Exception("ID_EVENT_EVENT_NOT_BELONG_TO_PROJECT");
                }
                $this->throwExceptionIfEventIsRegistered($projectUid, $arrayData["EVN_UID"], $this->arrayFieldNameForException["eventUid"], $timerEventUid);
            }
            //Verify data - Field definition
            $arrayFieldDefinition = array();
            $bpmnEvent = \BpmnEventPeer::retrieveByPK($arrayFinalData["EVN_UID"]);
            switch ($bpmnEvent->getEvnType()) {
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
            if (!empty($arrayFieldDefinition)) {
                $process->throwExceptionIfDataNotMetFieldDefinition($arrayFinalData, $arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            }
            $arrayFieldDefinition = array();
            $arrayValidateData = array(
                "TMREVN_DAY"    => array("/^(?:0[1-9]|[12][0-9]|3[01])$/", $this->arrayFieldNameForException["timerEventDay"]),
                "TMREVN_HOUR"   => array("/^(?:[0-1]\d|2[0-3])$/",         $this->arrayFieldNameForException["timerEventHour"]),
                "TMREVN_MINUTE" => array("/^(?:[0-5]\d)$/",                $this->arrayFieldNameForException["timerEventMinute"])
            );
            switch ($arrayFinalData["TMREVN_OPTION"]) {
                case "HOURLY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE" => array("type" => "date",   "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE"   => array("type" => "date",   "required" => false, "empty" => true,  "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_MINUTE"     => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute")
                    );
                    break;
                case "DAILY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE"         => array("type" => "date",   "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE"           => array("type" => "date",   "required" => false, "empty" => true,  "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_HOUR"               => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE"             => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute"),
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "array",  "required" => false, "empty" => true,  "defaultValues" => array(1, 2, 3, 4, 5, 6, 7), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
                case "MONTHLY":
                    $arrayFieldDefinition = array(
                        "TMREVN_START_DATE"         => array("type" => "date",   "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventStartDate"),
                        "TMREVN_END_DATE"           => array("type" => "date",   "required" => false, "empty" => true,  "defaultValues" => array(), "fieldNameAux" => "timerEventEndDate"),
                        "TMREVN_DAY"                => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventDay"),
                        "TMREVN_HOUR"               => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE"             => array("type" => "string", "required" => true,  "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute"),
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "array",  "required" => false, "empty" => true,  "defaultValues" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
                case "EVERY":
                    $arrayFieldDefinition = array(
                        "TMREVN_HOUR"   => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventHour"),
                        "TMREVN_MINUTE" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventMinute")
                    );
                    $arrayValidateData["TMREVN_HOUR"][0]   = "/^(?:0?\d|[1-9]\d*)$/";
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
                    $arrayValidateData["TMREVN_DAY"][0]    = "/^(?:0?\d|[1-9]\d*)$/";
                    $arrayValidateData["TMREVN_HOUR"][0]   = "/^(?:0?\d|[1-9]\d*)$/";
                    $arrayValidateData["TMREVN_MINUTE"][0] = "/^(?:0?\d|[1-9]\d*)$/";
                    break;
                case "WAIT-UNTIL-SPECIFIED-DATE-TIME":
                    $arrayFieldDefinition = array(
                        "TMREVN_CONFIGURATION_DATA" => array("type" => "string", "required" => true, "empty" => false, "defaultValues" => array(), "fieldNameAux" => "timerEventConfigurationData")
                    );
                    break;
            }
            if (!empty($arrayFieldDefinition)) {
                $process->throwExceptionIfDataNotMetFieldDefinition($arrayFinalData, $arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
            }
            foreach ($arrayValidateData as $key => $value) {
                if (isset($arrayData[$key]) && !preg_match($value[0], $arrayData[$key])) {
                    throw new \Exception(\G::LoadTranslation("ID_INVALID_VALUE", array($value[1])));
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
    public function create($projectUid, array $arrayData)
    {
        try {
            //Verify data
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$validator = new \ProcessMaker\BusinessModel\Validator();
            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");
           
           
            //Verify data
            //$process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            $this->throwExceptionIfDataIsInvalid("", $projectUid, $arrayData);
            //Create
           
            $arrayData = $this->unsetFields($arrayData);
            try {
                $timerEvent = new \TimerEvent();
                $bpmnEvent = \BpmnEventPeer::retrieveByPK($arrayData["EVN_UID"]);
                $timerEventUid = \ProcessMaker\Util\Common::generateUID();
                $arrayData["TMREVN_START_DATE"]    = (isset($arrayData["TMREVN_START_DATE"]) && $arrayData["TMREVN_START_DATE"] . "" != "")?       $arrayData["TMREVN_START_DATE"] : null;
                $arrayData["TMREVN_END_DATE"]      = (isset($arrayData["TMREVN_END_DATE"]) && $arrayData["TMREVN_END_DATE"] . "" != "")?           $arrayData["TMREVN_END_DATE"] : null;
                $arrayData["TMREVN_NEXT_RUN_DATE"] = (isset($arrayData["TMREVN_NEXT_RUN_DATE"]) && $arrayData["TMREVN_NEXT_RUN_DATE"] . "" != "")? $arrayData["TMREVN_NEXT_RUN_DATE"] : null;
                $arrayData["TMREVN_CONFIGURATION_DATA"] = serialize((isset($arrayData["TMREVN_CONFIGURATION_DATA"]))? $arrayData["TMREVN_CONFIGURATION_DATA"] : "");
                $timerEvent->fromArray($arrayData, \BasePeer::TYPE_FIELDNAME);
                $timerEvent->setTmrevnUid($timerEventUid);
                $timerEvent->setPrjUid($projectUid);
                if ($bpmnEvent->getEvnType() == "START") {
                    switch ($arrayData["TMREVN_OPTION"]) {
                        case "HOURLY":
                        case "DAILY":
                        case "MONTHLY":
                        case "EVERY":
                            $timerEvent->setTmrevnNextRunDate($this->getNextRunDateByDataAndDatetime($arrayData, date("Y-m-d H:i:s")));
                            break;
                    }
                }
                if ($timerEvent->validate()) {
                    
                    $result = $timerEvent->save();
                    $cnn->commit();
                    //Return
                    return $this->getTimerEvent($timerEventUid);
                } else {
                    $msg = "";
                    foreach ($timerEvent->getValidationFailures() as $message) {
                        $msg = $msg . (($msg != "")? "\n" : "") . $message;
                    }
                    throw new \Exception("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "")? "\n" . $msg : ""));
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
    public function getTimerEvents($projectUid)
    {
        try {
            $arrayTimerEvent = array();
            //Verify data
           // $process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            //Get data
           $results = $this->objMysql->_select("workflow.timer_event", [], ["workflow_id" => $projectUid]);
            foreach($results as $result) {
                $row = $rsCriteria->getRow();
                $row["TMREVN_CONFIGURATION_DATA"] = unserialize($row["TMREVN_CONFIGURATION_DATA"]);
                $arrayTimerEvent[] = $this->getTimerEventDataFromRecord($row);
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
    public function getTimerEvent($timerEventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsTimerEvent($timerEventUid, $this->arrayFieldNameForException["timerEventUid"]);
            //Get data
           $result = $this->objMysql->_select("workflow.timer_event", [], ["id" => $timerEventUid]);
            $row = $result[0];
            $row["TMREVN_CONFIGURATION_DATA"] = unserialize($row["TMREVN_CONFIGURATION_DATA"]);
            //Return
            return (!$flagGetRecord)? $this->getTimerEventDataFromRecord($row) : $row;
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
    public function getTimerEventByEvent($projectUid, $eventUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $process = new \ProcessMaker\BusinessModel\Process();
            $bpmnEvent = \BpmnEventPeer::retrieveByPK($eventUid);
            $process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            if (is_null($bpmnEvent)) {
                throw new \Exception(\G::LoadTranslation("ID_EVENT_NOT_EXIST", array($this->arrayFieldNameForException["eventUid"], $eventUid)));
            }
            if ($bpmnEvent->getPrjUid() != $projectUid) {
                throw new \Exception(\G::LoadTranslation("ID_EVENT_EVENT_NOT_BELONG_TO_PROJECT", array($this->arrayFieldNameForException["eventUid"], $eventUid, $this->arrayFieldNameForException["projectUid"], $projectUid)));
            }
            //Get data
            if (!$this->existsEvent($projectUid, $eventUid)) {
                //Return
                return array();
            }
            $criteria = $this->getTimerEventCriteria();
            $criteria->add(\TimerEventPeer::PRJ_UID, $projectUid, \Criteria::EQUAL);
            $criteria->add(\TimerEventPeer::EVN_UID, $eventUid, \Criteria::EQUAL);
            $rsCriteria = \TimerEventPeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(\ResultSet::FETCHMODE_ASSOC);
            $result = $rsCriteria->next();
            $row = $rsCriteria->getRow();
            $row["TMREVN_CONFIGURATION_DATA"] = unserialize($row["TMREVN_CONFIGURATION_DATA"]);
            //Return
            return (!$flagGetRecord)? $this->getTimerEventDataFromRecord($row) : $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }
   
}
