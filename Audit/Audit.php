<?php

class Audit extends BaseAudit
{

    public function insertHistory ($aData)
    {
        // id of project
        $this->setAppUid ($aData['APP_UID']);
        
        // id of case
        $this->setTasUid($aData['TAS_UID']);

        // id of process
        $this->setProUid ($aData['PRO_UID']);

        // user id
        $this->setUsrUid ($aData['USER_UID']);

        // status
        //$this->setAppStatus ($aData['APP_STATUS']);

        // date
        $this->setHistoryDate ($aData['APP_UPDATE_DATE']);

        // additional data
        $this->setHistoryData ($aData['APP_DATA']);
        
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
    
    public function getHistory($PRO_UID, $APP_UID) {
        $oDataset = $this->database->_query("SELECT * FROM audits WHERE JSON_EXTRACT(data, '$.reference_number') = '0' AND source_id = 641");

        foreach($oDataset as $aRow) {
            $objElement = new \Element($PRO_UID, $APP_UID);

            $title = $objElement->getName();

            $aRow["DYN_TITLE"] = $title;
            $arrData = json_decode($aRow['data'], true);


            if(isset($arrData['before']) && !empty($arrData['before'])) {
                $changedValues=unserialize($arrData['before']);

                $html="<table border='0' cellpadding='0' cellspacing='0'>";
                $sw_add=false;

                foreach ($changedValues as $key => $value) {

                    if (($value!=null) && (!is_array($value))) {
                        $sw_add=true;
                        $html.="<tr>";
                        $html.="<td><b>$key:</b> </td>";
                        $html.="<td>$value</td>";
                        $html.="</tr>";
                    }

                    if (is_array($value)) {
                        $html.="<tr>";
                        $html.="<td><b>$key:</b> </td>";
                        $html.="<td>";
                        $html.="<table>";
                        foreach ($value as $key1 => $value1) {

                            $html.="<tr>";
                            //$html.="<td><b>$key1</b></td>";
                            $html.="<td>";
                            if (is_array($value1)) {
                                $sw_add=true;
                                $html.="<table>";
                                foreach ($value1 as $key2 => $value2) {
                                    $html.="<tr>";
                                    $html.="<td><b>$key2:</b></td>";
                                    $html.="<td>$value2</td>";
                                    $html.="</tr>";
                                }
                                $html.="</table>";
                            } else {
                                $html.= $value1;
                            }
                            $html.="</td>";
                            $html.="</tr>";
                        }
                        $html.="</table>";
                        $html.="</td>";
                        $html.="</tr>";
                        $html.="</td>";
                    }
                }

                $html.="</table>";

                $aRow['FIELDS']    = $html;

                if ($sw_add) {
                    $aDynHistory[] = $aRow;
                }
            }

        }

      return $aDynHistory;
    }

}
