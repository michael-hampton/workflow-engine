<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of FormAdmin
 *
 * @author michael.hampton
 */
class FormAdmin
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the title of a DynaForm
     *
     * @param string $processUid         Unique id of Process
     * @param string $dynaFormTitle      Title
     * @param string $dynaFormUidExclude Unique id of DynaForm to exclude
     *
     * return bool Return true if exists the title of a DynaForm, false otherwise
     */
    public function existsTitle ($processUid, $dynaFormTitle, $dynaFormUidExclude = "")
    {
        try {
            $sql = "SELECT DYN_UID, DYN_TITLE FROM workflow.form WHERE PRO_UID = ?";
            $arrWhere = array($processUid);

            if ( $dynaFormUidExclude != "" )
            {
                $sql .= " AND DYN_UID != ?";
                $arrWhere[] = $dynaFormUidExclude;
            }

            $sql .= " WHERE DYN_TITLE = ?";
            $arrWhere[] = $dynaFormTitle;

            $results = $this->objMysql->_query ($sql, $arrWhere);

            if ( isset ($results[0]) && !empty ($results[0]) )
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
     * Verify if a DynaForm is assigned some Steps
     *
     * @param string $dynaFormUid Unique id of DynaForm
     * @param string $processUid  Unique id of Process
     *
     * return bool Return true if a DynaForm is assigned some Steps, false otherwise
     */
    public function dynaFormDepends ($dynUid, $proUid)
    {
        $oCriteria = new \Criteria();

        $sql = "SELECT DYN_TYPE FROM workflow.form WHERE DYN_UID = ? AND PRO_UID = ?";
        $results = $this->objMysql->_query ($sql, [$dynUid, $proUid]);

        $dataDyna = $results[0];

        if ( $dataDyna['DYN_TYPE'] == 'grid' )
        {
            $formsDepend = array();

            $sql2 = "SELECT DYN_UID, DYN_TITLE FROM workflow.form WHERE PRO_UID = ? AND DYN_TYPE = ?";
            $results2 = $this->objMysql->_query ($sql2, [$proUid, "xmlform"]);

            foreach ($results2 as $result) {
                $dynFields = $dynHandler->getFields ();
                foreach ($dynFields as $field) {
                    $sType = \Step::getAttribute ($field, 'type');
                    if ( $sType == 'grid' )
                    {
                        $sxmlgrid = \Step::getAttribute ($field, 'xmlgrid');
                        $aGridInfo = explode ("/", $sxmlgrid);
                        if ( $aGridInfo[0] == $proUid && $aGridInfo[1] == $dynUid )
                        {
                            $formsDepend[] = $dataForms["DYN_TITLE"];
                        }
                    }
                }
            }
            if ( !empty ($formsDepend) )
            {
                $message = "You can not delete the grid '$dynUid', because it is in the ";
                $message .= (count ($formsDepend) == 1) ? 'form' : 'forms';
                $message .= ': ' . implode (', ', $formsDepend);
                return $message;
            }
        }
        else
        {
            $flagDepend = false;
            $stepsDepends = \Step::verifyDynaformAssigStep ($dynUid, $proUid);
            $messageSteps = '(0) Depends in steps';
            if ( !empty ($stepsDepends) )
            {
                $flagDepend = true;
                $countSteps = count ($stepsDepends);
                $messTemp = '';
                foreach ($stepsDepends as $value) {
                    $messTemp .= ", the task '" . $value['CON_VALUE'] . "' position " . $value['STEP_POSITION'];
                }
                $messageSteps = "($countSteps) Depends in steps in" . $messTemp;
            }
            $stepSupervisorsDepends = \StepSupervisor::verifyDynaformAssigStepSupervisor ($dynUid, $proUid);
            $messageStepsSupervisors = '(0) Depends in steps supervisor';
            if ( !empty ($stepSupervisorsDepends) )
            {
                $flagDepend = true;
                $countSteps = count ($stepSupervisorsDepends);
                $messageStepsSupervisors = "($countSteps) Depends in steps supervisor";
            }
            $objectPermissionDepends = \ObjectPermission::verifyDynaformAssigObjectPermission ($dynUid, $proUid);
            $messageObjectPermission = '(0) Depends in permissions';
            if ( !empty ($objectPermissionDepends) )
            {
                $flagDepend = true;
                $countSteps = count ($objectPermissionDepends);
                $messageObjectPermission = "($countSteps) Depends in permissions";
            }
            $caseTrackerDepends = \CaseTrackerObject::verifyDynaformAssigCaseTracker ($dynUid, $proUid);
            $messageCaseTracker = '(0) Depends in case traker';
            if ( !empty ($caseTrackerDepends) )
            {
                $flagDepend = true;
                $countSteps = count ($caseTrackerDepends);
                $messageCaseTracker = "($countSteps) Depends in case traker";
            }
            $dynaformDepends = \Dynaform::verifyDynaformAssignDynaform ($dynUid, $proUid);
            $messageDynaform = '(0) Depends in case traker';
            if ( !empty ($dynaformDepends) )
            {
                $flagDepend = true;
                $countSteps = count ($dynaformDepends);
                $messageDynaform = "($countSteps) Depends in dynaform";
            }
            if ( $flagDepend )
            {
                $message = "You can not delete the dynaform '$dynUid', because it has the following dependencies: \n\n";
                $message .= $messageSteps . ".\n" . $messageStepsSupervisors . ".\n";
                $message .= $messageObjectPermission . ".\n" . $messageCaseTracker . "\n";
                $message .= $messageDynaform;
                return $message;
            }
            return '';
        }
    }

    /**
     * Verify if a DynaForm has relation with a Step Supervisor
     *
     * @param string $dynaFormUid Unique id of DynaForm
     *
     * return bool Return true if a DynaForm has relation with a Step Supervisor, false otherwise
     */
    public function dynaFormRelationStepSupervisor ($dynaFormUid)
    {
        try {
            $stepSupervisor = new \StepSupervisor();
            $arrayData = $stepSupervisor->loadInfo ($dynaFormUid);
            if ( is_array ($arrayData) )
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
     * Throw the exception "The DynaForm doesn't exist"
     *
     * @param string $dynaFormUid           Unique id of DynaForm
     * @param string $fieldNameForException Field name for the exception
     *
     * @return void
     */
    private function throwExceptionDynaFormDoesNotExist ($dynaFormUid, $fieldNameForException)
    {
        throw new \Exception (
        'ID_DYNAFORM_DOES_NOT_EXIST'
        );
    }

    /**
     * Verify if doesn't exists the DynaForm in table DYNAFORM
     *
     * @param string $dynaFormUid           Unique id of DynaForm
     * @param string $processUid            Unique id of Process
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if doesn't exists the DynaForm in table DYNAFORM
     */
    public function throwExceptionIfNotExistsDynaForm ($dynaFormUid, $processUid)
    {
        try {
            $sql = "SELECT DYN_UID FROM workflow.form WHERE DYN_UID = ? ";
            $arrWhere = array($dynaFormUid);

            if ( $processUid != "" )
            {
                $sql .= " AND PRO_UID = ?";
                $arrWhere[] = $processUid;
            }

            $results = $this->objMysql->_query ($sql, $arrWhere);

            if ( isset ($results[0]) && !empty ($results[0]) )
            {
                $this->throwExceptionDynaFormDoesNotExist ($dynaFormUid);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a DynaForm
     *
     * @param string $processUid            Unique id of Process
     * @param string $dynaFormTitle         Title
     * @param string $fieldNameForException Field name for the exception
     * @param string $dynaFormUidExclude    Unique id of DynaForm to exclude
     *
     * return void Throw exception if exists the title of a DynaForm
     */
    public function throwExceptionIfExistsTitle ($processUid, $dynaFormTitle, $dynaFormUidExclude = "")
    {
        try {
            if ( $this->existsTitle ($processUid, $dynaFormTitle) )
            {
                throw new \Exception ("ID_DYNAFORM_TITLE_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function retrieveByPK ($pk)
    {
        $results = $this->objMysql->_select ("workflow.form", [], ["DYN_UID" => $pk]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        return $results;
    }

    /**
     * Get DynaForm record
     *
     * @param string $dynaFormUid                   Unique id of DynaForm
     * @param array  $arrayVariableNameForException Variable name for exception
     * @param bool   $throwException Flag to throw the exception if the main parameters are invalid or do not exist
     *                               (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array Returns an array with DynaForm record, ThrowTheException/FALSE otherwise
     */
    public function getDynaFormRecordByPk ($dynaFormUid, array $arrayVariableNameForException, $throwException = true)
    {
        try {
            $obj = $this->retrieveByPK ($dynaFormUid);
            if ( $obj === FALSE )
            {
                if ( $throwException )
                {
                    $this->throwExceptionDynaFormDoesNotExist (
                            $dynaFormUid
                    );
                }
                else
                {
                    return false;
                }
            }
            //Return
            return $obj;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create DynaForm for a Process
     *
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the new DynaForm created
     */
    public function create ($processUid, $arrayData)
    {
        try {
            unset ($arrayData["DYN_UID"]);
            unset ($arrayData["COPY_IMPORT"]);
            unset ($arrayData["PMTABLE"]);
            //Verify data
            (new Process())->throwExceptionIfNotExistsProcess ($processUid);
            $this->throwExceptionIfExistsTitle ($processUid, $arrayData["DYN_TITLE"]);
            //Create

            $dynaForm = new \Dynaform();
            $arrayData["PRO_UID"] = $processUid;
            $dynaFormUid = $dynaForm->create ($arrayData);
            //Return
            unset ($arrayData["PRO_UID"]);

            $arrayData = array_merge (array("DYN_UID" => $dynaFormUid), $arrayData);


            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update DynaForm
     *
     * @param string $dynaFormUid Unique id of DynaForm
     * @param array  $arrayData   Data
     *
     * return array Return data of the DynaForm updated
     */
    public function update ($dynaFormUid, $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsDynaForm ($dynaFormUid, "");
            //Load DynaForm

            $dynaForm = new \Dynaform();
            $arrayDynaFormData = $dynaForm->Load ($dynaFormUid);

            $processUid = $arrayDynaFormData["PRO_UID"];

            //Verify data
            if ( isset ($arrayData["DYN_TITLE"]) )
            {
                $this->throwExceptionIfExistsTitle ($processUid, $arrayData["DYN_TITLE"], $dynaFormUid);
            }

            //Update
            $arrayData["DYN_UID"] = $dynaFormUid;
            $dynaForm->update ($arrayData);

            //Return
            unset ($arrayData["DYN_UID"]);

            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete DynaForm
     *
     * @param string $dynaFormUid Unique id of DynaForm
     *
     * return void
     */
    public function delete ($dynaFormUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsDynaForm ($dynaFormUid, "");

            //Load DynaForm
            $dynaForm = new \Dynaform();
            $arrayDynaFormData = $dynaForm->Load ($dynaFormUid);

            $processUid = $arrayDynaFormData["PRO_UID"];
            //Verify dependences dynaforms

            $resultDepends = $this->dynaFormDepends ($dynaFormUid, $processUid);

            if ( $resultDepends != "" )
            {
                throw new \Exception ($resultDepends);
            }

            //Delete
            //In table DYNAFORM
            $result = $dynaForm->remove ($dynaFormUid);
            //In table STEP

            $step = new \Step();
            $step->removeStep ("DYNAFORM", $dynaFormUid);

            //In table OBJECT_PERMISSION
            $objPermission = new \ObjectPermission();
            $objPermission->removeByObject ("DYNAFORM", $dynaFormUid);

            //In table STEP_SUPERVISOR
            $stepSupervisor = new \StepSupervisor();
            $stepSupervisor->removeByObject ("DYNAFORM", $dynaFormUid);

            //In table CASE_TRACKER_OBJECT
            $caseTrackerObject = new \CaseTrackerObject();
            $caseTrackerObject->removeByObject ("DYNAFORM", $dynaFormUid);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Copy/Import a DynaForm
     *
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the new DynaForm created
     */
    public function copyImport ($processUid, $arrayData)
    {
        try {
            unset ($arrayData["DYN_UID"]);
            unset ($arrayData["PMTABLE"]);
            //Verify data
            (new Process())->throwExceptionIfNotExistsProcess ($processUid);

            if ( !isset ($arrayData["COPY_IMPORT"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            if ( !isset ($arrayData["COPY_IMPORT"]["PRJ_UID"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["COPY_IMPORT"]["PRJ_UID"] = trim ($arrayData["COPY_IMPORT"]["PRJ_UID"]);

            if ( $arrayData["COPY_IMPORT"]["PRJ_UID"] == "" )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            if ( !isset ($arrayData["COPY_IMPORT"]["DYN_UID"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED");
            }

            $arrayData["COPY_IMPORT"]["DYN_UID"] = trim ($arrayData["COPY_IMPORT"]["DYN_UID"]);

            if ( $arrayData["COPY_IMPORT"]["DYN_UID"] == "" )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }

            $this->throwExceptionIfExistsTitle ($processUid, $arrayData["DYN_TITLE"]);

            //Copy/Import Uids
            $processUidCopyImport = $arrayData["COPY_IMPORT"]["PRJ_UID"];
            $dynaFormUidCopyImport = $arrayData["COPY_IMPORT"]["DYN_UID"];

            //Verify data
            (new Process())->throwExceptionIfNotExistsProcess ($processUidCopyImport);

            $this->throwExceptionIfNotExistsDynaForm ($dynaFormUidCopyImport, $processUidCopyImport);

            //Create
            $arrayData = $this->create ($processUid, $arrayData);


            //Return
            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for DynaForm
     *
     * return object
     */
    public function getDynaFormCriteria ()
    {
        try {
            $criteria = "SELECT DYN_UID, DYN_TITLE, DYN_DESCRIPTION, DYN_TYPE, DYN_CONTENT, DYN_VERSION, DYN_UPDATE_DATE FROM workflow.form ";

            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a DynaForm from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data DynaForm
     */
    public function getDynaFormDataFromRecord ($record)
    {
        try {
            if ( $record["DYN_VERSION"] == 0 )
            {
                $record["DYN_VERSION"] = 1;
            }
            $record["DYN_CONTENT"] = preg_replace ("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", $record["DYN_CONTENT"]);
            return array(
                $this->getFieldNameByFormatFieldName ("DYN_UID") => $record["DYN_UID"],
                $this->getFieldNameByFormatFieldName ("DYN_TITLE") => $record["DYN_TITLE"],
                $this->getFieldNameByFormatFieldName ("DYN_DESCRIPTION") => $record["DYN_DESCRIPTION"] . "",
                $this->getFieldNameByFormatFieldName ("DYN_TYPE") => $record["DYN_TYPE"] . "",
                $this->getFieldNameByFormatFieldName ("DYN_CONTENT") => $record["DYN_CONTENT"] . "",
                $this->getFieldNameByFormatFieldName ("DYN_VERSION") => (int) ($record["DYN_VERSION"]),
                $this->getFieldNameByFormatFieldName ("DYN_UPDATE_DATE") => $record["DYN_UPDATE_DATE"]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a DynaForm
     *
     * @param string $dynaFormUid Unique id of DynaForm
     *
     * return array Return an array with data of a DynaForm
     */
    public function getDynaForm ($dynaFormUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsDynaForm ($dynaFormUid, "");

            //Get data
            $criteria = $this->getDynaFormCriteria ();
            $sql .= "WHERE DYN_UID = ?";
            $results = $this->objMysql->_query ($sql, [$dynaFormUid]);


            //Return
            return $this->getDynaFormDataFromRecord ($results[0]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a DynaForm
     *
     * @param string $projectUid Unique id of Project
     * @param string $dynaFormUid Unique id of DynaForm
     *
     * return array Return an array with data of a DynaForm
     */
    public function getDynaFormFields ($projectUid, $dynaFormUid)
    {
        try {
            $arrayVariables = array();
            $arrayVariablesDef = array();
            //Verify data
            $this->proUid ($projectUid);

            $this->throwExceptionIfNotExistsDynaForm ($dynaFormUid, "");

            $sql = "SELECT f.*,  IF(rf.field_id IS NOT NULL, 1, 0) as required_field FROM workflow.fields f
                    INNER JOIN workflow.step_fields sf ON sf.field_id = f.field_id
                    LEFT JOIN workflow.required_fields rf ON rf.field_id = f.field_id AND rf.step_id = sf.step_id
                    WHERE sf.step_id = ? ";

            $results = $this->objMysql->_query ($sql, [$dynaFormUid]);

            foreach ($results as $key => $value) {
                $valueType = (isset ($value[0]["valueType"])) ? $value[0]["valueType"] : null;
                $maxLength = (isset ($value[0]["maxlength"])) ? $value[0]["maxlength"] : null;
                $label = (isset ($value[0]["label"])) ? $value[0]["label"] : null;
                $defaultValue = (isset ($value[0]["default_value"])) ? $value[0]["default_value"] : null;
                $required = (isset ($value[0]["required_field"])) ? $value[0]["required_field"] : null;

                if ( isset ($value[0]["variable"]) )
                {
                    $variable = $value[0]["variable"];
                    $criteria = new \Criteria ("workflow");
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_NAME);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_FIELD_TYPE);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_FIELD_SIZE);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_LABEL);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_DBCONNECTION);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_SQL);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_NULL);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_DEFAULT);
                    $criteria->addSelectColumn (\ProcessVariablesPeer::VAR_ACCEPTED_VALUES);
                    $criteria->add (\ProcessVariablesPeer::PRJ_UID, $projectUid, \Criteria::EQUAL);
                    $criteria->add (\ProcessVariablesPeer::VAR_NAME, $variable, \Criteria::EQUAL);
                    $rsCriteria = \ProcessVariablesPeer::doSelectRS ($criteria);
                    $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);
                    $rsCriteria->next ();
                    while ($aRow = $rsCriteria->getRow ()) {
                        $valueTypeMerged = ($valueType == null && $valueType == '') ? $aRow['VAR_FIELD_TYPE'] : $valueType;
                        $maxLengthMerged = ($maxLength == null && $maxLength == '') ? (int) $aRow['VAR_FIELD_SIZE'] : $maxLength;
                        $labelMerged = ($label == null && $label == '') ? $aRow['VAR_LABEL'] : $label;
                        $defaultValueMerged = ($defaultValue == null && $defaultValue == '') ? $aRow['VAR_DEFAULT'] : $defaultValue;
                        $requiredMerged = ($required == null && $required == '') ? ($aRow['VAR_NULL'] == 1) ? false : true : $required;
                        $dbConnectionMerged = ($dbConnection == null && $dbConnection == '') ? $aRow['VAR_DBCONNECTION'] : $dbConnection;
                        $sqlMerged = ($sql == null && $sql == '') ? $aRow['VAR_SQL'] : $sql;
                        $optionsMerged = ($options == null && $options == '') ? $aRow['VAR_ACCEPTED_VALUES'] : $options;
                        $aVariables = array('valueType' => $valueTypeMerged,
                            'maxLength' => $maxLengthMerged,
                            'label' => $labelMerged,
                            'defaultValue' => $defaultValueMerged,
                            'required' => $requiredMerged,
                            'dbConnection' => $dbConnectionMerged,
                            'sql' => $sqlMerged,
                            'options' => $optionsMerged);
                        //fields properties
                        if ( isset ($value[0]["pickType"]) )
                        {
                            $aVariables = array_merge (array('pickType' => $value[0]["pickType"]), $aVariables);
                        }
                        if ( isset ($value[0]["placeHolder"]) )
                        {
                            $aVariables = array_merge (array('placeHolder' => $value[0]["placeHolder"]), $aVariables);
                        }
                        if ( isset ($value[0]["dependentsField"]) )
                        {
                            $aVariables = array_merge (array('dependentsField' => $value[0]["dependentsField"]), $aVariables);
                        }
                        if ( isset ($value[0]["hint"]) )
                        {
                            $aVariables = array_merge (array('hint' => $value[0]["hint"]), $aVariables);
                        }
                        if ( isset ($value[0]["readonly"]) )
                        {
                            $aVariables = array_merge (array('readonly' => $value[0]["readonly"]), $aVariables);
                        }
                        if ( isset ($value[0]["colSpan"]) )
                        {
                            $aVariables = array_merge (array('colSpan' => $value[0]["colSpan"]), $aVariables);
                        }
                        if ( isset ($value[0]["type"]) )
                        {
                            $aVariables = array_merge (array('type' => $value[0]["type"]), $aVariables);
                        }
                        if ( isset ($value[0]["name"]) )
                        {
                            $aVariables = array_merge (array('name' => $value[0]["name"]), $aVariables);
                        }
                        $aVariables = array_merge (array('variable' => $variable), $aVariables);
                        $arrayVariables[] = $aVariables;
                        $rsCriteria->next ();
                    }
                }
                else
                {
                    $arrayVariablesDef[] = $value[0];
                }
            }
            $arrayVariables = array_merge ($arrayVariables, $arrayVariablesDef);
            //Return
            return $arrayVariables;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
