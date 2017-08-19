<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of CaseTrackerObject
 *
 * @author michael.hampton
 */
class CaseTrackerObject
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Verify if exists the record in table CASE_TRACKER_OBJECT
     *
     * @param string $processUid Unique id of Process
     * @param string $type       Type of Step (DYNAFORM, INPUT_DOCUMENT, OUTPUT_DOCUMENT)
     * @param string $objectUid  Unique id of Object
     * @param int    $position   Position
     * @param string $caseTrackerObjectUidExclude Unique id of Case Tracker Object to exclude
     *
     * return bool Return true if exists the record in table CASE_TRACKER_OBJECT, false otherwise
     */
    public function existsRecord ($processUid, $type, $objectUid, $position = 0, $caseTrackerObjectUidExclude = "")
    {
        try {
            $sql = "SELECT CTO_UID FROM case_tracker_objects WHERE PRO_UID = ?";
            $arrParameters = [];

            if ( $caseTrackerObjectUidExclude != "" )
            {
                $sql .= " AND CTO_UID != ?";
                $arrParameters[] = $caseTrackerObjectUidExclude;
            }

            if ( $type != "" )
            {
                $sql .= " AND CTO_TYPE_OBJ = ?";
                $arrParameters[] = $type;
            }

            if ( $objectUid != "" )
            {
                $sql .= " AND CTO_UID_OBJ = ?";
                $arrParameters[] = $objectUid;
            }

            if ( $position > 0 )
            {
                $sql .= " AND CTO_POSITION = ?";
                $arrParameters[] = $position;
            }

            $results = $this->objMysql->_query ($sql, $arrParameters);

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

    public function deleteAllObjects ($processUid)
    {
        $objCaseTracker = new \CaseTrackerObject();
        $objCaseTracker->setProUid ($processUid);
        $objCaseTracker->delete ();
    }

    /**
     * Create Case Tracker Object for a Process
     *
     * @param string $processUid Unique id of Process
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Case Tracker Object created
     */
    public function create ($processUid, $arrayData)
    {
        try {
            unset ($arrayData["CTO_UID"]);
            //Verify data
            $process = new Process();
            $process->throwExceptionIfNotExistsProcess ($processUid);

            if ( !isset ($arrayData["CTO_TYPE_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED " . strtolower ("CTO_TYPE_OBJ"));
            }

            if ( !isset ($arrayData["CTO_UID_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED " . strtolower ("CTO_UID_OBJ"));
            }

            $step = new \BusinessModel\Step();

            $msg = $step->existsObjectUid ($arrayData["CTO_TYPE_OBJ"], $arrayData["CTO_UID_OBJ"]);

            if ( $msg != "" )
            {
                throw new \Exception ($msg);
            }

            if ( $this->existsRecord ($processUid, $arrayData["CTO_TYPE_OBJ"], $arrayData["CTO_UID_OBJ"]) )
            {
                throw new \Exception ("ID_RECORD_EXISTS_IN_TABLE " . $processUid . ", " . $arrayData["CTO_TYPE_OBJ"] . ", " . $arrayData["CTO_UID_OBJ"], "CASE_TRACKER_OBJECT");
            }

            $ctoPosition = $arrayData["CTO_POSITION"];

            $sql = "SELECT COUNT(*) + 1 AS count FROM case_tracker_objects WHERE PRO_UID = ?";
            $results = $this->objMysql->_query ($sql, [$processUid]);

            $arrayData["CTO_POSITION"] = $results[0]['count'];

            //Create
            $caseTrackerObject = new \CaseTrackerObject();

            $arrayData["PRO_UID"] = $processUid;
            $caseTrackerObjectUid = $caseTrackerObject->create ($arrayData);
            $arrayData["CTO_POSITION"] = $ctoPosition;
            $arrayData["CTO_UID"] = $caseTrackerObjectUid;
            //$this->update ($caseTrackerObjectUid, $arrayData);
            //Return
            unset ($arrayData["PRO_UID"]);
            unset ($arrayData["cto_uid"]);
            return array_merge (array("cto_uid" => $caseTrackerObjectUid), $arrayData);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCurrentValues ($processUid)
    {
        $results = $this->objMysql->_query ("SELECT `CTO_TYPE_OBJ`, GROUP_CONCAT(`CTO_UID_OBJ`) AS ids FROM `case_tracker_objects` WHERE `PRO_UID` = ? GROUP BY  `CTO_TYPE_OBJ`", [$processUid]);

        $arrObjects = [];
        
        if(!isset($results[0]) || empty($results[0])) {
            return false;
        }
        
        foreach ($results as $result) {
            $arrObjects[$result['CTO_TYPE_OBJ']] = explode(",", $result['ids']);
        }
        
        return $arrObjects;
    }

    /**
     * Update Case Tracker Object
     *
     * @param string $caseTrackerObjectUid Unique id of Case Tracker Object
     * @param array  $arrayData Data
     *
     * return array Return data of the Case Tracker Object updated
     */
    public function update ($caseTrackerObjectUid, $arrayData)
    {
        try {
            $caseTrackerObject = new \CaseTrackerObject();
            $arrayCaseTrackerObjectData = $caseTrackerObject->load ($caseTrackerObjectUid);
            //Uids
            $processUid = $arrayCaseTrackerObjectData->getProUid ();
            //Verify data
            if ( !$caseTrackerObject->caseTrackerObjectExists ($caseTrackerObjectUid) )
            {
                throw new \Exception ("ID_CASE_TRACKER_OBJECT_DOES_NOT_EXIST");
            }

            if ( isset ($arrayData["CTO_TYPE_OBJ"]) && !isset ($arrayData["CTO_UID_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED " . strtolower ("CTO_UID_OBJ"));
            }

            if ( !isset ($arrayData["CTO_TYPE_OBJ"]) && isset ($arrayData["CTO_UID_OBJ"]) )
            {
                throw new \Exception ("ID_UNDEFINED_VALUE_IS_REQUIRED " . strtolower ("CTO_TYPE_OBJ"));
            }

            if ( isset ($arrayData["CTO_TYPE_OBJ"]) && isset ($arrayData["CTO_UID_OBJ"]) )
            {
                $step = new \BusinessModel\Step();
                $msg = $step->existsObjectUid ($arrayData["CTO_TYPE_OBJ"], $arrayData["CTO_UID_OBJ"]);

                if ( $msg != "" )
                {
                    throw new \Exception ($msg);
                }
                if ( $this->existsRecord ($processUid, $arrayData["CTO_TYPE_OBJ"], $arrayData["CTO_UID_OBJ"], 0, $caseTrackerObjectUid) )
                {
                    throw new \Exception ("ID_RECORD_EXISTS_IN_TABLE " . $processUid . ", " . $arrayData["CTO_TYPE_OBJ"] . ", " . $arrayData["CTO_UID_OBJ"], "CASE_TRACKER_OBJECT");
                }
            }
            //Flags
            $flagDataOject = (isset ($arrayData["CTO_TYPE_OBJ"]) && isset ($arrayData["CTO_UID_OBJ"])) ? 1 : 0;
            $flagDataCondition = (isset ($arrayData["CTO_CONDITION"])) ? 1 : 0;
            $flagDataPosition = (isset ($arrayData["CTO_POSITION"])) ? 1 : 0;

            //Update
            $tempPosition = (isset ($arrayData["CTO_POSITION"])) ? $arrayData["CTO_POSITION"] : $arrayCaseTrackerObjectData->getCtoPosition ();
            $arrayData["CTO_POSITION"] = $arrayCaseTrackerObjectData->getCtoPosition ();
            $arrayData["CTO_UID"] = $caseTrackerObjectUid;
            $arrayData['PRO_UID'] = $arrayCaseTrackerObjectData->getProUid ();
            $arrayData['CTO_TYPE_OBJ'] = $arrayCaseTrackerObjectData->getCtoTypeObj ();
            $arrayData['CTO_UID_OBJ'] = $arrayCaseTrackerObjectData->getCtoUidObj ();
            $caseTrackerObject->update ($arrayData);

            if ( $tempPosition != $arrayCaseTrackerObjectData->getCtoPosition () )
            {
                $this->moveCaseTrackerObject ($caseTrackerObjectUid, $arrayData['PRO_UID'], $tempPosition);
            }
            //Return
            unset ($arrayData["CTO_UID"]);
            if ( $flagDataOject == 0 )
            {
                unset ($arrayData["CTO_TYPE_OBJ"]);
                unset ($arrayData["CTO_UID_OBJ"]);
            }
            if ( $flagDataCondition == 0 )
            {
                unset ($arrayData["CTO_CONDITION"]);
            }
            if ( $flagDataPosition == 0 )
            {
                unset ($arrayData["CTO_POSITION"]);
            }
            unset ($arrayData["PRO_UID"]);
            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Case Tracker Object
     *
     * @param string $caseTrackerObjectUid Unique id of Case Tracker Object
     *
     * return void
     */
    public function delete ($caseTrackerObjectUid)
    {
        try {
            $caseTrackerObject = new \CaseTrackerObject();
            $arrayCaseTrackerObjectData = $caseTrackerObject->load ($caseTrackerObjectUid);
            //Uids
            $processUid = $arrayCaseTrackerObjectData["PRO_UID"];
            //Verify data
            if ( !$caseTrackerObject->caseTrackerObjectExists ($caseTrackerObjectUid) )
            {
                throw new \Exception ("ID_CASE_TRACKER_OBJECT_DOES_NOT_EXIST");
            }
            //Delete
            $caseTrackerObject->remove ($caseTrackerObjectUid);
            $caseTrackerObject->reorderPositions ($processUid, $arrayCaseTrackerObjectData["CTO_POSITION"]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Case Tracker Object
     *
     * @param string $caseTrackerObjectUid Unique id of Case Tracker Object
     *
     * return array Return an array with data of a Case Tracker Object
     */
    public function getCaseTrackerObject ($caseTrackerObjectUid)
    {
        try {
            //Verify data
            $caseTrackerObject = new \CaseTrackerObject();
            if ( !$caseTrackerObject->caseTrackerObjectExists ($caseTrackerObjectUid) )
            {
                throw new \Exception ("ID_CASE_TRACKER_OBJECT_DOES_NOT_EXIST");
            }
            //Get data
            $dynaform = new \Dynaform();
            $inputDocument = new \InputDocument();
            $outputDocument = new \OutputDocument();
            $criteria = new \Criteria ("workflow");
            $criteria->add (\CaseTrackerObjectPeer::CTO_UID, $caseTrackerObjectUid, \Criteria::EQUAL);
            $rsCriteria = \CaseTrackerObjectPeer::doSelectRS ($criteria);
            $rsCriteria->setFetchmode (\ResultSet::FETCHMODE_ASSOC);
            $rsCriteria->next ();
            $row = $rsCriteria->getRow ();
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
                    $titleObj = $arrayData["INP_DOC_TITLE"];
                    $descriptionObj = $arrayData["INP_DOC_DESCRIPTION"];
                    break;
                case "OUTPUT_DOCUMENT":
                    $arrayData = $outputDocument->getByUid ($row["CTO_UID_OBJ"]);
                    $titleObj = $arrayData["OUT_DOC_TITLE"];
                    $descriptionObj = $arrayData["OUT_DOC_DESCRIPTION"];
                    break;
            }
            return array(
                "cto_uid" => $row["CTO_UID"],
                "cto_type_obj" => $row["CTO_TYPE_OBJ"],
                "cto_uid_obj" => $row["CTO_UID_OBJ"],
                "cto_condition" => $row["CTO_CONDITION"],
                "cto_position" => (int) ($row["CTO_POSITION"]),
                "obj_title" => $titleObj,
                "obj_description" => $descriptionObj
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate Process Uid
     * @var string $cto_uid. Uid for Process
     * @var string $pro_uid. Uid for Task
     * @var string $cto_pos. Position for Step
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return void
     */
    public function moveCaseTrackerObject ($cto_uid, $pro_uid, $cto_pos)
    {
        $aCaseTrackerObject = (new CaseTracker())->getCaseTrackerObjects ($pro_uid);

        foreach ($aCaseTrackerObject as $dataCaseTracker) {
            if ( $dataCaseTracker['cto_uid'] == $cto_uid )
            {
                $prStepPos = (int) $dataCaseTracker['cto_position'];
            }
        }

        $seStepPos = $cto_pos;
        //Principal Step is up
        if ( $prStepPos == $seStepPos )
        {
            return true;
        }
        elseif ( $prStepPos < $seStepPos )
        {
            $modPos = 'UP';
            $newPos = $seStepPos;
            $iniPos = $prStepPos + 1;
            $finPos = $seStepPos;
        }
        else
        {
            $modPos = 'DOWN';
            $newPos = $seStepPos;
            $iniPos = $seStepPos;
            $finPos = $prStepPos - 1;
        }

        $range = range ($iniPos, $finPos);
        foreach ($aCaseTrackerObject as $dataCaseTracker) {
            if ( (in_array ($dataCaseTracker['cto_position'], $range)) && ($dataCaseTracker['cto_uid'] != $cto_uid) )
            {
                $caseTrackerObjectIds[] = $dataCaseTracker['cto_uid'];
                $caseTrackerObjectPos[] = $dataCaseTracker['cto_position'];
            }
        }
        foreach ($caseTrackerObjectIds as $key => $value) {
            if ( $modPos == 'UP' )
            {
                $tempPos = ((int) $caseTrackerObjectPos[$key]) - 1;
                $this->changePosCaseTrackerObject ($value, $tempPos);
            }
            else
            {
                $tempPos = ((int) $caseTrackerObjectPos[$key]) + 1;
                $this->changePosCaseTrackerObject ($value, $tempPos);
            }
        }
        $this->changePosCaseTrackerObject ($cto_uid, $newPos);
    }

    /**
     * Validate Process Uid
     * @var string $pro_uid. Uid for process
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return string
     */
    public function changePosCaseTrackerObject ($cto_uid, $pos)
    {
        $data = array(
            'CTO_UID' => $cto_uid,
            'CTO_POSITION' => $pos
        );
        $oCaseTrackerObject = new \CaseTrackerObject();
        $oCaseTrackerObject->update ($data);
    }

}
