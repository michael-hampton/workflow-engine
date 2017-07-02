<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarBusinessHours
 *
 * @author michael.hampton
 */
class CalendarBusinessHours extends BaseCalendarBusinessHours
{

    private $objMysql;

    public function __construct ()
    {
        parent::__construct();
        $this->objMysql = new Mysql2();
    }

    public function getCalendarBusinessHours ($CalendarUid)
    {
        $sql = "SELECT CALENDAR_UID, CALENDAR_BUSINESS_DAY, CALENDAR_BUSINESS_START, CALENDAR_BUSINESS_END FROM calendar.calendar_business_hours WHERE CALENDAR_UID = ?";
        $sql .= " ORDER BY CALENDAR_BUSINESS_DAY DESC, CALENDAR_BUSINESS_START ASC";
        $arrParameters = array($CalendarUid);
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $fields = array();
        $count = 0;

        foreach ($results as $row) {
            $fields[$count] = $row;

            $count++;
        }

        return $fields;
    }

    public function retrieveByPK ($CalendarUid, $CalendarBusinessDay, $CalendarBusinessStart, $CalendarBusinessEnd)
    {
        $result = $this->objMysql->_select ("calendar.calendar_business_hours", [], ["CALENDAR_UID" => $CalendarUid, "CALENDAR_BUSINESS_DAY" => $CalendarBusinessDay, "CALENDAR_BUSINESS_START" => $CalendarBusinessStart, "CALENDAR_BUSINESS_END" => $CalendarBusinessEnd]);
    
        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }
        
       $objCalendar = new CalendarBusinessHours();
       
       return $objCalendar;
        
    }

    public function deleteAllCalendarBusinessHours ($CalendarUid)
    {
        $toDelete = $this->getCalendarBusinessHours ($CalendarUid);
        foreach ($toDelete as $key => $businessHoursInfo) {
            $CalendarUid = $businessHoursInfo['CALENDAR_UID'];
            $CalendarBusinessDay = $businessHoursInfo['CALENDAR_BUSINESS_DAY'];
            $CalendarBusinessStart = $businessHoursInfo['CALENDAR_BUSINESS_START'];
            $CalendarBusinessEnd = $businessHoursInfo['CALENDAR_BUSINESS_END'];
            //if exists the row in the database propel will update it, otherwise will insert.
            $tr = $this->retrieveByPK ($CalendarUid, $CalendarBusinessDay, $CalendarBusinessStart, $CalendarBusinessEnd);
            if ( ( is_object ($tr) && get_class ($tr) == 'CalendarBusinessHours' ) )
            {
                $this->objMysql->_delete ("calendar.calendar_business_hours", ["CALENDAR_UID" => $CalendarUid]);
            }
        }
    }

    public function saveCalendarBusinessHours ($aData)
    {
        $CalendarUid = $aData['CALENDAR_UID'];
        $CalendarBusinessDay = $aData['CALENDAR_BUSINESS_DAY'];
        $CalendarBusinessStart = $aData['CALENDAR_BUSINESS_START'];
        $CalendarBusinessEnd = $aData['CALENDAR_BUSINESS_END'];
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid, $CalendarBusinessDay, $CalendarBusinessStart, $CalendarBusinessEnd);
        if ( !( is_object ($tr) && get_class ($tr) == 'CalendarBusinessHours' ) )
        {
            $tr = new CalendarBusinessHours();
        }
        $tr->setCalendarUid ($CalendarUid);
        $tr->setCalendarBusinessDay ($CalendarBusinessDay);
        $tr->setCalendarBusinessStart ($CalendarBusinessStart);
        $tr->setCalendarBusinessEnd ($CalendarBusinessEnd);
        if ( $tr->validate () )
        {
            // we save it, since we get no validation errors, or do whatever else you like.
            $res = $tr->save ();
        }
        else
        {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = $CalendarBusinessDay . '<hr/>';
            $validationFailuresArray = $tr->getValidationFailures ();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage () . "<br/>";
            }
            //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
            G::SendTemporalMessage ($msg);
        }
    }

}
