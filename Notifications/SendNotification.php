<?php

class SendNotification extends Notification
{

    private $arrEmailAddresses = array();
    private $taskId;

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
    public function buildEmail (Task $objTask, Users $objUser, $system = "task_manager", $blSendToAllParticipants = true)
    {
        try {
            $this->setVariables ($objTask->getStepId (), $system);

            if ( empty ($this->message) )
            {
                return false;
            }

            $this->subject = $this->message['message_subject'];
            $this->body = $this->message['message_body'];

            $this->taskId = $objTask->getTasUid ();

            $noteRecipientsList = array();

            if ( !empty ($this->arrEmailAddresses) )
            {
                $noteRecipientsList[] = $this->arrEmailAddresses;
            }

            if ( (int) $this->sendToAll === 1 )
            {
                $objTask = (new Task())->retrieveByPk ($this->taskId);
                $participants = $this->getTo ($objTask);
                $noteRecipientsList = array_merge ($noteRecipientsList, $participants['to']);

                if ( isset ($participants['cc']) && !empty ($participants['cc']) )
                {
                    $this->cc = implode (",", $participants['cc']);
                }
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
            $this->notificationEmail ($this->recipient, $this->subject, $this->body, $objUser);

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function getEmailConfiguration ()
    {
        $emailServer = new \BusinessModel\EmailServer();

        $arrayEmailServerDefault = $emailServer->getEmailServerDefault ();

        if ( count ($arrayEmailServerDefault) > 0 )
        {
            $arrayDataEmailServerConfig = array(
                "MESS_ENGINE" => $arrayEmailServerDefault["MESS_ENGINE"],
                "MESS_SERVER" => $arrayEmailServerDefault["MESS_SERVER"],
                "MESS_PORT" => (int) ($arrayEmailServerDefault["MESS_PORT"]),
                "MESS_RAUTH" => (int) ($arrayEmailServerDefault["MESS_RAUTH"]),
                "MESS_ACCOUNT" => $arrayEmailServerDefault["MESS_ACCOUNT"],
                "MESS_PASSWORD" => $arrayEmailServerDefault["MESS_PASSWORD"],
                "MESS_FROM_MAIL" => $arrayEmailServerDefault["MESS_FROM_MAIL"],
                "MESS_FROM_NAME" => $arrayEmailServerDefault["MESS_FROM_NAME"],
                "SMTPSecure" => $arrayEmailServerDefault["SMTPSECURE"],
                "MESS_TRY_SEND_INMEDIATLY" => (int) ($arrayEmailServerDefault["MESS_TRY_SEND_INMEDIATLY"]),
                "MAIL_TO" => $arrayEmailServerDefault["MAIL_TO"],
                "MESS_DEFAULT" => (int) ($arrayEmailServerDefault["MESS_DEFAULT"]),
                "MESS_ENABLED" => 1,
                "MESS_BACKGROUND" => "",
                "MESS_PASSWORD_HIDDEN" => "",
                "MESS_EXECUTE_EVERY" => "",
                "MESS_SEND_MAX" => ""
            );

            //Return
            return $arrayDataEmailServerConfig;
        }
    }

    private function getTo (Task $objTask)
    {
        $sTo = null;

        $sCc = null;

        $arrayResp = array();


        $group = new TeamFunctions ();

        $oUser = new Users ();

        /**
         * If task is a self service task we only send to users whi have participated in the case which has been done previously (to already populated)
         * else we get the assigned task users and put them into the array if they arent already there
         */
        $taskType = trim ($objTask->getTasSelfserviceTimeUnit ()) !== "" ? 'SELF-SERVICE' : 'NORMAL';
        $blParallel = false;

        if ( $taskType === "SELF-SERVICE" )
        {
            if ( trim ($objTask->getTasUid ()) === "" )
            {

                $arrayTaskUser = array();

                $arrayAux1 = $objTask->getGroupsOfTask ($objTask->getTasUid (), 1);


                $arrDone = [];

                foreach ($arrayAux1 as $arrayGroup) {

                    if ( !in_array ($arrayGroup['team_id'], $arrDone) )
                    {
                        $arrayAux2 = $group->getUsersOfGroup ($arrayGroup ["team_id"]);

                        foreach ($arrayAux2 as $arrayUser) {

                            $arrayTaskUser [] = $arrayUser ["usrid"];
                        }
                    }

                    $arrDone[] = $arrayGroup['team_id'];
                }

                $arrayAux1 = $objTask->getUsersOfTask ($objTask->getTasUid (), 1);

                foreach ($arrayAux1 as $arrayUser) {

                    $arrayTaskUser [] = $arrayUser ["USR_UID"];
                }

                $objMysql = new Mysql2();

                $results2 = $objMysql->_query ("SELECT usrid, username, firstName, lastName, user_email FROM user_management.poms_users WHERE usrid IN(" . implode (",", $arrayTaskUser) . ")");

                $to = null;

                $cc = null;

                $sw = 1;

                foreach ($results2 as $row) {

                    $toAux = ((($row ["firstName"] != "") || ($row ["lastName"] != "")) ? $row ["firstName"] . " " . $row ["lastName"] . " " : "") . "<" . $row ["user_email"] . ">";

                    if ( $sw == 1 )
                    {

                        $to = $toAux;

                        $sw = 0;
                    }
                    else
                    {

                        $cc = $cc . (($cc != null) ? "," : null) . $toAux;
                    }
                }

                $arrayResp ['to'] = $to;

                $arrayResp ['cc'] = $cc;
            }
        }
        elseif ( $blParallel === true )
        {

            $to = null;

            $cc = null;

            $sw = 1;

            $oDerivation = new Derivation ();

            $userFields = $oDerivation->getUsersFullNameFromArray ($oDerivation->getAllUsersFromAnyTask ($objTask->getTasUid ()));

            if ( isset ($userFields) )
            {

                foreach ($userFields as $row) {

                    $toAux = ((($row ["USR_FIRSTNAME"] != "") || ($row ["USR_LASTNAME"] != "")) ? $row ["USR_FIRSTNAME"] . " " . $row ["USR_LASTNAME"] . " " : "") . "<" . $row ["USR_EMAIL"] . ">";

                    if ( $sw == 1 )
                    {

                        $to = $toAux;

                        $sw = 0;
                    }
                    else
                    {

                        $cc = $cc . (($cc != null) ? "," : null) . $toAux;
                    }
                }

                $arrayResp ['to'] = $to;
                $arrayResp ['cc'] = $cc;
            }
        }
        else
        {
            $arrayResp ['to'] = $this->getTaskUsers ();
        }


        return $arrayResp;
    }

    /**
     *
     * @param type $sendto
     * @param type $message_subject
     * @param type $message_body
     */
    public function notificationEmail ($sendto, $message_subject, $message_body, Users $objUser)
    {


        $aConfiguration = $this->getEmailConfiguration ();
        $oSpool = new EmailFunctions();

        if ( !empty ($aConfiguration) )
        {
            $user = (new \BusinessModel\UsersFactory())->getUser ($objUser->getUserId ());
            $from = $user->getFirstName () . " " . $user->getLastName () . ($user->getUser_email () != "" ? " <" . $user->getUser_email () . ">" : "");
            $from = (new BusinessModel\EmailServer())->buildFrom ($aConfiguration, $from);
        }
        else
        {
            $from = trim ($this->from) !== "" ? $this->from : $this->defaultFrom;
            $from = 'EasyFlow <' . $this->defaultFrom . '>';
        }

        $msgError = "";

        if ( !isset ($aConfiguration['MESS_ENABLED']) || $aConfiguration['MESS_ENABLED'] != '1' )
        {

            $msgError = "The default configuration wasn't defined";

            $aConfiguration['MESS_ENGINE'] = '';
        }

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
