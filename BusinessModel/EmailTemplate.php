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
            throw new Exception (\G::LoadTranslation ('ID_TEMPLATE_PARAMETER_EMPTY'));
        }
        $templateFile = fopen (PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID'] . PATH_SEP . $arrayData['TEMPLATE'], 'w');
        $content = stripslashes ($arrayData['CONTENT']);
        $content = str_replace ('@amp@', '&', $content);
        $content = base64_decode ($content);
        fwrite ($templateFile, $content);
        fclose ($templateFile);
    }

}
