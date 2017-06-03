<?php

class SendNotification extends Notifications
{

    private $elementId;
    private $arrEmailAddresses = array();

    /**
     *
     * @param type $status
     * @param type $system
     */
    public function setVariables ($status, $system)
    {
        error_reporting (E_ALL);
        $this->setStatus ($status);
        $this->setSystem ("task_manager");
        $this->setMessage ();
    }

    /**
     *
     * @param type $projectId
     */
    public function setProjectId ($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getArrEmailAddresses ()
    {
        return $this->arrEmailAddresses;
    }

    public function setArrEmailAddresses ($arrEmailAddresses)
    {
        $this->arrEmailAddresses = $arrEmailAddresses;
    }

    /**
     *
     * @param type $elementId
     */
    public function setElementId ($elementId)
    {
        $this->elementId = $elementId;
    }

    // Gets the parameters for the message
    // The triggering status parameters should be defined as []
    public function getMessageParameters ()
    {

        $pattern = "/\[([^\]]+)\]/";
        $found = preg_match_all ($pattern, $this->message['message_subject'], $arrTest);
        $found = preg_match_all ($pattern, $this->message['message_body'], $arrTest);

        //$arrTest = array_merge ($arrTest1, $arrTest2);
        $arrParameters = array();

        $arrDone = array();

        foreach ($arrTest as $strField) {
            $strField = str_replace ("[", "", $strField);
            $strField = str_replace ("]", "", $strField);

            if ( !in_array ($strField, $arrDone) )
            {
                $arrParameters[] = $strField;
            }

            $arrDone[] = $strField;
        }

        return $arrParameters;
    }

    /**
     *
     * @param type $status
     * @param type $arrData
     * @param type $system
     * @return boolean
     */
    public function buildEmail ($status, $arrData, $system = "task_manager", $blSendToAllParticipants = true)
    {
        error_reporting (0);
        $this->setVariables ($status, $system);
        $arrParameters = $this->getMessageParameters ();

        if ( empty ($this->message) )
        {
            return false;
        }

        $this->subject = $this->message['message_subject'];
        $this->body = $this->message['message_body'];

        if ( !empty ($this->arrEmailAddresses) )
        {
            $this->recipient = implode (",", $this->arrEmailAddresses);
        }
        else
        {
            if ( $blSendToAllParticipants === true )
            {
                $case = new Cases();
                $p = $case->getUsersParticipatedInCase ($this->projectId);

                $noteRecipientsList = array();

                if ( !empty ($p) )
                {
                    foreach ($p as $userParticipated) {
                        if ( $userParticipated != '' )
                        {
                            $objUsers = new UsersFactory();
                            $arrUser = $objUsers->getUsers (trim ($userParticipated));

                            if ( isset ($arrUser[0]) && !empty ($arrUser[0]) )
                            {
                                $emailAddress = $arrUser[0]->getUser_email ();
                                $noteRecipientsList[] = $emailAddress;
                            }
                        }
                    }
                }


                $noteRecipientsList[] = $this->message['to'];

                $noteRecipients = implode (",", $noteRecipientsList);

                $this->recipient = $noteRecipients;
            }
            else
            {
                $this->recipient = $this->message['to'];
            }
        }

        $pattern = "/\[([^\]]+)\]/";
        $found = preg_match_all ($pattern, $this->message['message_subject'], $subjectKeys);
        $found = preg_match_all ($pattern, $this->message['message_body'], $bodyKeys);


        foreach ($subjectKeys[0] as $subjectKey) {
            $subjectKey2 = str_replace ("[", "", $subjectKey);
            $subjectKey2 = str_replace ("]", "", $subjectKey2);

            if ( isset ($arrData[$subjectKey2]) )
            {
                $this->subject = str_replace ($subjectKey, $arrData[$subjectKey2], $this->subject);
            }
        }

        foreach ($bodyKeys[0] as $bodyKey) {
            $subjectKey2 = str_replace ("[", "", $bodyKey);
            $subjectKey2 = str_replace ("]", "", $subjectKey2);

            if ( isset ($arrData[$subjectKey2]) )
            {
                $this->body = str_replace ($bodyKey, $arrData[$subjectKey2], $this->body);
            }
        }

        $this->save ();

        //	sending email notification
        $this->notificationEmail ($this->recipient, $this->subject, $this->body);

        return true;
    }

    /**
     *
     */
    public function save ()
    {
        $this->objMysql->_query ("UPDATE workflow.notifications_sent
                                SET status = 2
                                WHERE case_id = ?
                                AND status != 3", [$this->elementId]
        );

        $id = $this->objMysql->_insert (
                "workflow.notifications_sent", array(
            "subject" => $this->subject,
            "message" => $this->body,
            "recipient" => $this->recipient,
            "date_sent" => date ("Y-m-d H:i:s"),
            "project_id" => $this->projectId,
            "case_id" => $this->elementId,
            "status" => 1,
            "step_id" => $this->status
                )
        );
    }

    /**
     *
     * @param type $sendto
     * @param type $message_subject
     * @param type $message_body
     */
    public function notificationEmail ($sendto, $message_subject, $message_body)
    {
        $environment = "DEV";

        switch ($environment) {

            case "DEV":
                $message_subject = "[DEBUG:" . $environment . "]" . $message_subject;
                break;

            case "TEST":
                $message_subject = "[DEBUG:" . $environment . "]" . $message_subject;
                break;

            default:
                $message_subject = $message_subject;
                break;
        }

        $headers = 'From:EasyFlow<donotreply@easyflow.co.uk>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion ();

        $message = $sendto . " " . $message_subject . " " . $message_body;

        $this->logInfo2 ($message);

        mail ($sendto, $message_subject, $message_body, $headers);
    }

}
