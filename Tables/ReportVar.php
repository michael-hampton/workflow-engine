<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportVar
 *
 * @author michael.hampton
 */
class ReportVar extends BaseReportVar
{

    /**
     * Create the report var registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function create ($aData)
    {
        try {
            if ( isset ($aData['REP_VAR_UID']) && $aData['REP_VAR_UID'] == '' )
            {
                unset ($aData['REP_VAR_UID']);
            }
           
            $oReportVar = new ReportVar();
            $oReportVar->loadObject ($aData);
            
            if ( $oReportVar->validate () )
            {
                $iResult = $oReportVar->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oReportVar->getValidationFailures ();
                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
