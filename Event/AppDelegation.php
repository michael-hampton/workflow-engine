<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppDelegation
 *
 * @author michael.hampton
 */
class AppDelegation
{

    private $del_delegate_date;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getRisk ()
    {
        try {
            $risk = 0.2;
            //Return
            return $risk;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Set the value of [del_delegate_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setDelDelegateDate ($v)
    {
        if ( $v !== null && !is_int ($v) )
        {
            $ts = strtotime ($v);
            //Date/time accepts null values
            if ( $v == '' )
            {
                $ts = null;
            }
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse date/time value for [del_delegate_date] from input: " .
                var_export ($v, true));
            }
        }
        else
        {
            $ts = $v;
        }
        if ( $this->del_delegate_date !== $ts )
        {
            $this->del_delegate_date = date ("Y-m-d H:i:s");
        }
    }

// setDelDelegateDate()

    /**
     * Get the [optionally formatted] [del_delegate_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelDelegateDate ($format = 'Y-m-d H:i:s')
    {
        if ( $this->del_delegate_date === null || $this->del_delegate_date === '' )
        {
            return null;
        }
        elseif ( !is_int ($this->del_delegate_date) )
        {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime ($this->del_delegate_date);
            if ( $ts === -1 || $ts === false )
            {
                throw new PropelException ("Unable to parse value of [del_delegate_date] as date/time value: " .
                var_export ($this->del_delegate_date, true));
            }
        }
        else
        {
            $ts = $this->del_delegate_date;
        }
        if ( $format === null )
        {
            return $ts;
        }
        elseif ( strpos ($format, '%') !== false )
        {
            return strftime ($format, $ts);
        }
        else
        {
            return date ($format, $ts);
        }
    }

    public function calculateRiskDate (Flow $objFlow, $dueDate, $risk)
    {
        try {
            $data = array();
            $data['TAS_DURATION'] = $objFlow->getTasDuration ();
            $data['TAS_TIMEUNIT'] = $objFlow->getTasTimeUnit ();
            $data['TAS_TYPE_DAY'] = $objFlow->getTasTypeDay ();

            $riskTime = $data['TAS_DURATION'] - ($data['TAS_DURATION'] * $risk);

            //Calendar - Use the dates class to calculate dates
            $calendar = new \BusinessModel\Calendar();
            $arrayCalendarData = array();
            if ( $calendar->pmCalendarUid == "" )
            {
                $calendar->getCalendar ($objFlow->getCalendarUid ());
                $arrayCalendarData = $calendar->getCalendarData ($objFlow->getCalendarUid ());
            }

            $this->setDelDelegateDate ('now');

            //Risk date
            $riskDate = $calendar->dashCalculateDate ($this->getDelDelegateDate (), $riskTime, $data['TAS_TIMEUNIT'], $arrayCalendarData);

            return $riskDate;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function calculateDueDate (Task $objTask)
    {
        
        $aData['TAS_DURATION'] = $objTask->getTasDuration ();
        $aData['TAS_TIMEUNIT'] = $objTask->getTasTimeunit ();
        $aData['TAS_TYPE_DAY'] = $objTask->getTasTypeDay ();
        
        if ( trim ($objTask->getCalendarUid ()) !== "" )
        {
            $aCalendarUID = $objTask->getCalendarUid ();
        }
        else
        {
            $aCalendarUID = '';
        }

        $calendar = new \BusinessModel\Calendar();
        $arrayCalendarData = $calendar->getCalendarData ($aCalendarUID);
        
        if ( $calendar->pmCalendarUid == "" )
        {
            $calendar->getCalendar (null, $this->getProUid (), $this->getTasUid ());
            $arrayCalendarData = $calendar->getCalendarData ($aCalendarUID);
        }

        $initDate = date ('Y-m-d H:i:s', strtotime ('now'));
        $date = new DateTime ($initDate);
        $timezone = 'Europe/London';
        $date->setTimezone (new DateTimeZone ($timezone)); // +04
        $timezone = $date->format ('Y-m-d H:i:s');
        
        $dueDate = $calendar->dashCalculateDate ($initDate, $aData["TAS_DURATION"], $aData["TAS_TIMEUNIT"], $arrayCalendarData);

        return $dueDate;
    }

    public function test ()
    {
        $objTask = new Task();
        $objTask->setTasDuration(5);
        $objTask->setTasTimeunit ("DAYS");
        $objTask->setTasTypeDay ("CALENDAR DAYS");
        $objTask->setCalendarUid (15);

        $delTaskDueDate = $this->calculateDueDate ($objFlow);
        $delRiskDate = $this->calculateRiskDate ($objFlow, date ("Y-m-d H:i:s"), $this->getRisk ());

    }

    public function calculateDuration ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $results = $this->objMysql->_query ("SELECT ca.*, m.step_condition FROM calendar.`calendar_assignees` ca
                                            INNER JOIN workflow.status_mapping M ON m.id = ca.USER_UID
                                            WHERE OBJECT_TYPE = 'task'");


        $arrDone = array();
        $arrValues = [];

        foreach ($results as $result) {

            $objCases = new BusinessModel\Cases();
            $arrCases = $objCases->getCasesForTask (new Flow ($result['USER_UID']));

            $now = new DateTime();
            $calendar = new \BusinessModel\Calendar();

            foreach ($arrCases['rows'] as $parentId => $caseId) {

                if ( !in_array ($result['USER_UID'], $arrDone) )
                {
                    $calendar->getCalendar ($result['CALENDAR_UID']);
                    $calData = $calendar->getCalendarData ($result['CALENDAR_UID']);
                    $calculatedValues = $this->getValuesToStoreForCalculateDuration (array("case_id" => $caseId, "parentId" => $parentId, "TASK" => $result['step_condition']), $calendar, $calData, $now);
                    $calculatedValues['elementId'] = $caseId;
                    $calculatedValues['parentId'] = $parentId;
                    
                    return $calculatedValues;

                    $arrValues[] = $calculatedValues;
                }
            }

            $arrDone[] = $result['USER_UID'];
        }
    }

    public function getValuesToStoreForCalculateDuration ($row, $calendar, $calData, $nowDate)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }


        $result = $this->objMysql->_select ("workflow.workflow_data", [], ["object_id" => $row['parentId']]);
        $auditData = json_decode ($result[0]['audit_data'], true);
        $workflowData = json_decode ($result[0]['workflow_data'], true);
        $arrData = [];

        foreach ($workflowData['elements'] as $elementId => $element) {

            if ( $elementId === $row['case_id'] )
            {
                $arrData['workflow_object'] = $element;
            }
        }


        foreach ($auditData['elements'] as $elementId => $element) {
            if ( $elementId === $row['case_id'] )
            {
                $arrData['audit_object'] = $element;
            }
        }

        $arrData['TASK'] = json_decode ($row['TASK'], true);

        $rowValues = $this->completeRowDataForCalculateDuration ($arrData, $nowDate);

        $arrMike = array(
            "dInitDate" => $rowValues['dInitDate']->format ('Y-m-d H:i:s'),
            "dDueDate" => $rowValues['dDueDate']->format ('Y-m-d H:i:s'),
            "dDelegateDate" => $rowValues['dDelegateDate']->format ('Y-m-d H:i:s'),
            "dNow" => $rowValues['dNow']->format ('Y-m-d H:i:s'),
            "fTaskDuration" => $arrData['TASK']['task_properties']['TAS_DURATION'],
            "cTaskDurationUnit" => $arrData['TASK']['task_properties']['TAS_TIMEUNIT'],
        );

        $arrMike['dFinishDate'] = trim ($rowValues['dFinishDate']) !== "" ? $rowValues['dFinishDate']->format ('Y-m-d H:i:s') : date ("Y-m-d H:i:s", strtotime ("+2 days"));
        ;


        return Array(
            'isStarted' => $this->createDateFromString ($arrMike['dInitDate']) != null ? 1 : 0,
            'isFinished' => $this->createDateFromString ($arrMike['dFinishDate']) != null ? 1 : 0,
            'isDelayed' => $this->calculateDelayTime ($calendar, $calData, $arrMike) > 0 ? 1 : 0,
            'queueTime' => $this->calculateQueueTime ($calendar, $calData, $arrMike),
            'delayTime' => $this->calculateDelayTime ($calendar, $calData, $arrMike),
            'durationTime' => $this->calculateNetProcessingTime ($calendar, $calData, $arrMike),
            'percentDelay' => $this->calculateOverduePercentage ($calendar, $calData, $arrMike)
        );
    }

    //time in days from init or delegate date to finish or today's date
    private function calculateNetProcessingTime ($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $this->selectDate ($rowValues['dInitDate'], $rowValues['dDelegateDate'], 'max');
        $endDateForCalc = $this->selectDate ($rowValues['dFinishDate'], $rowValues['dNow'], 'min');
        return $calendar->dashCalculateDurationWithCalendar (
                        - (new DateTime ($initDateForCalc))->format ('Y-m-d H:i:s'), (new DateTime ($endDateForCalc))->format ('Y-m-d H:i:s'), $calData) / (24 * 60 * 60);
    }

    private function calculateOverduePercentage ($calendar, $calData, $rowValues)
    {
        if ( $rowValues['fTaskDuration'] == 0 )
        {
            return 0;
        }
        //TODO 8 daily/hours must be extracted from calendar
        $taskTime = ($rowValues['cTaskDurationUnit'] == 'DAYS') ? $rowValues['fTaskDuration'] * 8 / 24 : $rowValues['fTaskDuration'] / 24;
        return $this->calculateDelayTime ($calendar, $calData, $rowValues) * 100 / $taskTime;
    }

    //by default min function returns de null value if one of the params is null
    //to avoid that behaviour this function was created so the function returns the first
    //not null date or if both are not null the mix/max date
    //NOTE date1 and date2 are DateTime objects.
    private function selectDate ($date1, $date2, $compareFunction)
    {
        if ( $date1 == null )
            return $date2;
        if ( $date2 == null )
            return $date1;
        return $compareFunction ($date1, $date2);
    }

    //time in days from delegate date to init date
    private function calculateQueueTime ($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $rowValues['dDelegateDate'];

        $endDateForCalc = $this->selectDate ($rowValues['dInitDate'], $rowValues['dNow'], 'min');
        return $calendar->dashCalculateDurationWithCalendar (
                        (new DateTime ($initDateForCalc))->format ('Y-m-d H:i:s'), (new DateTime ($endDateForCalc))->format ('Y-m-d H:i:s'), $calData) / (24 * 60 * 60);
    }

    //time in days from due date to finish or today date
    private function calculateDelayTime ($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $rowValues['dDueDate'];
        $endDateForCalc = $rowValues['dFinishDate'];
        return $calendar->dashCalculateDurationWithCalendar (
                        (new DateTime ($initDateForCalc))->format ('Y-m-d H:i:s'), (new DateTime ($endDateForCalc))->format ('Y-m-d H:i:s'), $calData) / (24 * 60 * 60);
    }

    //Creates a DateTime object from a string. If the string is null or empty a null object is returned
    private function createDateFromString ($stringDate)
    {
        if ( $stringDate == null || $stringDate == '' )
            return null;
        return new DateTime ($stringDate);
    }

    function get_next ($array, $key)
    {
        $currentKey = key ($array);
        while ($currentKey !== null && $currentKey != $key) {
            next ($array);
            $currentKey = key ($array);
        }
        return next ($array);
    }

    //to avoid aplying many times the same conversions and functions the row data
    //is used to create dates as DateTime objects and other fields are stracted also,
    //so the array returned will work as a "context" object for the rest of the functions.
    private function completeRowDataForCalculateDuration ($row, $nowDate)
    {

        //$firstAudit = array_shift (array_slice ($row['audit_object']['steps'], 0, 1));
        $taskId = $row['TASK']['task_properties']['TAS_UID'];

        $step = $row['audit_object']['steps'][$taskId];

        $next = $this->get_next ($row['audit_object']['steps'], $taskId);

        return Array(
            'dDelegateDate' => $this->createDateFromString (date ("Y-m-d H:i:s")),
            'dInitDate' => $this->createDateFromString ($step['dateCompleted']),
            'dDueDate' => $this->createDateFromString (date ("Y-m-d H:i:s")),
            'dFinishDate' => $this->createDateFromString (!empty ($next) ? $next['dateCompleted'] : null),
            'fTaskDuration' => $row['TASK']['task_properties']['TAS_DURATION'] * 1.0,
            'cTaskDurationUnit' => $row['TASK']['task_properties']['TAS_TIMEUNIT'],
            'dNow' => $nowDate,
            'row' => $row
        );
    }

}
