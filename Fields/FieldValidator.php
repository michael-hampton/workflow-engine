<?php

class FieldValidator
{

    private $stepId;

    /**
     * 
     * @param type $stepId
     */
    public function __construct ($stepId)
    {
        $this->stepId = $stepId;
    }

    /**
     * Checks if value of an variable is integer number
     *
     * @param int $num The variable being evaluated
     *
     * @return bool Returns true if $num is an integer, false otherwise
     */
    public function isInt ($num)
    {
        $num = $num . "";
        return (preg_match ("/^[\+\-]?(?:0|[1-9]\d*)$/", $num)) ? true : false;
    }

    /**
     * Checks if value of an variable is real number
     *
     * @param float $num The variable being evaluated
     *
     * @return bool Returns true if $num is an real, false otherwise
     */
    public function isReal ($num)
    {
        $num = $num . "";
        return (preg_match ("/^[\+\-]?(?:0|[1-9]\d*)(?:\.\d+)?$/", $num)) ? true : false;
    }

    /**
     * Checks if value of an variable is boolean
     *
     * @param bool $bool The variable being evaluated
     *
     * @return bool Returns true if $bool is an boolean, false otherwise
     */
    public function isBool ($bool)
    {
        if ( is_bool ($bool) === true )
        {
            return true;
        }
        $bool = $bool . "";
        return (preg_match ("/^(?:true|false)$/i", $bool)) ? true : false;
    }

    /**
     * Checks if value of an variable have valid URL format
     *
     * @param string $url The variable being evaluated
     *
     * @return bool Returns true if $bool have valid URL format, false otherwise
     */
    public function isUrl ($url)
    {
        return (preg_match ("/(((^https?)|(^ftp)):\/\/([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\/+\\@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i", $url)) ? true : false;
    }

    /**
     * Checks if value of an variable have valid email format
     *
     * @param string $email The variable being evaluated
     *
     * @return bool Returns true if $bool have valid email format, false otherwise
     */
    public function isEmail ($email)
    {
        return (preg_match ("/^(\w+)([\-+.\'][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/", $email)) ? true : false;
    }

    /**
     * Checks if value of an variable have valid IP format
     *
     * @param string $ip The variable being evaluated
     *
     * @return bool Returns true if $bool have valid IP format, false otherwise
     */
    public function isIp ($ip)
    {
        return (preg_match ("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) ? true : false;
    }

    /**
     * Validate fields
     *
     * @param array $arrayData           Fields to be validate
     * @param array $arrayDataValidators Validator for each field
     *
     * @return array Returns an array with key "succes" in true or false
     */
    public function validate ($arrFormData)
    {
        $objFields = new \BusinessModel\FieldFactory();
        $arrRequiredFields = $objFields->getRequiredFields (new Task ($this->stepId));

        if ( !is_array ($arrFormData) )
        {
            throw (new Exception ("Fields no is array"));
        }
        if ( !is_array ($arrRequiredFields) )
        {
            throw (new Exception ("Validators no is array"));
        }
        if ( count ($arrFormData) == 0 )
        {
            throw (new Exception ("Fields is empty"));
        }

        if ( count ($arrRequiredFields) == 0 )
        {
            //throw (new Exception ("Validators is empty"));
        }

        $arrErrorsCodes = array();
        $intCount = 0;

        if ( !empty ($arrFormData) )
        {
            foreach ($arrRequiredFields as $arrRequiredField) {

                if ( is_array ($arrRequiredField) && count ($arrRequiredField) > 0 )
                {
                    $field = $arrRequiredField['field_identifier'];

                    if ( isset ($arrRequiredField["type"]) )
                    {
                        if ( isset ($arrFormData[$field]) )
                        {
                            switch ($arrRequiredField["type"]) {
                                case "int":
                                    if ( !self::isInt ($arrFormData[$field]) )
                                    {
                                        $result["success"] = false;
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" not is an integer number"
                                        );
                                    }
                                    break;
                                case "real":
                                    if ( !self::isReal ($arrFormData[$field]) )
                                    {
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" not is an real number"
                                        );
                                    }
                                    break;
                                case "bool":
                                case "boolean":
                                    if ( !self::isBool ($arrFormData[$field]) )
                                    {
                                        $result["success"] = false;
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" not is an boolean"
                                        );
                                    }
                                    break;
                                default:
                                    //string
                                    break;
                            }
                        }
                    }

                    if ( isset ($arrRequiredField["validation"]) )
                    {
                        if ( isset ($arrFormData[$field]) )
                        {
                            switch ($arrRequiredField["validation"]) {
                                case "url":
                                    if ( !self::isUrl ($arrFormData[$field]) )
                                    {
                                        $result["success"] = false;
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" have not an valid URL format"
                                        );
                                    }
                                    break;
                                case "email":
                                    if ( !self::isEmail ($arrFormData[$field]) )
                                    {
                                        $result["success"] = false;
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" have not an valid email format"
                                        );
                                    }
                                    break;
                                case "ip":
                                    if ( !self::isIp ($arrFormData[$field]) )
                                    {
                                        $result["success"] = false;
                                        $arrErrorsCodes[$intCount] = str_replace (
                                                array("{0}"), array($field), "Field \"{0}\" have not an valid IP format"
                                        );
                                    }
                                    break;
                            }
                        }
                    }

                    if ( isset ($arrRequiredField["maxlength"]) && $arrRequiredField['maxlength'] > 0 )
                    {
                        if ( isset ($arrFormData[$field]) && (strlen ($arrFormData[$field] . "") > (int) ($arrRequiredField["maxlength"])) )
                        {
                            $result["success"] = false;
                            $arrErrorsCodes[$intCount] = str_replace (
                                    array("{0}", "{1}", "{2}"), array($field, $arrRequiredField["maxlength"], strlen ($arrFormData[$field] . "")), "Field \"{0}\" should be max {1} chars, {2} given"
                            );
                        }
                    }
                    
                     if (isset($arrRequiredField["minlength"])) {
                        if (isset($arrFormData[$field]) && !(strlen($arrFormData[$field] . "") >= (int)($arrRequiredField["minlength"]))) {
                            $result["success"] = false;
                            $arrErrorsCodes[$intCount] = str_replace(
                                array("{0}", "{1}", "{2}"),
                                array($field, $arrRequiredField["minlength"], strlen($arrFormData[$field] . "")),
                                "Field \"{0}\" should be min {1} chars, {2} given"
                            );
                        }
                    }

                    if (
                            !isset ($arrFormData[$arrRequiredField['field_identifier']]) ||
                            trim ($arrFormData[$arrRequiredField['field_identifier']]) == "" ||
                            $arrFormData[$arrRequiredField['field_identifier']] == "null" )
                    {
                        $arrErrorsCodes[$intCount]['id'] = $arrRequiredField['field_identifier'];
                        $arrErrorsCodes[$intCount]['message'] = "data_missing";
                    }

                    if ( isset ($arrRequiredField['expected_output']) && !empty ($arrRequiredField['expected_output']) )
                    {
                        if ( isset ($arrFormData[$arrRequiredField['field_identifier']]) && strtolower ($arrFormData[$arrRequiredField['field_identifier']]) != strtolower ($arrRequiredField['expected_output']) )
                        {
                            $arrErrorsCodes[$intCount]['id'] = $arrRequiredField['field_identifier'];
                            $arrErrorsCodes[$intCount]['message'] = "incorrect_data";
                        }
                    }
                }

                $intCount++;
            }
        }

        return $arrErrorsCodes;
    }

}
