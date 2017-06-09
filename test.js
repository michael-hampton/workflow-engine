 $nrt = array("\n", "\r", "\t");
        $nrthtml = array("(n /)", "(r /)", "(t /)");

        $sContent = "[mike] test [lexi]";

        $strContentAux = str_replace($nrt, $nrthtml, $sContent);

        $iOcurrences = preg_match_all('/\[([^\]]+)\]/', $sContent, $arrayMatch1, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        $nl2brRecursive = true;

        if ($iOcurrences) {
            $arrayGrid = array();

            for ($i = 0; $i <= $iOcurrences - 1; $i++) {
                $arrayGrid[] = $arrayMatch1[1][$i][0];
            }

            $arrayGrid = array_unique($arrayGrid);

            $aFields = array(
                "mike" => "Mike",
                "lexi" => "Lexi"
            );

            foreach ($arrayGrid as $index => $value) {
                if ($value !== "") {
                    $grdName = $value;

                    $strContentAux1 = $strContentAux;
                    $strContentAux = null;

                    if (isset($aFields[$grdName]) && trim($aFields[$grdName]) !== "") {
                        $newValue = str_replace($nrt, $nrthtml, nl2br($aFields[$grdName]));
                        $newValue = urlencode($aFields[$grdName]);
                        $newValue = stripcslashes($aFields[$grdName]);

                        $strContentAux .= str_replace("[".$grdName."]", $newValue, $strContentAux1);

                    }

                }
            }
        }

        $strContentAux = str_replace($nrthtml, $nrt, $strContentAux);
        $sContent = $strContentAux;

       return $sContent;
