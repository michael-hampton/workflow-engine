<?php

class FileLoggerException extends Exception
{
    
}

/**
 * File logger
 * 
 * Log notices, warnings, errors or fatal errors into a log file.
 * 
 * @author gehaxelt
 */
class Log
{

    /**
     * Holds the file handle.
     * 
     * @var resource
     */
    protected $fileHandle = null;

    /**
     * The time format to show in the log.
     * 
     * @var string
     */
    protected $timeFormat = 'd.m.Y - H:i:s';

    /**
     * The file permissions.
     */
    const FILE_CHMOD = 756;
    const NOTICE = '[NOTICE]';
    const WARNING = '[WARNING]';
    const ERROR = '[ERROR]';
    const FATAL = '[FATAL]';

    /**
     * Opens the file handle.
     * 
     * @param string $logfile The path to the loggable file.
     */
    public function __construct ($logfile)
    {
        if ( $this->fileHandle == null )
        {
            $this->openLogFile ($logfile);
        }
    }

    /**
     * Closes the file handle.
     */
    public function __destruct ()
    {
        $this->closeLogFile ();
    }

    /**
     * Logs the message into the log file.
     * 
     * @param  string $message     The log message.
     * @param  int    $messageType Optional: urgency of the message.
     */
    public function log ($message, $messageType = Log::WARNING)
    {
        if ( $this->fileHandle == null )
        {
            throw new FileLoggerException ('Logfile is not opened.');
        }

        if ( is_array ($message) )
        {
            $string = "";

            foreach ($message as $key => $value) {
                if ( is_array ($value) )
                {
                    /* TODO */
                }
                else
                {
                    $string .= "\t {" . $key . ': ' . $value . "}\n ";
                }
            }

            $message = $string;
        }

        if ( $messageType != Log::NOTICE &&
                $messageType != Log::WARNING &&
                $messageType != Log::ERROR &&
                $messageType != Log::FATAL
        )
        {
            throw new FileLoggerException ('Wrong $messagetype given.');
        }

        $this->writeToLogFile ("[" . $this->getTime () . "]" . $messageType . " - " . $message);
    }

    /**
     * Writes content to the log file.
     * 
     * @param string $message
     */
    private function writeToLogFile ($message)
    {

        try {
            flock ($this->fileHandle, LOCK_EX);
            fwrite ($this->fileHandle, $message . PHP_EOL);
            flock ($this->fileHandle, LOCK_UN);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns the current timestamp.
     * 
     * @return string with the current date
     */
    private function getTime ()
    {
        return date ($this->timeFormat);
    }

    /**
     * Closes the current log file.
     */
    protected function closeLogFile ()
    {
        if ( $this->fileHandle != null )
        {
            fclose ($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * Opens a file handle.
     * 
     * @param string $logFile Path to log file.
     */
    public function openLogFile ($logFile)
    {
        $this->closeLogFile ();

        if ( !is_dir (dirname ($logFile)) )
        {
            if ( !mkdir (dirname ($logFile), Log::FILE_CHMOD, true) )
            {
                throw new FileLoggerException ('Could not find or create directory for log file.');
            }
        }

        if ( !$this->fileHandle = fopen ($logFile, 'a+') )
        {
            throw new FileLoggerException ('Could not open file handle.');
        }
    }

}
