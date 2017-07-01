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

    public function __construct ()
    {
        
    }
    
     public function getRisk()
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
    public function setDelDelegateDate($v)
    {
        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            //Date/time accepts null values
            if ($v == '') {
                $ts = null;
            }
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [del_delegate_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->del_delegate_date !== $ts) {
            $this->del_delegate_date = date("Y-m-d H:i:s");
        }
    } // setDelDelegateDate()

    
     /**
     * Get the [optionally formatted] [del_delegate_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getDelDelegateDate($format = 'Y-m-d H:i:s')
    {
        if ($this->del_delegate_date === null || $this->del_delegate_date === '') {
            return null;
        } elseif (!is_int($this->del_delegate_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->del_delegate_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [del_delegate_date] as date/time value: " .
                    var_export($this->del_delegate_date, true));
            }
        } else {
            $ts = $this->del_delegate_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }
    
    public function calculateRiskDate(Flow $objFlow, $dueDate, $risk)
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
            if ($calendar->pmCalendarUid == "") {
                $calendar->getCalendar($objFlow->getCalendarUid ());
                $arrayCalendarData = $calendar->getCalendarData($objFlow->getCalendarUid ());
            }
            
             $this->setDelDelegateDate( 'now' );
            
            //Risk date
            $riskDate = $calendar->dashCalculateDate($this->getDelDelegateDate(), $riskTime, $data['TAS_TIMEUNIT'], $arrayCalendarData);
    
            return $riskDate;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function calculateDueDate (Flow $objFlow)
    {
        $aData['TAS_DURATION'] = $objFlow->getTasDuration ();
        $aData['TAS_TIMEUNIT'] = $objFlow->getTasTimeUnit ();
        $aData['TAS_TYPE_DAY'] = $objFlow->getTasTypeDay ();
        if ( trim ($objFlow->getCalendarUid ()) !== "" )
        {
            $aCalendarUID = $objFlow->getCalendarUid ();
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
            $arrayCalendarData = $calendar->getCalendarData ();
        }

        $initDate = date ('Y-m-d', strtotime ('-30 days'));
        $date = new DateTime ($initDate);
        $timezone = 'Europe/London';
        $date->setTimezone (new DateTimeZone ($timezone)); // +04
        $timezone = $date->format ('Y-m-d H:i:s');

        $dueDate = $calendar->dashCalculateDate($initDate, $aData["TAS_DURATION"], $aData["TAS_TIMEUNIT"], $arrayCalendarData);

        return $dueDate;
    }

    public function test ()
    {
        $objFlow = new Flow();
        $objFlow->setTasDuration (5);
        $objFlow->setTasTimeUnit ("DAYS");
        $objFlow->setTasTypeDay ("CALENDAR DAYS");
        $objFlow->setCalendarUid (15);

        $delTaskDueDate = $this->calculateDueDate($objFlow);
        $delRiskDate    = $this->calculateRiskDate($objFlow, date("Y-m-d H:i:s"), $this->getRisk());
    }

}
