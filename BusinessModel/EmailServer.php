<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of EmailServer
 *
 * @author michael.hampton
 */
class EmailServer
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    public function checkRecordByName ($accountName)
    {
        $result = $this->objMysql->_select ("email_server", [], ["MESS_ACCOUNT" => $accountName]);

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return false;
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("email_server", [], ["MESS_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $this->getEmailServer ($pk);
    }

    /**
     * Verify if does not exist the Email Server in table EMAIL_SERVER
     *
     * @param string $emailServerUid        Unique id of Email Server
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exist the Email Server in table EMAIL_SERVER
     */
    public function throwExceptionIfNotExistsEmailServer ($emailServerUid)
    {
        try {
            $obj = $this->retrieveByPK ($emailServerUid);
            if ( $obj === false )
            {
                throw new \Exception ("ID_EMAIL_SERVER_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $emailServerUid Unique id of Email Server
     * @param array  $arrayData      Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($emailServerUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayEmailServerData = ($emailServerUid == "") ? array() : $this->getEmailServer ($emailServerUid, true);
            $flagInsert = ($emailServerUid == "") ? true : false;
            $arrayFinalData = array_merge ($arrayEmailServerData, $arrayData);
            //Verify data

            switch ($arrayFinalData["MESS_ENGINE"]) {
                case "PHPMAILER":
                    $arrayFieldDefinition["MESS_SERVER"]["required"] = true;
                    $arrayFieldDefinition["MESS_SERVER"]["empty"] = false;
                    $arrayFieldDefinition["MESS_PORT"]["required"] = true;
                    $arrayFieldDefinition["MESS_PORT"]["empty"] = false;
                    $arrayFieldDefinition["MESS_ACCOUNT"]["required"] = true;
                    $arrayFieldDefinition["MESS_ACCOUNT"]["empty"] = false;
                    $arrayFieldDefinition["SMTPSECURE"]["required"] = true;
                    $arrayFieldDefinition["SMTPSECURE"]["empty"] = false;

                    if ( (int) ($arrayFinalData["MESS_RAUTH"]) == 1 )
                    {
                        $arrayFieldDefinition["MESS_PASSWORD"]["required"] = true;
                        $arrayFieldDefinition["MESS_PASSWORD"]["empty"] = false;
                    }
                    break;
                case "MAIL":
                    $arrayFieldDefinition["SMTPSECURE"]["empty"] = true;
                    $arrayFieldDefinition["SMTPSECURE"]["defaultValues"] = array();
                    break;
            }

            if ( isset ($arrayFinalData["MESS_TRY_SEND_INMEDIATLY"]) && (int) ($arrayFinalData["MESS_TRY_SEND_INMEDIATLY"]) == 1 )
            {
                $arrayFieldDefinition["MAIL_TO"]["required"] = true;
                $arrayFieldDefinition["MAIL_TO"]["empty"] = false;
            }

            //Verify data Test Connection
            if ( isset ($_SERVER["SERVER_NAME"]) )
            {
                $arrayTestConnectionResult = $this->testConnection ($arrayFinalData);
                $msg = "";
                foreach ($arrayTestConnectionResult as $key => $value) {
                    $arrayTest = $value;
                    if ( !$arrayTest["result"] )
                    {
                        $msg = $msg . (($msg != "") ? ", " : "") . $arrayTest["title"] . " (Error: " . $arrayTest["message"] . ")";
                    }
                }
                if ( $msg != "" )
                {
                    throw new \Exception ($msg);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Email Server
     *
     * @param string $emailServerUid Unique id of Group
     * @param array  $arrayData      Data
     *
     * return array Return data of the Email Server updated
     */
    public function update ($emailServerUid, $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData);
            $this->throwExceptionIfDataIsEmpty ($arrayData);
            //Verify data
            $this->throwExceptionIfNotExistsEmailServer ($emailServerUid);
            $this->throwExceptionIfDataIsInvalid ($emailServerUid, $arrayData);
            //Update
            try {
                $emailServer = $this->retrieveByPK ($emailServerUid);
                if ( isset ($arrayData['MESS_PASSWORD']) )
                {
                    $passwd = $arrayData['MESS_PASSWORD'];
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
                    $arrayData['MESS_PASSWORD'] = $passwd;
                    if ( $arrayData['MESS_PASSWORD'] != '' )
                    {
                        $arrayData['MESS_PASSWORD'] = 'hash:' . $arrayData['MESS_PASSWORD'];
                        $arrayData['MESS_PASSWORD'] = $this->encrypt ($arrayData['MESS_PASSWORD'], 'EMAILENCRYPT');
                    }
                }
                $emailServer->loadObject ($arrayData);

                if ( $emailServer->validate () )
                {
                    $result = $emailServer->save ();

                    if ( isset ($arrayData["MESS_DEFAULT"]) && (int) ($arrayData["MESS_DEFAULT"]) == 1 )
                    {
                        //$this->setEmailServerDefaultByUid ($emailServerUid);
                    }

                    //Return

                    return $arrayData;
                }
                else
                {
                    $msg = "";
                    foreach ($emailServer->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "") ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Email Server
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new Email Server created
     */
    public function create (array $arrayData)
    {
        try {

            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData);
            $this->throwExceptionIfDataIsEmpty ($arrayData);

            if ( $this->checkRecordByName ($arrayData['MESS_ACCOUNT']) )
            {
                $result = $this->objMysql->_select ("email_server", [], ["MESS_ACCOUNT" => $arrayData['MESS_ACCOUNT']]);
                $this->update ($result[0]['MESS_UID'], $arrayData);
            }

            //Set data
            unset ($arrayData["MESS_UID"]);
            //Create
            try {
                $emailServer = new \EmailServer();
                $passwd = $arrayData["MESS_PASSWORD"];
                $passwdDec = $this->decrypt ($passwd, "EMAILENCRYPT");
                $auxPass = explode ("hash:", $passwdDec);
                if ( count ($auxPass) > 1 )
                {
                    if ( count ($auxPass) == 2 )
                    {
                        $passwd = $auxPass[1];
                    }
                    else
                    {
                        array_shift ($auxPass);
                        $passwd = implode ("", $auxPass);
                    }
                }
                $arrayData["MESS_PASSWORD"] = $passwd;
                if ( $arrayData["MESS_PASSWORD"] != "" )
                {
                    $arrayData["MESS_PASSWORD"] = "hash:" . $arrayData["MESS_PASSWORD"];
                    $arrayData["MESS_PASSWORD"] = $this->encrypt ($arrayData["MESS_PASSWORD"], "EMAILENCRYPT");
                }
                $emailServer->loadObject ($arrayData);

                if ( $emailServer->validate () )
                {
                    $emailServerUid = $emailServer->save ();

                    //Return
                    return $this->getEmailServer ($emailServerUid);
                }
                else
                {
                    $msg = "";
                    foreach ($emailServer->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED " . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Email Server
     *
     * return object
     */
    public function getEmailServerCriteria ()
    {
        try {
            $criteria = "SELECT `MESS_UID`, `MESS_ENGINE`, `MESS_SERVER`, `MESS_PORT`, `MESS_RAUTH`, `MESS_ACCOUNT`, `MESS_PASSWORD`, `MESS_FROM_MAIL`, `MESS_FROM_NAME`, `SMTPSECURE`, `MESS_DEFAULT` FROM task_manager.`email_server`";
            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Email Servers
     *
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     *
     * return array Return an array with all Email Servers
     */
    public function getEmailServers ($arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayEmailServer = array();
            //Verify data
            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayEmailServer;
            }
            //SQL
            $criteria = $this->getEmailServerCriteria ();
            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {
                $criteria .= " WHERE (
                        MESS_ENGINE LIKE '%" . $arrayFilterData["filter"] . "%' OR 
                        MESS_SERVER LIKE '%" . $arrayFilterData["filter"] . "%' OR 
                        MESS_ACCOUNT LIKE '%" . $arrayFilterData["filter"] . "%' OR
                        MESS_FROM_NAME LIKE '%" . $arrayFilterData["filter"] . "%' OR
                        SMTPSECURE LIKE '%" . $arrayFilterData["filter"] . "%'
                        )";
            }
            //Number records total

            $countResult = $this->objMysql->_query ($criteria);

            $numRecTotal = count ($countResult);
            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {

                if ( in_array ($sortField, array("MESS_ENGINE", "MESS_SERVER", "MESS_ACCOUNT", "MESS_FROM_NAME", "SMTPSECURE")) )
                {
                    $sortField = $sortField;
                }
                else
                {
                    $sortField = "MESS_ENGINE";
                }
            }
            else
            {
                $sortField = "MESS_ENGINE";
            }

            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $criteria .= " ORDER BY " . $sortField . " DESC";
            }
            else
            {
                $criteria .= " ORDER BY " . $sortField . " ASC";
            }

            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . (int) ($limit);
            }

            if ( !is_null ($start) )
            {
                $criteria .= "OFFSET " . (int) ($start);
            }

            $results = $this->objMysql->_query ($criteria);

            foreach ($results as $row) {
                $passwd = $row["MESS_PASSWORD"];
                $passwdDec = $this->decrypt ($passwd, "EMAILENCRYPT");
                $auxPass = explode ("hash:", $passwdDec);
                if ( count ($auxPass) > 1 )
                {
                    if ( count ($auxPass) == 2 )
                    {
                        $passwd = $auxPass[1];
                    }
                    else
                    {
                        array_shift ($auxPass);
                        $passwd = implode ("", $auxPass);
                    }
                }
                $row["MESS_PASSWORD"] = $passwd;
                $arrayEmailServer[] = $this->getEmailServerDataFromRecord ($row);
            }
            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($start)) ? $start : 0),
                "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                "filter" => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                "data" => $arrayEmailServer
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Email Server
     */
    public function getEmailServerDataFromRecord (array $record)
    {
        try {
            $objEmailServer = new \EmailServer();
            $objEmailServer->setMessAccount ($record['MESS_ACCOUNT']);
            $objEmailServer->setMessDefault ($record['MESS_DEFAULT']);
            $objEmailServer->setMessEngine ($record['MESS_ENGINE']);
            $objEmailServer->setMessFromMail ($record['MESS_FROM_MAIL']);
            $objEmailServer->setMessFromName ($record['MESS_FROM_NAME']);
            $objEmailServer->setMessPort ($record['MESS_PORT']);
            $objEmailServer->setMessRauth ($record['MESS_RAUTH']);
            $objEmailServer->setMessServer ($record['MESS_SERVER']);
            $objEmailServer->setMessUid ($record['MESS_UID']);
            $objEmailServer->setSmtpsecure ($record['SMTPSECURE']);
            $objEmailServer->setMessPassword ($record['MESS_PASSWORD']);

            return $objEmailServer;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get data of a Email Server
     *
     * @param string $emailServerUid Unique id of Email Server
     * @param bool   $flagGetRecord  Value that set the getting
     *
     * return array Return an array with data of a Email Server
     */
    public function getEmailServer ($emailServerUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            //$this->throwExceptionIfNotExistsEmailServer ($emailServerUid);
            //Get data
            //SQL
            $criteria = $this->getEmailServerCriteria ();

            $criteria .= " WHERE MESS_UID = ?";
            $arrParameters = array($emailServerUid);
            $results = $this->objMysql->_query ($criteria, $arrParameters);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                return false;
            }

            $row = $results[0];

            $row["MESS_PORT"] = (int) ($row["MESS_PORT"]);
            $row["MESS_RAUTH"] = (int) ($row["MESS_RAUTH"]);
            $row["MESS_DEFAULT"] = (int) ($row["MESS_DEFAULT"]);
            $row["MESS_BACKGROUND"] = '';
            $row["MESS_PASSWORD_HIDDEN"] = '';
            $row["MESS_EXECUTE_EVERY"] = '';
            $row["MESS_SEND_MAX"] = '';
            //Return
            return (!$flagGetRecord) ? $this->getEmailServerDataFromRecord ($row) : $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test connection
     *
     * @param array $arrayData Data
     *
     * return array Return array with result of test connection
     */
    public function testConnection (array $arrayData)
    {
        try {
            $arrayMailTestName = array(
                1 => "verifying_mail",
                2 => "sending_email"
            );

            $arrayPhpMailerTestName = array(
                1 => "resolving_name",
                2 => "check_port",
                3 => "establishing_connection_host",
                4 => "login",
                5 => "sending_email"
            );

            $arrayResult = array();

            switch ($arrayData["MESS_ENGINE"]) {
                case "MAIL":
                    $arrayDataAux = $arrayData;
                    $arrayDataAux["MESS_TRY_SEND_INMEDIATLY"] = 1;
                    $arrayDataAux["MAIL_TO"] = "admin@processmaker.com";
                    $arrayResult[$arrayMailTestName[1]] = $this->testConnectionByStep ($arrayDataAux);
                    $arrayResult[$arrayMailTestName[1]]["title"] = "ID_EMAIL_SERVER_TEST_CONNECTION_VERIFYING_MAIL";
                    if ( isset ($arrayData["MESS_TRY_SEND_INMEDIATLY"]) && (int) ($arrayData["MESS_TRY_SEND_INMEDIATLY"]) == 1 && $arrayData['MAIL_TO'] != '' )
                    {
                        $arrayResult[$arrayMailTestName[2]] = $this->testConnectionByStep ($arrayData);
                        $arrayResult[$arrayMailTestName[2]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_SENDING_EMAIL");
                    }
                    break;
                case "PHPMAILER":
                    $arrayData['MAIL_TO'] = "bluetiger_uan@yahoo.com";
                    $numSteps = ($arrayData['MAIL_TO'] != '') ? count ($arrayPhpMailerTestName) :
                            count ($arrayPhpMailerTestName) - 1;
                    for ($step = 1; $step <= $numSteps; $step++) {
                        $arrayResult[$arrayPhpMailerTestName[$step]] = $this->testConnectionByStep ($arrayData, $step);
                        switch ($step) {
                            case 1:
                                $arrayResult[$arrayPhpMailerTestName[$step]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_RESOLVING_NAME");
                                break;
                            case 2:
                                $arrayResult[$arrayPhpMailerTestName[$step]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_CHECK_PORT");
                                break;
                            case 3:
                                $arrayResult[$arrayPhpMailerTestName[$step]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_ESTABLISHING_CON_HOST");
                                break;
                            case 4:
                                $arrayResult[$arrayPhpMailerTestName[$step]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_LOGIN");
                                break;
                            case 5:
                                $arrayResult[$arrayPhpMailerTestName[$step]]["title"] = ("ID_EMAIL_SERVER_TEST_CONNECTION_SENDING_EMAIL");
                                break;
                        }
                    }
                    break;
            }
            //Result
            return $arrayResult;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test connection by step
     *
     * @param array $arrayData Data
     * @param int   $step      Step
     *
     * return array Return array with result of test connection by step
     */
    public function testConnectionByStep (array $arrayData, $step = 0)
    {
        try {
            // MAIL
            if ( $arrayData["MESS_ENGINE"] == "MAIL" )
            {
                $arrayDataMail = array();
                $eregMail = "/^[0-9a-zA-Z]+(?:[._][0-9a-zA-Z]+)*@[0-9a-zA-Z]+(?:[._-][0-9a-zA-Z]+)*\.[0-9a-zA-Z]{2,3}$/";
                $arrayDataMail["FROM_EMAIL"] = ($arrayData["MESS_FROM_MAIL"] != "" && preg_match ($eregMail, $arrayData["MESS_FROM_MAIL"])) ? $arrayData["MESS_FROM_MAIL"] : "";
                $arrayDataMail["FROM_NAME"] = ($arrayData["MESS_FROM_NAME"] != "") ? $arrayData["MESS_FROM_NAME"] : \G::LoadTranslation ("ID_MESS_TEST_BODY");
                $arrayDataMail["MESS_ENGINE"] = "MAIL";
                $arrayDataMail["MESS_SERVER"] = "localhost";
                $arrayDataMail["MESS_PORT"] = 25;
                $arrayDataMail["MESS_ACCOUNT"] = $arrayData["MAIL_TO"];
                $arrayDataMail["MESS_PASSWORD"] = "";
                $arrayDataMail["TO"] = $arrayData["MAIL_TO"];
                $arrayDataMail["MESS_RAUTH"] = true;

                $arrayTestMailResult = array();

                try {
                    $arrayTestMailResult = $this->sendTestMail ($arrayDataMail);
                } catch (Exception $e) {
                    $arrayTestMailResult["status"] = false;
                    $arrayTestMailResult["message"] = $e->getMessage ();
                }
                $arrayResult = array(
                    "result" => $arrayTestMailResult["status"],
                    "message" => ""
                );
                if ( $arrayTestMailResult["status"] == false )
                {
                    $arrayResult["message"] = "ID_SENDMAIL_NOT_INSTALLED";
                }
                //Return
                return $arrayResult;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function buildFrom ($configuration, $from = '')
    {
        if ( !isset ($configuration['MESS_FROM_NAME']) )
        {
            $configuration['MESS_FROM_NAME'] = '';
        }
        if ( !isset ($configuration['MESS_FROM_MAIL']) )
        {
            $configuration['MESS_FROM_MAIL'] = '';
        }
        if ( $from != '' )
        {
            if ( !preg_match ('/(.+)@(.+)\.(.+)/', $from, $match) )
            {
                if ( $configuration['MESS_FROM_MAIL'] != '' )
                {
                    $from .= ' <' . $configuration['MESS_FROM_MAIL'] . '>';
                }
                else if ( $configuration['MESS_ENGINE'] == 'PHPMAILER' && preg_match ('/(.+)@(.+)\.(.+)/', $configuration['MESS_ACCOUNT'], $match) )
                {
                    $from .= ' <' . $configuration['MESS_ACCOUNT'] . '>';
                }
                else
                {
                    $from .= ' <info@' . ((isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : 'processmaker.com') . '>';
                }
            }
        }
        else
        {
            if ( $configuration['MESS_FROM_NAME'] != '' && $configuration['MESS_FROM_MAIL'] != '' )
            {
                $from = $configuration['MESS_FROM_NAME'] . ' <' . $configuration['MESS_FROM_MAIL'] . '>';
            }
            else if ( $configuration['MESS_FROM_NAME'] != '' && $configuration['MESS_ENGINE'] == 'PHPMAILER' && preg_match ('/(.+)@(.+)\.(.+)/', $configuration['MESS_ACCOUNT'], $match) )
            {
                $from = $configuration['MESS_FROM_NAME'] . ' <' . $configuration['MESS_ACCOUNT'] . '>';
            }
            else if ( $configuration['MESS_FROM_NAME'] != '' )
            {
                $from = $configuration['MESS_FROM_NAME'] . ' <info@' . ((isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : 'processmaker.com') . '>';
            }
            else if ( $configuration['MESS_FROM_MAIL'] != '' )
            {
                $from = $configuration['MESS_FROM_MAIL'];
            }
            else if ( $configuration['MESS_ENGINE'] == 'PHPMAILER' && preg_match ('/(.+)@(.+)\.(.+)/', $configuration['MESS_ACCOUNT'], $match) )
            {
                $from = $configuration['MESS_ACCOUNT'];
            }
            else if ( $configuration['MESS_ENGINE'] == 'PHPMAILER' && $configuration['MESS_ACCOUNT'] != '' && !preg_match ('/(.+)@(.+)\.(.+)/', $configuration['MESS_ACCOUNT'], $match) )
            {
                $from = $configuration['MESS_ACCOUNT'] . ' <info@' . ((isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : 'processmaker.com') . '>';
            }
            else
            {
                $from = 'info@' . ((isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : 'processmaker.com');
            }
        }
        return $from;
    }

    /**
     * Send a test email
     *
     * @param array $arrayData Data
     *
     * return array Return array with result of send test mail
     */
    public function sendTestMail (array $arrayData)
    {
        try {
            $aConfiguration = array(
                "MESS_ENGINE" => $arrayData["MESS_ENGINE"],
                "MESS_SERVER" => $arrayData["MESS_SERVER"],
                "MESS_PORT" => (int) ($arrayData["MESS_PORT"]),
                "MESS_ACCOUNT" => $arrayData["MESS_ACCOUNT"],
                "MESS_PASSWORD" => $arrayData["MESS_PASSWORD"],
                "MESS_FROM_NAME" => $arrayData["FROM_NAME"],
                "MESS_FROM_MAIL" => $arrayData["FROM_EMAIL"],
                "MESS_RAUTH" => (int) ($arrayData["MESS_RAUTH"]),
                "SMTPSecure" => (isset ($arrayData["SMTPSecure"])) ? $arrayData["SMTPSecure"] : "none"
            );
            $sFrom = $this->buildFrom ($aConfiguration);

            $sSubject = "test subject";
            $msg = "test body";

            switch ($arrayData["MESS_ENGINE"]) {
                case "MAIL":
                    $engine = "ID_MESS_ENGINE_TYPE_1";
                    break;
                case "PHPMAILER":
                    $engine = "ID_MESS_ENGINE_TYPE_2";
                    break;
                case "OPENMAIL":
                    $engine = "ID_MESS_ENGINE_TYPE_3";
                    break;
            }

            $oSpool = new \EmailFunctions();
            $sBody = "Test Mike";

            $oSpool->setConfig ($aConfiguration);

            $oSpool->create (
                    array(
                        "msg_uid" => "",
                        "app_uid" => "",
                        "del_index" => 0,
                        "app_msg_type" => "TEST",
                        "app_msg_subject" => $sSubject,
                        "app_msg_from" => $sFrom,
                        "app_msg_to" => $arrayData["TO"],
                        "app_msg_body" => $sBody,
                        "app_msg_cc" => "",
                        "app_msg_bcc" => "",
                        "app_msg_attach" => "",
                        "app_msg_template" => "",
                        "app_msg_status" => "pending",
                        "app_msg_attach" => ""
                    )
            );

            $oSpool->sendMail ();

            //Return
            $arrayTestMailResult = array();

            if ( $oSpool->status == "sent" )
            {
                $arrayTestMailResult["status"] = true;
                $arrayTestMailResult["success"] = true;
                $arrayTestMailResult["msg"] = "ID_MAIL_TEST_SUCCESS";
            }
            else
            {
                $arrayTestMailResult["status"] = false;
                $arrayTestMailResult["success"] = false;
                $arrayTestMailResult["msg"] = $oSpool->error;
            }

            return $arrayTestMailResult;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
