<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Mysql.php';

class Comments extends BaseComments
{

    private $objMysql;

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function postNewNote ($projectId, $caseId, $usrUid, $noteContent, $notify = true, $noteAvalibility = "PUBLIC", $noteRecipients = "", $noteType = "USER", $noteDate = "now")
    {
        $this->setAppUid ($projectId);
        $this->setUserUid ($usrUid);
        $this->setNoteDate ($noteDate);
        $this->setNoteContent ($noteContent);
        $this->setNoteType ($noteType);
        $this->setRecipients ($noteRecipients);

        if ( $this->validate () )
        {
            $this->save ();
            $msg = '';
        }
        else
        {
            $msg = '';
            $validationFailuresArray = $this->getValidationFailures ();
            foreach ($validationFailuresArray as $strValidationFailure) {
                $msg .= $strValidationFailure . "<br/>";
            }
            //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
        }

        if ( $msg != "" )
        {
            $response['success'] = "ID_FAILURE";
            $response['message'] = $msg;
        }
        else
        {
            $response['success'] = 'success';
            $response['message'] = '';
        }

        if ( $notify )
        {
            if ( $noteRecipients == "" )
            {
                $noteRecipientsA = array();
                $oCase = new Cases();
                $p = $oCase->getUsersParticipatedInCase ($appUid);

                foreach ($p as $key => $userParticipated) {
                    $noteRecipientsA[] = $userParticipated;
                }

                $noteRecipients = implode (",", $noteRecipientsA);
            }

            $this->sendNoteNotification ($caseId, $projectId, $usrUid, $noteContent, $noteRecipients);
        }
        return $response;
    }

    public function sendNoteNotification ($appUid, $projectId, $usrUid, $noteContent, $noteRecipients, $sFrom = "")
    {
        try {

            if ( $this->objMysql === null )
            {
                $this->getConnection ();
            }


            $aUser = $this->objMysql->_select ("user_management.poms_users", array(), array("username" => $usrUid));

            $authorName = ((($aUser[0]['firstName'] != '') || ($aUser[0]['lastName'] != '')) ? $aUser[0]['firstName'] . ' ' . $aUser[0]['lastName'] . ' ' : '') . '<' . $aUser[0]['email_address'] . '>';
            $objCase = new Cases();
            $arrData = $objCase->getCaseInfo ($projectId, $appUid);

            $configNoteNotification['subject'] = "NEW COMMENT ADDED";
            $configNoteNotification['body'] = $arrData->getId () . ": " . $arrData->getName () . "<br />" . ": $authorName<br /><br />$noteContent";
            $sFrom = "bluetiger_uan@yahoo.com";
            $sBody = nl2br ($configNoteNotification['body']);
            $oUser = new Users();
            $recipientsArray = explode (",", $noteRecipients);
            foreach ($recipientsArray as $recipientUid) {
                $aUser = $oUser->load ($recipientUid);
                $sTo = ((($aUser['USR_FIRSTNAME'] != '') || ($aUser['USR_LASTNAME'] != '')) ? $aUser['USR_FIRSTNAME'] . ' ' . $aUser['USR_LASTNAME'] . ' ' : '') . '<' . $aUser['USR_EMAIL'] . '>';

                mail($sTo, $configNoteNotification['subject'], $sBody);
            }
            //Send derivation notification - End
        } catch (Exception $oException) {
            throw $oException;
        }
    }

    public function save ()
    {
        $objMysql = new Mysql2();
        $id = $objMysql->_insert ($this->table, $this->object, false);
        return $id;
    }

    public function getAttachment ()
    {
        $objMysql = new Mysql2();
        return $objMysql->_select ($this->table, array(), array("id" => $this->id));
    }

    public function getAllComments ($sourceId)
    {
        $objMysql = new Mysql2();
        return $objMysql->_select ($this->table, array(), array("source_id" => $sourceId));
    }

    public function doCount ($appUid, $usrUid = '', $start = '', $limit = 25, $sort = 'APP_NOTES.NOTE_DATE', $dir = 'DESC', $dateFrom = '', $dateTo = '', $search = '')
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $arrParameters = array();

        $sql = "SELECT * FROM task_manager.comments c WHERE 1=1";

        if ( $usrUid != '' )
        {
            $sql .= " AND c.username = ?";
            $arrParameters[] = $usrUid;
        }

        if ( $dateFrom != '' )
        {
            $sql .= " AND `datetime` >= ?";
            $arrParameters[] = $dateFrom;
        }
        if ( $dateTo != '' )
        {
            $sql .= " AND `datetime` <= ?";
            $arrParameters[] = $dateTo;
        }

        if ( $search != '' )
        {
            $sql .= " AND comment LIKE  '%' . $search . '%'";
        }

        if ( $dir == 'DESC' )
        {
            $sql .= " ORDER BY " . $sort . " DESC";
        }
        else
        {
            $sql .= " ORDER BY " . $sort . " ASC";
        }

        $results = $this->objMysql->_query ($sql, $arrParameters);
        return count ($results);
    }

    public function getNotesList (
    $appUid, $usrUid = '', $start = '', $limit = 25, $sort = 'APP_NOTES.NOTE_DATE', $dir = 'DESC', $dateFrom = '', $dateTo = '', $search = '')
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $arrParameters = array();

        $sql = "SELECT * FROM task_manager.comments c WHERE 1=1";

        if ( $usrUid != '' )
        {
            $sql .= " AND c.username = ?";
            $arrParameters[] = $usrUid;
        }

        if ( $dateFrom != '' )
        {
            $sql .= " AND `datetime` >= ?";
            $arrParameters[] = $dateFrom;
        }
        if ( $dateTo != '' )
        {
            $sql .= " AND `datetime` <= ?";
            $arrParameters[] = $dateTo;
        }

        if ( $search != '' )
        {
            $sql .= " AND comment LIKE  '%' . $search . '%'";
        }

        if ( $dir == 'DESC' )
        {
            $sql .= " ORDER BY " . $sort . " DESC";
        }
        else
        {
            $sql .= " ORDER BY " . $sort . " ASC";
        }

        $response = array();
        $totalCount = $this->doCount ($appUid, $usrUid, $start, $limit, $sort, $dir, $dateFrom, $dateTo, $search);
        $response['totalCount'] = $totalCount;

        $response['notes'] = array();
        if ( $start != '' )
        {
            $sql .= " LIMIT " . $limit;
            $sql .= " OFFSET " . $start;
        }

        $results = $this->objMysql->_query ($sql, $arrParameters);

        foreach ($results as $key => $result) {
            $result['comment'] = htmlentities (stripslashes ($result['comment']), ENT_QUOTES, 'UTF-8');
            $response['notes'][] = $result;
        }

        $result['array'] = $response;
        return $result;


        //`datetime` BETWEEN '2017-02-16' AND '2017-02-21'
    }

    public function addCaseNote ($projectId, $caseId, $userUid, $note, $sendMail)
    {
        $response = $this->postNewNote($projectId, $caseId, $userUid, $note, false);
        
        if ( $sendMail == 1 )
        {
            $case = new Cases();
            $p = $case->getUsersParticipatedInCase($projectId, $caseId);
            $noteRecipientsList = array();
            foreach ($p["array"] as $key => $userParticipated) {
                if ( $key != '' )
                {
                    $noteRecipientsList[] = $key;
                }
            }
            $noteRecipients = implode (",", $noteRecipientsList);
            $note = stripslashes ($note);
            $this->sendNoteNotification($caseId, $projectId, $usrUid, $note, $noteRecipients);
        }
        return $response;
    }

}
