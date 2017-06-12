<?php
class MessageType
{
  /**
     * Create Message-Type
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Message-Type created
     */
    public function create($projectUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            $process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);

            $this->throwExceptionIfDataIsInvalid("", $projectUid, $arrayData);

            //Create

            try {
                $messageType = new \MessageType();

                $messageType->fromArray($arrayData, \BasePeer::TYPE_FIELDNAME);


                $messageType->setPrjUid($projectUid);

                if ($messageType->validate()) {

                    $result = $messageType->save();

                    /*if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                        $variable = new \ProcessMaker\BusinessModel\MessageType\Variable();
                        $variable->setFormatFieldNameInUppercase($this->formatFieldNameInUppercase);
                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;
                            $arrayResult = $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }*/

                    //Return
                    return $this->getMessageType($messageTypeUid);
                } else {
                    $msg = "";

                    foreach ($messageType->getValidationFailures() as $validationFailure) {
                        $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure->getMessage();
                    }

                    throw new \Exception("ID_RECORD_CANNOT_BE_CREATED" . (($msg != "")? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {

                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

	 public function update($messageTypeUid, $arrayData)
    {
        try {
            //Verify data
           

            $this->throwExceptionIfDataIsNotArray($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty($arrayData, "\$arrayData");

            //Set data

            //Set variables
            $arrayMessageTypeData = $this->getMessageType($messageTypeUid, true);

            //Verify data
            $this->throwExceptionIfNotExistsMessageType($messageTypeUid, $this->arrayFieldNameForException["messageTypeUid"]);

            $this->throwExceptionIfDataIsInvalid($messageTypeUid, $arrayMessageTypeData["PRJ_UID"], $arrayData);

            //Update

            try {
                $messageType = \MessageTypePeer::retrieveByPK($messageTypeUid);
                $messageType->fromArray($arrayData, \BasePeer::TYPE_FIELDNAME);

                if ($messageType->validate()) {

                    $result = $messageType->save();


                    /*if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                        $variable = new \ProcessMaker\BusinessModel\MessageType\Variable();
                        $variable->setFormatFieldNameInUppercase($this->formatFieldNameInUppercase);
                        foreach ($arrayData["MSGT_VARIABLES"] as $key => $value) {
                            $arrayVariable = $value;
                            $arrayResult = $variable->create($messageTypeUid, $arrayVariable);
                        }
                    }*/


                    return true
                } else {
                    $msg = "";

                    foreach ($messageType->getValidationFailures() as $validationFailure) {
                        $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure->getMessage();
                    }

                    throw new \Exception("ID_REGISTRY_CANNOT_BE_UPDATED" . (($msg != "")? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                $cnn->rollback();

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
    public function throwExceptionIfDataIsInvalid($messageTypeUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayMessageTypeData = ($messageTypeUid == "")? array() : $this->getMessageType($messageTypeUid, true);
            $flagInsert           = ($messageTypeUid == "")? true : false;

            $arrayFinalData = array_merge($arrayMessageTypeData, $arrayData);

            //Verify data - Field definition

            //Verify data
            if (isset($arrayData["MSGT_NAME"])) {
                $this->throwExceptionIfExistsName($projectUid, $arrayData["MSGT_NAME"], $this->arrayFieldNameForException["messageTypeName"], $messageTypeUid);
            }

            if (isset($arrayData["MSGT_VARIABLES"]) && count($arrayData["MSGT_VARIABLES"]) > 0) {
                $this->throwExceptionCheckIfThereIsRepeatedVariableName($arrayData["MSGT_VARIABLES"]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

 public function throwExceptionCheckIfThereIsRepeatedVariableName(array $arrayDataVariables)
    {
        try {
            $i = 0;
            $arrayDataVarAux = $arrayDataVariables;

            while ($i <= count($arrayDataVariables) - 1) {
                if (array_key_exists("MSGTV_NAME", $arrayDataVariables[$i])) {
                    $msgtvNameAux = $arrayDataVariables[$i]["MSGTV_NAME"];
                    $counter = 0;

                    foreach ($arrayDataVarAux as $key => $value) {
                        if ($value["MSGTV_NAME"] == $msgtvNameAux) {
                            $counter = $counter + 1;
                        }
                    }

                    if ($counter > 1) {
                        throw new \Exception("ID_MESSAGE_TYPE_NAME_VARIABLE_EXISTS");
                    }
                }

                $i = $i + 1;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
