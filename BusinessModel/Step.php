<?php

namespace BusinessModel;

class Step
{

    public $objMysql;

    public function __construct ()
    {
        $this->objMyql = new \Mysql2();
    }

    public function getConnection ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the record in table STEP
     *
     * @param string $taskUid        Unique id of Task
     * @param string $type           Type of Step (DYNAFORM, INPUT_DOCUMENT, OUTPUT_DOCUMENT)
     * @param string $objectUid      Unique id of Object
     * @param int    $position       Position
     * @param string $stepUidExclude Unique id of Step to exclude
     *
     * return bool Return true if exists the record in table STEP, false otherwise
     */
    public function existsRecord ($taskUid, $type, $objectUid, $position = 0, $stepUidExclude = "")
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {

            $sql = "SELECT STEP_UID FROM step WHERE TAS_UID = ?";
            $arrParameters = array($taskUid);

            if ( $stepUidExclude != "" )
            {
                $sql .= " AND STEP_UID != ?";
                $arrParameters[] = $stepUidExclude;
            }

            if ( $type != "" )
            {
                $sql .= " AND STEP_TYPE_OBJ = ?";
                $arrParameters[] = $type;
            }

            if ( $objectUid != "" )
            {
                $sql .= " AND STEP_UID_OBJ = ?";
                $arrParameters[] = $objectUid;
            }

            $result = $this->objMysql->_query ($sql, $arrParameters);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the "Object UID" in the corresponding table
     *
     * @param string $type                  Type of Step (DYNAFORM, INPUT_DOCUMENT, OUTPUT_DOCUMENT)
     * @param string $objectUid             Unique id of Object
     * @param string $fieldNameForException Field name for the exception
     *
     * return strin Return empty string if $objectUid exists in the corresponding table, return string with data if $objectUid doesn't exist
     */
    public function existsObjectUid ($type, $objectUid)
    {
        try {
            $msg = "";

            switch ($type) {
                case "DYNAFORM":

                    break;
                case "INPUT_DOCUMENT":
                    $inputdoc = new \InputDocument();

                    if ( !$inputdoc->InputExists ($objectUid) )
                    {
                        $msg = "ID_INPUT_DOCUMENT_DOES_NOT_EXIST";
                    }
                    break;
                case "OUTPUT_DOCUMENT":
                    $outputdoc = new \OutputDocument();

                    if ( !$outputdoc->OutputExists ($objectUid) )
                    {
                        $msg = "ID_OUTPUT_DOCUMENT_DOES_NOT_EXIST";
                    }
                    break;
            }

            return $msg;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if Type Object has invalid value
     *
     * @param string $stepTypeObj Type Object
     *
     * return void Throw exception if Type Object has invalid value
     */
    public function throwExceptionIfHaveInvalidValueInTypeObj ($stepTypeObj)
    {
        $arrayDefaultValues = array("DYNAFORM", "INPUT_DOCUMENT", "OUTPUT_DOCUMENT", "EXTERNAL");

        if ( !in_array ($stepTypeObj, $arrayDefaultValues) )
        {

            throw new \Exception ("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES");
        }
    }

    /**
     * Verify if Mode has invalid value
     *
     * @param string $stepMode Mode
     *
     * return void Throw exception if Mode has invalid value
     */
    public function throwExceptionIfHaveInvalidValueInMode ($stepMode)
    {
        $arrayDefaultValues = array("EDIT", "VIEW");

        if ( !in_array ($stepMode, $arrayDefaultValues) )
        {

            throw new \Exception ("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES");
        }
    }

    /**
     * Verify if doesn't exist the Step in table STEP
     *
     * @param string $stepUid Unique id of Step
     *
     * return void Throw exception if doesn't exist the Step in table STEP
     */
    public function throwExceptionIfNotExistsStep ($stepUid)
    {

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.step", [], ["STEP_UID" => $stepUid]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            throw new Exception ("STEP ID DOESNT EXIST");
        }
    }

    /**
     * Verify if doesn't exist the Process in table PROCESS
     *
     * @param string $processUid Unique id of Process
     *
     * return void Throw exception if doesn't exist the Process in table PROCESS
     */
    public function throwExceptionIfNotExistsProcess ($processUid)
    {
        $process = new \BusinessModel\Process();

        if ( !$process->processExists ($processUid) )
        {
            throw new \Exception ("ID_PROJECT_DOES_NOT_EXIST");
        }
    }

    /**
     * Create Step for a Task
     *
     * @param string $taskUid    Unique id of Task
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Step created
     */
    public function create ($taskUid, $processUid, $arrayData)
    {
        try {

            unset ($arrayData["STEP_UID"]);

            //Verify data
            $task = new \BusinessModel\Task();

            $this->throwExceptionIfNotExistsProcess ($processUid);

            $task->throwExceptionIfNotExistsTask ($processUid, $taskUid);

            if ( !isset ($arrayData["STEP_TYPE_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_TYPE_OBJ"] = trim ($arrayData["STEP_TYPE_OBJ"]);

            if ( $arrayData["STEP_TYPE_OBJ"] == "" )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            if ( !isset ($arrayData["STEP_UID_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_UID_OBJ"] = trim ($arrayData["STEP_UID_OBJ"]);

            if ( $arrayData["STEP_UID_OBJ"] == "" )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            if ( !isset ($arrayData["STEP_MODE"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_MODE"] = trim ($arrayData["STEP_MODE"]);

            if ( $arrayData["STEP_MODE"] == "" )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            $this->throwExceptionIfHaveInvalidValueInTypeObj ($arrayData["STEP_TYPE_OBJ"]);

            $this->throwExceptionIfHaveInvalidValueInMode ($arrayData["STEP_MODE"]);

            $msg = $this->existsObjectUid ($arrayData["STEP_TYPE_OBJ"], $arrayData["STEP_UID_OBJ"]);

            if ( $msg != "" )
            {
                throw new \Exception ($msg);
            }

            if ( $this->existsRecord ($taskUid, $arrayData["STEP_TYPE_OBJ"], $arrayData["STEP_UID_OBJ"]) )
            {
                throw new \Exception ("ID_RECORD_EXISTS_IN_TABLE");
            }

            $arrayData['PRO_UID'] = $processUid;
            $arrayData['TAS_UID'] = $taskUid;

            $this->throwExceptionIfNotExistsStep ($taskUid);
            $step = new \Step();
            $stepUid = $step->update ($arrayData);

            //Return
            unset ($arrayData["STEP_UID"]);

            $arrayData = array_merge (array("STEP_UID" => $stepUid), $arrayData);

            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Steps of a Task
     *
     * @param string $taskUid Unique id of Task
     *
     * return array Return an array with all Steps of a Task
     */
    public function getSteps ($taskUid, $type = '')
    {
        try {

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            $arrayStep = array();
            $step = new Step();
            //Verify data
            $task = new Task();
            $task->throwExceptionIfNotExistsTask (null, $taskUid);

            $results = $this->objMysql->_select ("workflow.step", [], ["TAS_UID" => $taskUid], ["STEP_UID" => "ASC"]);

            foreach ($results as $row) {
                $arrayData = $step->getStep ($row["STEP_UID"], $type);

                if ( count ($arrayData) > 0 )
                {
                    $arrayStep[] = $arrayData;
                }
            }

            //Return
            return $arrayStep;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Step
     *
     * @param string $stepUid Unique id of Step
     *
     * return array Return an array with data of a Step
     */
    public function getStep ($stepUid, $type = '')
    {
        try {

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }

            $arrayStep = array();

            //Verify data
            $this->throwExceptionIfNotExistsStep ($stepUid);

            //Get data
            $arrWhere = array("STEP_UID" => $stepUid);

            if ( $type !== "" )
            {
                $arrWhere['STEP_TYPE_OBJ'] = $type;
            }

            $results = $this->objMysql->_select ("workflow.step", [], $arrWhere);

            $arrayStep = array();
            $titleObj = '';
            $descriptionObj = '';

            foreach ($results as $row) {

                switch ($row["STEP_TYPE_OBJ"]) {
                    case "DYNAFORM":
                        //$dynaform = new \Dynaform();
                        //$arrayData = $dynaform->load ($row["STEP_UID_OBJ"]);
                        //$titleObj = $arrayData["DYN_TITLE"];
                        break;
                    case "INPUT_DOCUMENT":
                        //$inputDocument = new \InputDocument();
                        //$arrayData = $inputDocument->getByUid ($row["STEP_UID_OBJ"]);
                        //if ( $arrayData === false )
                        //{
                        //return $arrayStep;
                        //}

                        break;
                    case "OUTPUT_DOCUMENT":
                        $outputDocument = new \OutputDocument();
                        $arrayData = $outputDocument->getByUid ($row["STEP_UID_OBJ"]);

                        if ( $arrayData === false )
                        {
                            return $arrayStep;
                        }

                        $titleObj = $arrayData->getOutDocTitle ();
                        $descriptionObj = $arrayData->getOutDocDescription ();

                        break;
                    case "EXTERNAL":
                        $titleObj = "unknown " . $row["STEP_UID"];

                        if ( isset ($externalSteps) && is_array ($externalSteps) && count ($externalSteps) > 0 )
                        {
                            foreach ($externalSteps as $value) {
                                if ( $value->sStepId == $row["STEP_UID_OBJ"] )
                                {
                                    $titleObj = $value->sStepTitle;
                                }
                            }
                        }
                        break;
                }

                $objStep = new \Step();
                $objStep->setStepUid ($stepUid);
                $objStep->setStepTypeObj ($row["STEP_TYPE_OBJ"]);
                $objStep->setStepMode ($row["STEP_MODE"]);
                $objStep->setStepCondition ($row["STEP_CONDITION"]);
                $objStep->setStepUidObj ($row["STEP_UID_OBJ"]);
                $objStep->setTitle ($titleObj);
                $objStep->setDescription ($descriptionObj);

                //Return
                $arrayStep[] = $objStep;
            }

            return $objStep;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
