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
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
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
            $results = $this->objMysql->_query($criteria, $arrParameters);

            if(!isset($results[0]) || empty($results[0])) {
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

}
