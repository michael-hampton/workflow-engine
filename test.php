<?php

    class FileLoggerException extends Exception {}
    
    /**
     * File logger
     * 
     * Log notices, warnings, errors or fatal errors into a log file.
     * 
     * @author gehaxelt
     */
    class Log {
        
        /**
         * Holds the file handle.
         * 
         * @var resource
         */
        protected $fileHandle = NULL;
        
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
        public function __construct($logfile) {
            if($this->fileHandle == NULL){
                $this->openLogFile($logfile);
            }
        }
        
        /**
         * Closes the file handle.
         */
        public function __destruct() {
            $this->closeLogFile();
        }
        
        /**
         * Logs the message into the log file.
         * 
         * @param  string $message     The log message.
         * @param  int    $messageType Optional: urgency of the message.
         */
        public function log($message, $messageType = Log::WARNING) {
            if($this->fileHandle == NULL){
                throw new FileLoggerException('Logfile is not opened.');
            }
            
            if(is_array($message)) {
                $string = "";
                
                foreach($message as $key => $value) {
                    if(is_array($value)) {
                        /* TODO */
  
                    } else {
                        $string .=  "\t {".$key.': '.$value."}\n ";
                    }
                }
                
                $message = $string;
            }
            
            if($messageType != Log::NOTICE &&
               $messageType != Log::WARNING &&
               $messageType != Log::ERROR &&
               $messageType != Log::FATAL
            ){
                throw new FileLoggerException('Wrong $messagetype given.');
            }
            
            $this->writeToLogFile("[".$this->getTime()."]".$messageType." - ".$message);
        }
        
        /**
         * Writes content to the log file.
         * 
         * @param string $message
         */
        private function writeToLogFile($message) {
            
            try {
                 flock($this->fileHandle, LOCK_EX);
            fwrite($this->fileHandle, $message.PHP_EOL);
            flock($this->fileHandle, LOCK_UN);
            } catch(Exception $e) {
                throw $e;
            }
        }
        
        /**
         * Returns the current timestamp.
         * 
         * @return string with the current date
         */
        private function getTime() {
            return date($this->timeFormat);
        }
        
        /**
         * Closes the current log file.
         */
        protected function closeLogFile() {
            if($this->fileHandle != NULL) {
                fclose($this->fileHandle);
                $this->fileHandle = NULL;
            }
        }
        
        /**
         * Opens a file handle.
         * 
         * @param string $logFile Path to log file.
         */
        public function openLogFile($logFile) {
            $this->closeLogFile();
            
            if(!is_dir(dirname($logFile))){
                if(!mkdir(dirname($logFile), Log::FILE_CHMOD, true)){
                    throw new FileLoggerException('Could not find or create directory for log file.');
                }
            }
            
            if(!$this->fileHandle = fopen($logFile, 'a+')){
                throw new FileLoggerException('Could not open file handle.');
            }
        }
        
    }

  (new Log($_SERVER['DOCUMENT_ROOT']."v3/app/logs/test.log"))->log(
                                                                         array(
                                                                               'mike 1' => "test 0",
                                                                               'lexi 2' => "test 1",
                                                                               'uan 3' => "test 2",
                                                                               'bish 5' => "test 3",
                                                                               'paul 6' => "test 4"
                                                                               ), Log::NOTICE);




 $blIsParralelTask = true;
        $blTaskUsersCompleted = 0;

        $this->completeAuditObject($arrCompleteData, $blIsParralelTask);
        
        if($blIsParralelTask === true) {

            foreach($this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'] as $key => $parallelUser) {
                if(isset($parallelUser['date_completed']) && trim($parallelUser['date_completed']) !== "") {
                    $blTaskUsersCompleted++;
                }
            }
            
            if(count($this->objAudit['steps'][$this->_workflowStepId]['parallelUsers']) !== $blTaskUsersCompleted) {
                $this->objWorkflow['current_step'] = $this->_workflowStepId;
            }
        }




 public function getParallelUsers()
    {
        $arrUsers = array("michael.hampton", "admin", "lexi.hampton");
        
        return $arrUsers;
    }

    /**
     * @param array $arrCompleteData
     * @return bool
     */
    /**
     * @param array $arrCompleteData
     * @return bool
     */
    private function completeAuditObject(array $arrCompleteData = [], $isParallel = false)
    {
        if($isParallel === true && !isset($this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'])) {
            
            $arrUsers = $this->getParallelUsers();
            
            $parallelUsers = [];
            
            foreach($arrUsers as $key => $user) {
                $parallelUsers[$key]['username'] = $user;
            }
            
            $this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'] = $parallelUsers;
        }
        
        $this->objAudit['steps'][$this->_workflowStepId]['step'] = $this->_workflowStepId;
        $this->objAudit['steps'][$this->_workflowStepId]['dateCompleted'] = date("Y-m-d H:i:s");

        if (!empty($arrCompleteData)) {
            $this->objAudit['steps'][$this->_workflowStepId]['status'] = $arrCompleteData['status'];
            $this->objAudit['steps'][$this->_workflowStepId]['completed_by'] = $arrCompleteData['completed_by'];
            
            if($isParallel === true && isset($this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'])) {
                if(isset($arrCompleteData['completed_by']) && trim($arrCompleteData['completed_by']) !== "") {
                    foreach($this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'] as $key => $parallelUser) {
                       if(trim($parallelUser['username']) === trim($arrCompleteData['completed_by'])) {
                        $this->objAudit['steps'][$this->_workflowStepId]['parallelUsers'][$key]['date_completed'] = date("Y-m-d H:i:s");
                       }
                    }
                }
            }

        }

        return true;
    }
    
