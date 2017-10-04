<?php

class ConsolidatedCases
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function saveConsolidated ($data)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $status = $data['con_status'];
        $sTasUid = $data['tas_uid'];
        $sDynUid = $data['dyn_uid'];
        $sProUid = $data['pro_uid'];
        $sRepTabUid = $data['rep_uid'];
        $tableName = $data['table_name'];
        $title = $data['title'];

        if ( $sRepTabUid != '' )
        {

            if ( !$status )
            {

                $oCaseConsolidated = new CaseConsolidatedCore();
                $oCaseConsolidated = $oCaseConsolidated->retrieveByPK ($sTasUid);

                if ( !(is_object ($oCaseConsolidated)) || get_class ($oCaseConsolidated) != 'CaseConsolidatedCore' )
                {
                    $oCaseConsolidated = new CaseConsolidatedCore();
                    $oCaseConsolidated->setTasUid ($sTasUid);
                    $oCaseConsolidated->setConStatus ('INACTIVE');
                    $oCaseConsolidated->save ();
                }
                else
                {
                    $oCaseConsolidated->delete ($results2[0]['TAS_UID']);
                }
                return 1;
            }

            $result = $this->objMysql->_select ("report_tables.additional_tables", [], ["ADD_TAB_UID" => $sRepTabUid]);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                $rptUid = $result[0]['ADD_TAB_UID'];

                $rpts = new AdditionalTables();

                if ( $rptUid != null )
                {
                    $rpts->deleteAll ($rptUid);
                }

                //$this->objMysql->_query("drop table if exists ".$oldTableName);
            }
        }

        $arrData['PRO_UID'] = $sProUid;

        $arrData['ADD_TAB_CLASS_NAME'] = $tableName;
        $arrData['ADD_TAB_TYPE'] = "GLOBAL";
        $arrData['REP_TAB_GRID'] = '';
        $arrData['DBS_UID'] = 'wf';
        $arrData['ADD_TAB_DESCRIPTION'] = "test";
        $arrData['REP_TAB_CREATE_DATE'] = date ("Y-m-d H:i:s");
        $arrData['REP_TAB_STATUS'] = 'ACTIVE';
        $arrData['ADD_TAB_NAME'] = $title;
        $arrData['FIELDS'] = array();


        $id = $rpts->create ($arrData);

        $arrData['ADD_TAB_UID'] = $id;

        //$_POST['form']['REP_TAB_UID'] = $id;

        $oReportVar = new ReportVar();
        $oReportTables = new BusinessModel\ReportTable();
        $oReportTables->deleteAllReportVars ($arrData['ADD_TAB_UID']);

        $pmDyna = new BusinessModel\Form();
        $fieldsDyna = $pmDyna->getFieldsForStep (new Task ($sTasUid));

        foreach ($fieldsDyna as $value) {


            if ( $value->getFieldType () == "text" || $value->getFieldType () == 'textarea' || $value->getFieldType () == 'select' || $value->getFieldType () == 'checkbox' || $value->getFieldType () == 'date' || $value->getFieldType () == 'hidden' )
            {
                $arrData['form']['FIELDS'][] = $value->getFieldId () . '-' . $value->getFieldType ();
            }
        }

        $aFieldsClases = array();
        $i = 1;
        $aFieldsClases[$i]['field_name'] = 'APP_UID';
        $aFieldsClases[$i]['FLD_NULL'] = 'off';
        $aFieldsClases[$i]['FLD_KEY'] = 'on';
        $aFieldsClases[$i]['FLD_AUTO_INCREMENT'] = 'off';
        $aFieldsClases[$i]['FLD_DESCRIPTION'] = '';
        $aFieldsClases[$i]['field_type'] = 'VARCHAR';
        $aFieldsClases[$i]['field_size'] = 32;
        $i++;

        $aFieldsClases[$i]['field_name'] = 'APP_STATUS';
        $aFieldsClases[$i]['FLD_NULL'] = 'off';
        $aFieldsClases[$i]['FLD_KEY'] = 'on';
        $aFieldsClases[$i]['FLD_AUTO_INCREMENT'] = 'off';
        $aFieldsClases[$i]['FLD_DESCRIPTION'] = '';
        $aFieldsClases[$i]['field_type'] = 'VARCHAR';
        $aFieldsClases[$i]['field_size'] = 32;
        $i++;

        $aFieldsClases[$i]['field_name'] = 'APP_NUMBER';
        $aFieldsClases[$i]['FLD_NULL'] = 'off';
        $aFieldsClases[$i]['FLD_KEY'] = 'on';
        $aFieldsClases[$i]['FLD_AUTO_INCREMENT'] = 'off';
        $aFieldsClases[$i]['FLD_DESCRIPTION'] = '';
        $aFieldsClases[$i]['field_type'] = 'VARCHAR';
        $aFieldsClases[$i]['field_size'] = 255;

        foreach ($arrData['form']['FIELDS'] as $sField) {
            $aField = explode ('-', $sField);

            if ( $aField[1] == 'title' || $aField[1] == 'submit' )
            {
                continue;
            }
            $i++;
            $aFieldsClases[$i]['field_name'] = $aField[0];
            $aFieldsClases[$i]['FLD_NULL'] = 'off';
            $aFieldsClases[$i]['FLD_KEY'] = 'off';
            $aFieldsClases[$i]['FLD_AUTO_INCREMENT'] = 'off';
            $aFieldsClases[$i]['FLD_DESCRIPTION'] = '';

            switch ($aField[1]) {
                case 'currency':
                case 'percentage':
                    $sType = 'number';
                    $aFieldsClases[$i]['field_type'] = 'number';
                    $aFieldsClases[$i]['field_size'] = 11;
                    break;
                case 'text':
                case 'password':
                case 'select':
                case 'yesno':
                case 'checkbox':
                case 'radiogroup':
                case 'hidden':
                case "link":
                    $sType = 'char';
                    $aFieldsClases[$i]['field_type'] = 'VARCHAR';
                    $aFieldsClases[$i]['field_size'] = 255;
                    break;
                case 'textarea':
                    $sType = 'text';
                    $aFieldsClases[$i]['field_type'] = 'TEXT';
                    $aFieldsClases[$i]['field_size'] = '';
                    break;
                case 'date':
                    $sType = 'date';
                    $aFieldsClases[$i]['field_type'] = 'DATE';
                    $aFieldsClases[$i]['field_size'] = '';
                    break;
                default:
                    $sType = 'char';
                    $aFieldsClases[$i]['field_type'] = 'VARCHAR';
                    $aFieldsClases[$i]['field_size'] = 255;
                    break;
            }

            $oReportVar->create (array('REP_TAB_UID' => $arrData['ADD_TAB_UID'],
                'PRO_UID' => $arrData['PRO_UID'],
                'REP_VAR_NAME' => $aField[0],
                'REP_VAR_TYPE' => $sType));
        }

        $pmTable = new pmTable();

        $arrData['form']['REP_TAB_TYPE'] = "NORMAL";

        $oAdditionalTables = new AdditionalTables();

        $oReportTables->dropTable ($arrData['ADD_TAB_CLASS_NAME']);
        $pmTable->createTable ($arrData['ADD_TAB_CLASS_NAME'], 'report', 'NORMAL', $aFieldsClases);
        $oAdditionalTables->populateReportTable ($arrData['PRO_UID']);
        $sRepTabUid = $arrData['ADD_TAB_UID'];

        $caseConsolidated = new CaseConsolidatedCore();

        $oCaseConsolidated = $caseConsolidated->retrieveByPk ($sTasUid);


        if ( !(is_object ($oCaseConsolidated)) || get_class ($oCaseConsolidated) != 'CaseConsolidatedCore' )
        {
            $oCaseConsolidated = new CaseConsolidatedCore();
            $oCaseConsolidated->setTasUid ($sTasUid);
        }

        $results2 = $this->objMysql->_select ("workflow.CASE_CONSOLIDATED", [], ["TAS_UID" => $sTasUid]);

        if ( isset ($results2[0]) && !empty ($results2[0]) )
        {
            $oCaseConsolidated->retrieveByPk ($results2[0]['TAS_UID']);
            $oCaseConsolidated->delete ($results2[0]['TAS_UID']);
        }

        if ( !(is_object ($oCaseConsolidated)) || get_class ($oCaseConsolidated) != 'CaseConsolidatedCore' )
        {
            $oCaseConsolidated = new CaseConsolidatedCore();
            $oCaseConsolidated->setTasUid ($sTasUid);
        }

        $oCaseConsolidated->setConStatus ('ACTIVE');
        $oCaseConsolidated->setDynUid ($sDynUid);
        $oCaseConsolidated->setRepTabUid ($sRepTabUid);
        $oCaseConsolidated->save ();
    }

    public function getCases ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $results = $this->objMysql->_query ("SELECT am.APP_UID, am.CASE_UID FROM workflow.app_message am
                                            inner JOIN workflow.case_consolidated cc ON cc.TAS_UID = am.DEL_INDEX
                                            WHERE am.APP_MSG_SHOW_MESSAGE = 1
                                            AND am.APP_MSG_TO LIKE '%bluetiger_uan@yahoo.com%'");

        $arrCases = [];

        foreach ($results as $result) {
            $arrCases[] = (new \BusinessModel\Cases())->getCaseInfo ($result['APP_UID'], $result['CASE_UID']);
        }

        return $arrCases;
    }

    public function getListTabs ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $sql = "SELECT   t.step_name, 
                    w.workflow_id, 
                    w.workflow_name, 
                     COUNT(am.DEL_INDEX) AS NUMREC, 
                    t.TAS_UID  FROM workflow.app_message am
                    INNER JOIN workflow.task t ON t.TAS_UID = am.DEL_INDEX
                    inner JOIN workflow.case_consolidated cc ON cc.TAS_UID = am.DEL_INDEX
                    INNER JOIN workflow.workflows w ON w.workflow_id = t.PRO_UID
                    WHERE am.APP_MSG_SHOW_MESSAGE = 1
                    AND am.APP_MSG_TO LIKE '%bluetiger_uan@yahoo.com%'
                    GROUP BY t.TAS_UID";


        $results = $this->objMysql->_query ($sql);

        if ( isset ($results[0]) && !empty ($results[0]) )
        {
            foreach ($results as $key => $row) {
                //$dynaformUid = $row['DYN_UID'];
                $tabTitle = $row['step_name'] . " (" . (($row['NUMREC'] > 0) ? $row["NUMREC"] : 0) . ")";
                $tabTitle = htmlentities (substr ($row['workflow_name'], 0, 25) . ((strlen ($row['workflow_name']) > 25) ? "..." : null) . " / " . $tabTitle, ENT_QUOTES, "UTF-8");
                $grdTitle = htmlentities ($row['workflow_name'] . " / " . $tabTitle, ENT_QUOTES, "UTF-8");

                $results[$key]['tab_title'] = $tabTitle;
                $results[$key]['grd_title'] = $grdTitle;
            }

            return $results;
        }
    }

}
