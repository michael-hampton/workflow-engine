<?php

class FileUpload
{

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
