<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailActions
 *
 * @author michael.hampton
 */
class EmailActions
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function loadAbeRequest ($AbeRequestsUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.ABE_REQUEST", [], ["ABE_REQ_UID" => $AbeRequestsUid]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        return $result[0];
    }

    public function postNote ($httpData)
    {
        //extract(getExtJSParams());
        $appUid = (isset ($httpData->appUid)) ? $httpData->appUid : '';
        $delIndex = (isset ($httpData->delIndex)) ? $httpData->delIndex : '';
        $usrUid = (isset ($httpData->usrUid)) ? $httpData->usrUid : '';
        $appNotes = new Comments();
        $noteContent = addslashes ($httpData->noteText);

        $result = $appNotes->postNewNote ($appUid, $delIndex, $usrUid, $noteContent, false);
        //return true;
        //die();
        //send the response to client
        @ini_set ('implicit_flush', 1);
        ob_start ();
        //echo G::json_encode($result);
        @ob_flush ();
        @flush ();
        @ob_end_flush ();
        ob_implicit_flush (1);
        //return true;
        //send notification in background
        $oCase = new BusinessModel\Cases();
        $p = $oCase->getUsersParticipatedInCase ($appUid);

        $noteRecipients = implode (",", $p);


        $appNotes->sendNoteNotification ($appUid, $usrUid, $noteContent, $noteRecipients);
    }

    public function uploadAbeRequest ($data)
    {
        try {
            $abeRequestsInstance = new AbeRequest();
            $abeRequestsInstance->createOrUpdate ($data);
        } catch (Exception $error) {
            throw $error;
        }
    }

}
