<?php

class SendNotification extends Notification
{
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

    public function getTaskUsers ()
    {
        $case = new \BusinessModel\Cases();
        $p = $case->getUsersParticipatedInCase ($this->projectId);

        if ( !empty ($p) )
        {
            $noteRecipientsList = [];

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

            return $noteRecipientsList;
        }

        return false;
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

            $noteRecipientsList = array();

            if ( !empty ($this->arrEmailAddresses) )
            {
                $noteRecipientsList[] = $this->arrEmailAddresses;
            }

            if ( (int) $this->sendToAll === 1 )
            {
                $participants = $this->getTaskUsers ();
                $noteRecipientsList = array_merge ($noteRecipientsList, $participants);
            }

            if ( !in_array ($this->message['to'], $noteRecipientsList) && trim ($this->message['to']) !== '' )
            {
                $noteRecipientsList[] = $this->message['to'];
            }

            $noteRecipients = implode (",", $noteRecipientsList);

            $this->recipient = $noteRecipients;

            $objCases = new \BusinessModel\Cases();

            $Fields = $objCases->getCaseVariables ((int) $this->elementId, (int) $this->projectId, (int) $objTask->getStepId ());

            $this->subject = $objCases->replaceDataField ($this->subject, $Fields);
            $this->body = $objCases->replaceDataField ($this->body, $Fields);

            $this->sendMessage ();

            //	sending email notification
            $this->notificationEmail ($this->recipient, $this->subject, $this->body);

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
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

        $from = trim ($this->from) !== "" ? $this->from : $this->defaultFrom;
        $fromName = trim ($this->fromName) !== "" ? $this->fromName : "";
       if(trim($sendto) !== "") {
                $oSpool = new emailFunctions();
                $oSpool->setConfig($dataLastEmail['configuration']);
                $oSpool->create(array(
                    "msg_uid" => "",
                    'app_uid' => $this->projectId,
                    'del_index' => 1,
                    "app_msg_type" => "DERIVATION",
                    "app_msg_subject" => $message_subject,
                    'app_msg_from' => $from,
                    "app_msg_to" => $sendto,
                    'app_msg_body' => $message_body,
                    "app_msg_cc" => $this->cc,
                    "app_msg_bcc" => "",
                    "app_msg_attach" => "",
                    "app_msg_template" => "",
                    "app_msg_status" => "pending",
                    "app_msg_error" => $dataLastEmail['msgError']
                ));
                if ($dataLastEmail['msgError'] == '') {
                    if (($dataLastEmail['configuration']["MESS_BACKGROUND"] == "") ||
                        ($dataLastEmail['configuration']["MESS_TRY_SEND_INMEDIATLY"] == "1")
                    ) {
                        $oSpool->sendMail();
                    }
                }

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

        $headers = '<' . $fromName . '>' . $from . '' . "\r\n" .
                'X-Mailer: PHP/' . phpversion ();

        if ( trim ($this->cc) !== '' )
        {
            $headers .= " CC: " . $this->cc . "\n";
        }

        if ( trim ($this->bcc) !== "" )
        {
            $headers .= "BCC: " . $this->bcc . "\n";
        }

        $message = $sendto . " " . $message_subject . " " . $message_body;

        $this->logInfo2 ($message);

        mail ($sendto, $message_subject, $message_body, $headers);
    }

}
