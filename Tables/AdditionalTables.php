<?php

function validateType ($value, $type)
{
    switch ($type) {
        case 'int':
            $value = str_replace (",", "", $value);
            $value = str_replace (".", "", $value);
            break;
        case 'FLOAT':
        case 'DOUBLE':
            $pos = strrpos ($value, ",");
            $pos = ($pos === false) ? 0 : $pos;

            $posPoint = strrpos ($value, ".");
            $posPoint = ($posPoint === false) ? 0 : $posPoint;

            if ( $pos > $posPoint )
            {
                $value2 = substr ($value, $pos + 1);
                $value1 = substr ($value, 0, $pos);
                $value1 = str_replace (".", "", $value1);
                $value = $value1 . "." . $value2;
            }
            else
            {
                if ( $posPoint )
                {
                    $value2 = substr ($value, $posPoint + 1);
                    $value1 = substr ($value, 0, $posPoint);
                    $value1 = str_replace (",", "", $value1);
                    $value = $value1 . "." . $value2;
                }
            }
            break;
        default:
            break;
    }
    return $value;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdditionalTables
 *
 * @author michael.hampton
 */
class AdditionalTables extends BaseAdditionalTables
{

    private $objMysql;

    public function __construct ()
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();
    }

    /**
     * Function load
     * access public
     */
    public function load ($sUID, $bFields = false)
    {
        $aFields = $this->retrieveByPK ($sUID);

        if ( is_null ($aFields) )
        {
            return null;
        }

        $this->loadObject ($aFields);

        if ( $bFields )
        {
            $aFields['FIELDS'] = $this->getFields ();
        }
        return $aFields;
    }

    /**
     * Populate the report table with all case data
     * @param string $sType
     * @param string $sProcessUid
     * @param string $sGrid
     * @return number
     */
    public function populateReportTable ($pro_uid, $rep_uid, $tableName, $validate)
    {
        $results = $this->objMysql->_select ("workflow.workflow_data");

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        foreach ($results as $result) {
            $workflowData = json_decode ($result['workflow_data'], true);
            $projectId = $result['object_id'];

            if ( isset ($workflowData['elements']) && !empty ($workflowData['elements']) )
            {
                foreach ($workflowData['elements'] as $elementId => $element) {
                    if ( $element['workflow_id'] === $pro_uid )
                    {
                        $objElement = new Elements ($projectId, $elementId);
                        $this->updateReportTables ($objElement);
                    }
                }
            }
        }
    }

    public function getAllData ($sUID, $start = null, $limit = null, $keyOrderUppercase = true, $filter = '', $appUid = false, $search = '')
    {
        $addTab = new AdditionalTables();
        $aData = $addTab->load ($sUID, true);

        if ( !isset ($_SESSION['PROCESS']) )
        {
            $_SESSION["PROCESS"] = $aData['PRO_UID'];
        }
        $aData['DBS_UID'] = isset ($aData['DBS_UID']) ? $aData['DBS_UID'] : 'workflow';

        $sql = " SELECT ";

        foreach ($aData['FIELDS'] as $aField) {
            $sql .= $aField['FLD_NAME'] . ",";
        }

        $sql = rtrim ($sql, ',');

        $sql .= " FROM task_manager.rpt_" . strtolower ($aData['ADD_TAB_CLASS_NAME']);

        if ( $filter != '' && is_string ($filter) )
        {
            $stringOr = '';
            $closure = '';
            $types = array('INTEGER', 'BIGINT', 'SMALLINT', 'TINYINT', 'DECIMAL', 'DOUBLE', 'FLOAT', 'REAL');
            foreach ($aData['FIELDS'] as $aField) {
                if ( ($appUid == false && $aField['FLD_NAME'] != 'APP_UID') || ($appUid == true) )
                {
                    if ( in_array ($aField['FLD_TYPE'], $types) )
                    {
                        if ( is_numeric ($filter) )
                        {
                            $stringOr = $stringOr . $aField['FLD_NAME'] . ' = "' . $filter . ';';
                        }
                    }
                    else
                    {
                        $stringOr = $stringOr . $aField['FLD_NAME'] . ' LIKE "%' . $filter . '%"';
                    }
                }
            }
        }

        if ( $search !== '' && is_string ($search) )
        {
            try {
                $object = json_decode ($search);
                if ( isset ($object->where) )
                {
                    $stringAnd = "";
                    $closure = "";
                    $fields = $object->where;
                    foreach ($fields as $key => $value) {
                        if ( is_string ($value) )
                        {
                            $stringAnd = $stringAnd . " AND " . $key . " = " . $value;
                        }
                        if ( is_object ($value) )
                        {

                            if ( isset ($value->like) )
                            {
                                $stringAnd = $stringAnd . " AND $key LIKE '%" . $value->like . "%'";
                            }
                            if ( isset ($value->nlike) )
                            {
                                $stringAnd = $stringAnd . " AND $key NOT LIKE '%" . $value->nlike . "%'";
                            }

                            if ( isset ($value->neq) )
                            {
                                $stringAnd = $stringAnd . " AND $key != " . $value->like . " ";
                            }
                        }
                    }
                    if ( !empty ($stringAnd) )
                    {
                        $stringAnd = " AND (" . $stringAnd . ")";
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        $count = $this->objMysql->_query ($sql);
        $count = count ($count);

        if ( isset ($_POST['sort']) )
        {
            if ( $_POST['dir'] == 'ASC' )
            {
                $sql .= "ORDER BY " . $_POST['sort'] . " ASC";
            }
            else
            {
                $sql .= "ORDER BY " . $_POST['sort'] . " DESC";
            }
        }

        if ( isset ($limit) )
        {
            $sql .= " LIMIT " . (int) $limit;
        }
        if ( isset ($start) )
        {
            $sql .= " OFFSET " . (int) $start;
        }

        $results = $this->objMysql->_query ($sql);

        return array('rows' => $results, 'count' => $count);
    }

    public function getFields ()
    {

        $results = $this->objMysql->_select ("report_tables.fields", [], ["ADD_TAB_UID" => $this->getAddTabUid ()]);


        foreach ($results as $auxField) {

            if ( $auxField['FLD_TYPE'] == 'TIMESTAMP' )
            {
                $auxField['FLD_TYPE'] = 'DATETIME';
            }
            $this->fields[] = $auxField;
        }
        return $this->fields;
    }

    /**
     * verify if Additional Table row specified in [sUID] exists.
     *
     * @param      string $sUID   the uid of the additional table
     */
    public function exists ($sUID)
    {
        try {
            $oPro = $this->retrieveByPk ($sUID);
            if ( is_object ($oPro) && get_class ($oPro) == 'AdditionalTables' )
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

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     AdditionalTables
     */
    public function retrieveByPK ($pk)
    {
        $results = $this->objMysql->_select ("report_tables.additional_tables", [], ["ADD_TAB_UID" => $pk]);

        return isset ($results[0]) && !empty ($results[0]) ? $results[0] : null;
    }

    /**
     * Update the report table with a determinated case data
     * @param string $proUid
     * @param string $appUid
     * @param string $appNumber
     * @param string $caseData
     */
    public function updateReportTables (Elements $objElement)
    {

        //echo $objElement->getSource_id();
        $appUid = $objElement->getId ();
        $proUid = $objElement->getSource_id ();

        $workflowId = (new \BusinessModel\Cases())->getCaseInfo ($proUid, $appUid)->getWorkflow_id ();

        $results = $this->objMysql->_select ("report_tables.additional_tables", [], ["PRO_UID" => $workflowId]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return FALSE;
        }

        foreach ($results as $result) {

            $className = "rpt_" . strtolower ($result['ADD_TAB_CLASS_NAME']);

            $criteria = $this->objMysql->_query ("SELECT `COLUMN_NAME`, DATA_TYPE  
                                                    FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                                                    WHERE `TABLE_SCHEMA`='task_manager' 
                                                        AND `TABLE_NAME`='" . $className . "';");


            if ( !isset ($criteria[0]) || empty ($criteria[0]) )
            {
                continue;
            }

            $record = $this->objMysql->_select ("task_manager.{$className}", [], ["pro_uid" => $objElement->getSource_id (), "APP_UID" => $objElement->getId ()]);

            $objSaveReport = new SaveReport();
            $objSaveReport->setProjectId ($objElement->getSource_id ());
            $objSaveReport->setAppUid ($objElement->getId ());

            if ( isset ($record[0]) && !empty ($record[0]) )
            {
                $objSaveReport->setBlUpdate (TRUE);
            }

            $objSaveReport->setTableName ($className);

            $fieldTypes = array();

            foreach ($criteria as $field) {
                $fieldTypes[] = array($field['COLUMN_NAME'] => $field['DATA_TYPE']);
            }

            switch ($result['ADD_TAB_TYPE']) {
                //switching by report table type
                case 'NORMAL':
                case "GLOBAL":

                    // parsing empty values to null
                    if ( !is_array ($objElement->arrElement) )
                    {
                        $objElement->arrElement = json_decode ($objElement->arrElement, TRUE);
                    }

                    foreach ($objElement->arrElement as $i => $v) {
                        foreach ($fieldTypes as $fieldType) {
                            foreach ($fieldType as $name => $type) {
                                if ( $i == $name )
                                {
                                    $v = validateType ($v, $type);
                                    unset ($name);
                                    $objSaveReport->setVariable ($i, ($v === '' ? null : $v));
                                }
                            }
                        }
                    }

                    $objSaveReport->save ();

                    break;
            }
        }
    }

    public static function getPHPName ($sName)
    {
        $sName = trim ($sName);
        $aAux = explode ('_', $sName);
        foreach ($aAux as $iKey => $sPart) {
            $aAux[$iKey] = ucwords (strtolower ($sPart));
        }
        return implode ('', $aAux);
    }

    public function loadByName ($name)
    {
        try {

            $results = $this->objMysql->_select ("report_tables.additional_tables", [], ["ADD_TAB_CLASS_NAME" => $name]);

            if ( !isset ($results[0]) || empty ($results[0]) )
            {
                return false;
            }

            return $results;
        } catch (Exception $oError) {
            throw($oError);
        }
    }

    /**
     * Create & Update function
     */
    public function create ($aData, $aFields = array())
    {
        try {
            $oAdditionalTables = new AdditionalTables();
            $oAdditionalTables->loadObject ($aData);
            if ( $oAdditionalTables->validate () )
            {
                $iResult = $oAdditionalTables->save ();

                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oAdditionalTables->getValidationFailures ();
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
