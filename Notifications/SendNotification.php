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

        $from = trim ($this->from) !== "" ? $this->from : $this->defaultFrom;
        $fromName = trim ($this->fromName) !== "" ? $this->fromName : "";

        $aTaskInfo = $this->objMysql->_query ("SELECT * FROM `email_server` WHERE `MESS_FROM_MAIL` = ?", [$from]);

        $aConfiguration = (!is_null ((new \BusinessModel\EmailServer())->retrieveByPK ($aTaskInfo[0]['MESS_UID']))) ?
                (new \BusinessModel\EmailServer())->getEmailServer ($aTaskInfo[0]['MESS_UID'], true) :
                "bluetiger_uan@yahoo.com";
        $msgError = '';
        
        $aConfiguration['CASE_UID'] = $this->elementId;
        $aConfiguration['APP_UID'] = $this->projectId;

        $dataLastEmail['msgError'] = $msgError;
        $dataLastEmail['configuration'] = $aConfiguration;
        $dataLastEmail['subject'] = $message_subject;
        $dataLastEmail['pathEmail'] = '';
        $dataLastEmail['swtplDefault'] = 0;
        $dataLastEmail['body'] = $message_body;
        $dataLastEmail['from'] = $from;

        if ( trim ($sendto) !== "" )
        {
            $oSpool = new EmailFunctions();
            $oSpool->setConfig ($dataLastEmail['configuration']);
            $oSpool->create (array(
                "msg_uid" => "",
                "case_id" => $this->elementId,
                'app_uid' => $this->projectId,
                'del_index' => $this->status,
                "app_msg_type" => "DERIVATION",
                "app_msg_subject" => $message_subject,
                'app_msg_from' => $from,
                "app_msg_to" => $sendto,
                'app_msg_body' => $message_body,
                "app_msg_cc" => $this->cc,
                "app_msg_bcc" => $this->bcc,
                "app_msg_attach" => "",
                "app_msg_template" => "",
                "app_msg_status" => "pending",
                "app_msg_error" => $dataLastEmail['msgError']
            ));
            if ( $dataLastEmail['msgError'] == '' )
            {
                if ( ($dataLastEmail['configuration']["MESS_BACKGROUND"] == "") ||
                        ($dataLastEmail['configuration']["MESS_TRY_SEND_INMEDIATLY"] == "1")
                )
                {
                    $oSpool->sendMail ();
                }
            }
        }
    }

}
