<?php

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
        $folder_path = strstr ($strPath, '.') ? dirname ($strPath) : $strPath;

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
                throw new Exception ('The filename is empty!');
            }

            if ( filesize ($file) > ((((ini_get ('upload_max_filesize') + 0)) * 1024) * 1024) )
            {
                throw new Exception ('The size of upload file exceeds the allowed by the server!');
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
                    foreach ($winPath as $k => $v) {
                        if ( $v != "" )
                        {
                            $file.= $v . "\\";
                        }
                    }
                    $file = substr ($file, 0, -1);
                }
            }

            $password = new Password();

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
                        throw new Exception ("Could not upload file " . $ex);
                    }
                }
            }
            else
            {
                $result->success = false;
                $result->fileError = true;
                throw (new Exception ($result));
            }

            return $result;
        }
        else
        {
            throw new Exception ('ID_PMTABLE_UPLOADING_FILE_PROBLEM');
        }
    }

}
