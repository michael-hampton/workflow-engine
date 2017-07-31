<?php

class Download
{
    /**
     * Download InputDocument
     *
     * @param $app_uid
     * @param $app_doc_uid
     * @param $version
     * @throws \Exception
     */
    public function downloadInputDocument($app_uid, $app_doc_uid = null, $version = null)
    {
        try {
            $oAppDocument = new \DocumentVersion();
            if ($version == 0) {
                $docVersion = $oAppDocument->getLastDocVersion($app_doc_uid);
            } else {
                $docVersion = $version;
            }

            $fields = $oAppDocument->load($app_doc_uid, $docVersion, $app_uid);

            $objInput = (new \BusinessModel\InputDocument())->getInputDocument($oAppDocument->getDocUid());
            $inputDestination = $objInput->setDestinationPath();

            $sAppDocUid = $fields->getAppDocUid();
            $iDocVersion = $fields->getDocVersion();
            $info = pathinfo($fields->getAppDocFilename());

            $app_uid = $this->getPathFromUIDPlain($fields->getAppUid());
            $file = $this->getPathFromFileUIDPlain($fields->getAppUid(), $sAppDocUid);
            $ext = (isset($info['extension']) ? $info['extension'] : '');

            $realPath = PATH_DOCUMENT . $app_uid . '/' . $file[0] . $file[1] . '_' . $iDocVersion . '.' . $ext;
            $realPath1 = PATH_DOCUMENT . $app_uid . '/' . $file[0] . $file[1] . '.' . $ext;

            if (!file_exists($realPath) && file_exists($realPath1)) {
                $realPath = $realPath1;
            }

            $filename = $info['basename'];
            $mimeType = $this->mime_content_type($filename);

            header('HTTP/1.0 206');
            header('Pragma: public');
            header('Expires: -1');
            header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
            header('Content-Transfer-Encoding: binary');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Length: " . filesize($realPath));
            header("Content-Type: $mimeType");
            header("Content-Description: File Transfer");

            if ($fp = fopen($realPath, 'rb')) {
                ob_end_clean();
                while (!feof($fp) and (connection_status() == 0)) {
                    print(fread($fp, 8192));
                    flush();
                }
                @fclose($fp);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the file stored in '0' dir as splitted.
     * @param string $appUid
     * @param string $fileUid
     * @param int $splitSize
     * @param int $pieces
     * @return array index:0 got the path, index:1 got the filename
     */
    public function getPathFromFileUIDPlain($appUid, $fileUid, $splitSize = 3, $pieces = 3)
    {
        $response = array();
        if ($appUid == $this->getBlackHoleDir()) {
            $dirArray = array();
            if (is_string($fileUid) && strlen($fileUid) >= 32) {
                for ($i = 0; $i < $pieces; $i++) {
                    $dirArray[] = substr($fileUid, 0, $splitSize);
                    $len = strlen($fileUid);
                    $fileUid = substr($fileUid, $splitSize, $len);
                }
            }
            $response[] = implode($dirArray, '/') . '/';
            $response[] = $fileUid;
        } else {
            $response[] = '';
            $response[] = $fileUid;
        }
        return $response;
    }

    /**
     * Get the default blank directory 0 for external files
     */
    public function getBlackHoleDir()
    {
        //len32:12345678901234567890123456789012
        return "00000000000000000000000000000000";
    }

    /**
     * Funtion used to fix 32K issue related to ext3 max subdirectory storage.
     * @param string $uid
     * @param int $splitSize
     * @param int $pieces
     * @return string xxx/xxx/xxx/xxxxxxxxxxxxxxxxxxxxx
     */
    public function getPathFromUIDPlain($uid, $splitSize = 3, $pieces = 3)
    {
        $dirArray = array();
        if (is_string($uid) && strlen($uid) >= 32 && $uid != $this->getBlackHoleDir()) {
            for ($i = 0; $i < $pieces; $i++) {
                $dirArray[] = substr($uid, 0, $splitSize);
                $len = strlen($uid);
                $uid = substr($uid, $splitSize, $len);
            }
        }
        $dirArray[] = $uid;
        $newfileStructure = implode($dirArray, '/');
        return $newfileStructure;
    }

    public function mime_content_type($filename)
    {
        $idx = explode('.', $filename);
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode - 1]);
        $mimet = array(
            'ai' => 'application/postscript',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'asc' => 'text/plain',
            'atom' => 'application/atom+xml',
            'avi' => 'video/x-msvideo',
            'bcpio' => 'application/x-bcpio',
            'bmp' => 'image/bmp',
            'cdf' => 'application/x-netcdf',
            'cgm' => 'image/cgm',
            'cpio' => 'application/x-cpio',
            'cpt' => 'application/mac-compactpro',
            'crl' => 'application/x-pkcs7-crl',
            'crt' => 'application/x-x509-ca-cert',
            'csh' => 'application/x-csh',
            'css' => 'text/css',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'djv' => 'image/vnd.djvu',
            'djvu' => 'image/vnd.djvu',
            'doc' => 'application/msword',
            'dtd' => 'application/xml-dtd',
            'dvi' => 'application/x-dvi',
            'dxr' => 'application/x-director',
            'eps' => 'application/postscript',
            'etx' => 'text/x-setext',
            'ez' => 'application/andrew-inset',
            'gif' => 'image/gif',
            'gram' => 'application/srgs',
            'grxml' => 'application/srgs+xml',
            'gtar' => 'application/x-gtar',
            'hdf' => 'application/x-hdf',
            'hqx' => 'application/mac-binhex40',
            'html' => 'text/html',
            'html' => 'text/html',
            'ice' => 'x-conference/x-cooltalk',
            'ico' => 'image/x-icon',
            'ics' => 'text/calendar',
            'ief' => 'image/ief',
            'ifb' => 'text/calendar',
            'iges' => 'model/iges',
            'igs' => 'model/iges',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/x-javascript',
            'kar' => 'audio/midi',
            'latex' => 'application/x-latex',
            'm3u' => 'audio/x-mpegurl',
            'man' => 'application/x-troff-man',
            'mathml' => 'application/mathml+xml',
            'me' => 'application/x-troff-me',
            'mesh' => 'model/mesh',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mif' => 'application/vnd.mif',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpga' => 'audio/mpeg',
            'ms' => 'application/x-troff-ms',
            'msh' => 'model/mesh',
            'mxu m4u' => 'video/vnd.mpegurl',
            'nc' => 'application/x-netcdf',
            'oda' => 'application/oda',
            'ogg' => 'application/ogg',
            'pbm' => 'image/x-portable-bitmap',
            'pdb' => 'chemical/x-pdb',
            'pdf' => 'application/pdf',
            'pgm' => 'image/x-portable-graymap',
            'pgn' => 'application/x-chess-pgn',
            'php' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'png' => 'image/png',
            'pnm' => 'image/x-portable-anymap',
            'ppm' => 'image/x-portable-pixmap',
            'ppt' => 'application/vnd.ms-powerpoint',
            'ps' => 'application/postscript',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-pn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'ras' => 'image/x-cmu-raster',
            'rdf' => 'application/rdf+xml',
            'rgb' => 'image/x-rgb',
            'rm' => 'application/vnd.rn-realmedia',
            'roff' => 'application/x-troff',
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',
            'sgm' => 'text/sgml',
            'sgml' => 'text/sgml',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'shtml' => 'text/html',
            'silo' => 'model/mesh',
            'sit' => 'application/x-stuffit',
            'skd' => 'application/x-koan',
            'skm' => 'application/x-koan',
            'skp' => 'application/x-koan',
            'skt' => 'application/x-koan',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'snd' => 'audio/basic',
            'spl' => 'application/x-futuresplash',
            'src' => 'application/x-wais-source',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            'svg' => 'image/svg+xml',
            'swf' => 'application/x-shockwave-flash',
            't' => 'application/x-troff',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz' => 'application/x-tar',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tr' => 'application/x-troff',
            'tsv' => 'text/tab-separated-values',
            'txt' => 'text/plain',
            'ustar' => 'application/x-ustar',
            'vcd' => 'application/x-cdlink',
            'vrml' => 'model/vrml',
            'vxml' => 'application/voicexml+xml',
            'wav' => 'audio/x-wav',
            'wbmp' => 'image/vnd.wap.wbmp',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wml' => 'text/vnd.wap.wml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'wmlc' => 'application/vnd.wap.wmlc',
            'wmls' => 'text/vnd.wap.wmlscript',
            'wmlsc' => 'application/vnd.wap.wmlscriptc',
            'wmlsc' => 'application/vnd.wap.wmlscriptc',
            'wrl' => 'model/vrml',
            'xbm' => 'image/x-xbitmap',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'xls' => 'application/vnd.ms-excel',
            'xml xsl' => 'application/xml',
            'xpm' => 'image/x-xpixmap',
            'xslt' => 'application/xslt+xml',
            'xul' => 'application/vnd.mozilla.xul+xml',
            'xwd' => 'image/x-xwindowdump',
            'xyz' => 'chemical/x-xyz',
            'zip' => 'application/zip'
        );
        if (isset($mimet[$idx])) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Delete InputDocument
     *
     * @param string $inputDocumentUid
     *
     * return array Return an array with data of an InputDocument
     */
    public function removeInputDocument($inputDocumentUid)
    {
        try {
            $oAppDocument = (new DocumentVersion())->retrieveByPK($inputDocumentUid, 1);
            if (is_null($oAppDocument) || $oAppDocument->getAppDocStatus() == 'DELETED') {
                throw new \Exception("ID_CASES_INPUT_DOES_NOT_EXIST");
            }

            $oAppDocument->remove($inputDocumentUid);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}

?>
