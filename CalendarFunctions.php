<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarFunctions
 *
 * @author michael.hampton
 */
class CalendarFunctions
{

    /**
     * Small function used to add important information about the calcs and actions
     * to the log (that log will be saved)
     *
     * @name addCalendarLog
     * @param text $msg
     * @access public
     *
     */
    function addCalendarLog ($msg)
    {
        $this->calendarLog .= "\n" . date ("D M j G:i:s T Y") . ": " . $msg;
    }

}
