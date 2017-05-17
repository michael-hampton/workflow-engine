<?php
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;

class D {
    public function __construct ()
    {
         
    }
    
    public function truncateFile()
    {
        $filename = DEBUG_LOCATION;

        $handle = fopen($filename, 'r+');
        ftruncate($handle, 100000);
        rewind($handle);
        echo fread($handle, filesize($filename));
        fclose($handle);
    }

        public function logInfo($message)
    {
        $logger = new FileAdapter(DEBUG_LOCATION);
        $logger->log($message, \Phalcon\Logger::INFO);
        //$this->truncateFile();
    }
    
    public function log($message)
    {
        $logger = new FileAdapter(DEBUG_LOCATION);
        $logger->log($message, \Phalcon\Logger::ERROR);
    }
    
    public function setLog($e)
    {
        $message = get_class($e) . ": " . $e->getMessage() . "\n";
        $message .= " File=" . $e->getFile() . "\n";
        $message .= " Line=" . $e->getLine() . "\n";
        $message .= $e->getTraceAsString();
        
        $this->log($message);
    }
}

