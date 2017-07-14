<?php

abstract class BaseNotification implements Persistent
{

    protected $status;
    protected $system;
    protected $message;
    protected $subject;
    protected $body;
    protected $objMysql;
    protected $recipient;
    protected $hasRead;
    protected $dateSent;
    protected $triggeringStatus;
    protected $projectId;
    protected $id;
    protected $sentByUser;
    protected $parentId;
    protected $stepData;
    protected $stepName;
    
    private $arrFieldMapping = array(
        "notificationId" => array("accessor" => "getId", "mutator" => "setId"),
        "step" => array("accessor" => "getTriggeringStatus", "mutator" => "setTriggeringStatus"),
        "subject" => array("accessor" => "getSubject", "mutator" => "setSubject"),
        "recipient" => array("accessor" => "getRecipient", "mutator" => "setRecipient"),
        "body" => array("accessor" => "getBody", "mutator" => "setBody"),
        "sentByUser" => array("accessor" => "getSentByUser", "mutator" => "setSentByUser"),
        "parentId" => array("accessor" => "getParentId", "mutator" => "setParentId"),
        "step_data" => array("accessor" => "getStepData", "mutator" => "setStepData"),
        "step_name" => array("accessor" => "getStepName", "mutator" => "setStepName")
    );
    public $arrNotificationData = array();
    public $arrMessages = array();

    public function __construct ()
    {
        if ( !defined ("ENVIROMENT") )
        {
            define ("ENVIROMENT", "DEV");
        }

        $this->objMysql = new Mysql2();
    }

    /**
     * 
     * @param type $arrNotification
     * @return boolean
     */
    public function loadObject (array $arrData)
    {
        foreach ($arrData as $formField => $formValue) {

            if ( isset ($this->arrFieldMapping[$formField]) )
            {
                $mutator = $this->arrFieldMapping[$formField]['mutator'];

                if ( method_exists ($this, $mutator) && is_callable (array($this, $mutator)) )
                {
                    if ( isset ($this->arrFieldMapping[$formField]) && trim ($formValue) != "" )
                    {
                        call_user_func (array($this, $mutator), $formValue);
                    }
                }
            }
        }

        return true;
    }
    
    public function validate ()
    {
        ;
    }

    /**
     * 
     * @param type $status
     */
    protected function setStatus ($status)
    {
        $this->status = $status;
    }

    /**
     * 
     * @param type $system
     */
    public function setSystem ($system)
    {
        $this->system = $system;
    }

    /**
     * 
     * @param type $message
     */
    public function logInfo2 ($message)
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/FormBuilder/app/logs/mail.log";

        $date = date ("Y-m-d h:m:s");
        $level = "info";

        $message = "[" . $date . "] [" . $level . "] " . $message . "]";

        $message .= file_get_contents ($file);

        // log to our default location
        file_put_contents ($file, $message);
    }

    /**
     * 
     * @return boolean
     */
    public function setMessage ()
    {
        $objMysql = new Mysql2();
        $arrResult = $objMysql->_select ("auto_notifications", array(), array("triggering_status" => $this->status, "system" => $this->system));
        
        if ( !empty ($arrResult) )
        {
            $this->message = $arrResult[0];
        }
        else
        {
            return false;
        }
    }

    function getRecipient ()
    {
        return $this->recipient;
    }

    function getMessage ()
    {
        return $this->message;
    }

    function getSubject ()
    {
        return $this->subject;
    }

    function getBody ()
    {
        return $this->body;
    }

    function getHasRead ()
    {
        return $this->hasRead;
    }

    function getDateSent ()
    {
        return $this->dateSent;
    }
    
    /**
     * 
     * @param type $hasRead
     */
    function setHasRead ($hasRead)
    {
        $this->hasRead = $hasRead;
    }

    /**
     * 
     * @param type $dateSent
     */
    function setDateSent ($dateSent)
    {
        $this->dateSent = $dateSent;
    }

    function getTriggeringStatus ()
    {
        return $this->triggeringStatus;
    }

    function getId ()
    {
        return $this->id;
    }

    /**
     * 
     * @param type $triggeringStatus
     */
    function setTriggeringStatus ($triggeringStatus)
    {
        $this->triggeringStatus = $triggeringStatus;
        $this->arrNotificationData['triggering_status'] = $triggeringStatus;
        $this->arrNotificationData['system'] = "task_manager";
    }

    /**
     * 
     * @param type $id
     */
    function setId ($id)
    {
        $this->id = $id;
    }

    /**
     * 
     * @param type $subject
     */
    function setSubject ($subject)
    {
        $this->subject = $subject;
        $this->arrNotificationData['message_subject'] = $subject;
        $this->arrMessages['subject'] = $subject;
    }

    /**
     * 
     * @param type $body
     */
    function setBody ($body)
    {
        $this->body = $body;
        $this->arrNotificationData['message_body'] = $body;
        $this->arrMessages['message'] = $body;
    }

    /**
     * 
     * @param type $recipient
     */
    function setRecipient ($recipient)
    {
        $this->recipient = $recipient;
        $this->arrNotificationData['to'] = $recipient;
        $this->arrMessages['recipient'] = $recipient;
    }

    function getParentId ()
    {
        return $this->parentId;
    }

    /**
     * 
     * @param type $parentId
     */
    function setParentId ($parentId)
    {
        $this->parentId = $parentId;
        $this->arrMessages['parent_id'] = $parentId;
    }

    function getProjectId ()
    {
        return $this->projectId;
    }

    /**
     * 
     * @param type $projectId
     */
    public function setProjectId ($projectId)
    {
        $this->projectId = $projectId;
    }

    function getSentByUser ()
    {
        return $this->sentByUser;
    }

    /**
     * 
     * @param type $sentByUser
     */
    function setSentByUser ($sentByUser)
    {
        $this->sentByUser = $sentByUser;
        $this->arrMessages['sent_by_user'] = $sentByUser;
    }
    
    function getStepData ()
    {
        return $this->stepData;
    }

    /**
     * 
     * @param type $stepData
     */
    function setStepData ($stepData)
    {
        $this->stepData = $stepData;
    }
    
    function getStepName ()
    {
        return $this->stepName;
    }

    function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    /**
     * 
     */
    public function save ()
    {
        if ( isset ($this->id) && is_numeric ($this->id) )
        {
            $this->objMysql->_update ("task_manager.auto_notifications", $this->arrNotificationData, array("id" => $this->id));
        }
        else
        {
            $this->objMysql->_insert ("task_manager.auto_notifications", array($this->arrNotificationData));
        }
    }

    /**
     * 
     * @param type $arrUpdate
     * @param type $id
     */
    public function update ($arrUpdate, $id)
    {
        $this->objMysql->_update ("workflow.notifications_sent", $arrUpdate, array("id" => $id));
    }

    public function saveNewMessage ()
    {
        $this->arrMessages['date_sent'] = date ("Y-m-d H:i:s");
        $this->arrMessages['status'] = 1;
        $this->objMysql->_insert ("workflow.notifications_sent", $this->arrMessages);
    }

}
