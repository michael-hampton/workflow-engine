<?php

/**
 *
 * @package workflow.engine.ProcessMaker
 */
class EmailFunctions
{

    public $config;
    private $fileData;
    private $spool_id;
    public $status;
    public $error;
    private $ExceptionCode = array(); //Array to define the Expetion codes
    private $aWarnings = array(); //Array to store the warning that were throws by the class
    private $longMailEreg;
    private $mailEreg;

    /**
     * Class constructor - iniatilize default values
     *
     * @param none
     * @return none
     */
    public function __construct ()
    {
        $this->config = array();
        $this->fileData = array();
        $this->spool_id = '';
        $this->status = 'pending';
        $this->error = '';

        $this->ExceptionCode['FATAL'] = 1;
        $this->ExceptionCode['WARNING'] = 2;
        $this->ExceptionCode['NOTICE'] = 3;

        $this->longMailEreg = "/(.*)(<([\w\-\.']+@[\w\-_\.]+\.\w{2,5})+>)/";
        $this->mailEreg = "/^([\w\-_\.']+@[\w\-_\.]+\.\w{2,5}+)$/";
    }

    /**
     * get all files into spool in a list
     *
     * @param none
     * @return none
     */
    public function getSpoolFilesList ()
    {
        $sql = "SELECT * FROM APP_MESSAGE WHERE APP_MSG_STATUS ='pending'";
        $results = $this->objMysql->_query ($sql);
        foreach ($results as $result)
            ; {
            $this->spool_id = $rs->getString ('APP_MSG_UID');
            $this->fileData['subject'] = $rs->getString ('APP_MSG_SUBJECT');
            $this->fileData['from'] = $rs->getString ('APP_MSG_FROM');
            $this->fileData['to'] = $rs->getString ('APP_MSG_TO');
            $this->fileData['body'] = $rs->getString ('APP_MSG_BODY');
            $this->fileData['date'] = $rs->getString ('APP_MSG_DATE');
            $this->fileData['cc'] = $rs->getString ('APP_MSG_CC');
            $this->fileData['bcc'] = $rs->getString ('APP_MSG_BCC');
            $this->fileData['template'] = $rs->getString ('APP_MSG_TEMPLATE');
            $this->fileData['attachments'] = array(); //$rs->getString('APP_MSG_ATTACH');
            $this->fileData['error'] = $rs->getString ('APP_MSG_ERROR');
            if ( $this->config['MESS_ENGINE'] == 'OPENMAIL' )
            {
                if ( $this->config['MESS_SERVER'] != '' )
                {
                    if ( ($sAux = @gethostbyaddr ($this->config['MESS_SERVER']) ) )
                    {
                        $this->fileData['domain'] = $sAux;
                    }
                    else
                    {
                        $this->fileData['domain'] = $this->config['MESS_SERVER'];
                    }
                }
                else
                {
                    $this->fileData['domain'] = gethostbyaddr ('127.0.0.1');
                }
            }
            $this->sendMail ();
        }
    }

    /**
     * create a msg record for spool
     *
     * @param array $aData
     * @return none
     */
    public function create ($aData)
    {
        if ( is_array ($aData['app_msg_attach']) )
        {
            $attachment = $aData['app_msg_attach'];
        }
        else
        {
            $attachment = @unserialize ($aData['app_msg_attach']);
            if ( $attachment === false )
            {
                $attachment = explode (',', $aData['app_msg_attach']);
            }
        }
        $aData['app_msg_attach'] = serialize ($attachment);
        $aData['app_msg_show_message'] = (isset ($aData['app_msg_show_message'])) ? $aData['app_msg_show_message'] : 1;
        $aData["app_msg_error"] = (isset ($aData["app_msg_error"])) ? $aData["app_msg_error"] : '';
        $sUID = $this->db_insert ($aData);

        $aData['app_msg_date'] = isset ($aData['app_msg_date']) ? $aData['app_msg_date'] : '';

        if ( isset ($aData['app_msg_status']) )
        {
            $this->status = strtolower ($aData['app_msg_status']);
        }

        $aData["contentTypeIsHtml"] = (isset ($aData["contentTypeIsHtml"])) ? $aData["contentTypeIsHtml"] : true;

        $this->setData ($sUID, $aData["app_msg_subject"], $aData["app_msg_from"], $aData["app_msg_to"], $aData["app_msg_body"], $aData["app_msg_date"], $aData["app_msg_cc"], $aData["app_msg_bcc"], $aData["app_msg_template"], $aData["app_msg_attach"], $aData["contentTypeIsHtml"], $aData["app_msg_error"]);
    }

    /**
     * set configuration
     *
     * @param array $aConfig
     * @return none
     */
    public function setConfig ($aConfig)
    {
        // Processing password
        $passwd = isset ($aConfig['MESS_PASSWORD']) ? $aConfig['MESS_PASSWORD'] : '';
        $passwdDec = $this->decrypt ($passwd, 'EMAILENCRYPT');
        $auxPass = explode ('hash:', $passwdDec);
        if ( count ($auxPass) > 1 )
        {
            if ( count ($auxPass) == 2 )
            {
                $passwd = $auxPass[1];
            }
            else
            {
                array_shift ($auxPass);
                $passwd = implode ('', $auxPass);
            }
        }
        $aConfig['MESS_PASSWORD'] = $passwd;

        // Validating authorization flag
        if ( !isset ($aConfig['SMTPAuth']) )
        {
            if ( isset ($aConfig['MESS_RAUTH']) )
            {
                if ( $aConfig['MESS_RAUTH'] == false || (is_string ($aConfig['MESS_RAUTH']) && $aConfig['MESS_RAUTH'] == 'false') )
                {
                    $aConfig['MESS_RAUTH'] = 0;
                }
                else
                {
                    $aConfig['MESS_RAUTH'] = 1;
                }
            }
            else
            {
                $aConfig['MESS_RAUTH'] = 0;
            }
            $aConfig['SMTPAuth'] = $aConfig['MESS_RAUTH'];
        }

        // Validating for old configurations
        if ( !isset ($aConfig['MESS_FROM_NAME']) )
        {
            $aConfig['MESS_FROM_NAME'] = '';
        }
        if ( !isset ($aConfig['MESS_FROM_MAIL']) )
        {
            $aConfig['MESS_FROM_MAIL'] = '';
        }

        $this->config = $aConfig;
    }

    /**
     * set email parameters
     *
     * @param string $sAppMsgUid, $sSubject, $sFrom, $sTo, $sBody, $sDate, $sCC, $sBCC, $sTemplate
     * @return none
     */
    public function setData ($sAppMsgUid, $sSubject, $sFrom, $sTo, $sBody, $sDate = "", $sCC = "", $sBCC = "", $sTemplate = "", $aAttachment = array(), $bContentTypeIsHtml = true, $sError = "")
    {
        $this->spool_id = $sAppMsgUid;
        $this->fileData['subject'] = $sSubject;
        $this->fileData['from'] = $sFrom;
        $this->fileData['to'] = $sTo;
        $this->fileData['body'] = $sBody;
        $this->fileData['date'] = ($sDate != '' ? $sDate : date ('Y-m-d H:i:s'));
        $this->fileData['cc'] = $sCC;
        $this->fileData['bcc'] = $sBCC;
        $this->fileData['template'] = $sTemplate;
        $this->fileData['attachments'] = $aAttachment;
        $this->fileData['envelope_to'] = array();
        $this->fileData["contentTypeIsHtml"] = $bContentTypeIsHtml;
        $this->fileData["error"] = $sError;

        if ( array_key_exists ('MESS_ENGINE', $this->config) )
        {
            if ( $this->config['MESS_ENGINE'] == 'OPENMAIL' )
            {
                if ( $this->config['MESS_SERVER'] != '' )
                {
                    if ( ($sAux = @gethostbyaddr ($this->config['MESS_SERVER']) ) )
                    {
                        $this->fileData['domain'] = $sAux;
                    }
                    else
                    {
                        $this->fileData['domain'] = $this->config['MESS_SERVER'];
                    }
                }
                else
                {
                    $this->fileData['domain'] = gethostbyaddr ('127.0.0.1');
                }
            }
        }
    }

    /**
     * send mail
     *
     * @param none
     * @return boolean true or exception
     */
    public function sendMail ()
    {
        try {
            $this->handleFrom ();
            $this->handleEnvelopeTo ();
            $this->handleMail ();
            $this->updateSpoolStatus ();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * update the status to spool
     *
     * @param none
     * @return none
     */
    private function updateSpoolStatus ()
    {

        $oAppMessage = (new AppMessage())->retrieveByPK ($this->spool_id);
        if ( is_array ($this->fileData['attachments']) )
        {
            $attachment = implode (",", $this->fileData['attachments']);
            $oAppMessage->setappMsgAttach ($attachment);
        }
        $oAppMessage->setappMsgstatus ($this->status);
        $oAppMessage->setappMsgsenddate (date ('Y-m-d H:i:s'));
        $oAppMessage->save ();
    }

    /**
     * handle the email that was set in "TO" parameter
     *
     * @param none
     * @return boolean true or exception
     */
    private function handleFrom ()
    {
        $eregA = "/^'.*@.*$/";

        if ( strpos ($this->fileData['from'], '<') !== false )
        {
            //to validate complex email address i.e. Erik A. O <erik@colosa.com>
            $ereg = (preg_match ($eregA, $this->fileData["from"])) ? $this->longMailEreg : "/^(.*)(<(.*)>)$/";
            preg_match ($ereg, $this->fileData["from"], $matches);

            if ( isset ($matches[1]) && $matches[1] != '' )
            {
                //drop the " characters if they exist
                $this->fileData['from_name'] = trim (str_replace ('"', '', $matches[1]));
            }
            else
            {
                //if the from name was not set
                $this->fileData['from_name'] = '';
            }

            if ( !isset ($matches[3]) )
            {
                throw new Exception ('Invalid email address in FROM parameter (' . $this->fileData['from'] . ')', $this->ExceptionCode['WARNING']);
            }

            $this->fileData['from_email'] = trim ($matches[3]);
        }
        else
        {
            //to validate simple email address i.e. erik@colosa.com
            $ereg = (preg_match ($eregA, $this->fileData["from"])) ? $this->mailEreg : "/^(.*)$/";
            preg_match ($ereg, $this->fileData["from"], $matches);

            if ( !isset ($matches[0]) )
            {
                throw new Exception ('Invalid email address in FROM parameter (' . $this->fileData['from'] . ')', $this->ExceptionCode['WARNING']);
            }

            $this->fileData['from_name'] = '';
            $this->fileData['from_email'] = $matches[0];
        }

        // Set reply to
        preg_match ($this->longMailEreg, $this->fileData['from_name'], $matches);
        if ( isset ($matches[3]) )
        {
            $this->fileData['reply_to'] = $matches[3];
            $this->fileData['reply_to_name'] = isset ($matches[1]) ? $matches[1] : $this->fileData['from_name'];
        }
        else
        {
            preg_match ($this->mailEreg, $this->fileData['from_name'], $matches);
            if ( isset ($matches[1]) )
            {
                $this->fileData['reply_to'] = $matches[1];
                $this->fileData['reply_to_name'] = '';
            }
            else
            {
                $this->fileData['reply_to'] = '';
                $this->fileData['reply_to_name'] = '';
            }
        }
    }

    /**
     * handle all recipients to compose the mail
     *
     * @param none
     * @return boolean true or exception
     */
    private function handleEnvelopeTo ()
    {
        $hold = array();
        $holdcc = array();
        $holdbcc = array();
        $text = trim ($this->fileData['to']);

        $textcc = '';
        $textbcc = '';
        if ( isset ($this->fileData['cc']) && trim ($this->fileData['cc']) != '' )
        {
            $textcc = trim ($this->fileData['cc']);
        }

        if ( isset ($this->fileData['bcc']) && trim ($this->fileData['bcc']) != '' )
        {
            $textbcc = trim ($this->fileData['bcc']);
        }

        if ( false !== (strpos ($text, ',')) )
        {
            $hold = explode (',', $text);

            foreach ($hold as $val) {
                if ( strlen ($val) > 0 )
                {
                    $this->fileData['envelope_to'][] = "$val";
                }
            }
        }
        elseif ( $text != '' )
        {
            $this->fileData['envelope_to'][] = "$text";
        }
        else
        {
            $this->fileData['envelope_to'] = array();
        }

        //CC
        if ( false !== (strpos ($textcc, ',')) )
        {
            $holdcc = explode (',', $textcc);

            foreach ($holdcc as $valcc) {
                if ( strlen ($valcc) > 0 )
                {
                    $this->fileData['envelope_cc'][] = "$valcc";
                }
            }
        }
        elseif ( $textcc != '' )
        {
            $this->fileData['envelope_cc'][] = "$textcc";
        }
        else
        {
            $this->fileData['envelope_cc'] = array();
        }

        //BCC
        if ( false !== (strpos ($textbcc, ',')) )
        {
            $holdbcc = explode (',', $textbcc);

            foreach ($holdbcc as $valbcc) {
                if ( strlen ($valbcc) > 0 )
                {
                    $this->fileData['envelope_bcc'][] = "$valbcc";
                }
            }
        }
        elseif ( $textbcc != '' )
        {
            $this->fileData['envelope_bcc'][] = "$textbcc";
        }
        else
        {
            $this->fileData['envelope_bcc'] = array();
        }
    }

    /**
     * handle and compose the email content and parameters
     *
     * @param none
     * @return none
     */
    private function handleMail ()
    {
        if ( count ($this->fileData['envelope_to']) > 0 )
        {
            if ( array_key_exists ('MESS_ENGINE', $this->config) )
            {
                switch ($this->config['MESS_ENGINE']) {
                    case 'MAIL':
                    case 'PHPMAILER':
                        require_once PATH_THIRDPARTY . "phpmailer/phpmailer.php";

                        switch ($this->config['MESS_ENGINE']) {
                            case 'MAIL':
                                $oPHPMailer = new PHPMailer();
                                $oPHPMailer->Mailer = 'mail';
                                break;
                            case 'PHPMAILER':
                                $oPHPMailer = new PHPMailer (true);
                                $oPHPMailer->Mailer = 'smtp';
                                break;
                        }

                        $oPHPMailer->SMTPAuth = (isset ($this->config['SMTPAuth']) ? $this->config['SMTPAuth'] : '');

                        switch ($this->config['MESS_ENGINE']) {
                            case 'MAIL':
                                break;
                            case 'PHPMAILER':
                                //Posible Options for SMTPSecure are: "", "ssl" or "tls"
                                if ( isset ($this->config['SMTPSecure']) && preg_match ('/^(ssl|tls)$/', $this->config['SMTPSecure']) )
                                {
                                    $oPHPMailer->SMTPSecure = $this->config['SMTPSecure'];
                                }
                                break;
                        }
                        $oPHPMailer->CharSet = "UTF-8";
                        $oPHPMailer->Encoding = "8bit";
                        $oPHPMailer->Host = $this->config['MESS_SERVER'];
                        $oPHPMailer->Port = $this->config['MESS_PORT'];
                        $oPHPMailer->Username = $this->config['MESS_ACCOUNT'];
                        $oPHPMailer->Password = $this->config['MESS_PASSWORD'];
                        $oPHPMailer->SetFrom ($this->fileData['from_email'], utf8_decode ($this->fileData['from_name']));

                        if ( isset ($this->fileData['reply_to']) )
                        {
                            if ( $this->fileData['reply_to'] != '' )
                            {
                                $oPHPMailer->AddReplyTo ($this->fileData['reply_to'], $this->fileData['reply_to_name']);
                            }
                        }

                        $msSubject = $this->fileData['subject'];

                        if ( !(mb_detect_encoding ($msSubject, "UTF-8") == "UTF-8") )
                        {
                            $msSubject = utf8_encode ($msSubject);
                        }

                        $oPHPMailer->Subject = $msSubject;

                        $msBody = $this->fileData['body'];

                        if ( !(mb_detect_encoding ($msBody, "UTF-8") == "UTF-8") )
                        {
                            $msBody = utf8_encode ($msBody);
                        }

                        $oPHPMailer->Body = $msBody;

                        $attachment = @unserialize ($this->fileData['attachments']);
                        if ( $attachment === false )
                        {
                            $attachment = $this->fileData['attachments'];
                        }
                        if ( is_array ($attachment) )
                        {
                            foreach ($attachment as $key => $fileAttach) {
                                if ( file_exists ($fileAttach) )
                                {
                                    $oPHPMailer->AddAttachment ($fileAttach, is_int ($key) ? '' : $key );
                                }
                            }
                        }

                        foreach ($this->fileData['envelope_to'] as $sEmail) {
                            if ( strpos ($sEmail, '<') !== false )
                            {
                                preg_match ($this->longMailEreg, $sEmail, $matches);
                                $sTo = trim ($matches[3]);
                                $sToName = trim ($matches[1]);
                                $oPHPMailer->AddAddress ($sTo, $sToName);
                            }
                            else
                            {
                                $oPHPMailer->AddAddress ($sEmail);
                            }
                        }

                        //CC
                        foreach ($this->fileData['envelope_cc'] as $sEmail) {
                            if ( strpos ($sEmail, '<') !== false )
                            {
                                preg_match ($this->longMailEreg, $sEmail, $matches);
                                if ( isset ($matches[1]) && isset ($matches[3]) )
                                {
                                    $sTo = trim ($matches[3]);
                                    $sToName = trim ($matches[1]);
                                }
                                else
                                {
                                    $sTo = "bluetiger_uan@yahoo.com";
                                    $sToName = "Michael Hampton";
                                }

                                $oPHPMailer->AddCC ($sTo, $sToName);
                            }
                            else
                            {
                                $oPHPMailer->AddCC ($sEmail);
                            }
                        }

                        //BCC
                        foreach ($this->fileData['envelope_bcc'] as $sEmail) {
                            if ( strpos ($sEmail, '<') !== false )
                            {
                                preg_match ($this->longMailEreg, $sEmail, $matches);
                                $sTo = trim ($matches[3]);
                                $sToName = trim ($matches[1]);
                                $oPHPMailer->AddBCC ($sTo, $sToName);
                            }
                            else
                            {
                                $oPHPMailer->AddBCC ($sEmail);
                            }
                        }

                        $oPHPMailer->IsHTML ($this->fileData["contentTypeIsHtml"]);

                        if ( $this->config['MESS_ENGINE'] == 'MAIL' )
                        {
                            $oPHPMailer->WordWrap = 300;
                        }

                        if ( $oPHPMailer->Send () )
                        {
                            $this->error = '';
                            $this->status = 'sent';
                        }
                        else
                        {
                            $this->error = $oPHPMailer->ErrorInfo;
                            $this->status = 'failed';
                        }
                        break;
                    case 'OPENMAIL':
                        //G::LoadClass ('package');
                        //G::LoadClass ('smtp');
                        $pack = new package ($this->fileData);
                        $header = $pack->returnHeader ();
                        $body = $pack->returnBody ();
                        $send = new smtp();
                        $send->setServer ($this->config['MESS_SERVER']);
                        $send->setPort ($this->config['MESS_PORT']);
                        $send->setUsername ($this->config['MESS_ACCOUNT']);

                        $passwd = $this->config['MESS_PASSWORD'];
                        $passwdDec = $this->decrypt ($passwd, 'EMAILENCRYPT');
                        $auxPass = explode ('hash:', $passwdDec);

                        if ( count ($auxPass) > 1 )
                        {
                            if ( count ($auxPass) == 2 )
                            {
                                $passwd = $auxPass[1];
                            }
                            else
                            {
                                array_shift ($auxPass);
                                $passwd = implode ('', $auxPass);
                            }
                        }

                        $this->config['MESS_PASSWORD'] = $passwd;
                        $send->setPassword ($this->config['MESS_PASSWORD']);
                        $send->setReturnPath ($this->fileData['from_email']);
                        $send->setHeaders ($header);
                        $send->setBody ($body);
                        $send->setEnvelopeTo ($this->fileData['envelope_to']);
                        if ( $send->sendMessage () )
                        {
                            $this->error = '';
                            $this->status = 'sent';
                        }
                        else
                        {
                            $this->error = implode (', ', $send->returnErrors ());
                            $this->status = 'failed';
                        }
                        break;
                }
            }
        }
    }

    /**
     * gets all warnings
     *
     * @param none
     * @return string $this->aWarnings
     */
    public function getWarnings ()
    {
        if ( sizeof ($this->aWarnings) != 0 )
        {
            return $this->aWarnings;
        }

        return false;
    }

    /**
     * db_insert
     *
     * @param array $db_spool
     * @return string $sUID;
     */
    public function db_insert ($db_spool)
    {
        $spool = new AppMessage();
        $spool->setMsgUid ($db_spool['msg_uid']);
        $spool->setAppUid ($db_spool['app_uid']);
        $spool->setDelIndex ($db_spool['del_index']);
        $spool->setAppMsgType ($db_spool['app_msg_type']);
        $spool->setAppMsgSubject ($db_spool['app_msg_subject']);
        $spool->setAppMsgFrom ($db_spool['app_msg_from']);
        $spool->setAppMsgTo ($db_spool['app_msg_to']);
        $spool->setAppMsgBody ($db_spool['app_msg_body']);
        $spool->setAppMsgDate (date ('Y-m-d H:i:s'));
        $spool->setAppMsgCc ($db_spool['app_msg_cc']);
        $spool->setAppMsgBcc ($db_spool['app_msg_bcc']);
        $spool->setappMsgAttach ($db_spool['app_msg_attach']);
        $spool->setAppMsgTemplate ($db_spool['app_msg_template']);
        $spool->setAppMsgStatus ($db_spool['app_msg_status']);
        $spool->setAppMsgSendDate (date ('Y-m-d H:i:s')); // Add by Ankit
        $spool->setAppMsgShowMessage ($db_spool['app_msg_show_message']); // Add by Ankit
        $spool->setAppMsgError ($db_spool['app_msg_error']);
        $spool->setCaseUid ($db_spool['case_id']);

        if ( !$spool->validate () )
        {
            $errors = $spool->getValidationFailures ();
            $this->status = 'error';
            $errorMsg = '';

            foreach ($errors as $key => $value) {
                $errorMsg .= "Validation error - " . $value->getMessage ($key) . "\n";
            }
            
            throw new Exception($errorMsg);
        }
        else
        {
            //echo "Saving - validation ok\n";
            $this->status = 'success';
            $spool->updatePrevious ();
            $sUID = $spool->save ();

            $spool->setAppMsgUid ($sUID);
        }

        return $sUID;
    }

    /**
     * Encrypt string
     *
     * @access public
     * @param string $string
     * @param string $key
     * @return string
     */
    public static function encrypt ($string, $key)
    {
        //print $string;
        //    if ( defined ( 'ENABLE_ENCRYPT' ) && ENABLE_ENCRYPT == 'yes' ) {
        if ( strpos ($string, '|', 0) !== false )
        {
            return $string;
        }
        $result = '';
        for ($i = 0; $i < strlen ($string); $i ++) {
            $char = substr ($string, $i, 1);
            $keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
            $char = chr (ord ($char) + ord ($keychar));
            $result .= $char;
        }

        $result = base64_encode ($result);
        $result = str_replace ('/', '°', $result);
        $result = str_replace ('=', '', $result);
        return $result;
    }

    /**
     * Decrypt string
     *
     * @access public
     * @param string $string
     * @param string $key
     * @return string
     */
    public function decrypt ($string, $key)
    {
        //   if ( defined ( 'ENABLE_ENCRYPT' ) && ENABLE_ENCRYPT == 'yes' ) {
        //if (strpos($string, '|', 0) !== false) return $string;
        $result = '';
        $string = str_replace ('°', '/', $string);
        $string_jhl = explode ("?", $string);
        $string = base64_decode ($string);
        $string = base64_decode ($string_jhl[0]);

        for ($i = 0; $i < strlen ($string); $i ++) {
            $char = substr ($string, $i, 1);
            $keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
            $char = chr (ord ($char) - ord ($keychar));
            $result .= $char;
        }
        if ( !empty ($string_jhl[1]) )
        {
            $result .= '?' . $string_jhl[1];
        }
        return $result;
    }

}
