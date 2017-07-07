<?php

/**
 * CalendarAssignments.php
 * @package    workflow.engine.classes.model
 */

/**
 * Skeleton subclass for representing a row from the 'CALENDAR_ASSIGNMENTS' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    workflow.engine.classes.model
 */
class CalendarAssignment extends BaseCalendarAssignment
{

    private $objMysql;

    public function __construct ()
    {
        parent::__construct();
        $this->objMysql = new Mysql2();
    }

    public function retrieveByPk ($objectId, $objectType)
    {
        $result = $this->objMysql->_select("calendar.calendar_assignees", [], ["USER_UID" => $objectId, "OBJECT_TYPE" => $objectType]);
        
        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }
        
        $objCalendarAssignment = new CalendarAssignment();
        $objCalendarAssignment->setCalendarUid($result[0]['CALENDAR_UID']);
        
        return $objCalendarAssignment;
    }

}
