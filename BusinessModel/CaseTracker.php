<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of CaseTracker
 *
 * @author michael.hampton
 */
class CaseTracker
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Update Case Tracker data of a Process
     *
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the Case Tracker updated
     */
    public function update ($processUid, $arrayData)
    {
        try {
            $arrayDataIni = $arrayData;
            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($processUid, "prj_uid");

            //Update
            $caseTracker = new \CaseTracker();
            $arrayData = array("PRO_UID" => $processUid);

            if ( isset ($arrayDataIni["map_type"]) )
            {
                $arrayData["CT_MAP_TYPE"] = $arrayDataIni["map_type"];
            }
            if ( isset ($arrayDataIni["routing_history"]) )
            {
                $arrayData["CT_DERIVATION_HISTORY"] = (int) ($arrayDataIni["routing_history"]);
            }
            if ( isset ($arrayDataIni["message_history"]) )
            {
                $arrayData["CT_MESSAGE_HISTORY"] = (int) ($arrayDataIni["message_history"]);
            }

            $caseTracker->update ($arrayData);
            $arrayData = $arrayDataIni;
            //Return
            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Case Tracker data of a Process
     *
     * @param string $processUid Unique id of Process
     *
     * return array Return an array with data of Case Tracker of a Process
     */
    public function getCaseTracker ($processUid)
    {
        try {
            $arrayCaseTracker = array();
            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($processUid);
            //Get data
            $results = $this->objMysql->_select ("case_tracker", [], ["PRO_UID" => $processUid]);



            if ( isset ($results[0]) && !empty ($results[0]) )
            {
                $row = $results[0];
                $arrayCaseTracker = $row;
            }
            else
            {
                $caseTracker = new \CaseTracker();
                $arrayCaseTracker = array(
                    "PRO_UID" => $processUid,
                    "CT_MAP_TYPE" => "PROCESSMAP",
                    "CT_DERIVATION_HISTORY" => 1,
                    "CT_MESSAGE_HISTORY" => 1
                );
                $caseTracker->create ($arrayCaseTracker);
            }
            return array(
                "map_type" => $arrayCaseTracker["CT_MAP_TYPE"],
                "routing_history" => (int) ($arrayCaseTracker["CT_DERIVATION_HISTORY"]),
                "message_history" => (int) ($arrayCaseTracker["CT_MESSAGE_HISTORY"])
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get available Case Tracker Objects of a Process
     *
     * @param string $processUid Unique id of Process
     *
     * return array Return an array with the Case Tracker Objects available of a Process
     */
    public function getAvailableCaseTrackerObjects ($processUid)
    {
        try {
            $arrayAvailableCaseTrackerObject = array();
            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($processUid);
            //Get Uids
            $arrayDynaFormUid = array();
            $arrayInputDocumentUid = array();
            $arrayOutputDocumentUid = array();

            $results = $this->objMysql->_select ("case_tracker_objects", [], ["PRO_UID" => $processUid]);

            foreach ($results as $row) {
                switch ($row["CTO_TYPE_OBJ"]) {
                    case "DYNAFORM":
                        $arrayDynaFormUid[] = $row["CTO_UID_OBJ"];
                        break;
                    case "INPUT_DOCUMENT":
                        $arrayInputDocumentUid[] = $row["CTO_UID_OBJ"];
                        break;
                    case "OUTPUT_DOCUMENT":
                        $arrayOutputDocumentUid[] = $row["CTO_UID_OBJ"];
                        break;
                }
            }
            //Array DB
            $arrayCaseTrackerObject = array();

            //DynaForms
            $sql = "SELECT t.step_name FROM workflow.`step` s
                    INNER JOIN workflow.task t ON t.TAS_UID = s.`TAS_UID`
                    INNER JOIN workflow.status_mapping sm ON sm.TAS_UID = t.TAS_UID
                    WHERE `STEP_TYPE_OBJ` = 'DYNAFORM'
                    AND sm.workflow_id = ?
                    AND s.`STEP_UID_OBJ` NOT IN(".implode(",", $arrayDynaFormUid).")
                    GROUP BY s.TAS_UID";

            $results2 = $this->objMysql->_query ($sql, [$processUid]);


            foreach ($results2 as $row) {
                $arrayCaseTrackerObject[] = array(
                    "obj_uid" => $row["STEP_UID_OBJ"],
                    "obj_title" => $row["step_name"],
                    "obj_type" => "DYNAFORM"
                );
            }

            //InputDocuments
            $results3 = $this->objMysql->_query ("SELECT d.`id`, `name`, `description` FROM `documents` d
                                                INNER JOIN workflow.step s ON s.STEP_TYPE_OBJ = 'INPUT_DOCUMENT'
                                                INNER JOIN workflow.status_mapping m on m.TAS_UID = s.TAS_UID
                                                WHERE m.workflow_id = ?
                                                AND d.`id` NOT IN(" . implode (",", $arrayInputDocumentUid) . ")", [$processUid]);

            foreach ($results3 as $row) {
                if ( $row["name"] . "" == "" )
                {
                    //There is no transaltion for this Document name, try to get/regenerate the label
                    $inputDocument = new \InputDocument();
                    $inputDocumentObj = $inputDocument->load ($row['id']);
                    $row["INP_DOC_TITLE"] = $inputDocumentObj['name'];
                }
                $arrayCaseTrackerObject[] = array(
                    "obj_uid" => $row["id"],
                    "obj_title" => $row["name"],
                    "obj_description" => $row["description"],
                    "obj_type" => "INPUT_DOCUMENT"
                );
            }

            //OutputDocuments
            $results4 = $this->objMysql->_query ("SELECT d.`id`, `OUT_DOC_TITLE`, `OUT_DOC_DESCRIPTION` FROM workflow.output_document d
                    INNER JOIN workflow.step s ON s.STEP_TYPE_OBJ = 'OUTPUT_DOCUMENT'
                    INNER JOIN workflow.status_mapping m on m.TAS_UID = s.TAS_UID
                    WHERE m.workflow_id = ?
                    AND d.`id` NOT IN(" . implode (",", $arrayOutputDocumentUid) . ")", [$processUid]);


            foreach ($results4 as $row) {
                $arrayCaseTrackerObject[] = array(
                    "obj_uid" => $row["id"],
                    "obj_title" => $row["OUT_DOC_TITLE"],
                    "obj_description" => $row["OUT_DOC_DESCRIPTION"],
                    "obj_type" => "OUTPUT_DOCUMENT"
                );
            }
            $arrayCaseTrackerObject = $this->sort (
                    $arrayCaseTrackerObject, array("obj_type", "obj_title"), SORT_ASC
            );

            return $arrayCaseTrackerObject;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function sort ($data, $columns, $direction = SORT_ASC)
    {
        if ( empty ($data) )
        {
            return $data;
        }

        $composedData = array();

        if ( is_array ($direction) )
        {
            if ( count ($direction) !== count ($columns) )
            {
                echo "PHP Warning:  ProcessMaker\\Util\\ArrayUtil::sort(): Argument (array)#2 and Argument (array)#3 lengths must be equals.";
                return false;
            }
        }

        foreach ($data as $row) {
            $j = 0;
            foreach ($columns as $i => $col) {
                if ( !isset ($row[$col]) )
                {
                    echo "PHP Warning:  ProcessMaker\\Util\\ArrayUtil::sort(): Undefined key: $col, is set on Argument (array)#2, it must be set on Argument (array)#1";
                    return false;
                }
                $composedData[$j++][] = $row[$col];
                $composedData[$j++] = is_array ($direction) ? $direction[$i] : $direction;
            }
        }

        $composedData[] = & $data;

        if ( PHP_VERSION_ID < 50400 )
        {
            switch (count ($columns)) {
                case 1: array_multisort ($composedData[0], $composedData[1], $composedData[2]);
                    break;
                case 2: array_multisort ($composedData[0], $composedData[1], $composedData[2], $composedData[3], $composedData[4]);
                    break;
                case 3: array_multisort ($composedData[0], $composedData[1], $composedData[2], $composedData[3], $composedData[4], $composedData[5], $composedData[6]);
                    break;
                case 4: array_multisort ($composedData[0], $composedData[1], $composedData[2], $composedData[3], $composedData[4], $composedData[5], $composedData[6], $composedData[7], $composedData[8]);
                    break;
                case 5: array_multisort ($composedData[0], $composedData[1], $composedData[2], $composedData[3], $composedData[4], $composedData[5], $composedData[6], $composedData[7], $composedData[8], $composedData[9], $composedData[10]);
                    break;
                default:
                    return false;
            }
        }
        else
        {
            call_user_func_array ("array_multisort", $composedData);
        }

        return $data;
    }

    /**
     * Get all Case Tracker Objects of a Process
     *
     * @param string $processUid Unique id of Process
     *
     * return array Return an array with all Case Tracker Objects of a Process
     */
    public function getCaseTrackerObjects ($processUid)
    {
        try {
            $arrayCaseTrackerObject = array();
            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($processUid, "prj_uid");
            $dynaform = new \Dynaform();
            $inputDocument = new \InputDocument();
            $outputDocument = new \OutputDocument();
            $arrayCaseTrackerObject = array();


            $results = $this->objMysql->_select ("case_tracker_objects", [], ["PRO_UID" => $processUid]);


            foreach ($results as $row) {
                $titleObj = "";
                $descriptionObj = "";

                switch ($row["CTO_TYPE_OBJ"]) {
                    case "DYNAFORM":
                        $arrayData = $dynaform->load ($row["CTO_UID_OBJ"]);
                        $titleObj = $arrayData["DYN_TITLE"];
                        $descriptionObj = $arrayData["DYN_DESCRIPTION"];
                        break;
                    case "INPUT_DOCUMENT":
                        $arrayData = $inputDocument->getByUid ($row["CTO_UID_OBJ"]);
                        $titleObj = $arrayData["name"];
                        $descriptionObj = $arrayData["description"];
                        break;
                    case "OUTPUT_DOCUMENT":
                        $arrayData = $outputDocument->getByUid ($row["CTO_UID_OBJ"]);
                        $titleObj = $arrayData["OUT_DOC_TITLE"];
                        $descriptionObj = $arrayData["OUT_DOC_DESCRIPTION"];
                        break;
                }

                $arrayCaseTrackerObject[] = array(
                    "cto_uid" => $row["CTO_UID"],
                    "cto_type_obj" => $row["CTO_TYPE_OBJ"],
                    "cto_uid_obj" => $row["CTO_UID_OBJ"],
                    "cto_condition" => $row["CTO_CONDITION"],
                    "cto_position" => (int) ($row["CTO_POSITION"]),
                    "obj_title" => $titleObj,
                    "obj_description" => $descriptionObj
                );
            }

            $arrayCaseTrackerObject = $this->sort ($arrayCaseTrackerObject, array("cto_position"), SORT_ASC);

            return $arrayCaseTrackerObject;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
