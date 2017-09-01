<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reportTableCSV
 *
 * @author michael.hampton
 */
class reportTableCSV
{

    protected $className;
    protected $classPeerName;
    protected $dynUid;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Internal method: remove malicious code, fix missing end tags, fix illegal nesting, convert deprecated tags, validate CSS, preserve rich formatting 
     * @author Marcelo Cuiza
     * @access protected
     * @param Array or String $input
     * @param String $type (url)
     * @return Array or String $input
     */
    private function xssFilterHard ($input, $type = "")
    {
        if ( is_array ($input) )
        {
            if ( sizeof ($input) )
            {
                foreach ($input as $i => $val) {
                    if ( is_array ($val) || is_object ($val) && sizeof ($val) )
                    {
                        $input[$i] = $this->xssFilterHard ($val);
                    }
                    else
                    {
                        if ( !empty ($val) )
                        {
                            if ( !is_object (json_decode ($val)) )
                            {
                                $inputFiltered = $purifier->purify ($val);
                                if ( $type != "url" && !strpos (basename ($val), "=") )
                                {
                                    $inputFiltered = htmlspecialchars ($inputFiltered, ENT_NOQUOTES, 'UTF-8');
                                }
                                else
                                {
                                    $inputFiltered = str_replace ('&amp;', '&', $inputFiltered);
                                }
                            }
                            else
                            {
                                $jsArray = json_decode ($val, true);
                                if ( is_array ($jsArray) && sizeof ($jsArray) )
                                {
                                    foreach ($jsArray as $j => $jsVal) {
                                        if ( is_array ($jsVal) && sizeof ($jsVal) )
                                        {
                                            $jsArray[$j] = $this->xssFilterHard ($jsVal);
                                        }
                                        else
                                        {
                                            if ( !empty ($jsVal) )
                                            {
                                                $jsArray[$j] = $purifier->purify ($jsVal);
                                            }
                                        }
                                    }
                                    $inputFiltered = json_encode ($jsArray);
                                }
                                else
                                {
                                    $inputFiltered = $val;
                                }
                            }
                        }
                        else
                        {
                            $inputFiltered = "";
                        }
                        $input[$i] = $inputFiltered;
                    }
                }
            }
            return $input;
        }
        else
        {
            if ( !isset ($input) || empty ($input) )
            {
                return '';
            }
            else
            {
                if ( is_object ($input) )
                {
                    if ( sizeof ($input) )
                    {
                        foreach ($input as $j => $jsVal) {
                            if ( is_array ($jsVal) || is_object ($jsVal) && sizeof ($jsVal) )
                            {
                                $input->j = $this->xssFilterHard ($jsVal);
                            }
                            else
                            {
                                if ( !empty ($jsVal) )
                                {
                                    $input->j = $purifier->purify ($jsVal);
                                }
                            }
                        }
                    }
                    return $input;
                }
                if ( !is_object (json_decode ($input)) )
                {
                    if ( $type != "url" && !strpos (basename ($input), "=") )
                    {
                        $input = addslashes (htmlspecialchars ($input, ENT_COMPAT, 'UTF-8'));
                    }
                    else
                    {
                        $input = str_replace ('&amp;', '&', $input);
                    }
                }
                else
                {
                    $jsArray = json_decode ($input, true);
                    if ( is_array ($jsArray) && sizeof ($jsArray) )
                    {
                        foreach ($jsArray as $j => $jsVal) {
                            if ( is_array ($jsVal) || is_object ($jsVal) && sizeof ($jsVal) )
                            {
                                $jsArray[$j] = $this->xssFilterHard ($jsVal);
                            }
                            else
                            {
                                if ( !empty ($jsVal) )
                                {
                                    $jsArray[$j] = $purifier->purify ($jsVal);
                                }
                            }
                        }
                        $input = json_encode ($jsArray);
                    }
                }
                return $input;
            }
        }
    }

    public function importCSV ($httpData, $arrFile)
    {
        $countRow = 250;
        $tmpfilename = $arrFile['FileUpload2']['tmp_name'];
        //$tmpfilename = $filter->xssFilterHard($tmpfilename, 'path');
        if ( preg_match ('/[\x00-\x08\x0b-\x0c\x0e\x1f]/', file_get_contents ($tmpfilename)) === 0 )
        {
            $filename = $arrFile['FileUpload2']['name'];
            //$filename = $filter->xssFilterHard($filename, 'path');
            if ( $oFile = fopen ($this->xssFilterHard ($tmpfilename, 'path'), 'r') )
            {
                $oAdditionalTables = new AdditionalTables();
                $aAdditionalTables = $oAdditionalTables->load ($_POST['form']['ADD_TAB_UID'], true);

                $sErrorMessages = '';
                $i = 1;
                $conData = 0;

                $insert = 'REPLACE INTO rpt_' . strtolower ($aAdditionalTables['ADD_TAB_CLASS_NAME']) . ' (';

                $query = '';
                $swHead = false;

                while (($aAux = fgetcsv ($oFile, 4096, $_POST['form']['CSV_DELIMITER'])) !== false) {

                    if ( !is_null ($aAux[0]) )
                    {
                        if ( count ($aAdditionalTables['FIELDS']) > count ($aAux) )
                        {
                            $this->success = false;
                            $this->message = 'INVALID_FILE INCORRECT NUMBER OF COLUMNS';
                            return 0;
                        }

                        if ( $i == 1 )
                        {
                            $j = 0;
                            foreach ($aAdditionalTables['FIELDS'] as $aField) {

                                $insert .= $aField['FLD_NAME'] . ', ';
                                if ( $aField['FLD_NAME'] === $aAux[$j] )
                                {
                                    $swHead = true;
                                }
                                $j ++;
                            }
                            $insert = substr ($insert, 0, -2);
                            $insert .= ') VALUES ';
                        }
                        if ( $swHead == false )
                        {
                            $queryRow = '(';
                            $j = 0;

                            foreach ($aAdditionalTables['FIELDS'] as $aField) {
                                $conData++;
                                $temp = (array_key_exists ($j, $aAux)) ? '"' . addslashes (stripslashes (utf8_encode ($aAux[$j]))) . '"' : '""';
                                if ( $temp == '' )
                                {
                                    switch ($aField['FLD_TYPE']) {
                                        case 'DATE':
                                        case 'TIMESTAMP':
                                            $temp = 'NULL';
                                            break;
                                    }
                                }
                                $j ++;
                                $queryRow .= $temp . ',';
                            }
                            $query .= substr ($queryRow, 0, -1) . '),';

                            try {
                                if ( $conData == $countRow )
                                {
                                    $query = substr ($query, 0, -1);
                                    executeQuery ($insert . $query . ';', $aAdditionalTables['DBS_UID']);
                                    $query = '';
                                    $conData = 0;
                                }
                            } catch (Exception $oError) {
                                $sErrorMessages .= 'ID_ERROR_INSERT_LINE' . ': ' . 'ID_LINE' . ' ' . $i . '. ';
                            }
                        }
                        else
                        {
                            $swHead = false;
                        }
                        $i ++;
                    }
                }
                fclose ($oFile);
                if ( $conData > 0 )
                {
                    $query = substr ($query, 0, -1);

                    $this->objMysql->_query ($insert . $query . ';');
                }
            }
            if ( $sErrorMessages != '' )
            {
                $this->success = false;
                $this->message = $sErrorMessages;
            }
            else
            {
                $this->success = true;
                $this->message = $filename . 'IMPORTED_SUCCESSFULLY';
            }
        }
        else
        {
            $sMessage = 'ID_UPLOAD_VALID_CSV_FILE';
            $this->success = false;
            $this->message = $sMessage;
        }
    }

    /**
     * export a pm tables record to CSV
     *
     * @param string $httpData->id
     */
    public function exportCSV ($httpData)
    {
        $result = new StdClass();
        try {
            $link = '';
            $size = '';
            $bytesSaved = 0;
            $oAdditionalTables = new AdditionalTables();
            $aAdditionalTables = $oAdditionalTables->load ($httpData['ADD_TAB_UID'], true);

            $sDelimiter = $httpData['CSV_DELIMITER'];
            $resultData = $oAdditionalTables->getAllData ($httpData['ADD_TAB_UID'], null, null, false);

            $rows = $resultData['rows'];
            $PUBLIC_ROOT_PATH = PATH_DATA_PUBLIC . 'csv' . PATH_SEP;
            $filenameOnly = "rpt_" . strtolower ($aAdditionalTables['ADD_TAB_CLASS_NAME'] . "_" . date ("Y-m-d") . '_' . date ("Hi") . ".csv");
            $filename = $PUBLIC_ROOT_PATH . $filenameOnly;

            $fp = fopen ($filename, "wb");
            $swColumns = true;
            foreach ($rows as $cols) {
                $SDATA = "";
                $header = "";
                $cnt = $cntC = count ($cols);
                foreach ($cols as $key => $val) {
                    if ( $swColumns )
                    {
                        $header .= $key;
                        if ( --$cntC > 0 )
                        {
                            $header .= $sDelimiter;
                        }
                        else
                        {
                            $header .= "\n";
                            $bytesSaved += fwrite ($fp, $header);
                            $swColumns = false;
                        }
                    }
                    $SDATA .= addslashes ($val);
                    if ( --$cnt > 0 )
                    {
                        $SDATA .= $sDelimiter;
                    }
                }
                $SDATA .= "\n";
                $bytesSaved += fwrite ($fp, $SDATA);
            }
            fclose ($fp);
            // $filenameLink = "pmTables/streamExported?f=$filenameOnly";
            $filenameLink = "streamExported?f=$filenameOnly";
            $size = round (($bytesSaved / 1024), 2) . " Kb";
            $filename = $filenameOnly;
            $link = $filenameLink;
            $result->success = true;
            $result->filename = $filenameOnly;
            $result->link = $link;
            $result->message = "Generated file: $filenameOnly, size: $size";
        } catch (Exception $e) {
            $result->success = false;
            $result->message = $e->getMessage ();
        }

        return $result;
    }

    /**
     * import a pm table
     *
     * @param string $httpData->id
     */
    public function import ($httpData, $arrFiles)
    {
        define ('ERROR_PM_TABLES_OVERWRITE', 1);
        define ('ERROR_PROCESS_NOT_EXIST', 2);
        define ('ERROR_RP_TABLES_OVERWRITE', 3);
        define ('ERROR_NO_REPORT_TABLE', 4);
        define ('ERROR_OVERWRITE_RELATED_PROCESS', 5);
        $fromAdmin = false;
        if ( isset ($httpData["form"]["TYPE_TABLE"]) && !empty ($httpData["form"]["TYPE_TABLE"]) )
        {
            if ( $httpData["form"]["TYPE_TABLE"] == 'admin' )
            {
                $fromAdmin = true;
            }
        }

        try {
            $result = new stdClass();
            $errors = '';
            $fromConfirm = false;
            $overWrite = isset ($arrFiles['form']['OVERWRITE']) ? true : false;
            if ( isset ($_POST["form"]["FROM_CONFIRM"]) && !empty ($_POST["form"]["FROM_CONFIRM"]) )
            {
                $fromConfirm = $_POST["form"]["FROM_CONFIRM"];
                $_FILES['form'] = $_SESSION['FILES_FORM'];
            }

            //save the file
            if ( $arrFiles['FileUpload2']['error'] !== 0 )
            {
                throw new Exception ('ID_PMTABLE_UPLOADING_FILE_PROBLEM');
            }

            $_SESSION['FILES_FORM'] = $arrFiles['FileUpload2'];

            $PUBLIC_ROOT_PATH = PATH_DATA_PUBLIC . 'csv' . PATH_SEP;
            $filename = $arrFiles['FileUpload2']['name'];
            $tempName = $arrFiles['FileUpload2']['tmp_name'];

            if ( !$fromConfirm )
            {
                (new \BusinessModel\FileUpload())->uploadFile ($tempName, $PUBLIC_ROOT_PATH, $filename);
            }
            if ( $fromConfirm == 'clear' )
            {
                $fromConfirm = true;
            }

            $fileContent = file_get_contents ($PUBLIC_ROOT_PATH . $filename);

            if ( strpos ($fileContent, '-----== ProcessMaker Open Source Private Tables ==-----') === false )
            {
                $result->success = false;
                $result->errorType = 'notice';
                $result->message = $filename . 'ID_PMTABLE_INVALID_FILE';
                return $result;
            }

            $currentProUid = '';
            if ( isset ($_POST["form"]["PRO_UID_HELP"]) && !empty ($_POST["form"]["PRO_UID_HELP"]) )
            {
                $currentProUid = $_POST["form"]["PRO_UID_HELP"];
            }
            else
            {
                if ( isset ($httpData["form"]["PRO_UID"]) && !empty ($httpData["form"]["PRO_UID"]) )
                {
                    $currentProUid = $httpData["form"]["PRO_UID"];

                    $_SESSION['PROCESS'] = $currentProUid;
                }
                else
                {
                    $currentProUid = $_SESSION['PROCESS'];
                }
            }

            //Get Additional Tables
            $arrayTableSchema = [];
            $arrayTableData = [];
            $f = fopen ($PUBLIC_ROOT_PATH . $filename, 'rb');
            $fdata = intval (fread ($f, 9));
            $type = fread ($f, $fdata);

            while (!feof ($f)) {

                switch ($type) {
                    case '@META':
                        $fdata = intval (fread ($f, 9));
                        break;
                    case '@SCHEMA':
                        $fdataUid = intval (fread ($f, 9));
                        $fdata = intval (fread ($f, 9));
                        $schema = fread ($f, $fdata);
                        $arrayTableSchema[] = unserialize ($schema);
                        break;
                    case '@DATA':
                        $fdata = intval (fread ($f, 9));
                        $tableName = fread ($f, $fdata);
                        $fdata = intval (fread ($f, 9));
                        if ( $fdata > 0 )
                        {
                            $data = fread ($f, $fdata);
                            $arrayTableData[$tableName] = unserialize ($data);
                        }
                        break;
                }
                $fdata = intval (fread ($f, 9));
                if ( $fdata > 0 )
                {
                    $type = fread ($f, $fdata);
                }
                else
                {
                    break;
                }
            }
            fclose ($f);

            //First Validate the file
            $reportTable = new \BusinessModel\ReportTable();
            $arrayOverwrite = array();
            $arrayRelated = array();
            $arrayMessage = array();
            $validationType = 0;
            if ( !$fromConfirm )
            {
                $aErrors = $reportTable->checkPmtFileThrowErrors (
                        $arrayTableSchema, $currentProUid, $fromAdmin, $overWrite, $_POST['form']['PRO_UID']
                );

                $countC = 0;
                $countM = 0;
                $countI = 0;
                foreach ($aErrors as $row) {
                    if ( $row['ERROR_TYPE'] == ERROR_PM_TABLES_OVERWRITE || $row['ERROR_TYPE'] == ERROR_RP_TABLES_OVERWRITE )
                    {
                        $arrayOverwrite[$countC] = $row;
                        $countC++;
                    }
                    else
                    {
                        if ( $row['ERROR_TYPE'] == ERROR_OVERWRITE_RELATED_PROCESS )
                        {
                            $arrayRelated[$countI] = $row;
                            $countI++;
                        }
                        else
                        {
                            $arrayMessage[$countM] = $row;
                            $countM++;
                        }
                    }
                }
                if ( sizeof ($aErrors) )
                {
                    $validationType = 1; //Yes no
                    throw new Exception ('ID_PMTABLE_IMPORT_WITH_ERRORS');
                }
            }

            //Then create the tables
            if ( isset ($_POST["form"]["TABLES_OF_NO"]) )
            {
                $arrayOfNo = $_POST["form"]["TABLES_OF_NO"];
                $arrayOfNew = $_POST["form"]["TABLES_OF_NEW"];
                $aTablesCreateNew = explode ('|', $arrayOfNew);
                $aTablesNoCreate = explode ('|', $arrayOfNo);
                $errors = $reportTable->createStructureOfTables (
                        $arrayTableSchema, $arrayTableData, $currentProUid, $fromAdmin, true, $aTablesNoCreate, $aTablesCreateNew
                );
            }
            else
            {
                $errors = $reportTable->createStructureOfTables (
                        $arrayTableSchema, $arrayTableData, $currentProUid, $fromAdmin, true
                );
            }


            if ( $errors == '' )
            {
                $result->success = true;
                $msg = 'ID_DONE';
            }
            else
            {
                $result->success = false;
                $result->errorType = 'warning';
                $msg = 'ID_PMTABLE_IMPORT_WITH_ERRORS' . "\n\n" . $errors;
            }
            $result->message = $msg;
        } catch (Exception $e) {
            $result = new stdClass();
            $result->fromAdmin = $fromAdmin;
            $result->arrayMessage = $arrayMessage;
            $result->arrayRelated = $arrayRelated;
            $result->arrayOverwrite = $arrayOverwrite;
            $result->validationType = $validationType;
            $result->errorType = 'error';
            $result->buildResult = ob_get_contents ();
            ob_end_clean ();
            $result->success = false;
            // if it is a propel exception message
            if ( preg_match ('/(.*)\s\[(.*):\s(.*)\]\s\[(.*):\s(.*)\]/', $e->getMessage (), $match) )
            {
                $result->message = $match[3];
                $result->type = 'ID_ERROR';
            }
            else
            {
                $result->message = $e->getMessage ();
                $result->type = 'ID_EXCEPTION';
            }
        }
        return $result;
    }

    /**
     * Export PM tables
     *
     * @author : Erik Amaru Ortiz <aortiz.erik@gmail.com>
     */
    public function export ($httpData)
    {
        $at = new AdditionalTables();
        $tablesToExport = json_decode (stripslashes ($httpData['rows']));
        try {
            $result = new stdClass();
            $EXPORT_TRACEBACK = array();
            $META = " \n-----== ProcessMaker Open Source Private Tables ==-----\n" . " @Ver: 1.0 Oct-2009\n" . " EasyFlow version: 1.0\n" . " -------------------------------------------------------\n" . " @Export Date: " . date ("l jS \of F Y h:i:s A") . "\n" . " @Server address: " . getenv ('SERVER_NAME') . " (" . getenv ('SERVER_ADDR') . ")\n" . "\n\n";

            foreach ($tablesToExport as $table) {

                $tableRecord = $at->load ($table->ADD_TAB_UID);
                $tableData = $at->getAllData ($table->ADD_TAB_UID, null, null, false);

                $table->ADD_TAB_NAME = $tableRecord['ADD_TAB_CLASS_NAME'];
                array_push ($EXPORT_TRACEBACK, array('uid' => $table->ADD_TAB_UID, 'name' => $table->ADD_TAB_NAME, 'num_regs' => $tableData['count'], 'schema' => $table->_SCHEMA ? 'yes' : 'no', 'data' => $table->_DATA ? 'yes' : 'no'
                ));
            }
            $sTrace = "TABLE UID                        TABLE NAME\tREGS\tSCHEMA\tDATA\n";
            foreach ($EXPORT_TRACEBACK as $row) {
                $sTrace .= "{$row['uid']}\t{$row['name']}\t\t{$row['num_regs']}\t{$row['schema']}\t{$row['data']}\n";
            }
            $META .= $sTrace;
            ///////////////EXPORT PROCESS
            $PUBLIC_ROOT_PATH = PATH_DATA_PUBLIC . 'csv' . PATH_SEP;
            $filenameOnly = strtolower ($tableRecord['ADD_TAB_CLASS_NAME'] . "_" . date ("Y-m-d") . '_' . date ("Hi") . ".pmt");
            $filename = $PUBLIC_ROOT_PATH . $filenameOnly;
            $fp = fopen ($filename, "wb");
            $bytesSaved = 0;
            $bufferType = '@META';
            $fsData = sprintf ("%09d", strlen ($META));
            $fsbufferType = sprintf ("%09d", strlen ($bufferType));
            $bytesSaved += fwrite ($fp, $fsbufferType); //writing the size of $oData
            $bytesSaved += fwrite ($fp, $bufferType); //writing the $oData
            $bytesSaved += fwrite ($fp, $fsData); //writing the size of $oData
            $bytesSaved += fwrite ($fp, $META); //writing the $oData
            foreach ($tablesToExport as $table) {
                if ( $table->_SCHEMA )
                {
                    $oAdditionalTables = new AdditionalTables();
                    $aData = $oAdditionalTables->load ($table->ADD_TAB_UID, true);
                    $bufferType = '@SCHEMA';
                    $SDATA = serialize ($aData);
                    $fsUid = sprintf ("%09d", strlen ($table->ADD_TAB_UID));
                    $fsData = sprintf ("%09d", strlen ($SDATA));
                    $fsbufferType = sprintf ("%09d", strlen ($bufferType));
                    $bytesSaved += fwrite ($fp, $fsbufferType); //writing the size of $oData
                    $bytesSaved += fwrite ($fp, $bufferType); //writing the $oData
                    $bytesSaved += fwrite ($fp, $fsUid); //writing the size of xml file
                    $bytesSaved += fwrite ($fp, $table->ADD_TAB_UID); //writing the xmlfile
                    $bytesSaved += fwrite ($fp, $fsData); //writing the size of xml file
                    $bytesSaved += fwrite ($fp, $SDATA); //writing the xmlfile
                }
                if ( $table->_DATA )
                {
                    //export data
                    $oAdditionalTables = new additionalTables();
                    $tableData = $oAdditionalTables->getAllData ($table->ADD_TAB_UID, null, null, false);
                    $SDATA = serialize ($tableData['rows']);
                    $bufferType = '@DATA';
                    $fsbufferType = sprintf ("%09d", strlen ($bufferType));
                    $fsTableName = sprintf ("%09d", strlen ($table->ADD_TAB_NAME));
                    $fsData = sprintf ("%09d", strlen ($SDATA));
                    $bytesSaved += fwrite ($fp, $fsbufferType); //writing type size
                    $bytesSaved += fwrite ($fp, $bufferType); //writing type
                    $bytesSaved += fwrite ($fp, $fsTableName); //writing the size of xml file
                    $bytesSaved += fwrite ($fp, $table->ADD_TAB_NAME); //writing the xmlfile
                    $bytesSaved += fwrite ($fp, $fsData); //writing the size of xml file
                    $bytesSaved += fwrite ($fp, $SDATA); //writing the xmlfile
                }
                //G::auditLog ("ExportTable", $table->ADD_TAB_NAME . " (" . $table->ADD_TAB_UID . ") ");
            }
            fclose ($fp);
            $filenameLink = "pmTables/streamExported?f=$filenameOnly";
            $size = round (($bytesSaved / 1024), 2) . " Kb";
            $filename = $filenameOnly;
            $link = $filenameLink;
            $result->success = true;
            $result->filename = $filenameOnly;
            $result->link = $link;
            $result->message = "Generated file: $filenameOnly, size: $size";
        } catch (Exception $e) {
            $result = new stdClass();
            $result->success = false;
            $result->message = $e->getMessage ();
        }
        return $result;
    }

    public function exportList ($ids = array())
    {
        $sql = "SELECT ADD_TAB_UID, ADD_TAB_CLASS_NAME, ADD_TAB_DESCRIPTION FROM report_tables.additional_tables";
        $arrParameters = [];

        $uids = explode (',', $ids);
        foreach ($uids as $UID) {
            if ( !isset ($CC) )
            {
                $sql .= " WHERE ADD_TAB_UID = ?";
                $arrParameters[] = $UID;
            }
            else
            {
                $sql .= " OR ADD_TAB_UID = ?";
                $arrParameters[] = $UID;
            }
        }

        $sql .= " AND ADD_TAB_UID != ''";

        $results = $this->objMysql->_query ($sql, $arrParameters);

        $addTables = array();
        foreach ($results as $result) {
            $addTables[] = $result;
        }
        return $addTables;
    }

    /**
     * Get all dynaform variables
     *
     * @param $sProcessUID
     */
    public function getDynaformVariables ($sProcessUID, $excludeFieldsList, $allowed = true, $option = "VARIABLE")
    {
        $dynaformVariables = array();

        $sql = "SELECT f.* FROM workflow.`step` s
                inner join workflow.step_fields SF on SF.step_id = s.STEP_UID
                INNER JOIN workflow.fields f ON f.field_id = sf.field_id
                 WHERE s.`STEP_TYPE_OBJ` = 'DYNAFORM' AND s.PRO_UID = ?
                 GROUP BY f.field_id";
        $results = $this->objMysql->_query ($sql, [$sProcessUID]);

        foreach ($results as $result) {

            if ( $allowed )
            {
                if ( isset ($column['type']) && !in_array ($column['type'], $excludeFieldsList) )
                {
                    switch ($option) {
                        case "VARIABLE":
                            if ( array_key_exists ("variable", $column) )
                            {
                                if ( $column["variable"] != "" )
                                {
                                    $dynaformVariables[] = $column["variable"];
                                }
                            }
                            break;
                        case "DATA":
                            $dynaformVariables[] = $column;
                            break;
                    }
                }
            }
            else
            {
                $results2 = $this->objMysql->_select ("workflow.workflow_variables", [], ["field_id" => $result['field_id']]);

                if ( isset ($results2[0]) && !empty ($results2[0]) )
                {
                    $dynaformVariables[] = $results2[0]['variable_name'];
                }
                else
                {
                    $dynaformVariables[] = $result['field_identifier'];
                }
            }
        }

        return $dynaformVariables;
    }

}
