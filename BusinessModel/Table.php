<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of Table
 *
 * @author michael.hampton
 */
class Table
{

    /**
     * Save Data for Table
     * @var string $tab_data. Data for table
     * @var string $pro_uid. Uid for process
     * @var string $reportFlag. If is report table
     * @var string $createRep. Flag for create table
     *
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return array
     */
    public function saveTable($tab_data, $pro_uid = '', $reportFlag = false, $createRep = true)
    {
        // CHANGE CASE UPPER TABLE
        $fieldsValidate = array();
        $tableName      = '';
        $tableCon       = 'workflow';
        $dataValidate   = array_change_key_case($tab_data, CASE_UPPER);
        $oAdditionalTables = new AdditionalTables();
        // VALIDATION TABLE DATA
        if ($reportFlag) {
            $pro_uid = $this->validateProUid($pro_uid);
            $dataValidate['TAB_UID']            = (isset($dataValidate['REP_UID'])) ? $dataValidate['REP_UID'] : '';
            $dataValidate['PRO_UID']            = $pro_uid;
            $dataValidate['REP_TAB_NAME']       = $this->validateTabName($dataValidate['REP_TAB_NAME'], $reportFlag);
            $tempRepTabName                     = $dataValidate['REP_TAB_CONNECTION'];
            $dataValidate['REP_TAB_CONNECTION'] = $this->validateRepConnection($tempRepTabName, $pro_uid);
            if ($dataValidate['REP_TAB_TYPE'] == 'GRID') {
                $dataValidate['REP_TAB_GRID']   = $this->validateRepGrid($dataValidate['REP_TAB_GRID'], $pro_uid);
            }
            $fieldsValidate = $this->getDynafields($pro_uid, $dataValidate['REP_TAB_TYPE'], $dataValidate['REP_TAB_GRID']);
            if (empty($fieldsValidate)) {
                $fieldsValidate['NAMES'] = array();
                $fieldsValidate['INDEXS'] = array();
                $fieldsValidate['UIDS'] = array();
            }
            $repTabClassName = $oAdditionalTables->getPHPName($dataValidate['REP_TAB_NAME']);
            $tableName = $dataValidate['REP_TAB_NAME'];
            $tableCon  = $dataValidate['REP_TAB_CONNECTION'];
        } else {
            $dataValidate['TAB_UID']            = (isset($dataValidate['PMT_UID'])) ? $dataValidate['PMT_UID'] : '';
            $dataValidate['PMT_TAB_NAME']       = $this->validateTabName($dataValidate['PMT_TAB_NAME']);
            $dataValidate['PMT_TAB_CONNECTION'] = 'workflow';
            $repTabClassName = $oAdditionalTables->getPHPName($dataValidate['PMT_TAB_NAME']);
            $tableName = $dataValidate['PMT_TAB_NAME'];
            $tableCon  = $dataValidate['PMT_TAB_CONNECTION'];
        }

        // VERIFY COLUMNS TABLE
        $oFields = new Fields();
        $columns = $dataValidate['FIELDS'];

        // Reserved Words Table, Field, Sql
        $reservedWords = array ('ALTER','CLOSE','COMMIT','CREATE','DECLARE','DELETE',
            'DROP','FETCH','FUNCTION','GRANT','INDEX','INSERT','OPEN','REVOKE','ROLLBACK',
            'SELECT','SYNONYM','TABLE','UPDATE','VIEW','APP_UID','ROW','PMTABLE');
        $reservedWordsPhp = array ('case','catch','cfunction','class','clone','const','continue',
            'declare','default','do','else','elseif','enddeclare','endfor','endforeach','endif',
            'endswitch','endwhile','extends','final','for','foreach','function','global','goto',
            'if','implements','interface','instanceof','private','namespace','new','old_function',
            'or','throw','protected','public','static','switch','xor','try','use','var','while');
        $reservedWordsSql = G::reservedWordsSql();

        if ($reportFlag) {
            $defaultColumns = $this->getReportTableDefaultColumns($dataValidate['REP_TAB_TYPE']);
            $columns = array_merge( $defaultColumns, $columns );
        }

        // validations
        if ($createRep) {
            if (is_array( $oAdditionalTables->loadByName( $tableName ) )) {
                throw new \Exception(G::loadTranslation('ID_PMTABLE_ALREADY_EXISTS', array($tableName)));
            }
        }
        if (in_array( strtoupper( $tableName ), $reservedWords ) ||
            in_array( strtoupper( $tableName ), $reservedWordsSql )) {
            throw (new \Exception(G::LoadTranslation("ID_PMTABLE_INVALID_NAME", array($tableName))));
        }

        //backward compatility
        $flagKey = false;
        $columnsStd = array();
        foreach ($columns as $i => $column) {
            if (isset($columns[$i]['fld_dyn'])) {
                $columns[$i]['fld_dyn'] = ($reportFlag) ? $columns[$i]['fld_dyn'] : '';
                $columns[$i]['field_dyn'] = $columns[$i]['fld_dyn'];
                unset($columns[$i]['fld_dyn']);
            } else {
                $columns[$i]['fld_dyn'] = '';
            }

            if (isset($columns[$i]['fld_name'])) {
                $columns[$i]['field_name'] = G::toUpper($columns[$i]['fld_name']);
                unset($columns[$i]['fld_name']);
            }
            if (isset($columns[$i]['fld_label'])) {
                $columns[$i]['field_label'] = $columns[$i]['fld_label'];
                unset($columns[$i]['fld_label']);
            }
            if (isset($columns[$i]['fld_type'])) {
                $columns[$i]['field_type'] = $columns[$i]['fld_type'];
                unset($columns[$i]['fld_type']);
            }
            if (isset($columns[$i]['fld_size'])) {
                $columns[$i]['field_size'] = $columns[$i]['fld_size'];
                if (!is_int($columns[$i]['field_size'])) {
                    throw (new \Exception("The property fld_size: '". $columns[$i]['field_size'] . "' is incorrect numeric value."));
                } else {
                    $columns[$i]['field_size'] = (int)$columns[$i]['field_size'];
                }
                unset($columns[$i]['fld_size']);
            }
            if (isset($columns[$i]['fld_key'])) {
                $columns[$i]['field_key'] = $columns[$i]['fld_key'];
                unset($columns[$i]['fld_key']);
            }
            if (isset($columns[$i]['fld_null'])) {
                $columns[$i]['field_null'] = $columns[$i]['fld_null'];
                unset($columns[$i]['fld_null']);
            }
            if (isset($columns[$i]['fld_autoincrement'])) {
                $columns[$i]['field_autoincrement'] = $columns[$i]['fld_autoincrement'];
                unset($columns[$i]['fld_autoincrement']);
            }

            // VALIDATIONS
            if (in_array(strtoupper($columns[$i]['field_name']), $reservedWordsSql) ||
                in_array( strtolower( $columns[$i]['field_name']), $reservedWordsPhp ) ||
                $columns[$i]['field_name'] == '') {
                throw (new \Exception("The property fld_name: '". $columns[$i]['field_name'] . "' is incorrect value."));
            }
            if ($columns[$i]['field_label'] == '') {
                throw (new \Exception("The property fld_label: '". $columns[$i]['field_label'] . "' is incorrect value."));
            }
            $columns[$i]['field_type'] = $this->validateFldType($columns[$i]['field_type']);
            if (isset($columns[$i]['field_autoincrement']) && $columns[$i]['field_autoincrement']) {
                $typeCol = $columns[$i]['field_type'];
                if (! ($typeCol === 'INTEGER' || $typeCol === 'TINYINT' || $typeCol === 'SMALLINT' || $typeCol === 'BIGINT')) {
                    $columns[$i]['field_autoincrement'] = false;
                }
            }
            if (isset($columns[$i]['field_dyn']) && $columns[$i]['field_dyn'] != '') {
                $res = array_search($columns[$i]['field_dyn'], $fieldsValidate['NAMES']);
                if ($res === false) {
                    throw (new \Exception("The property fld_dyn: '".$columns[$i]['field_dyn']."' is incorrect."));
                } else {
                    $columns[$i]['_index']    = $fieldsValidate['INDEXS'][$res];
                    $columns[$i]['field_uid'] = $fieldsValidate['UIDS'][$res];
                }
            }

            $temp = new \stdClass();
            foreach ($columns[$i] as $key => $valCol) {
                eval('$temp->' . str_replace('fld', 'field', $key) . " = '" . $valCol . "';");
            }
            $temp->uid = (isset($temp->uid)) ? $temp->uid : '';
            $temp->_index = (isset($temp->_index)) ? $temp->_index : '';
            $temp->field_uid = (isset($temp->field_uid)) ? $temp->field_uid : '';
            $temp->field_dyn = (isset($temp->field_dyn)) ? $temp->field_dyn : '';

            $temp->field_key = (isset($temp->field_key)) ? $temp->field_key : 0;
            $temp->field_null = (isset($temp->field_null)) ? $temp->field_null : 1;
            $temp->field_dyn = (isset($temp->field_dyn)) ? $temp->field_dyn : '';
            $temp->field_filter = (isset($temp->field_filter)) ? $temp->field_filter : 0;
            $temp->field_autoincrement = (isset($temp->field_autoincrement)) ? $temp->field_autoincrement : 0;

            if (!$reportFlag) {
                unset($temp->_index);
                unset($temp->field_filter);
            }
            if ($temp->field_key == 1 || $temp->field_key == true) {
                $flagKey = true;
            }
            $columnsStd[$i] = $temp;
        }
        if (!$flagKey) {
            throw (new \Exception("The fields must have a key 'fld_key'"));
        }

        $pmTable = new \pmTable($tableName);
        $pmTable->setDataSource($tableCon);
        $pmTable->setColumns($columnsStd);
        $pmTable->setAlterTable(true);
        if (!$createRep) {
            $pmTable->setKeepData(true);
        }
        $pmTable->build();
        $buildResult = ob_get_contents();
        ob_end_clean();
        unset($buildResult);

        // Updating additional table struture information
        if ($reportFlag) {
            $addTabData = array(
                'ADD_TAB_UID' => $dataValidate['TAB_UID'],
                'ADD_TAB_NAME' => $dataValidate['REP_TAB_NAME'],
                'ADD_TAB_CLASS_NAME' => $repTabClassName,
                'ADD_TAB_DESCRIPTION' => $dataValidate['REP_TAB_DSC'],
                'ADD_TAB_PLG_UID' => '',
                'DBS_UID' => ($dataValidate['REP_TAB_CONNECTION'] ? $dataValidate['REP_TAB_CONNECTION'] : 'workflow'),
                'PRO_UID' => $dataValidate['PRO_UID'],
                'ADD_TAB_TYPE' => $dataValidate['REP_TAB_TYPE'],
                'ADD_TAB_GRID' => $dataValidate['REP_TAB_GRID']
            );
        } else {
            $addTabData = array(
                'ADD_TAB_UID' => $dataValidate['TAB_UID'],
                'ADD_TAB_NAME' => $dataValidate['PMT_TAB_NAME'],
                'ADD_TAB_CLASS_NAME' => $repTabClassName,
                'ADD_TAB_DESCRIPTION' => $dataValidate['PMT_TAB_DSC'],
                'ADD_TAB_PLG_UID' => '',
                'DBS_UID' => ($dataValidate['PMT_TAB_CONNECTION'] ? $dataValidate['PMT_TAB_CONNECTION'] : 'workflow'),
                'PRO_UID' => '',
                'ADD_TAB_TYPE' => '',
                'ADD_TAB_GRID' => ''
            );
        }
        if ($createRep) {
            //new report table
            //create record
            $addTabUid = $oAdditionalTables->create( $addTabData );
        } else {
            //editing report table
            //updating record
            $addTabUid = $dataValidate['TAB_UID'];
            $oAdditionalTables->update( $addTabData );

            //removing old data fields references
            $oCriteria = new \Criteria( 'workflow' );
            $oCriteria->add( \FieldsPeer::ADD_TAB_UID, $dataValidate['TAB_UID'] );
            \FieldsPeer::doDelete( $oCriteria );
        }
        // Updating pmtable fields
        foreach ($columnsStd as $i => $column) {
            $column = (array)$column;
            $field = array (
                'FLD_UID' => $column['uid'],
                'FLD_INDEX' => $i,
                'ADD_TAB_UID' => $addTabUid,
                'FLD_NAME' => $column['field_name'],
                'FLD_DESCRIPTION' => $column['field_label'],
                'FLD_TYPE' => $column['field_type'],
                'FLD_SIZE' => (!isset($column['field_size']) || $column['field_size'] == '') ? null : $column['field_size'],
                'FLD_NULL' => $column['field_null'] ? 1 : 0,
                'FLD_AUTO_INCREMENT' => $column['field_autoincrement'] ? 1 : 0,
                'FLD_KEY' => $column['field_key'] ? 1 : 0,
                'FLD_FOREIGN_KEY' => 0,
                'FLD_FOREIGN_KEY_TABLE' => '',
                'FLD_DYN_NAME' => $column['field_dyn'],
                'FLD_DYN_UID' => $column['field_uid'],
                'FLD_FILTER' => (isset($column['field_filter']) && $column['field_filter']) ? 1 : 0
            );
            $oFields->create( $field );
        }
        if ($reportFlag) {
            $rep_uid   = $addTabUid;
            $this->generateDataReport($pro_uid, $rep_uid, false);
        }
        if ($createRep) {
            $tab_uid   = $addTabUid;
            return $this->getTable($tab_uid, $pro_uid, $reportFlag, false);
        }
    }
}
