 /**
     * Validate data by field definition
     *
     * @param array $arrayData                  Data
     * @param array $arrayFieldDefinition       Definition of fields
     * @param array $arrayFieldNameForException Fields for exception messages
     * @param bool  $flagValidateRequired       Validate required fields
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataNotMetFieldDefinition($arrayData, $arrayFieldDefinition, $arrayFieldNameForException, $flagValidateRequired = true)
    {
         try {
            $arrayType1 = [
                'int',
                'integer',
                'float',
                'real',
                'double',
                'bool',
                'boolean',
                'string',
                'date',
                'hour',
                'datetime'
            ];

            $arrayType2 = ['array', 'object'];

            foreach ($arrayData as $key => $value) {
                $fieldName = $key;
                $fieldValue = $value;

                if (isset($arrayFieldDefinition[$fieldName])) {

                    $arrayFieldDefinition[$fieldName]["type"] = strtolower($arrayFieldDefinition[$fieldName]["type"]);
                    $optionType = 0;
                    $optionType = ($optionType == 0 && in_array($arrayFieldDefinition[$fieldName]["type"], $arrayType1)) ? 1 : $optionType;
                    $optionType = ($optionType == 0 && in_array($arrayFieldDefinition[$fieldName]["type"], $arrayType2)) ? 2 : $optionType;

                    switch ($optionType) {
                        case 1:

                            //empty
                            if ($arrayFieldDefinition[$fieldName]['required'] === 'true' && trim($fieldValue) == '') {
                                throw new \Exception('ID_INVALID_VALUE_CAN_NOT_BE_EMPTY');
                            }

                            //defaultValues
                            if (isset($arrayFieldDefinition[$fieldName]['defaultValues']) &&
                                !empty($arrayFieldDefinition[$fieldName]['defaultValues']) &&
                                !in_array($fieldValue, $arrayFieldDefinition[$fieldName]['defaultValues'], true)
                            ) {
                                throw new \Exception('ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES');
                            }

                            $regexpDate = '[0-9]{2}[-|\/]{1}[0-9]{2}[-|\/]{1}[0-9]{4}';
                            $regexpTime = '(?:[0-1]\d|2[0-3])\:[0-5]\d\:[0-5]\d';

                            $regexpDatetime = $regexpDate . '\s' . $regexpTime;

                            switch ($arrayFieldDefinition[$fieldName]["type"]) {
                                case "date":
                                    if (!preg_match("/^" . $regexpDate . "$/", $fieldValue)) {
                                        throw new \Exception('ID_INVALID_VALUE');
                                    }
                                    break;
                                case "hour":
                                    $regexpTime = '/^' . $regexpTime . '$/';

                                    if (array_key_exists('regexp', $arrayFieldDefinition[$fieldName])) {
                                        $regexpTime = $arrayFieldDefinition[$fieldName]['regexp'];
                                    }

                                    if (!preg_match($regexpTime, $fieldValue)) {
                                        throw new \Exception('ID_INVALID_VALUE');
                                    }
                                    break;
                                case "datetime":
                                    if (!preg_match("/^" . $regexpDatetime . "$/", $fieldValue)) {
                                        throw new \Exception('ID_INVALID_VALUE');
                                    }
                                    break;
                            }


                            break;

                        case 2:
                            switch ($arrayFieldDefinition[$fieldName]["type"]) {
                                case "array":
                                    $regexpArray1 = "\s*array\s*\(";
                                    $regexpArray2 = "\)\s*";

                                    //type
                                    if (!is_array($fieldValue)) {
                                        if ($fieldValue != "" && !preg_match("/^" . $regexpArray1 . ".*" . $regexpArray2 . "$/", $fieldValue)) {
                                            throw new \Exception('ID_INVALID_VALUE_THIS_MUST_BE_ARRAY');
                                        }
                                    }

                                    //empty
                                    if (!$arrayFieldDefinition[$fieldName]["empty"]) {
                                        $arrayAux = array();

                                        if (is_array($fieldValue)) {
                                            $arrayAux = $fieldValue;
                                        }

                                        if (is_string($fieldValue) && trim($fieldValue) != '') {
                                            //eval("\$arrayAux = $fieldValue;");

                                            if (preg_match("/^" . $regexpArray1 . "(.*)" . $regexpArray2 . "$/", $fieldValue, $arrayMatch)) {
                                                if (trim($arrayMatch[1], " ,") != "") {
                                                    $arrayAux = [0];
                                                }
                                            }
                                        }

                                        if (empty($arrayAux)) {
                                            throw new \Exception('ID_INVALID_VALUE_CAN_NOT_BE_EMPTY');
                                        }
                                    }

                                    //defaultValues
                                    if (isset($arrayFieldDefinition[$fieldName]['defaultValues']) &&
                                        !empty($arrayFieldDefinition[$fieldName]['defaultValues'])
                                    ) {
                                        $arrayAux = [];

                                        if (is_array($fieldValue)) {
                                            $arrayAux = $fieldValue;
                                        }

                                        if (is_string($fieldValue) && trim($fieldValue) != '') {
                                            eval("\$arrayAux = $fieldValue;");
                                        }

                                        foreach ($arrayAux as $value) {
                                            if (!in_array($value, $arrayFieldDefinition[$fieldName]["defaultValues"], true)) {
                                                throw new \Exception('ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES');
                                            }
                                        }
                                    }
                                    break;
                            }
                            break;
                    }

                    echo $optionType;
                }

            }
        } catch(Exception $e) {
            throw $e;
        }
    }
