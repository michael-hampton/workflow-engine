<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OutputDocument
 *
 * @author michael.hampton
 */
class OutputDocument extends BaseOutputDocument
{

    use \BusinessModel\Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
        parent::__construct ();
    }

    public function getByUid ($sOutDocUid)
    {
        try {
            $oOutputDocument = $this->retrieveByPK ($sOutDocUid);
            if ( is_null ($oOutputDocument) )
            {
                return false;
            }
            $aFields = $oOutputDocument->toArray (BasePeer::TYPE_FIELDNAME);
            $this->fromArray ($aFields, BasePeer::TYPE_FIELDNAME);
            return $aFields;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Create the application document registry
     * @param array $aData
     * @return string
     * */
    public function create ($aData)
    {
        try {
            if ( isset ($aData['OUT_DOC_UID']) && $aData['OUT_DOC_UID'] == '' )
            {
                unset ($aData['OUT_DOC_UID']);
            }

            if ( !isset ($aData['OUT_DOC_GENERATE']) )
            {
                $aData['OUT_DOC_GENERATE'] = 'BOTH';
            }
            else
            {
                if ( $aData['OUT_DOC_GENERATE'] == '' )
                {
                    $aData['OUT_DOC_GENERATE'] = 'BOTH';
                }
            }
            $oOutputDocument = new OutputDocument();
            $oOutputDocument->loadObject ($aData);

            if ( $oOutputDocument->validate () )
            {
                if ( isset ($aData['OUT_DOC_TITLE']) )
                {
                    $oOutputDocument->setOutDocTitleContent ($aData['OUT_DOC_TITLE']);
                }
                if ( isset ($aData['OUT_DOC_DESCRIPTION']) )
                {
                    $oOutputDocument->setOutDocDescriptionContent ($aData['OUT_DOC_DESCRIPTION']);
                }
                $oOutputDocument->setOutDocFilenameContent ($aData['OUT_DOC_FILENAME']);
                if ( isset ($aData['OUT_DOC_TEMPLATE']) )
                {
                    $oOutputDocument->setOutDocTemplateContent ($aData['OUT_DOC_TEMPLATE']);
                }

                $id = $oOutputDocument->save ();

                return $id;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oOutputDocument->getValidationFailures ();
                foreach ($aValidationFailures as $message) {
                    $sMessage .= $message . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Update the application document registry
     * @param array $aData
     * @return string
     * */
    public function update ($aData)
    {
        try {
            $oOutputDocument = $this->retrieveByPK ($aData['OUT_DOC_UID']);
            if ( !is_null ($oOutputDocument) )
            {
                $oOutputDocument->fromArray ($aData, BasePeer::TYPE_FIELDNAME);
                if ( $oOutputDocument->validate () )
                {
                    if ( isset ($aData['OUT_DOC_TITLE']) )
                    {
                        $oOutputDocument->setOutDocTitleContent ($aData['OUT_DOC_TITLE']);
                    }
                    if ( isset ($aData['OUT_DOC_DESCRIPTION']) )
                    {
                        $oOutputDocument->setOutDocDescriptionContent ($aData['OUT_DOC_DESCRIPTION']);
                    }
                    if ( isset ($aData['OUT_DOC_FILENAME']) )
                    {
                        $oOutputDocument->setOutDocFilenameContent ($aData['OUT_DOC_FILENAME']);
                    }
                    if ( isset ($aData['OUT_DOC_TEMPLATE']) )
                    {
                        $oOutputDocument->setOutDocTemplateContent ($aData['OUT_DOC_TEMPLATE']);
                    }
                    $iResult = $oOutputDocument->save ();

                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oOutputDocument->getValidationFailures ();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure->getMessage () . '<br />';
                    }
                    throw (new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Remove the application document registry
     * @param array $aData
     * @return string
     * */
    public function remove ($sOutDocUid)
    {
        try {
            $oOutputDocument = $this->retrieveByPK ($sOutDocUid);

            if ( !is_null ($oOutputDocument) )
            {
                $iResult = $oOutputDocument->delete ();
                //Return
                return $iResult;
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /*
     * Generate the output document
     * @param string $sUID
     * @param array $aFields
     * @param string $sPath
     * @return variant
     */

    public function generate ($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape = false, $sTypeDocToGener = 'BOTH', $aProperties = array())
    {
        if ( ($sUID != '') && is_array ($aFields) && ($sPath != '') )
        {
            $objCases = new Cases();
            $sContent = $objCases->replaceDataField ($sContent, $aFields);

            $objFile = new FileUpload();

            $objFile->verifyPath ($sPath, true);
            //Start - Create .doc
            $oFile = fopen ($sPath . $sFilename . '.doc', 'wb');
            $size = array();
            $size["Letter"] = "216mm  279mm";
            $size["Legal"] = "216mm  357mm";
            $size["Executive"] = "184mm  267mm";
            $size["B5"] = "182mm  257mm";
            $size["Folio"] = "216mm  330mm";
            $size["A0Oversize"] = "882mm  1247mm";
            $size["A0"] = "841mm  1189mm";
            $size["A1"] = "594mm  841mm";
            $size["A2"] = "420mm  594mm";
            $size["A3"] = "297mm  420mm";
            $size["A4"] = "210mm  297mm";
            $size["A5"] = "148mm  210mm";
            $size["A6"] = "105mm  148mm";
            $size["A7"] = "74mm   105mm";
            $size["A8"] = "52mm   74mm";
            $size["A9"] = "37mm   52mm";
            $size["A10"] = "26mm   37mm";
            $size["Screenshot640"] = "640mm  480mm";
            $size["Screenshot800"] = "800mm  600mm";
            $size["Screenshot1024"] = "1024mm 768mm";
            $sizeLandscape["Letter"] = "279mm  216mm";
            $sizeLandscape["Legal"] = "357mm  216mm";
            $sizeLandscape["Executive"] = "267mm  184mm";
            $sizeLandscape["B5"] = "257mm  182mm";
            $sizeLandscape["Folio"] = "330mm  216mm";
            $sizeLandscape["A0Oversize"] = "1247mm 882mm";
            $sizeLandscape["A0"] = "1189mm 841mm";
            $sizeLandscape["A1"] = "841mm  594mm";
            $sizeLandscape["A2"] = "594mm  420mm";
            $sizeLandscape["A3"] = "420mm  297mm";
            $sizeLandscape["A4"] = "297mm  210mm";
            $sizeLandscape["A5"] = "210mm  148mm";
            $sizeLandscape["A6"] = "148mm  105mm";
            $sizeLandscape["A7"] = "105mm  74mm";
            $sizeLandscape["A8"] = "74mm   52mm";
            $sizeLandscape["A9"] = "52mm   37mm";
            $sizeLandscape["A10"] = "37mm   26mm";
            $sizeLandscape["Screenshot640"] = "480mm  640mm";
            $sizeLandscape["Screenshot800"] = "600mm  800mm";
            $sizeLandscape["Screenshot1024"] = "768mm  1024mm";
            if ( !isset ($aProperties['media']) )
            {
                $aProperties['media'] = 'Letter';
            }
            if ( $sLandscape )
            {
                $media = $sizeLandscape[$aProperties['media']];
            }
            else
            {
                $media = $size[$aProperties['media']];
            }
            $marginLeft = '15';
            if ( isset ($aProperties['margins']['left']) )
            {
                $marginLeft = $aProperties['margins']['left'];
            }
            $marginRight = '15';
            if ( isset ($aProperties['margins']['right']) )
            {
                $marginRight = $aProperties['margins']['right'];
            }
            $marginTop = '15';
            if ( isset ($aProperties['margins']['top']) )
            {
                $marginTop = $aProperties['margins']['top'];
            }
            $marginBottom = '15';
            if ( isset ($aProperties['margins']['bottom']) )
            {
                $marginBottom = $aProperties['margins']['bottom'];
            }
            fwrite ($oFile, '<html xmlns:v="urn:schemas-microsoft-com:vml"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:w="urn:schemas-microsoft-com:office:word"
            xmlns="http://www.w3.org/TR/REC-html40">
            <head>
            <meta http-equiv=Content-Type content="text/html; charset=utf-8">
            <meta name=ProgId content=Word.Document>
            <meta name=Generator content="Microsoft Word 9">
            <meta name=Originator content="Microsoft Word 9">
            <!--[if !mso]>
            <style>
            v\:* {behavior:url(#default#VML);}
            o\:* {behavior:url(#default#VML);}
            w\:* {behavior:url(#default#VML);}
            .shape {behavior:url(#default#VML);}
            </style>
            <![endif]-->
            <!--[if gte mso 9]><xml>
             <w:WordDocument>
              <w:View>Print</w:View>
              <w:DoNotHyphenateCaps/>
              <w:PunctuationKerning/>
              <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
              <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
             </w:WordDocument>
            </xml><![endif]-->
            <style>
            <!--
            @page WordSection1
             {size:' . $media . ';
             margin-left:' . $marginLeft . 'mm;
             margin-right:' . $marginRight . 'mm;
             margin-bottom:' . $marginBottom . 'mm;
             margin-top:' . $marginTop . 'mm;
             mso-header-margin:35.4pt;
             mso-footer-margin:35.4pt;
             mso-paper-source:0;}
            div.WordSection1
             {page:WordSection1;}
            -->
            </style>
            </head>
            <body>
            <div class=WordSection1>');
            fwrite ($oFile, $sContent);
            fwrite ($oFile, "\n</div></body></html>\n\n");
            fclose ($oFile);
            /* End - Create .doc */
            if ( $sTypeDocToGener == 'BOTH' || $sTypeDocToGener == 'PDF' )
            {
                $oFile = fopen ($sPath . $sFilename . '.html', 'wb');
                fwrite ($oFile, $sContent);
                fclose ($oFile);
                /* Start - Create .pdf */
                if ( isset ($aProperties['report_generator']) )
                {
                    switch ($aProperties['report_generator']) {
                        case 'TCPDF':
                            $this->generateTcpdf ($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape, $aProperties);
                            break;
                        case 'HTML2PDF':
                        default:
                            $this->generateHtml2ps_pdf ($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape, $aProperties);
                            break;
                    }
                }
                else
                {
                    $this->generateHtml2ps_pdf ($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape, $aProperties);
                }
            }
            //end if $sTypeDocToGener
            /* End - Create .pdf */
        }
        else
        {
            throw new Exception ('You tried to call to a generate method without send the Output Document UID, fields to use and the file path!');
        }
    }

    public function generateTcpdf ($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape = false, $aProperties = array())
    {
        require_once (PATH_THIRDPARTY . "tcpdf" . PATH_SEP . "config" . PATH_SEP . "lang" . PATH_SEP . "eng.php");
        require_once (PATH_THIRDPARTY . "tcpdf" . PATH_SEP . "tcpdf.php");
        $nrt = array("\n", "\r", "\t");
        $nrthtml = array("(n /)", "(r /)", "(t /)");
        $strContentAux = str_replace ($nrt, $nrthtml, $sContent);
        $sContent = null;
        while (preg_match ("/^(.*)<font([^>]*)>(.*)$/i", $strContentAux, $arrayMatch)) {
            $str = trim ($arrayMatch[2]);
            $strAttribute = null;
            if ( !empty ($str) )
            {
                $strAux = $str;
                $str = null;
                while (preg_match ("/^(.*)([\"'].*[\"'])(.*)$/", $strAux, $arrayMatch2)) {
                    $strAux = $arrayMatch2[1];
                    $str = str_replace (" ", "__SPACE__", $arrayMatch2[2]) . $arrayMatch2[3] . $str;
                }
                $str = $strAux . $str;
                //Get attributes
                $strStyle = null;
                $array = explode (" ", $str);
                foreach ($array as $value) {
                    $arrayAux = explode ("=", $value);
                    if ( isset ($arrayAux[1]) )
                    {
                        $a = trim ($arrayAux[0]);
                        $v = trim (str_replace (array("__SPACE__", "\"", "'"), array(" ", null, null), $arrayAux[1]));
                        switch (strtolower ($a)) {
                            case "color":
                                $strStyle = $strStyle . "color: $v;";
                                break;
                            case "face":
                                $strStyle = $strStyle . "font-family: $v;";
                                break;
                            case "size":
                                $arrayPt = array(0, 8, 10, 12, 14, 18, 24, 36);
                                $strStyle = $strStyle . "font-size: " . $arrayPt[intval ($v)] . "pt;";
                                break;
                            case "style":
                                $strStyle = $strStyle . "$v;";
                                break;
                            default:
                                $strAttribute = $strAttribute . " $a=\"$v\"";
                                break;
                        }
                    }
                }
                if ( $strStyle != null )
                {
                    $strAttribute = $strAttribute . " style=\"$strStyle\"";
                }
            }
            $strContentAux = $arrayMatch[1];
            $sContent = "<span" . $strAttribute . ">" . $arrayMatch[3] . $sContent;
        }
        $sContent = $strContentAux . $sContent;
        $sContent = str_ireplace ("</font>", "</span>", $sContent);
        $sContent = str_replace ($nrthtml, $nrt, $sContent);
        $sContent = str_replace ("margin-left", "text-indent", $sContent);
        // define Save file
        $sOutput = 2;
        $sOrientation = ($sLandscape == false) ? PDF_PAGE_ORIENTATION : 'L';
        $sMedia = (isset ($aProperties['media'])) ? $aProperties['media'] : PDF_PAGE_FORMAT;
        $sLang = (defined ('SYS_LANG')) ? SYS_LANG : 'en';
        // create new PDF document
        $pdf = new TCPDF ($sOrientation, PDF_UNIT, $sMedia, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator (PDF_CREATOR);
        $pdf->SetAuthor ($aFields['USR_USERNAME']);
        $pdf->SetTitle ('Processmaker');
        $pdf->SetSubject ($sFilename);
        $pdf->SetCompression (true);
        $margins = $aProperties['margins'];
        $margins["left"] = ($margins["left"] >= 0) ? $margins["left"] : PDF_MARGIN_LEFT;
        $margins["top"] = ($margins["top"] >= 0) ? $margins["top"] : PDF_MARGIN_TOP;
        $margins["right"] = ($margins["right"] >= 0) ? $margins["right"] : PDF_MARGIN_RIGHT;
        $margins["bottom"] = ($margins["bottom"] >= 0) ? $margins["bottom"] : PDF_MARGIN_BOTTOM;
        $pdf->setPrintHeader (false);
        $pdf->setPrintFooter (false);
        $pdf->SetLeftMargin ($margins['left']);
        $pdf->SetTopMargin ($margins['top']);
        $pdf->SetRightMargin ($margins['right']);
        $pdf->SetAutoPageBreak (true, $margins['bottom']);
        //$oServerConf = &serverConf::getSingleton ();
        // set some language dependent data:
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        //$lg['a_meta_dir'] = ($oServerConf->isRtl ($sLang)) ? 'rtl' : 'ltr';
        $lg['a_meta_dir'] = 'ltr';
        $lg['a_meta_language'] = $sLang;
        $lg['w_page'] = 'page';
        //set some language-dependent strings
        $pdf->setLanguageArray ($lg);
        if ( isset ($aProperties['pdfSecurity']) )
        {
            $tcpdfPermissions = array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');
            $pdfSecurity = $aProperties['pdfSecurity'];
            $userPass = $this->decrypt ($pdfSecurity['openPassword'], $sUID);
            $ownerPass = ($pdfSecurity['ownerPassword'] != '') ? $this->decrypt ($pdfSecurity['ownerPassword'], $sUID) : null;
            $permissions = explode ("|", $pdfSecurity['permissions']);
            $permissions = array_diff ($tcpdfPermissions, $permissions);
            $pdf->SetProtection ($permissions, $userPass, $ownerPass);
        }
        // ---------------------------------------------------------
        // set default font subsetting mode
        $pdf->setFontSubsetting (true);
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        //$pdf->SetFont('dejavusans', '', 14, '', true);
        // Detect chinese, japanese, thai
        if ( preg_match ('/[\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FFF}\x{0E00}-\x{0E7F}]/u', $sContent, $matches) )
        {
            $fileArialunittf = PATH_THIRDPARTY . "tcpdf" . PATH_SEP . "fonts" . PATH_SEP . "arialuni.ttf";
            $pdf->SetFont ((!file_exists ($fileArialunittf)) ? "kozminproregular" : $pdf->addTTFfont ($fileArialunittf, "TrueTypeUnicode", "", 32));
        }
        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage ();
        // set text shadow effect
        //$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
        // Print text using writeHTMLCell()
        // $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
        if ( mb_detect_encoding ($sContent) == 'UTF-8' )
        {
            $sContent = mb_convert_encoding ($sContent, 'HTML-ENTITIES', 'UTF-8');
        }
        $doc = new DOMDocument ('1.0', 'UTF-8');
        if ( $sContent != '' )
        {
            $doc->loadHtml ($sContent);
        }
        $pdf->writeHTML ($doc->saveXML (), false, false, false, false, '');
        // ---------------------------------------------------------
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$pdf->Output('example_00.pdf', 'I');
        //$pdf->Output('/home/hector/processmaker/example_00.pdf', 'D');
        switch ($sOutput) {
            case 0:
                // Vrew browser
                $pdf->Output ($sPath . $sFilename . '.pdf', 'I');
                break;
            case 1:
                // Donwnload
                $pdf->Output ($sPath . $sFilename . '.pdf', 'D');
                break;
            case 2:
                // Save file
                $pdf->Output ($sPath . $sFilename . '.pdf', 'F');
                break;
        }
    }

    /**
     * verify if Output row specified in [sUid] exists.
     *
     * @param      string $sUid   the uid of the Prolication
     */
    public function OutputExists ($sUid)
    {
        try {
            $oObj = $this->retrieveByPk ($sUid);
            if ( is_object ($oObj) && get_class ($oObj) == 'OutputDocument' )
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

    public function retrieveByPk ($pk)
    {
        $result = $this->objMysql->_select ("workflow.output_document", [], ["id" => $pk]);

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            $oDocument = new OutputDocument();
            $oDocument->setOutDocUid ($pk);
            $oDocument->loadObject ($result[0]);

            return $oDocument;
        }

        return [];
    }

    /**
     * Set the [out_doc_title] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocTitleContent ($sValue)
    {
        if ( $sValue !== null && !is_string ($sValue) )
        {
            $sValue = (string) $sValue;
        }
        if ( $sValue === '' )
        {
            try {
                $this->out_doc_title = $sValue;
            } catch (Exception $oError) {
                $this->out_doc_title = '';
                throw ($oError);
            }
        }
    }

    /**
     * Set the [out_doc_comment] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocDescriptionContent ($sValue)
    {
        if ( $sValue !== null && !is_string ($sValue) )
        {
            $sValue = (string) $sValue;
        }
        if ( $sValue === '' )
        {
            try {
                $this->out_doc_description = $sValue;
            } catch (Exception $oError) {
                $this->out_doc_description = '';
                throw ($oError);
            }
        }
    }

    /**
     * Set the [out_doc_filename] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocFilenameContent ($sValue)
    {
        if ( $sValue !== null && !is_string ($sValue) )
        {
            $sValue = (string) $sValue;
        }
        if ( $sValue === '' )
        {
            try {
                $this->out_doc_filename = $sValue;
            } catch (Exception $oError) {
                $this->out_doc_filename = '';
                throw ($oError);
            }
        }
    }

    /**
     * Set the [out_doc_template] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocTemplateContent ($sValue)
    {
        if ( $sValue !== null && !is_string ($sValue) )
        {
            $sValue = (string) $sValue;
        }

        try {
            $this->out_doc_template = $sValue;
        } catch (Exception $oError) {
            $this->out_doc_template = '';
            throw ($oError);
        }
    }

}
