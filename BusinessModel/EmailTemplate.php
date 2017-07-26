<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BusinessModel;

/**
 * Description of EmailTemplate
 *
 * @author michael.hampton
 */
class EmailTemplate
{

    public function getTemplates ($workflowId)
    {
        $data = array();

        if ( is_dir (PATH_DATA_MAILTEMPLATES . $workflowId) )
        {
            $dir = new \DirectoryIterator (PATH_DATA_MAILTEMPLATES . $workflowId);
            foreach ($dir as $fileinfo) {
                if ( !$fileinfo->isDot () )
                {
                    $filename = str_replace (".html", "", $fileinfo->getFilename ());
                    $data[$filename] = array(
                        "CONTENT" => file_get_contents (PATH_DATA_MAILTEMPLATES . $workflowId . "/" . $fileinfo->getFilename ()),
                        "TEMPLATE" => $filename
                    );
                }
            }

            return $data;
        }
    }

    public function editTemplate (array $arrayData)
    {
        //Action Validations
        if ( !isset ($arrayData['TEMPLATE']) )
        {
            $arrayData['TEMPLATE'] = '';
        }
        if ( $arrayData['TEMPLATE'] == '' )
        {
            throw new Exception (\G::LoadTranslation ('ID_TEMPLATE_PARAMETER_EMPTY'));
        }
        $data = array(
            'CONTENT' => file_get_contents (
                    PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID'] . PATH_SEP . $arrayData['TEMPLATE']
            ),
            'TEMPLATE' => $arrayData['TEMPLATE'],
        );
        global $G_PUBLISH;
        $G_PUBLISH = new \Publisher();
        $G_PUBLISH->AddContent ('xmlform', 'xmlform', 'actionsByEmail/actionsByEmail_FileEdit', '', $data);
        \G::RenderPage ('publish', 'raw');
        die ();
    }

    public function updateTemplate (array $arrayData)
    {
        //Action Validations
        if ( !isset ($arrayData['TEMPLATE']) )
        {
            $arrayData['TEMPLATE'] = '';
        }
        if ( !isset ($arrayData['CONTENT']) )
        {
            $arrayData['CONTENT'] = '';
        }
        if ( $arrayData['TEMPLATE'] == '' )
        {
            throw new Exception ('ID_TEMPLATE_PARAMETER_EMPTY');
        }

        (new \BusinessModel\FileUpload())->mk_dir (PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID']);

        $templateFile = fopen (PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID'] . PATH_SEP . $arrayData['TEMPLATE'] . ".html", 'w');
        $content = stripslashes ($arrayData['CONTENT']);
        $content = urldecode ($content);
        fwrite ($templateFile, $content);
        fclose ($templateFile);
    }

}
