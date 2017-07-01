<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarHolidays
 *
 * @author michael.hampton
 */
class CalendarHolidays extends BaseCalendarHolidays
{

    private $objMysql;

    public function __construct ()
    {
        parent::__construct();
        $this->objMysql = new Mysql2();
    }

    public function getCalendarHolidays ($CalendarUid)
    {

        $sql = "SELECT CALENDAR_UID, CALENDAR_HOLIDAY_NAME, CALENDAR_HOLIDAY_START, CALENDAR_HOLIDAY_END FROM calendar.calendar_holidays WHERE CALENDAR_UID = ?";
        $arrParameters = array($CalendarUid);
        $results = $this->objMysql->_query ($sql, $arrParameters);

        $fields = array();
        $count = 0;

        if ( !empty ($results) )
        {
            foreach ($results as $row) {
                $a = explode (" ", $row['CALENDAR_HOLIDAY_START']);
                $row['CALENDAR_HOLIDAY_START'] = $a[0];
                $a = explode (" ", $row['CALENDAR_HOLIDAY_END']);
                $row['CALENDAR_HOLIDAY_END'] = $a[0];
                $fields[$count] = $row;

                $count++;
            }
        }


        return $fields;
    }

    private function retrieveByPK ($CalendarUid, $CalendarHolidayName)
    {
        $result = $this->objMysql->_select ("workflow.calendar_holidays", [], ["CALENDAR_UID" => $CalendarUid, "CALENDAR_HOLIDAY_NAME" => $CalendarHolidayName]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objHoliday = new CalendarHolidays();
        return $objHoliday;
    }

    public function deleteAllCalendarHolidays ($CalendarUid)
    {
        $toDelete = $this->getCalendarHolidays ($CalendarUid);
        foreach ($toDelete as $key => $holidayInfo) {
            $CalendarUid = $holidayInfo['CALENDAR_UID'];
            $CalendarHolidayName = $holidayInfo['CALENDAR_HOLIDAY_NAME'];
            $CalendarHolidayStart = $holidayInfo['CALENDAR_HOLIDAY_START'];
            $CalendarHolidayEnd = $holidayInfo['CALENDAR_HOLIDAY_END'];
            //if exists the row in the database propel will update it, otherwise will insert.
            $tr = $this->retrieveByPK ($CalendarUid, $CalendarHolidayName);
            if ( ( is_object ($tr) && get_class ($tr) == 'CalendarHolidays' ) )
            {
                $this->objMysql->_delete ("calendar.calendar_holidays", ["CALENDAR_UID" => $CalendarUid, "CALENDAR_HOLIDAY_NAME" => $CalendarHolidayName]);
            }
        }
    }

    public function saveCalendarHolidays ($aData)
    {
        $CalendarUid = $aData['CALENDAR_UID'];
        $CalendarHolidayName = $aData['CALENDAR_HOLIDAY_NAME'];
        $CalendarHolidayStart = $aData['CALENDAR_HOLIDAY_START'];
        $CalendarHolidayEnd = $aData['CALENDAR_HOLIDAY_END'];
        //if exists the row in the database propel will update it, otherwise will insert.
        $tr = $this->retrieveByPK ($CalendarUid, $CalendarHolidayName);

        if ( !( is_object ($tr) && get_class ($tr) == 'CalendarHolidays' ) )
        {
            $tr = new CalendarHolidays();
        }

        $tr->setCalendarUid ($CalendarUid);
        $tr->setCalendarHolidayName ($CalendarHolidayName);
        $tr->setCalendarHolidayStart ($CalendarHolidayStart);
        $tr->setCalendarHolidayEnd ($CalendarHolidayEnd);
        if ( $tr->validate () )
        {
            $id = $tr->save ();
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
        //to do: uniform  coderror structures for all classes
    }

}
