class Step
{

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
    public function existsRecord($taskUid, $type, $objectUid, $position = 0, $stepUidExclude = "")
    {
        try {
            
            $sql = "SELECT STEP_UID FROM step_object WHERE TAS_UID = ?";
            $arrParameters = array($taskUid);
            
            if ($stepUidExclude != "") {
                $sql .= " AND STEP_UID != ?";
                $arrParameters[] = $stepUidExclude;
            }

            if ($type != "") {
                $sql .= " AND STEP_TYPE_OBJ = ?";
                $arrParameters[] = $type;
            }

            if ($objectUid != "") {
                $sql .= " AND STEP_UID_OBJ = ?";
                $arrParameters[] = $objectUid;
            }

           $result = $this->objMysql->_query($sql, $arrParameters);

            if(isset($result[0]) && !empty($result[0])) {
                return true;
            } else {
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
    public function existsObjectUid($type, $objectUid)
    {
        try {
            $msg = "";

            switch ($type) {
                case "DYNAFORM":
                    $dynaform = new \Dynaform();

                    if (!$dynaform->dynaformExists($objectUid)) {
                        $msg = "ID_DYNAFORM_DOES_NOT_EXIST";
                    }
                    break;
                case "INPUT_DOCUMENT":
                    $inputdoc = new \InputDocument();

                    if (!$inputdoc->InputExists($objectUid)) {
                        $msg = "ID_INPUT_DOCUMENT_DOES_NOT_EXIST";
                    }
                    break;
                case "OUTPUT_DOCUMENT":
                    $outputdoc = new \OutputDocument();

                    if (!$outputdoc->OutputExists($objectUid)) {
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
    public function throwExceptionIfHaveInvalidValueInTypeObj($stepTypeObj)
    {
        $arrayDefaultValues = array("DYNAFORM", "INPUT_DOCUMENT", "OUTPUT_DOCUMENT", "EXTERNAL");

        if (!in_array($stepTypeObj, $arrayDefaultValues)) {


            throw new \Exception("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES");
        }
    }

    /**
     * Verify if Mode has invalid value
     *
     * @param string $stepMode Mode
     *
     * return void Throw exception if Mode has invalid value
     */
    public function throwExceptionIfHaveInvalidValueInMode($stepMode)
    {
        $arrayDefaultValues = array("EDIT", "VIEW");

        if (!in_array($stepMode, $arrayDefaultValues)) {

            throw new \Exception("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES");
        }
    }

    /**
     * Verify if doesn't exist the Step in table STEP
     *
     * @param string $stepUid Unique id of Step
     *
     * return void Throw exception if doesn't exist the Step in table STEP
     */
    public function throwExceptionIfNotExistsStep($stepUid)
    {
        $step = new \Step();

        if (!$step->StepExists($stepUid)) {
            throw new \Exception("ID_STEP_DOES_NOT_EXIST");
        }
    }

    /**
     * Verify if doesn't exist the Process in table PROCESS
     *
     * @param string $processUid Unique id of Process
     *
     * return void Throw exception if doesn't exist the Process in table PROCESS
     */
    public function throwExceptionIfNotExistsProcess($processUid)
    {
        $process = new \Process();

        if (!$process->exists($processUid)) {
            throw new \Exception("ID_PROJECT_DOES_NOT_EXIST");
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
    public function create($taskUid, $processUid, $arrayData)
    {
        try {

            unset($arrayData["STEP_UID"]);

            //Verify data
            $task = new \ProcessMaker\BusinessModel\Task();

            $this->throwExceptionIfNotExistsProcess($processUid);

            $task->throwExceptionIfNotExistsTask($processUid, $taskUid);

            if (!isset($arrayData["STEP_TYPE_OBJ"])) {
                throw new \Exception("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_TYPE_OBJ"] = trim($arrayData["STEP_TYPE_OBJ"]);

            if ($arrayData["STEP_TYPE_OBJ"] == "") {
                throw new \Exception("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            if (!isset($arrayData["STEP_UID_OBJ"])) {
                throw new \Exception("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_UID_OBJ"] = trim($arrayData["STEP_UID_OBJ"]);

            if ($arrayData["STEP_UID_OBJ"] == "") {
                throw new \Exception("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            if (!isset($arrayData["STEP_MODE"])) {
                throw new \Exception("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["STEP_MODE"] = trim($arrayData["STEP_MODE"]);

            if ($arrayData["STEP_MODE"] == "") {
                throw new \Exception("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            $this->throwExceptionIfHaveInvalidValueInTypeObj($arrayData["STEP_TYPE_OBJ"]);

            $this->throwExceptionIfHaveInvalidValueInMode($arrayData["STEP_MODE"]);

            $msg = $this->existsObjectUid($arrayData["STEP_TYPE_OBJ"], $arrayData["STEP_UID_OBJ"]);

            if ($msg != "") {
                throw new \Exception($msg);
            }

            if ($this->existsRecord($taskUid, $arrayData["STEP_TYPE_OBJ"], $arrayData["STEP_UID_OBJ"])) {
                throw new \Exception("ID_RECORD_EXISTS_IN_TABLE");
            }


            $stepUid = 20;

            $this->throwExceptionIfNotExistsStep($stepUid);
            $step = new \Step();

            $result = $step->update($arrayData);

            //Return
            unset($arrayData["STEP_UID"]);

            $arrayData = array_merge(array("STEP_UID" => $stepUid), $arrayData);

            return $arrayData;
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
    public function getStep($stepUid)
    {
        try {
            $arrayStep = array();

            //Verify data
            $this->throwExceptionIfNotExistsStep($stepUid);

            //Get data
            $sql = "SELECT * FROM step_object WHERE STEP_UID = ?";
            $arrParameters = array($stepUid);

            $results = $this->objMysql->_query($sql, $arrParameters);

            $titleObj = "";
            $descriptionObj = "";

            $arrayStep = array();
            
            foreach($results as $row) {
                switch ($row["STEP_TYPE_OBJ"]) {
                    case "DYNAFORM":
                        $dynaform = new \Dynaform();
                        $arrayData = $dynaform->load($row["STEP_UID_OBJ"]);

                        $titleObj = $arrayData["DYN_TITLE"];
                        $descriptionObj = $arrayData["DYN_DESCRIPTION"];
                        break;
                    case "INPUT_DOCUMENT":
                        $inputDocument = new \InputDocument();
                        $arrayData = $inputDocument->getByUid($row["STEP_UID_OBJ"]);

                        if ($arrayData === false) {
                            return $arrayStep;
                        }

                        $titleObj = $arrayData["INP_DOC_TITLE"];
                        $descriptionObj = $arrayData["INP_DOC_DESCRIPTION"];
                        break;
                    case "OUTPUT_DOCUMENT":
                        $outputDocument = new \OutputDocument();
                        $arrayData = $outputDocument->getByUid($row["STEP_UID_OBJ"]);

                        if ($arrayData === false) {
                            return $arrayStep;
                        }

                        $titleObj = $arrayData["OUT_DOC_TITLE"];
                        $descriptionObj = $arrayData["OUT_DOC_DESCRIPTION"];
                        break;
                    case "EXTERNAL":
                        $titleObj = "unknown " . $row["STEP_UID"];

                        if (is_array($externalSteps) && count($externalSteps) > 0) {
                            foreach ($externalSteps as $key => $value) {
                                if ($value->sStepId == $row["STEP_UID_OBJ"]) {
                                    $titleObj = $value->sStepTitle;
                                }
                            }
                        }
                        break;
                }

                //Return
                $arrayStep[] = array(
                    $this->getFieldNameByFormatFieldName("STEP_UID")        => $stepUid,
                    $this->getFieldNameByFormatFieldName("STEP_TYPE_OBJ")   => $row["STEP_TYPE_OBJ"],
                    $this->getFieldNameByFormatFieldName("STEP_UID_OBJ")    => $row["STEP_UID_OBJ"],
                    $this->getFieldNameByFormatFieldName("STEP_CONDITION")  => $row["STEP_CONDITION"],
                    $this->getFieldNameByFormatFieldName("STEP_POSITION")   => (int)($row["STEP_POSITION"]),
                    $this->getFieldNameByFormatFieldName("STEP_MODE")       => $row["STEP_MODE"],
                    $this->getFieldNameByFormatFieldName("OBJ_TITLE")       => $titleObj,
                    $this->getFieldNameByFormatFieldName("OBJ_DESCRIPTION") => $descriptionObj
                );
            }

           

            return $arrayStep;
        } catch (\Exception $e) {
            throw $e;
        }
    }



/**************** MODEL ****************************/
 /*
    * update the step information using an array with all values
    * @param array $fields
    * @return variant
    */
    public function update ($fields)
    {
        try {
            $this->load( $fields['STEP_UID'] );
            $this->loadObject( $fields );
            if ($this->validate()) {
                $result = $this->save();
                return $result;
            } else {
                throw (new Exception( "Failed Validation in class " . get_class( $this ) . "." ));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

/************************* BASE MODEL ******************************************/

 protected $arrFieldMapping = array(
        'STEP_UID' => array('accessor' => 'getStepUid', 'mutator' => 'setStepUid', 'type' => 'int', 'required' => 'true'),
        'PRO_UID' => array('accessor' => 'getProUid', 'mutator' => 'setProUid', 'type' => 'string', 'required' => 'true'),
        'TAS_UID' => array('accessor' => 'getTasUid', 'mutator' => 'setTasUid', 'type' => 'string', 'required' => 'false'),
        'STEP_TYPE_OBJ' => array('accessor' => 'getStepTypeObj', 'mutator' => 'setStepTypeObj', 'type' => 'string', 'required' => 'true'),
	'STEP_UID_OBJ' => array('accessor' => 'getStepUidObj', 'mutator' => 'setStepUidObj', 'type' => 'string', 'required' => 'true'),
	'STEP_CONDITION' => array('accessor' => 'getStepCondition', 'mutator' => 'setStepCondition', 'type' => 'string', 'required' => 'true'),
	'STEP_MODE' => array('accessor' => 'getStepMode', 'mutator' => 'setStepMode', 'type' => 'string', 'required' => 'true')
    );

 /**
     * The value for the step_uid field.
     * @var        string
     */
    protected $step_uid = '';

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '0';

    /**
     * The value for the tas_uid field.
     * @var        string
     */
    protected $tas_uid = '0';

    /**
     * The value for the step_type_obj field.
     * @var        string
     */
    protected $step_type_obj = 'DYNAFORM';

    /**
     * The value for the step_uid_obj field.
     * @var        string
     */
    protected $step_uid_obj = '0';

    /**
     * The value for the step_condition field.
     * @var        string
     */
    protected $step_condition;

    /**
     * The value for the step_position field.
     * @var        int
     */
    protected $step_position = 0;

    /**
     * The value for the step_mode field.
     * @var        string
     */
    protected $step_mode = 'EDIT';

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [step_uid] column value.
     * 
     * @return     string
     */
    public function getStepUid()
    {

        return $this->step_uid;
    }

    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid()
    {

        return $this->pro_uid;
    }

    /**
     * Get the [tas_uid] column value.
     * 
     * @return     string
     */
    public function getTasUid()
    {

        return $this->tas_uid;
    }

    /**
     * Get the [step_type_obj] column value.
     * 
     * @return     string
     */
    public function getStepTypeObj()
    {

        return $this->step_type_obj;
    }

    /**
     * Get the [step_uid_obj] column value.
     * 
     * @return     string
     */
    public function getStepUidObj()
    {

        return $this->step_uid_obj;
    }

    /**
     * Get the [step_condition] column value.
     * 
     * @return     string
     */
    public function getStepCondition()
    {

        return $this->step_condition;
    }

    /**
     * Get the [step_position] column value.
     * 
     * @return     int
     */
    public function getStepPosition()
    {

        return $this->step_position;
    }

    /**
     * Get the [step_mode] column value.
     * 
     * @return     string
     */
    public function getStepMode()
    {

        return $this->step_mode;
    }

    /**
     * Set the value of [step_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_uid !== $v || $v === '') {
            $this->step_uid = $v;
            $this->modifiedColumns[] = StepPeer::STEP_UID;
        }

    } // setStepUid()

    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->pro_uid !== $v || $v === '0') {
            $this->pro_uid = $v;
            $this->modifiedColumns[] = StepPeer::PRO_UID;
        }

    } // setProUid()

    /**
     * Set the value of [tas_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setTasUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->tas_uid !== $v || $v === '0') {
            $this->tas_uid = $v;
            $this->modifiedColumns[] = StepPeer::TAS_UID;
        }

    } // setTasUid()

    /**
     * Set the value of [step_type_obj] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepTypeObj($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_type_obj !== $v || $v === 'DYNAFORM') {
            $this->step_type_obj = $v;
            $this->modifiedColumns[] = StepPeer::STEP_TYPE_OBJ;
        }

    } // setStepTypeObj()

    /**
     * Set the value of [step_uid_obj] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepUidObj($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_uid_obj !== $v || $v === '0') {
            $this->step_uid_obj = $v;
            $this->modifiedColumns[] = StepPeer::STEP_UID_OBJ;
        }

    } // setStepUidObj()

    /**
     * Set the value of [step_condition] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepCondition($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_condition !== $v) {
            $this->step_condition = $v;
            $this->modifiedColumns[] = StepPeer::STEP_CONDITION;
        }

    } // setStepCondition()

    /**
     * Set the value of [step_position] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setStepPosition($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->step_position !== $v || $v === 0) {
            $this->step_position = $v;
            $this->modifiedColumns[] = StepPeer::STEP_POSITION;
        }

    } // setStepPosition()

    /**
     * Set the value of [step_mode] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepMode($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_mode !== $v || $v === 'EDIT') {
            $this->step_mode = $v;
            $this->modifiedColumns[] = StepPeer::STEP_MODE;
        }

    } // setStepMode()


    public function save()
    {

	if(trim($id) === "") {
	     $result = $this-objMysql_insert("workflow.step_object", ['STEP_UID', 'PRO_UID', 'TAS_UID', 'STEP_TYPE_OBJ', 'STEP_CONDITION', 'STEP_MODE']);

        } else {
             $result = $this-objMysql_update("workflow.step_object", ['STEP_UID', 'PRO_UID', 'TAS_UID', 'STEP_TYPE_OBJ', 'STEP_CONDITION', 'STEP_MODE'], ['id' => $id]);
        }
       
    }

    public function loadObject(array $arrData)
    {
    }



}



/*
Navicat MySQL Data Transfer

Source Server         : dms-sql-001.kondor.dev
Source Server Version : 50715
Source Host           : dms-sql-001.kondor.dev:3306
Source Database       : central_workflow

Target Server Type    : MYSQL
Target Server Version : 50715
File Encoding         : 65001

Date: 2017-07-06 09:24:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for step_object
-- ----------------------------
DROP TABLE IF EXISTS `step_object`;
CREATE TABLE `step_object` (
  `STEP_UID` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `PRO_UID` int(11) NOT NULL,
  `TAS_UID` int(11) DEFAULT NULL,
  `STEP_TYPE_OBJ` enum('DYNAFORM','INPUT_DOCUMENT','OUTPUT_DOCUMENT','EXTERNAL') DEFAULT 'DYNAFORM',
  `STEP_UID_OBJ` int(11) DEFAULT NULL,
  `STEP_CONDITION` text,
  `STEP_MODE` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `STEP_UID` (`STEP_UID`),
  CONSTRAINT `step_object_ibfk_1` FOREIGN KEY (`STEP_UID`) REFERENCES `step` (`step_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS=1;
