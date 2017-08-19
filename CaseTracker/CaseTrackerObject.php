<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CaseTrackerObject
 *
 * @author michael.hampton
 */
class CaseTrackerObject extends BaseCaseTrackerObject
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function load ($Uid)
    {
        try {
            $oRow = $this->retrieveByPK ($Uid);

            if ( !is_null ($oRow) )
            {
                return $oRow;
            }
            else
            {
                throw (new Exception ("The row '$Uid' in table CaseTrackerObject doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function create ($aData)
    {
        try {

            $oCaseTrackerObject = new CaseTrackerObject();
            $oCaseTrackerObject->loadObject ($aData);

            if ( $oCaseTrackerObject->validate () )
            {
                $iResult = $oCaseTrackerObject->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oCaseTrackerObject->getValidationFailures ();

                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function update ($aData)
    {
        try {
            $oCaseTrackerObject = $this->retrieveByPK ($aData['CTO_UID']);

            if ( !is_null ($oCaseTrackerObject) )
            {
                $oCaseTrackerObject->loadObject ($aData);

                if ( $oCaseTrackerObject->validate () )
                {
                    $iResult = $oCaseTrackerObject->save ();
                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oCaseTrackerObject->getValidationFailures ();

                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function remove ($sCTOUID)
    {
        try {
            $oCaseTobj = $this->retrieveByPK ($sCTOUID);

            if ( is_object ($oCaseTobj) && get_class ($oCaseTobj) == 'CaseTrackerObject' )
            {
                $iResult = $oCaseTobj->delete ();
                return $iResult;
            }
            else
            {
                throw (new Exception ("The row '" . $sCTOUID . "' in table CaseTrackerObject doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function reorderPositions ($sProcessUID, $iPosition)
    {
        try {
            $sql = "SELECT * FROM case_tracker_objects WHERE PRO_UID = ? AND CTO_POSITION > ?";

            $results = $this->objMysql->_query ($sql . [$sProcessUID, $iPosition]);

            foreach ($results as $aRow) {
                $this->update (array('CTO_UID' => $aRow['CTO_UID'], 'PRO_UID' => $aRow['PRO_UID'], 'CTO_TYPE_OBJ' => $aRow['CTO_TYPE_OBJ'], 'CTO_UID_OBJ' => $aRow['CTO_UID_OBJ'], 'CTO_CONDITION' => $aRow['CTO_CONDITION'], 'CTO_POSITION' => $aRow['CTO_POSITION'] - 1
                ));
            }
        } catch (Exception $oException) {
            throw $oException;
        }
    }

    public function caseTrackerObjectExists ($Uid)
    {
        try {
            $oObj = $this->retrieveByPk ($Uid);

            if ( is_object ($oObj) && get_class ($oObj) == 'CaseTrackerObject' )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function removeByObject ($sType, $sObjUid)
    {
        try {
            $results = $this->objMysql->_select ("case_tracker_objects", [], ["CTO_TYPE_OBJ" => $sType, "CTO_UID_OBJ" => $sObjUid]);

            foreach ($results as $result) {
                
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * verify if a dynaform is assigned some steps
     *
     * @param string $proUid the uid of the process
     * @param string $dynUid the uid of the dynaform
     *
     * @return array
     */
    public function verifyDynaformAssigCaseTracker ($dynUid, $proUid)
    {
        $res = array();
        $results = $this->objMysql->_select ("case_tracker_objects", [], ["PRO_UID" => $proUid, "CTO_UID_OBJ" => $dynUid, "CTO_TYPE_OBJ" => "DYNAFORM"]);

        foreach ($results as $row) {
            $res[] = $row;
        }
        return $res;
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("case_tracker_objects", [], ["CTO_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return null;
        }

        return new CaseTrackerObject();
    }

}
