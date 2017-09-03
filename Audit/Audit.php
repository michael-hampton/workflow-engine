<?php

class Audit extends BaseAudit
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function insertHistory ($aData)
    {
        // id of project
        $this->setAppUid ($aData['APP_UID']);

        // id of case
        $this->setTasUid ($aData['TAS_UID']);

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

    public function getHistory ($APP_ID, $CASE_ID)
    {

        /*$result = $this->objMysql->_select ("workflow.workflow_data", [], ["object_id" => $APP_ID]);

        $workflowData = $result[0]['workflow_data'];
        $auditData = $result[0]['audit_data'];*/

        $html = "<table border='0' cellpadding='0' cellspacing='0'>";
        $sw_add = false;

        $audits = $this->objMysql->_select ("audit", [], ["project_id" => $APP_ID, "case_id" => $CASE_ID]);

        foreach ($audits as $audit) {
            $sql2 = "SELECT usrid, firstName, lastName, CONCAT(lastName, ' ', firstName) AS USR_NAME FROM user_management.poms_users WHERE username = ?";
            $result2 = $this->objMysql->_query ($sql2, [$audit['username']]);

            if ( isset ($result2[0]) && !empty ($result2[0]) )
            {
                $username = $result2[0]['USR_NAME'];
                $userId = $result2[0]['usrid'];
                $firstName = $result2[0]['firstName'];
                $lastName = $result2[0]['lastName'];
            }

            $changedValues = unserialize ($audit['message']);

            foreach ($changedValues as $key => $value) {
                if ( ($value != null) && (!is_array ($value)) )
                {
                    $sw_add = true;
                    $html.="<tr>";
                    $html.="<td><b>$key:</b> </td>";
                    $html.="<td>$value</td>";
                    $html.="</tr>";
                }
                if ( is_array ($value) )
                {
                    $html.="<tr>";
                    $html.="<td><b>$key (grid):</b> </td>";
                    $html.="<td>";
                    $html.="<table>";
                    foreach ($value as $key1 => $value1) {
                        $html.="<tr>";
                        $html.="<td><b>$key1</b></td>";
                        $html.="<td>";
                        if ( is_array ($value1) )
                        {
                            $sw_add = true;
                            $html.="<table>";
                            foreach ($value1 as $key2 => $value2) {
                                $html.="<tr>";
                                $html.="<td><b>$key2</b></td>";
                                $html.="<td>$value2</td>";
                                $html.="</tr>";
                            }
                            $html.="</table>";
                        }
                        $html.="</td>";
                        $html.="</tr>";
                    }
                    $html.="</table>";
                    $html.="</td>";
                    $html.="</tr>";
                    $html.="</td>";

                    if ( $sw_add )
                    {
                        $aDynHistory[] = $audit;
                    }
                }
            }
        }

        $html.="</table>";

        $aDynHistory['FIELDS'] = $html;


        return $aDynHistory;
    }

}
