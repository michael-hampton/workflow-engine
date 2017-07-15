<?php

class SendNotification extends Notification
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
        $this->projectId = (int) $projectId;
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
        $this->elementId = (int) $elementId;
    }

    /**
     *
     * @param type $status
     * @param type $arrData
     * @param type $system
     * @return boolean
     */
    public function buildEmail (Task $objTask, $system = "task_manager", $blSendToAllParticipants = true)
    {
        try {
            $this->setVariables ($objTask->getStepId (), $system);

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
                    $case = new \BusinessModel\Cases();
                    $p = $case->getUsersParticipatedInCase ($this->projectId);

                    $noteRecipientsList = array();

                    if ( !empty ($p) )
                    {
                        foreach ($p as $userParticipated) {
                            if ( $userParticipated != '' )
                            {
                                $objUsers = new \BusinessModel\UsersFactory();
                                $arrUser = $objUsers->getUsers (array("filter" => "mike", "filterOption" => trim ($userParticipated)));

                                if ( isset ($arrUser['data'][0]) && !empty ($arrUser['data'][0]) )
                                {
                                    $objUser = $arrUser['data'][0];
                                }

                                if ( is_object ($objUser) && get_class ($objUser) === "Users" )
                                {
                                    $emailAddress = $objUser->getUser_email ();
                                    $noteRecipientsList[] = $emailAddress;
                                }
                            }
                        }
                    }

                    if ( !in_array ($this->message['to'], $noteRecipientsList) )
                    {
                        $noteRecipientsList[] = $this->message['to'];
                    }

                    $noteRecipients = implode (",", $noteRecipientsList);

                    $this->recipient = $noteRecipients;
                }
                else
                {
                    $this->recipient = $this->message['to'];
                }
            }

            $objCases = new \BusinessModel\Cases();

            $Fields = $objCases->getCaseVariables ($this->elementId, $this->projectId, $objTask->getStepId ());
            
            $this->subject = $objCases->replaceDataField ($this->subject, $Fields);
            $this->body = $objCases->replaceDataField ($this->body, $Fields);
                        
            $this->save ();
            
            //	sending email notification
            $this->notificationEmail ($this->recipient, $this->subject, $this->body);
            
            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     *
     */
    public function save ()
    {
        $this->objMysql->_query ("UPDATE workflow.notifications_sent
                                SET status = 2
                                WHERE case_id = ?
                                AND project_id = ?
                                AND status != 3", [$this->elementId, $this->projectId]
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
