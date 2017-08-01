<?php

namespace BusinessModel;

class FileUpload
{

    /**
     * ************* path functions ****************
     */
    public function mk_dir ($strPath, $rights = 0770)
    {
        $folder_path = array($strPath);
        $oldumask = umask (0);
        while (!@is_dir (dirname (end ($folder_path))) && dirname (end ($folder_path)) != '/' && dirname (end ($folder_path)) != '.' && dirname (end ($folder_path)) != '') {
            array_push ($folder_path, dirname (end ($folder_path))); //var_dump($folder_path); die;
        }

        while ($parent_folder_path = array_pop ($folder_path)) {
            if ( !@is_dir ($parent_folder_path) )
            {
                if ( !@mkdir ($parent_folder_path, $rights) )
                {
                    error_log ("Can't create folder \"$parent_folder_path\"");
                    //umask( $oldumask );
                }
            }
        }
        umask ($oldumask);
    }

    /**
     * verify path
     *
     * @author Fernando Ontiveros Lira <fernando@colosa.com>
     * @access public
     * @param string $strPath path
     * @param boolean $createPath if true this public function will create the path
     * @return boolean
     */
    public function verifyPath ($strPath, $createPath = false)
    {
        if ( file_exists ($strPath) || @is_dir ($strPath) )
        {
            return true;
        }
        else
        {
            if ( $createPath )
            {
                //TODO:: Define Environment constants: Devel (0777), Production (0770), ...
                $this->mk_dir ($strPath, 0777);
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    /**
     * Upload a file and then copy to path+ nameToSave
     *
     * @author Mauricio Veliz <mauricio@colosa.com>
     * @access public
     * @param string $file
     * @param string $path
     * @param string $nameToSave
     * @param integer $permission
     * @return void
     * Note - new version used by user image upload
     */
    public function uploadFile ($file, $path, $nameToSave, $permission = 0755)
    {
        try {
            if ( $file == '' )
            {
                throw new \Exception ('The filename is empty!');
            }

            if ( filesize ($file) > ((((ini_get ('upload_max_filesize') + 0)) * 1024) * 1024) )
            {
                throw new \Exception ('The size of upload file exceeds the allowed by the server!');
            }
            $oldumask = umask (0);
            if ( !is_dir ($path) )
            {
                $this->verifyPath ($path, true);
            }

            if ( strtoupper (substr (PHP_OS, 0, 3)) === 'WIN' )
            {
                $file = str_replace ("\\\\", "\\", $file, $count);
                if ( !$count )
                {
                    $winPath = explode ("\\", $file);
                    $file = "";
                    foreach ($winPath as $v) {
                        if ( $v != "" )
                        {
                            $file.= $v . "\\";
                        }
                    }
                    $file = substr ($file, 0, -1);
                }
            }

            $password = new \Password();

            $file = $password->validateInput ($file, "path");
            $path = $password->validateInput ($path, "path");

            move_uploaded_file ($file, $path . "/" . $nameToSave);
            $nameToSave = $password->validateInput ($nameToSave, "path");
            @chmod ($path . "/" . $nameToSave, $permission);
            umask ($oldumask);
        } catch (Exception $oException) {
            throw $oException;
        }
    }

    /* Old version used by attachments to be removed */

    public function doUpload ($name, $path, $errors, $tmp_name)
    {
        $arrName = explode ('.', $name);
        $extention = strtolower (end ($arrName));

        if ( !$extention )
        {
            $extention = '.html';
            $_FILES['prf_file']['name'] = $_FILES['prf_file']['name'] . $extention;
        }

        $arrPath = explode ("/", $path);
        $file = end ($arrPath);

        if ( strpos ($file, "\\") > 0 )
        {
            $file = str_replace ('\\', '/', $file);
            $file = end (explode ("/", $file));
        }

        $path = str_replace ($file, '', $path);

        if ( $file == $name )
        {
            if ( $errors != 1 )
            {
                if ( $tmp_name != '' )
                {
                    try {
                        $content = file_get_contents ($tmp_name);
                        $result = array('file_content' => $content);

                        if ( !is_dir ($path) )
                        {
                            mkdir ($path);
                        }

                        move_uploaded_file ($tmp_name, $path . "/" . $file);
                    } catch (Exception $ex) {
                        throw new \Exception ("Could not upload file " . $ex);
                    }
                }
            }
            else
            {
                $result->success = false;
                $result->fileError = true;
                throw (new \Exception ($result));
            }

            return $result;
        }
        else
        {
            throw new \Exception ('ID_PMTABLE_UPLOADING_FILE_PROBLEM');
        }
    }

    /**
     * streaming a file
     *
     * @access public
     * @param string $file
     * @param boolean $download
     * @param string $downloadFileName
     * @return string
     */
    public function streamFile ($file, $download = false, $downloadFileName = '')
    {
        $typearray = explode ('.', basename ($file));
        $typefile = $typearray[count ($typearray) - 1];
        $filename = $file;

        if ( file_exists ($filename) )
        {
            switch (strtolower ($typefile)) {
                case 'swf':
                    $this->sendHeaders ($filename, 'application/x-shockwave-flash', $download, $downloadFileName);
                    break;
                case 'js':
                    $this->sendHeaders ($filename, 'text/javascript', $download, $downloadFileName);
                    break;
                case 'htm':
                case 'html':
                    $this->sendHeaders ($filename, 'text/html', $download, $downloadFileName);
                    break;
                case 'htc':
                    $this->sendHeaders ($filename, 'text/plain', $download, $downloadFileName);
                    break;
                case 'json':
                    $this->sendHeaders ($filename, 'text/plain', $download, $downloadFileName);
                    break;
                case 'gif':
                    $this->sendHeaders ($filename, 'image/gif', $download, $downloadFileName);
                    break;
                case 'png':
                    $this->sendHeaders ($filename, 'image/png', $download, $downloadFileName);
                    break;
                case 'jpg':
                    $this->sendHeaders ($filename, 'image/jpg', $download, $downloadFileName);
                    break;
                case 'css':
                    $this->sendHeaders ($filename, 'text/css', $download, $downloadFileName);
                    break;
                case 'xml':
                    $this->sendHeaders ($filename, 'text/xml', $download, $downloadFileName);
                    break;
                case 'txt':
                    $this->sendHeaders ($filename, 'text/html', $download, $downloadFileName);
                    break;
                case 'doc':
                case 'pdf':
                case 'pm':
                case 'po':
                    $this->sendHeaders ($filename, 'application/octet-stream', $download, $downloadFileName);
                    break;
                case 'php':
                    if ( $download )
                    {
                        $this->sendHeaders ($filename, 'text/plain', $download, $downloadFileName);
                    }
                    else
                    {
                        require_once ($filename);
                        return;
                    }
                    break;
                case 'tar':
                    $this->sendHeaders ($filename, 'application/x-tar', $download, $downloadFileName);
                    break;
                default:
                    //throw new Exception ( "Unknown type of file '$file'. " );
                    $this->sendHeaders ($filename, 'application/octet-stream', $download, $downloadFileName);
                    break;
            }
        }
        else
        {
            throw new \Exception ("File doesnt exist");
        }


        @readfile ($filename);
    }

    /**
     * sendHeaders
     *
     * @param string $filename
     * @param string $contentType default value ''
     * @param boolean $download default value false
     * @param string $downloadFileName default value ''
     *
     * @return void
     */
    public function sendHeaders ($filename, $contentType = '', $download = false, $downloadFileName = '')
    {
        if ( $download )
        {
            if ( $downloadFileName == '' )
            {
                $aAux = explode ('/', $filename);
                $downloadFileName = $aAux[count ($aAux) - 1];
            }
            header ('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        }
        header ('Content-Type: ' . $contentType);

        //if userAgent (BROWSER) is MSIE we need special headers to avoid MSIE behaivor.
        $userAgent = strtolower ($_SERVER['HTTP_USER_AGENT']);
        if ( preg_match ("/msie/i", $userAgent) )
        {
            //if ( ereg("msie", $userAgent)) {
            header ('Pragma: cache');

            if ( file_exists ($filename) )
            {
                $mtime = filemtime ($filename);
            }
            else
            {
                $mtime = date ('U');
            }
            $gmt_mtime = gmdate ("D, d M Y H:i:s", $mtime) . " GMT";
            header ('ETag: "' . G::encryptOld ($mtime . $filename) . '"');
            header ("Last-Modified: " . $gmt_mtime);
            header ('Cache-Control: public');
            header ("Expires: " . gmdate ("D, d M Y H:i:s", time () + 60 * 10) . " GMT"); //ten minutes
            return;
        }

        if ( !$download )
        {

            header ('Pragma: cache');

            if ( file_exists ($filename) )
            {
                $mtime = filemtime ($filename);
            }
            else
            {
                $mtime = date ('U');
            }
            $gmt_mtime = gmdate ("D, d M Y H:i:s", $mtime) . " GMT";
            header ('ETag: "' . G::encryptOld ($mtime . $filename) . '"');
            header ("Last-Modified: " . $gmt_mtime);
            header ('Cache-Control: public');
            header ("Expires: " . gmdate ("D, d M Y H:i:s", time () + 90 * 60 * 60 * 24) . " GMT");
            if ( isset ($_SERVER['HTTP_IF_MODIFIED_SINCE']) )
            {
                if ( $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime )
                {
                    header ('HTTP/1.1 304 Not Modified');
                    exit ();
                }
            }

            if ( isset ($_SERVER['HTTP_IF_NONE_MATCH']) )
            {
                if ( str_replace ('"', '', stripslashes ($_SERVER['HTTP_IF_NONE_MATCH'])) == G::encryptOld ($mtime . $filename) )
                {
                    header ("HTTP/1.1 304 Not Modified");
                    exit ();
                }
            }
        }
    }

    /**
     * resizeImage
     *
     * @param string $path,
     * @param string $resWidth
     * @param string $resHeight
     * @param string $saveTo default value null
     *
     * @return void
     */
    public function resizeImage ($path, $resWidth, $resHeight, $saveTo = null)
    {
        $imageInfo = getimagesize ($path);

        if ( !$imageInfo )
        {
            throw new Exception ("Could not get image information");
        }
        
        list ($width, $height) = $imageInfo;
        $percentHeight = $resHeight / $height;
        $percentWidth = $resWidth / $width;
        $percent = ($percentWidth < $percentHeight) ? $percentWidth : $percentHeight;
        $resWidth = $width * $percent;
        $resHeight = $height * $percent;

        // Resample
        $image_p = imagecreatetruecolor ($resWidth, $resHeight);
        imagealphablending ($image_p, false);
        imagesavealpha ($image_p, true);

        $background = imagecolorallocate ($image_p, 0, 0, 0);
        ImageColorTransparent ($image_p, $background); // make the new temp image all transparent
        
//Assume 3 channels if we can't find that information
        if ( !array_key_exists ("channels", $imageInfo) )
        {
            $imageInfo["channels"] = 3;
        }
        $memoryNeeded = Round (($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] + Pow (2, 16)) * 1.95) / (1024 * 1024);

        if ( $memoryNeeded < 80 )
        {
            $memoryNeeded = 80;
        }

        ini_set ('memory_limit', intval ($memoryNeeded) . 'M');

        $functions = array(IMAGETYPE_GIF => array('imagecreatefromgif', 'imagegif'
            ), IMAGETYPE_JPEG => array('imagecreatefromjpeg', 'imagejpeg'), IMAGETYPE_PNG => array('imagecreatefrompng', 'imagepng'));

        if ( !array_key_exists ($imageInfo[2], $functions) )
        {
            throw new Exception ("Image format not supported");
        }
        list ($inputFn, $outputFn) = $functions[$imageInfo[2]];

        $image = $inputFn ($path);
        imagecopyresampled ($image_p, $image, 0, 0, 0, 0, $resWidth, $resHeight, $width, $height);
        $outputFn ($image_p, $saveTo);

        if ( !is_null ($saveTo) )
        {
            $filter = new \Password();
            $saveTo = $filter->validateInput ($saveTo, "path");
        }

        chmod ($saveTo, 0666);
    }

}
