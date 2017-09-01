<?php

/**
 * PMScript - PMScript class
 */

/**
 * PMScript - PMScript class
 *
 * @copyright 2007 COLOSA
 * @package workflow.engine.ProcessMaker
 */
class ScriptFunctions
{

    /**
     * Original fields
     */
    public $aOriginalFields = array();

    /**
     * Fields to use
     */
    public $aFields = array();

    /**
     * Script
     */
    public $sScript = '';

    /**
     * Error has happened?
     */
    public $bError = false;

    /**
     * Affected fields
     */
    public $affected_fields;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Constructor of the class PMScript
     *
     * @return void
     */
    public function PMScript ()
    {
        $this->aFields['__ERROR__'] = 'none';
    }

    /**
     * Set the fields to use
     *
     * @param array $aFields
     * @return void
     */
    public function setFields ($aFields = array())
    {
        if ( !is_array ($aFields) )
        {
            $aFields = array();
        }
        $this->aOriginalFields = $this->aFields = $aFields;
    }

    /**
     * Set the current script
     *
     * @param string $sScript
     * @return void
     */
    public function setScript ($sScript = '')
    {
        if ( !defined ("T_ML_COMMENT") )
        {
            define ("T_ML_COMMENT", T_COMMENT);
        }
        else
        {
            if ( !defined ("T_DOC_COMMENT") )
            {
                define ("T_DOC_COMMENT", T_ML_COMMENT);
            }
        }

        $script = "<?php " . $sScript;
        $tokens = token_get_all ($script);
        $result = "";

        foreach ($tokens as $token) {
            if ( is_string ($token) )
            {
                $result .= $token;
            }
            else
            {
                list($id, $text) = $token;

                switch ($id) {
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                    case T_COMMENT:
                    case T_ML_COMMENT:  //we've defined this
                    case T_DOC_COMMENT: //and this
                        if ( $text != '<?php ' && $text != '<?php' && $text != '<? ' && $text != '<?' && $text != '<% ' && $text != '<%' )
                        {
                            $result .= $text;
                        }
                        break;
                    default:
                        $result .= $text;
                        break;
                }
            }
        }

        $this->sScript = trim ($result);
    }

    public function executeAndCatchErrors ($sScript, $sCode)
    {
        try {
            $sScript = str_replace ("<?php", "", $sScript);

            set_error_handler ('handleErrors');
            $_SESSION['_CODE_'] = $sCode;
            eval ($sScript);
            $this->evaluateVariable ();
            unset ($_SESSION['_CODE_']);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Execute the current script
     *
     * @return void
     */
    public function execute ()
    {
        $sScript = "";
        $iAux = 0;
        $iOcurrences = preg_match_all ('/\@(?:([\@\%\#\?\$\=])([a-zA-Z\_]\w*)|([a-zA-Z\_][\w\-\>\:]*)\(((?:[^\\\\\)]' . '*(?:[\\\\][\w\W])?)*)\))((?:\s*\[[\'"]?\w+[\'"]?\])+)?/', $this->sScript, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

        if ( $iOcurrences )
        {
            for ($i = 0; $i < $iOcurrences; $i ++) {

                $bEqual = false;
                $sAux = substr ($this->sScript, $iAux, $aMatch[0][$i][1] - $iAux);

                if ( !$bEqual )
                {
                    if ( strpos ($sAux, "==") !== false || strpos ($sAux, "!=") !== false || strpos ($sAux, ">") !== false || strpos ($sAux, "<") !== false || strpos ($sAux, ">=") !== false || strpos ($sAux, "<=") !== false || strpos ($sAux, "<>") !== false || strpos ($sAux, "===") !== false || strpos ($sAux, "!==") !== false )
                    {
                        $bEqual = false;
                    }
                    else
                    {
                        if ( strpos ($sAux, "=") !== false || strpos ($sAux, "+=") !== false || strpos ($sAux, "-=") !== false || strpos ($sAux, "*=") !== false || strpos ($sAux, "/=") !== false || strpos ($sAux, "%=") !== false || strpos ($sAux, ".=") !== false )
                        {
                            $bEqual = true;
                        }
                    }
                }
                if ( $bEqual )
                {
                    if ( strpos ($sAux, ';') !== false )
                    {
                        $bEqual = false;
                    }
                }
                if ( $bEqual )
                {
                    if ( !isset ($aMatch[5][$i][0]) )
                    {
                        eval ("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = null; }");
                    }
                    else
                    {
                        eval ("if (!isset(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")) { \$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . " = null; }");
                    }
                }

                $sScript .= $sAux;
                $iAux = $aMatch[0][$i][1] + strlen ($aMatch[0][$i][0]);

                switch ($aMatch[1][$i][0]) {
                    case '@':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToString(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToString(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '%':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToInteger(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToInteger(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '#':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToFloat(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToFloat(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '?':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToUrl(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToUrl(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '$':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmSqlEscape(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmSqlEscape(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '=':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                }
                $this->affected_fields[] = $aMatch[2][$i][0];
            }
        }
        $sScript .= substr ($this->sScript, $iAux);
        $sScript = "try {\n" . $sScript . "\n} catch (Exception \$oException) {\n " . " \$this->aFields['__ERROR__'] = utf8_encode(\$oException->getMessage());\n}";
        //echo '<pre>-->'; print_r($this->aFields); echo '<---</pre>';

        $this->executeAndCatchErrors ($sScript, $this->sScript);

        $this->aFields["__VAR_CHANGED__"] = implode (",", $this->affected_fields);
    }

    /**
     * Evaluate the current script
     *
     * @return void
     */
    public function evaluate ()
    {
        $bResult = null;
        $sScript = '';
        $iAux = 0;
        $bEqual = false;
        $variableIsDefined = true;
        $iOcurrences = preg_match_all ('/\@(?:([\@\%\#\?\$\=])([a-zA-Z\_]\w*)|([a-zA-Z\_][\w\-\>\:]*)\(((?:[^\\\\\)]' . '*(?:[\\\\][\w\W])?)*)\))((?:\s*\[[\'"]?\w+[\'"]?\])+)?/', $this->sScript, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        if ( $iOcurrences )
        {
            for ($i = 0; $i < $iOcurrences; $i ++) {
                // if the variables for that condition has not been previously defined then $variableIsDefined
                // is set to false
                if ( !isset ($this->aFields[$aMatch[2][$i][0]]) && !isset ($aMatch[5][$i][0]) )
                {
                    $this->aFields[$aMatch[2][$i][0]] = '';
                }
                else
                {
                    if ( !isset ($this->aFields[$aMatch[2][$i][0]]) )
                    {
                        eval ("\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . " = '';");
                    }
                    else
                    {
                        if ( isset ($aMatch[5][$i][0]) )
                        {
                            eval ("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . ")) {\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . " = '';}");
                        }
                        else
                        {
                            eval ("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) {\$this->aFields['" . $aMatch[2][$i][0] . "'] = '';}");
                        }
                    }
                }
                $sAux = substr ($this->sScript, $iAux, $aMatch[0][$i][1] - $iAux);
                if ( !$bEqual )
                {
                    if ( strpos ($sAux, '=') !== false )
                    {
                        $bEqual = true;
                    }
                }
                if ( $bEqual )
                {
                    if ( strpos ($sAux, ';') !== false )
                    {
                        $bEqual = false;
                    }
                }
                $sScript .= $sAux;
                $iAux = $aMatch[0][$i][1] + strlen ($aMatch[0][$i][0]);
                switch ($aMatch[1][$i][0]) {
                    case '@':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToString(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToString(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '%':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToInteger(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToInteger(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '#':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToFloat(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToFloat(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '?':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmToUrl(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmToUrl(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '$':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "pmSqlEscape(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            }
                            else
                            {
                                $sScript .= "pmSqlEscape(\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '=':
                        if ( $bEqual )
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        else
                        {
                            if ( !isset ($aMatch[5][$i][0]) )
                            {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            }
                            else
                            {
                                $sScript .= "\$this->aFields" . (isset ($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                }
            }
        }
        $sScript .= substr ($this->sScript, $iAux);
        if ( preg_match ('/\b(or|and|xor)\b/i', $sScript) )
        {
            $sScript = "( " . $sScript . " )";
        }
        $sScript = '$bResult = ' . $sScript . ';';
        // checks if the syntax is valid or if the variables in that condition has been previously defined
        if ( $variableIsDefined )
        {
            $this->bError = false;
            eval ($sScript);
        }
        else
        {
            // echo "<script> alert('".G::loadTranslation('MSG_CONDITION_NOT_DEFINED')."'); </script>";

            $this->bError = true;
        }
        return $bResult;
    }

    public function evaluateVariable ()
    {

        $searchTypes = array('checkgroup', 'dropdown', 'suggest');
        $pmTablesProxy = new reportTableCSV();
        $variableModule = new \BusinessModel\StepVariable();
        $processVariables = $pmTablesProxy->getDynaformVariables ($_SESSION['PROCESS'], $searchTypes, false);

        $this->affected_fields[] = "location";
        $this->affected_fields[] = "batch";

        $this->aFields['location'][1] = array(0 => "test1", 1 => "location");
        $this->aFields['batch'][1] = array(0 => "test2", 1 => "batch");

        $variables = $this->affected_fields;
        $variables = (is_array ($variables)) ? array_unique ($variables) : $variables;
        $newFields = array();
        $arrayValues = array();
        $arrayLabels = array();

        if ( is_array ($variables) && is_array ($processVariables) )
        {
            foreach ($variables as $var) {
                if ( strpos ($var, '_label') === false )
                {
                    if ( in_array ($var, $processVariables) )
                    {
                        if ( isset ($this->aFields[$var]) && is_array ($this->aFields[$var][1]) )
                        {
                            $varLabel = $var . '_label';
                            $arrayValue = $this->aFields[$var];
                            if ( is_array ($arrayValue) && sizeof ($arrayValue) )
                            {
                                foreach ($arrayValue as $val) {
                                    if ( is_array ($val) )
                                    {
                                        $val = array_values ($val);
                                        $arrayValues[] = $val[0];
                                        $arrayLabels[] = $val[1];
                                    }
                                }

                                if ( sizeof ($arrayLabels) )
                                {
                                    $varInfo = $variableModule->getVariableTypeByName ($_SESSION['PROCESS'], $var);

                                    if ( is_array ($varInfo) && sizeof ($varInfo) )
                                    {
                                        $varType = $varInfo['validation_type'];
                                        switch ($varType) {
                                            case 'array':
                                                $arrayLabels = '["' . implode ('","', $arrayLabels) . '"]';
                                                $newFields[$var] = $arrayValues;
                                                $newFields[$varLabel] = $arrayLabels;
                                                break;
                                            case 'string':
                                                $newFields[$var] = $arrayValues[0];
                                                $newFields[$varLabel] = $arrayLabels[0];
                                                break;
                                        }
                                        $this->affected_fields[] = $varLabel;
                                        $this->aFields = array_merge ($this->aFields, $newFields);
                                        unset ($newFields);
                                        unset ($arrayValues);
                                        unset ($arrayLabels);
                                    }
                                }
                            }
                        }

                        if ( isset ($this->aFields[$var]) && is_string ($this->aFields[$var]) )
                        {
                            $varInfo = $variableModule->getVariableTypeByName ($_SESSION['PROCESS'], $var);
                            $options = json_decode ($varInfo["accepted_values"]);

                            $no = count ($options);

                            for ($io = 0; $io < $no; $io++) {
                                if ( $options[$io]->value === $this->aFields[$var] )
                                {
                                    $this->aFields[$var . "_label"] = $options[$io]->label;
                                }
                            }

                            if ( $varInfo["db_connection"] !== "" && $varInfo["db_connection"] !== "none" && $varInfo["variation_sql"] !== "" )
                            {
                                try {
                                    $sql = (new BusinessModel\Cases())->replaceDataField ($varInfo['variation_sql'], $this->aFields);
                                    $results = $this->objMysql->_query ($sql);

                                    foreach ($results as $row) {
                                        if ( $row[0] === $this->aFields[$var] )
                                        {
                                            $this->aFields[$var . "_label"] = isset ($row[1]) ? $row[1] : $row[0];
                                        }
                                    }
                                } catch (Exception $ex) {
                                    
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}

//Start - Private functions

/**
 * Convert to string
 *
 * @param variant $vValue
 * @return string
 */
function pmToString ($vValue)
{
    return (string) $vValue;
}

/**
 * Convert to integer
 *
 * @param variant $vValue
 * @return integer
 */
function pmToInteger ($vValue)
{
    return (int) $vValue;
}

/**
 * Convert to float
 *
 * @param variant $vValue
 * @return float
 */
function pmToFloat ($vValue)
{
    return (float) $vValue;
}

/**
 * Convert to Url
 *
 * @param variant $vValue
 * @return url
 */
function pmToUrl ($vValue)
{
    return urlencode ($vValue);
}

/**
 * Convert to data base escaped string
 *
 * @param variant $vValue
 * @return string
 */
function pmSqlEscape ($vValue)
{
    return mysql_real_escape_string ($vValue);
}

/*
 * Convert to data base escaped string
 * @param string $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return void
 */

function handleErrors ($errno, $errstr, $errfile, $errline)
{
    if ( $errno != '' && ($errno != 8) && ($errno != 2048) )
    {
        if ( isset ($_SESSION['_CODE_']) )
        {
            $sCode = $_SESSION['_CODE_'];
            unset ($_SESSION['_CODE_']);
            registerError (1, $errstr, $errline - 1, $sCode);
        }
    }
}

/*
 * Handle Fatal Errors
 * @param variant $buffer
 * @return buffer
 */

function handleFatalErrors ($buffer)
{
    if ( preg_match ('/(error<\/b>:)(.+)(<br)/', $buffer, $regs) )
    {
        $err = preg_replace ('/<.*?>/', '', $regs[2]);
        $aAux = explode (' in ', $err);
        $sCode = isset ($_SESSION['_CODE_']) ? $_SESSION['_CODE_'] : null;
        unset ($_SESSION['_CODE_']);
        registerError (2, $aAux[0], 0, $sCode);
    }
    return $buffer;
}

/*
 * Register Error
 * @param string $iType
 * @param string $sError
 * @param string $iLine
 * @param string $sCode
 * @return void
 */

function registerError ($iType, $sError, $iLine, $sCode)
{
    $sType = ($iType == 1 ? 'ERROR' : 'FATAL');
    $errorMessage = $sError . ($iLine > 0 ? ' (line ' . $iLine . ')' : '') . ':<br /><br />' . $sCode;

    return array("Type" => $sType, "error" => $errorMessage);
}
