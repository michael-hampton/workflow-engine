<?php

class Audit extends BaseAudit
{

    public function insertHistory ($aData)
    {
        // id of case
        $this->setAppUid ($aData['APP_UID']);

        // id of process
        $this->setProUid ($aData['PRO_UID']);

        // id of step
        $this->setTasUid ($aData['TAS_UID']);

        // user id
        $this->setUsrUid ($aData['USER_UID']);

        // status
        $this->setAppStatus ($aData['APP_STATUS']);

        // date
        $this->setHistoryDate ($aData['APP_UPDATE_DATE']);

        // additional data
        $this->setHistoryData ($aData['APP_DATA']);

        if ( isset ($aData["OBJECT_TYPE"]) )
        {
            $this->setObjType ($aData["OBJECT_TYPE"]);
        }
        if ( $this->validate () )
        {
            $res = $this->save ();
        }
        else
        {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $this->getValidationFailures ();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage () . "<br/>";
            }
        }
    }

}
