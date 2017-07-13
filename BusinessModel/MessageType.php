<?php

namespace BusinessModel;

class MessageType
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Update Message-Type
     *
     * @param string $messageTypeUid Unique id of Message-Type
     * @param array  $arrayData      Data
     *
     * return array Return data of the Message-Type updated
     */
    public function update ($messageTypeUid, $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            $arrayDataBackup = $arrayData;
            unset ($arrayData["MSGT_UID"]);
            unset ($arrayData["PRJ_UID"]);
            //Set variables
            $arrayMessageTypeData = $this->getMessageType ($messageTypeUid, true);
            
            //Verify data
            $this->throwExceptionIfNotExistsMessageType ($messageTypeUid);
            $this->throwExceptionIfDataIsInvalid ($messageTypeUid, $arrayMessageTypeData[0]["workflow_id"], $arrayData);
            //Update
            try {
                $messageType = new \MessageType();
                $messageType->setPrjUid ($arrayMessageTypeData[0]["workflow_id"]);
                $messageType->setTitle ($arrayData['name']);
                $messageType->setDescription ($arrayData['description']);
                $messageType->setId($messageTypeUid);
                
                if ( $messageType->validate () )
                {

                    $variable = new \BusinessModel\MessageType\Variable();
                    $variable->deleteAll($messageTypeUid);

                    if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {

                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;

                            $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }

                    $messageType->save ();
                    //Return
                    $arrayData = $arrayDataBackup;
                    return $arrayData;
                }
                else
                {
                    $msg = "";
                    foreach ($messageType->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
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
     * Delete Message-Type
     *
     * @param string $messageTypeUid Unique id of Message-Type
     *
     * return void
     */
    public function delete($messageTypeUid)
    {
        try {
            $this->throwExceptionIfNotExistsMessageType($messageTypeUid);
            //Delete Message-Type-Variable
            $messageTypes = new \MessageType();
            $messageTypes->setId($messageTypeUid);
            $messageTypes->delete();

            $variable = new \BusinessModel\MessageType\Variable();
            $variable->deleteAll($messageTypeUid);
           
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Message-Type
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Message-Type created
     */
    public function create ($projectUid, array $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
//            $process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);
            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);
            //Create
            try {
                $messageType = new \MessageType();
                $messageType->setPrjUid ($projectUid);
                $messageType->setTitle ($arrayData['name']);
                $messageType->setDescription ($arrayData['description']);
                if ( $messageType->validate () )
                {
                    $messageTypeUid = $messageType->save ();

                    if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                        $variable = new \BusinessModel\MessageType\Variable();

                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;

                            $arrayResult = $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }

                    //Return
                    return $this->getMessageType ($messageTypeUid);
                }
                else
                {
                    $msg = "";
                    foreach ($messageType->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . (($msg != "") ? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $messageTypeUid Unique id of Message-Type
     * @param string $projectUid     Unique id of Project
     * @param array  $arrayData      Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($messageTypeUid, $projectUid, array $arrayData)
    {
        try {
            //Verify data
            if ( isset ($arrayData["name"]) )
            {
                $this->throwExceptionIfExistsName ($projectUid, $arrayData["name"], $messageTypeUid);
            }
            if ( isset ($arrayData["MSGT_VARIABLES"]) && count ($arrayData["MSGT_VARIABLES"]) > 0 )
            {
                $this->throwExceptionCheckIfThereIsRepeatedVariableName ($arrayData["MSGT_VARIABLES"]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Name of a Message-Type
     *
     * @param string $projectUid              Unique id of Project
     * @param string $messageTypeName         Name
     * @param string $messageTypeUidToExclude Unique id of Message to exclude
     *
     * return bool Return true if exists the Name of a Message-Type, false otherwise
     */
    public function existsName ($projectUid, $messageTypeName, $messageTypeUidToExclude = "")
    {
        try {
            $arrParameters = [];
            $sql = "SELECT * FROM workflow.message_type WHERE title = ? AND workflow_id = ?";
            $arrParameters[] = $messageTypeName;
            $arrParameters[] = $projectUid;
            if ( $messageTypeUidToExclude != "" )
            {
                $sql .= " AND id != ?";
                $arrParameters[] = $messageTypeUidToExclude;
            }
            $result = $this->objMysql->_query ($sql, $arrParameters);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
            return ($rsCriteria->next ()) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMessageType ($id, $flagGetRecord = false)
    {
        $result = $this->objMysql->_select ("workflow.message_type", [], ["id" => $id]);

        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }

        //Variable
        $arrayVariable = array();

        $variable = new \BusinessModel\MessageType\Variable();

        $criteriaMessageTypeVariable = $variable->getMessageTypeVariableCriteria();

        $criteriaMessageTypeVariable .= " WHERE MSGT_UID = ?";
        $arrParameters = array($id);

       $variableResults = $this->objMysql->_query($criteriaMessageTypeVariable, $arrParameters);

        foreach($variableResults as $variableResult) {

            if (!$flagGetRecord) {
                $arrayVariable[] = $variable->getMessageTypeVariableDataFromRecord($variableResult, false);
            } else {
                unset($variableResult["MSGTV_UID"]);

                $arrayVariable[] = $variableResult;
            }
        }

        $row["MSGT_VARIABLES"] = $arrayVariable;

        return $result;
    }

    public function throwExceptionCheckIfThereIsRepeatedVariableName (array $arrayDataVariables)
    {
        try {
            $i = 0;
            $arrayDataVarAux = $arrayDataVariables;
            while ($i <= count ($arrayDataVariables) - 1) {
                if ( array_key_exists ("MSGTV_NAME", $arrayDataVariables[$i]) )
                {
                    $msgtvNameAux = $arrayDataVariables[$i]["MSGTV_NAME"];
                    $counter = 0;
                    foreach ($arrayDataVarAux as $key => $value) {
                        if ( $value["MSGTV_NAME"] == $msgtvNameAux )
                        {
                            $counter = $counter + 1;
                        }
                    }
                    if ( $counter > 1 )
                    {
                        throw new \Exception ("Variable names must be unique");
                    }
                }
                $i = $i + 1;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function exists ($id)
    {
        $result = $this->getMessageType ($id);

        return isset ($result[0]) && !empty ($result[0]) ? true : false;
    }

    /**
     * Verify if exists the Name of a Message-Type
     *
     * @param string $projectUid              Unique id of Project
     * @param string $messageTypeName         Name
     * @param string $fieldNameForException   Field name for the exception
     * @param string $messageTypeUidToExclude Unique id of Message-Type to exclude
     *
     * return void Throw exception if exists the title of a Message-Type
     */
    public function throwExceptionIfExistsName ($projectUid, $messageTypeName, $messageTypeUidToExclude = "")
    {
        try {
            if ( $this->existsName ($projectUid, $messageTypeName, $messageTypeUidToExclude) )
            {
                throw new \Exception ("ID_MESSAGE_TYPE_NAME_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exist the Message-Type
     *
     * @param string $messageTypeUid        Unique id of Message-Type
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exist the Message-Type
     */
    public function throwExceptionIfNotExistsMessageType ($messageTypeUid)
    {
        try {
            $messageType = $this->getMessageType ($messageTypeUid);

            if ( !isset ($messageType[0]) || empty ($messageType[0]) )
            {
                throw new \Exception ("ID_MESSAGE_TYPE_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMessageTypesByProcess ($processUid)
    {
        $results = $this->objMysql->_select ("workflow.message_type", [], ["workflow_id" => $processUid]);

        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }

       foreach($results as $key => $result) {

            $arrayVariable = array();

            $variable = new \BusinessModel\MessageType\Variable();

            $criteriaMessageTypeVariable = $variable->getMessageTypeVariableCriteria();

            $criteriaMessageTypeVariable .= " WHERE MSGT_UID = ?";
            $arrParameters = array($result["MSGT_UID"]);

            $rsCriteriaMessageTypeVariable = $this->objMysql->_query($criteriaMessageTypeVariable, $arrParameters);

           foreach($rsCriteriaMessageTypeVariable as $row2) {
                $arrayVariable[] = $variable->getMessageTypeVariableDataFromRecord($row2, false);
            }

            $results[$key]["MSGT_VARIABLES"] = $arrayVariable;

        }


        return $results;
    }

}
