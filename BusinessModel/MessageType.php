<?php
class MessageType
{
    use Validator;
    private $objMysql;
    
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

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

//            $process->throwExceptionIfNotExistsProcess($projectUid, $this->arrayFieldNameForException["projectUid"]);

            $this->throwExceptionIfDataIsInvalid("", $projectUid, $arrayData);

            //Create

            try {
                $messageType = new \MessageTypes();

                $messageType->setPrjUid($projectUid);
                $messageType->setTitle($arrayData['name']);
                $messageType->setDescription($arrayData['description']);

                if ($messageType->validate()) {

                    $messageTypeUid = $messageType->save();

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
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfDataNotMetFieldDefinition($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, $flagInsert);
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
    
    public function getMessageType($id)
    {
        $result = $this->objMysql->_select("workflow.message_type", [], ["id" => $id]);
    }
}
