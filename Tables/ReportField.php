<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportFields
 *
 * @author michael.hampton
 */
class ReportField extends BaseReportField
{

    public function create ($aData)
    {

        try {
            $oFields = new ReportField();
            $oFields->loadObject ($aData);
            if ( $oFields->validate () )
            {
               $oFields->save ();
                return $aData['FLD_UID'];
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oFields->getValidationFailures ();
                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw(new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

}
