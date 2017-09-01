<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorHandler
 *
 * @author michael.hampton
 */
class ErrorHandler
{

    /**
     * Custom error handler
     * @param integer $code
     * @param string $description
     * @param string $file
     * @param interger $line
     * @param mixed $context
     * @return boolean
     */
    function handleError ($code, $description, $file = null, $line = null, $context = null)
    {
        $displayErrors = ini_get ("display_errors");
        $displayErrors = strtolower ($displayErrors);
        if ( error_reporting () === 0 )
        {
            return false;
        }
        list($error, $log) = $this->mapErrorCode ($code);
        $data = array(
            'level' => $log,
            'code' => $code,
            'error' => $error,
            'description' => $description,
            'file' => $file,
            'line' => $line,
            'context' => $context,
            'path' => $file,
            'message' => $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']'
        );
        
        $this->sendEmail(array("bluetiger_uan@yahoo.com"), $code, $description, $context);
        
        return $this->fileLog ($data);
    }

    /**
     * This method is used to write data in file
     * @param mixed $logData
     * @param string $fileName
     * @return boolean
     */
    function fileLog ($logData, $fileName = ERROR_LOG_FILE)
    {
        $fh = fopen ($fileName, 'a+');
        if ( is_array ($logData) )
        {
            $logData = print_r ($logData, 1);
        }
        $status = fwrite ($fh, $logData);
        fclose ($fh);
        
        return ($status) ? true : false;
    }

    /**
     * Map an error code into an Error word, and log location.
     *
     * @param int $code Error code to map
     * @return array Array of error word, and log location.
     */
    function mapErrorCode ($code)
    {
        $error = $log = null;
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                $log = LOG_ERR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                $log = LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                $log = LOG_NOTICE;
                break;
            case E_STRICT:
                $error = 'Strict';
                $log = LOG_NOTICE;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $error = 'Deprecated';
                $log = LOG_NOTICE;
                break;
            default :
                break;
        }
        return array($error, $log);
    }

    /**
     * sendEmail
     *
     * sends log messages in a structured html formatted email
     *
     * @param array $arr_email_address
     * @param string $level
     * @param string $message
     * @param $context
     * @return bool
     */
    private function sendEmail (array $arr_email_address = array(), $level = "info", $message = "", $context)
    {

        if ( empty ($arr_email_address) )
        {

            return TRUE;
        }

        // create email heading
        if ( in_array ($level, ['emergency', 'alert', 'critical']) )
        {

            $colour = "red";
        }
        elseif ( in_array ($level, ['error', 'warning']) )
        {

            $colour = "orange";
        }
        else
        {

            $colour = "green";
        }

        $email_heading = "<div style='width:100%; padding:5% 0 5% 0; color:white; background-color:{$colour};'>";
        $email_heading .= "<H1>" . strtoupper ($level) . "</H1>";
        $email_heading .= "</div>";

        // get the debug backtrace data to include in the message details
        $debTrace = debug_backtrace (0, 9);
        foreach ($debTrace as &$t) {
            unset ($t['args']);
            unset ($t['object']);
        }
        $log_position_string = "";
        if ( isset ($context['error_number']) )
        {

            $log_position_string .= "ERROR_CODE : " . $context['error_number'] . "<br>";
        }
        if ( isset ($message) )
        {

            $log_position_string.= "MESSAGE : <b>" . json_encode ($message) . "</b><br>";
        }
        if ( !isset ($context['error']) || $context['error'] !== false )
        {

            if ( isset ($context['line']) )
            {

                $log_position_string .= "LINE : <b>" . $context['line'] . "</b><br>";
            }
            else
            {

                $log_position_string .= "LINE : <b>" . $debTrace[1]['line'] . "</b><br>";
            }
            if ( isset ($context['file']) )
            {

                $log_position_string .= "FILE : <b>" . $context['file'] . "</b><br><br>";
            }
            else
            {

                $log_position_string .= "FILE : <b>" . $debTrace[1]['file'] . "</b><br><br>";
            }

            $strTrace = "<pre>" . print_r ($debTrace, true) . "</pre>";
            $log_position_string .= "BACKTRACE : " . $strTrace . "<br>";
        }

        // use wordwrap() if lines are longer than 70 characters
        $formatted_message = wordwrap ($log_position_string, 70);

        $headers = "From: " . strtolower ($_SERVER['HTTP_HOST']) . "Debugger  \r\n" .
                "content-type: text/html";

        mail (implode (', ', $arr_email_address), strtoupper (APP_ENVIRONMENT . " : " . $level), $email_heading .
                $formatted_message, $headers);
    }

}
