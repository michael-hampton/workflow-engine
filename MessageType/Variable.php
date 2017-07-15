<?php

/**
 * Created by PhpStorm.
 * User: michael.hampton
 * Date: 13/07/2017
 * Time: 10:31
 */

namespace BusinessModel\MessageType;

use BusinessModel\MessageType;
use BusinessModel\Validator;

class Variable
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    public function retrieveByPK ($messageTypeVariableUid)
    {

        $result = $this->objMysql->_select ("workflow.MESSAGE_TYPE_VARIABLE", [], ["MSGTV_NAME" => $messageTypeVariableUid]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result;
    }

    /**
     * Verify if exists the Message-Type-Variable
     *
     * @param string $messageTypeVariableUid Unique id of Message-Type-Variable
     *
     * return bool Return true if exists the Message-Type-Variable, false otherwise
     */
    public function exists ($messageTypeVariableUid)
    {
        try {
            $obj = $this->retrieveByPK ($messageTypeVariableUid);

            return $obj === false ? false : true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Name of a Message-Type-Variable
     *
     * @param string $messageTypeUid Unique id of Project
     * @param string $messageTypeVariableName Name
     * @param string $messageTypeVariableUidToExclude Unique id of Message-Type-Variable to exclude
     *
     * return bool Return true if exists the Name of a Message-Type-Variable, false otherwise
     */
    public function existsName ($messageTypeUid, $messageTypeVariableName, $messageTypeVariableUidToExclude = "")
    {
        try {
            $criteria = $this->getMessageTypeVariableCriteria ();

            $criteria .= " WHERE 1=1";

            if ( $messageTypeVariableUidToExclude != "" )
            {
                $criteria .= " AND MSGTV_UID != ?";
                $arrParameters = array($messageTypeVariableUidToExclude);
            }

            $criteria .= " AND MSGT_UID = ?";
            $arrParameters[] = $messageTypeUid;

            $criteria .= " AND MSGTV_NAME = ?";
            $arrParameters[] = $messageTypeVariableName;


            //QUERY
            $result = $this->objMysql->_query ($criteria, $arrParameters);

            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Name of a Message-Type-Variable
     *
     * @param string $messageTypeUid Unique id of Project
     * @param string $messageTypeVariableName Name
     * @param string $fieldNameForException Field name for the exception
     * @param string $messageTypeVariableUidToExclude Unique id of Message to exclude
     *
     * return void Throw exception if exists the Name of a Message-Type-Variable
     */
    public function throwExceptionIfExistsName ($messageTypeUid, $messageTypeVariableName, $messageTypeVariableUidToExclude = "")
    {
        try {
            if ( $this->existsName ($messageTypeUid, $messageTypeVariableName, $messageTypeVariableUidToExclude) )
            {
                throw new \Exception ("ID_MESSAGE_TYPE_VARIABLE_NAME_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $messageTypeVariableUid Unique id of Message-Type-Variable
     * @param string $messageTypeUid Unique id of Project
     * @param array $arrayData Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($messageTypeVariableUid, $messageTypeUid, array $arrayData)
    {
        try {
            //Verify data
            if ( isset ($arrayData["MSGTV_NAME"]) )
            {
                $this->throwExceptionIfExistsName ($messageTypeUid, $arrayData["MSGTV_NAME"], $messageTypeVariableUid);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exist the Message-Type-Variable
     *
     * @param string $messageTypeVariableUid Unique id of Message-Type-Variable
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exist the Message-Type-Variable
     */
    public function throwExceptionIfNotExistsMessageTypeVariable ($messageTypeVariableUid)
    {
        try {

            if ( !$this->exists ($messageTypeVariableUid) )
            {
                //throw new \Exception("ID_MESSAGE_TYPE_VARIABLE_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Variable
     *
     * @param string $messageUid Unique id of Project
     * @param array $arrayData Data
     *
     * return array Return data of the new Message created
     */
    public function create ($messageTypeUid, array $arrayData)
    {
        try {
            //Verify data
            $messageType = new MessageType();

            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            unset ($arrayData["MSGTV_UID"]);
            unset ($arrayData["MSGT_UID"]);

            //Verify data
            $messageType->throwExceptionIfNotExistsMessageType ($messageTypeUid);

            $this->throwExceptionIfDataIsInvalid ("", $messageTypeUid, $arrayData);

            try {
                $messageTypeVariable = new \MessageTypeVariable();

                $messageTypeVariable->loadObject ($arrayData);

                $messageTypeVariable->setMsgtUid ($messageTypeUid);

                if ( $messageTypeVariable->validate () )
                {

                    $messageTypeVariableUid = $messageTypeVariable->save ();

                    //Return
                    return $this->getMessageTypeVariable ($messageTypeVariableUid);
                }
                else
                {
                    $msg = "";

                    foreach ($messageTypeVariable->getValidationFailures () as $message) {
                        $msg = $msg . $msg != "" ? "\n" : "" . $message;
                    }

                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Message-Type-Variable
     *
     * @param string $messageTypeVariable Uid Unique id of Message-Type-Variable
     * @param array $arrayData Data
     *
     * return array Return data of the Message-Type-Variable updated
     */
    public function update ($messageTypeVariableUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");

            //Set data

            unset ($arrayData["MSGTV_UID"]);
            unset ($arrayData["MSGT_UID"]);

            //Set variables
            $arrayMessageTypeVariableData = $this->getMessageTypeVariable ($messageTypeVariableUid, true);

            //Verify data
            $this->throwExceptionIfNotExistsMessageTypeVariable ($messageTypeVariableUid);

            $this->throwExceptionIfDataIsInvalid ($messageTypeVariableUid, $arrayMessageTypeVariableData["MSGT_UID"], $arrayData);

            //Update

            try {
                $messageTypeVariable = $this->retrieveByPK ($messageTypeVariableUid);
                $messageTypeVariable->loadObject ($arrayData);

                if ( $messageTypeVariable->validate () )
                {

                    $messageTypeVariable->save ();

                    //Return

                    return $arrayData;
                }
                else
                {
                    $msg = "";

                    foreach ($messageTypeVariable->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure;
                    }

                    throw new \Exception ("ID_REGISTRY_CANNOT_BE_UPDATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Message-Type-Variable
     *
     * @param string $messageTypeVariable Uid Unique id of Message-Type
     *
     * return void
     */
    public function deleteAll ($messageTypeUid)
    {
        try {

            (new MessageType())->throwExceptionIfNotExistsMessageType ($messageTypeUid);

            $messageTypeVariable = new \MessageTypeVariable();
            $messageTypeVariable->setMsgtUid ($messageTypeUid);
            $messageTypeVariable->deleteAll ();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Message-Type-Variable
     *
     * @param string $messageTypeVariable Uid Unique id of Message-Type
     *
     * return void
     */
    public function delete ($messageTypeVariableUid)
    {
        try {
            //$this->throwExceptionIfNotExistsMessageTypeVariable($messageTypeVariableUid);

            $messageTypeVariable = new \MessageTypeVariable();
            $messageTypeVariable->setMsgtvUid ($messageTypeVariableUid);
            $messageTypeVariable->delete ();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Message-Type-Variable
     *
     * return object
     */
    public function getMessageTypeVariableCriteria ()
    {
        try {
            $criteria = "SELECT MSGTV_UID, MSGT_UID, MSGTV_NAME, MSGTV_DEFAULT_VALUE FROM workflow.MESSAGE_TYPE_VARIABLE";


            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Message-Type-Variable
     */
    public function getMessageTypeVariableDataFromRecord (array $record, $includeUid = true)
    {
        try {
            $objMessageType = new \MessageTypeVariable();

            if ( $includeUid )
            {
                $objMessageType->setMsgtvUid ($record['MSGTV_UID']);
            }

            $objMessageType->setMsgtvName ($record["MSGTV_NAME"]);
            $objMessageType->setMsgtvDefaultValue ($record["MSGTV_DEFAULT_VALUE"] . "");

            return $objMessageType;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Message-Type-Variable
     *
     * @param string $messageTypeUid {@min 32}{@max 32}
     * @param array $arrayFilterData Data of the filters
     * @param string $sortField Field name to sort
     * @param string $sortDir Direction of sorting (ASC, DESC)
     * @param int $start Start
     * @param int $limit Limit
     *
     * return array Return an array with all Message-Type-Variable
     */
    public function getMessageTypeVariables ($messageTypeUid, $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayMessage = array();

            //Verify data
            $messageType = new MessageType();

            $messageType->throwExceptionIfNotExistsMessageType ($messageTypeUid);

            //Get data
            if ( !is_null ($limit) && $limit . "" == "0" )
            {
                return $arrayMessage;
            }

            //SQL
            $criteria = $this->getMessageTypeVariableCriteria ();

            $criteria .= " WHERE MSGT_UID = ?";
            $arrParameters = array($messageTypeUid);

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {

                $criteria .= " AND MSGTV_NAME LIKE '%" . $arrayFilterData["filter"] . "%'";
            }

            $countResult = $this->objMysql->_query ($criteria, $arrParameters);

            $numRecTotal = count ($countResult);

            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {
                $sortField = strtoupper ($sortField);

                if ( in_array ($sortField, array("MSGTV_NAME")) )
                {
                    $sortField = trim ($sortField);
                }
                else
                {
                    $sortField = 'MSGTV_NAME';
                }
            }
            else
            {
                $sortField = 'MSGTV_NAME';
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

            $results = $this->objMysql->_query ($criteria, $arrParameters);

            foreach ($results as $result) {
                $arrayMessage[] = $this->getMessageTypeVariableDataFromRecord ($result);
            }

            //Return
            return array(
                "total" => $numRecTotal,
                "start" => (int) ((!is_null ($start)) ? $start : 0),
                "limit" => (int) ((!is_null ($limit)) ? $limit : 0),
                "filter" => (!is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"])) ? $arrayFilterData["filter"] : "",
                "data" => $arrayMessage
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Message-Type-Variable
     *
     * @param string $messageTypeVariableUid Unique id of Message-Type-Variable
     * @param bool $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Message-Type-Variable
     */
    public function getMessageTypeVariable ($messageTypeVariableUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsMessageTypeVariable ($messageTypeVariableUid);

            //Get data
            //SQL
            $criteria = $this->getMessageTypeVariableCriteria ();

            $criteria .= " WHERE MSGTV_UID = ?";
            $arrParameters = array($messageTypeVariableUid);

            $result = $this->objMysql->_query ($criteria, $arrParameters);


            $row = $result[0];

            //Return
            return (!$flagGetRecord) ? $this->getMessageTypeVariableDataFromRecord ($row) : $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
