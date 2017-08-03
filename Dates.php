<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dates
 *
 * @author michael.hampton
 */
class Dates
{

    private $holidays = array();
    private $weekends = array();
    private $range = array();
    private $skipEveryYear = true;
    private $calendarDays = false; //by default we are using working days
    private $hoursPerDay = 8; //you should change this

    /**
     * Function that calculate a final date based on $sInitDate and $iDuration
     * This function also uses a Calendar component (class.calendar.php) where all the definition of
     * a User, task, Process or default calendar is defined.
     * base on that information is possible to setup different calendars
     * and apply them to a task, process or user. Each calendar have Working Days, Business Hours and Holidays
     *
     * @name calculateDate
     * @access public
     * @param date $sInitDate
     * @param double $iDuration
     * @param string $sTimeUnit
     * @param string $iTypeDay
     * @param string $UsrUid
     * @param string $ProUid
     * @param string $TasUid
     * @return array('DUE_DATE'=>'Final calculated date formatted as Y-m-d H:i:s','DUE_DATE_SECONDS'=>'Final calculated date in seconds','OLD_DUE_DATE'=>'Using deprecate4d function','OLD_DUE_DATE_SECONDS'=>'Using deprecated function','DUE_DATE_LOG'=>'Log of all the calculations made')
     * @todo test this function with negative durations (for events)
     */

    public function calculateDate ($sInitDate, $iDuration, $sTimeUnit, $iTypeDay, $UsrUid = null, $ProUid = null, $TasUid = null)
    {
        //$oldDate=$this->calculateDate_noCalendar( $sInitDate, $iDuration, $sTimeUnit, $iTypeDay, $UsrUid, $ProUid, $TasUid);
        //Set Calendar when the object is instanced in this order/priority (Task, User, Process, Default)
        $calendarObj = new CalendarFunctions ($UsrUid, $ProUid, $TasUid);
        //Get next Business Hours/Range based on :
        switch (strtoupper ($sTimeUnit)) {
            case 'DAYS':
                $hoursToProcess = $iDuration * 8;
                break; //In Hours
            default:
                $hoursToProcess = $iDuration;
                break; //In Hours
        }
        $dateArray = explode (" ", $sInitDate);
        $currentDate = $dateArray[0];
        $currentTime = isset ($dateArray[1]) ? $dateArray[1] : "00:00:00";
        $startTime = (float) array_sum (explode (' ', microtime ()));
        $calendarObj->addCalendarLog ("* Starting at: $startTime");
        $calendarObj->addCalendarLog (">>>>> Hours to Process: $hoursToProcess");
        $calendarObj->addCalendarLog (">>>>> Current Date: $currentDate");
        $calendarObj->addCalendarLog (">>>>> Current Time: $currentTime");
        $array_hours = explode (":", $currentTime);
        $seconds2 = $array_hours[2];
        $minutes2 = 0;
        while ($hoursToProcess > 0) {
            $validBusinessHour = $calendarObj->getNextValidBusinessHoursRange ($currentDate, $currentTime);
            //For Date/Time operations
            $currentDateA = explode ("-", $validBusinessHour['DATE']);
            $currentTimeA = explode (":", $validBusinessHour['TIME']);
            $hour = $currentTimeA[0];
            $minute = $currentTimeA[1];
            $second = isset ($currentTimeA[2]) ? $currentTimeA[2] : 0;
            $month = $currentDateA[1];
            $day = $currentDateA[2];
            $year = $currentDateA[0];
            $normalizedDate = date ("Y-m-d H:i:s", mktime ($hour, $minute, $second, $month, $day, $year));
            $normalizedDateInt = mktime ($hour, $minute, $second, $month, $day, $year);
            $normalizedDateSeconds = ($hour * 60 * 60) + ($minute * 60);
            $arrayHour = explode (".", $hoursToProcess);
            if ( isset ($arrayHour[1]) )
            {
                $minutes1 = $arrayHour[1];
                $cadm = strlen ($minutes1);
                $minutes2 = (($minutes1 / pow (10, $cadm)) * 60);
            }
            $possibleTime = date ("Y-m-d H:i:s", mktime ($hour + $hoursToProcess, $minute + $minutes2, $second + $seconds2, $month, $day, $year));
            $possibleTimeInt = mktime ($hour + $hoursToProcess, $minute + $minutes2, $second + $seconds2, $month, $day, $year);
            $offsetPermitedMinutes = "0";
            $calendarBusinessEndA = explode (":", $validBusinessHour['BUSINESS_HOURS']['CALENDAR_BUSINESS_END']);
            $calendarBusinessEndNormalized = date ("Y-m-d H:i:s", mktime ($calendarBusinessEndA[0], $calendarBusinessEndA[1] + $offsetPermitedMinutes, 0, $month, $day, $year));
            $calendarBusinessEndInt = mktime ($calendarBusinessEndA[0], $calendarBusinessEndA[1] + $offsetPermitedMinutes, 0, $month, $day, $year);
            $calendarBusinessEndSeconds = ($calendarBusinessEndA[0] * 60 * 60) + ($calendarBusinessEndA[1] * 60);
            $calendarObj->addCalendarLog ("Possible time: $possibleTime");
            $calendarObj->addCalendarLog ("Current Start Date/Time: $normalizedDate");
            $calendarObj->addCalendarLog ("Calendar Business End: $calendarBusinessEndNormalized");
            if ( $possibleTimeInt > $calendarBusinessEndInt )
            {
                $currentDateTimeB = explode (" ", $calendarBusinessEndNormalized);
                $currentDate = $currentDateTimeB[0];
                $currentTime = $currentDateTimeB[1];
                $diff = abs ($normalizedDateSeconds - $calendarBusinessEndSeconds);
                $diffHours = $diff / 3600;
                $hoursToProcess = $hoursToProcess - $diffHours;
            }
            else
            {
                $currentDateTimeA = explode (" ", $possibleTime);
                $currentDate = $currentDateTimeA[0];
                $currentTime = $currentDateTimeA[1];
                $hoursToProcess = 0;
            }
            $calendarObj->addCalendarLog ("** Hours to Process: $hoursToProcess");
        }
        $calendarObj->addCalendarLog ("+++++++++++ Calculated Due Date $currentDate $currentTime");
        $result['DUE_DATE'] = $currentDate . " " . $currentTime;
        $result['DUE_DATE_SECONDS'] = strtotime ($currentDate . " " . $currentTime);
        //$result['OLD_DUE_DATE']        = date("Y-m-d H:i:s",$oldDate);
        //$result['OLD_DUE_DATE_SECONDS']= $oldDate;
        $endTime = (float) array_sum (explode (' ', microtime ()));
        $calendarObj->addCalendarLog ("* Ending at: $endTime");
        $calcTime = round ($endTime - $startTime, 3);
        $calendarObj->addCalendarLog ("** Processing time: " . sprintf ("%.4f", ($endTime - $startTime)) . " seconds");
        $result['DUE_DATE_LOG'] = $calendarObj->calendarLog;
        return $result;
    }

}
