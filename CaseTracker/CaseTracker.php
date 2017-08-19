<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CaseTracker
 *
 * @author michael.hampton
 */
class CaseTracker extends BaseCaseTracker
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function load ($sProcessUID)
    {
        try {
            $oRow = $this->retrieveByPK ($sProcessUID);
            if ( !is_null ($oRow) )
            {
                $aFields = $this->loadObject ($oRow);
                $this->setNew (false);
                return $aFields;
            }
            else
            {
                throw (new Exception ("The row '$sProcessUID' in table CASE_TRACKER doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function create ($aData)
    {
        try {
            if ( !isset ($aData['CT_MAP_TYPE']) )
            {
                $aData['CT_MAP_TYPE'] = 'PROCESSMAP';
            }
            $oCaseTracker = new CaseTracker();
            $oCaseTracker->loadObject ($aData);
            
            if ( $oCaseTracker->validate () )
            {
                $iResult = $oCaseTracker->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oCaseTracker->getValidationFailures ();
                
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
            $oCaseTracker = $this->retrieveByPK ($aData['PRO_UID']);
            
            if ( !is_null ($oCaseTracker) )
            {
                if ( $aData['CT_DERIVATION_HISTORY'] == '' )
                {
                    $aData['CT_DERIVATION_HISTORY'] = 0;
                }
                
                if ( $aData['CT_MESSAGE_HISTORY'] == '' )
                {
                    $aData['CT_MESSAGE_HISTORY'] = 0;
                }
                
                $oCaseTracker->loadObject ($aData);
                
                if ( $oCaseTracker->validate () )
                {
                    $iResult = $oCaseTracker->save ();
                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oCaseTracker->getValidationFailures ();
                    
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

    public function remove ($sProcessUID)
    {
        try {
            $this->setProUid ($sProcessUID);
            $iResult = $this->delete ();
            return $iResult;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function caseTrackerExists ($sUid)
    {
        try {
            $oObj = $this->retrieveByPk ($sUid);
            return (is_object ($oObj) && get_class ($oObj) == 'CaseTracker');
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
