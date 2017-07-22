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
class AdditionalTables
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
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
        
        $workflowId = (new \BusinessModel\Cases())->getCaseInfo($proUid, $appUid)->getWorkflow_id ();

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
            $objSaveReport->setProjectId($objElement->getSource_id());
            $objSaveReport->setAppUid($objElement->getId());

            if ( isset ($record[0]) && !empty ($record[0]) )
            {
                $objSaveReport->setBlUpdate(TRUE);
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
                        foreach ($fieldTypes as $key => $fieldType) {
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

}
