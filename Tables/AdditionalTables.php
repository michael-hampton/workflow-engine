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
        parent::__construct();
        $this->objMysql = new Mysql2();
    }

    
    /**
     * Function load
     * access public
     */
    public function load($sUID, $bFields = false)
    {
        $aFields = $this->retrieveByPK($sUID);
        
        if (is_null($oAdditionalTables)) {
            return null;
        }
        
        $this->loadObject($aFields);
        
        if ($bFields) {
            $aFields['FIELDS'] = $this->getFields();
        }
        return $aFields;
    }
    
    public function getFields()
    {
        if (count($this->fields) > 0) {
            return $this->fields;
        }
        
        $results = $this->objMysql->_select("report_tables.report_fields", [], ["ADD_TAB_UID" => $this->getAddTabUid()]);
        
        
        while ($results as $auxField) {
            
            if ($auxField['FLD_TYPE'] == 'TIMESTAMP') {
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
            $oPro = $this->retrieveByPk($sUID);
            if (is_object($oPro) && get_class($oPro) == 'AdditionalTables') {
                return true;
            } else {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
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

            $className = $result['ADD_TAB_CLASS_NAME'];

            $criteria = $this->objMysql->_query ("SELECT `COLUMN_NAME`, DATA_TYPE  
                                                    FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                                                    WHERE `TABLE_SCHEMA`='report_tables' 
                                                        AND `TABLE_NAME`='" . $className . "';");


            if ( !isset ($criteria[0]) || empty ($criteria[0]) )
            {
                continue;
            }

            $record = $this->objMysql->_select ("report_tables.{$className}", [], ["pro_uid" => $objElement->getSource_id (), "app_id" => $objElement->getId ()]);

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

                return $aData['ADD_TAB_UID'];
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
