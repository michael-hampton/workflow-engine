<?php

/**
 * Skeleton subclass for representing a row from the 'APP_MESSAGE' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package workflow.engine.classes.model
 */
class AppMessage extends BaseAppMessage
{

    private $data_spool;
    private $status_spool;
    private $error_spool;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getSpoolStatus ()
    {
        return $this->status_spool;
    }

    public function getSpoolError ()
    {
        return $this->error_spool;
    }

    /**
     * AppMessgae quick Save method
     *
     * @param Array(msg_uid, app_uid, del_index, app_msg_type, app_msg_subject, app_msg_from, app_msg_to,
     * app_msg_body, app_msg_cc, app_msg_bcc, app_msg_attach, app_msg_template, app_msg_status )
     *
     * @author Erik Amaru Ortiz <erik@colosa.com, aortiz.erik@gmai.com>
     * Date Aug 31th, 2009
     */
    public function quickSave2 ($data_spool)
    {
        $this->data_spool = $data_spool;
        $spool = new AppMessage();
        $spool->setMsgUid ($data_spool['msg_uid']);
        $spool->setAppUid ($data_spool['app_uid']);
        $spool->setDelIndex ($data_spool['del_index']);
        $spool->setAppMsgType ($data_spool['app_msg_type']);
        $spool->setAppMsgSubject ($data_spool['app_msg_subject']);
        $spool->setAppMsgFrom ($data_spool['app_msg_from']);
        $spool->setAppMsgTo ($data_spool['app_msg_to']);
        $spool->setAppMsgBody ($data_spool['app_msg_body']);
        $spool->setAppMsgDate (date ('Y-m-d H:i:s'));
        $spool->setAppMsgCc ($data_spool['app_msg_cc']);
        $spool->setAppMsgBcc ($data_spool['app_msg_bcc']);
        $spool->setappMsgAttach ($data_spool['app_msg_attach']);
        $spool->setAppMsgTemplate ($data_spool['app_msg_template']);
        $spool->setAppMsgStatus ($data_spool['app_msg_status']);
        $spool->setAppMsgError ($data_spool['app_msg_error']);
        if ( !$spool->validate () )
        {
            $this->error_spool = $spool->getValidationFailures ();
            $this->status_spool = 'error';
            $error_msg = "AppMessage::quickSave(): Validation error: \n";
            foreach ($this->error_spool as $key => $value) {
                $error_msg .= $value->getMessage ($key) . "\n";
            }
            throw new Exception ($error_msg);
        }
        else
        {
            //echo "Saving - validation ok\n";
            $this->error_spool = '';
            $this->status = 'success';
            $sUID = $spool->save ();
        }
        return $sUID;
    }

    public function quickSave ($aData)
    {
        if ( isset ($aData['app_msg_uid']) )
        {
            $o = $this->retrieveByPk ($aData['app_msg_uid']);
        }
        if ( isset ($o) && is_object ($o) && get_class ($o) == 'AppMessage' )
        {
            $o->loadObject ($aData);
            $o->setAppMsgDate (date ('Y-m-d H:i:s'));
            $o->save ();
            return $o->getAppMsgUid ();
        }
        else
        {
            $this->loadObject ($aData);
            $this->setAppMsgDate (date ('Y-m-d H:i:s'));
            $this->save ();
            return $this->getAppMsgUid ();
        }
    }

    public function updateStatus ($msgUid, $msgStatus)
    {
        $message = $this->retrieveByPk ($msgUid);
        $message->loadObject ($message);
        $message->setAppMsgStatus ($msgStatus);
        $message->save ();
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("workflow.APP_MESSAGE", [], ["APP_MSG_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $spool = new AppMessage();
        $db_spool = $result[0];

        $spool->setMsgUid ($db_spool['MSG_UID']);
        $spool->setAppUid ($db_spool['APP_UID']);
        $spool->setCaseUid ($db_spool['CASE_UID']);
        $spool->setDelIndex ($db_spool['DEL_INDEX']);
        $spool->setAppMsgType ($db_spool['APP_MSG_TYPE']);
        $spool->setAppMsgSubject ($db_spool['APP_MSG_SUBJECT']);
        $spool->setAppMsgFrom ($db_spool['APP_MSG_FROM']);
        $spool->setAppMsgTo ($db_spool['APP_MSG_TO']);
        $spool->setAppMsgBody ($db_spool['APP_MSG_BODY']);
        $spool->setAppMsgDate ($db_spool['APP_MSG_DATE']);
        $spool->setAppMsgCc ($db_spool['APP_MSG_CC']);
        $spool->setAppMsgBcc ($db_spool['APP_MSG_BCC']);
        $spool->setappMsgAttach ($db_spool['APP_MSG_ATTACH']);
        $spool->setAppMsgTemplate ($db_spool['APP_MSG_TEMPLATE']);
        $spool->setAppMsgStatus ($db_spool['APP_MSG_STATUS']);
        $spool->setAppMsgSendDate ($db_spool['APP_MSG_SEND_DATE']); // Add by Ankit
        $spool->setAppMsgShowMessage ($db_spool['APP_MSG_SHOW_MESSAGE']); // Add by Ankit
        $spool->setAppMsgError ($db_spool['APP_MSG_ERROR']);
        $spool->setAppMsgFrom($db_spool['APP_MSG_FROM']);
        $spool->setAppMsgUid($pk);

        return $spool;
    }

    public function updatePrevious ()
    {
        $this->objMysql->_query ("UPDATE workflow.APP_MESSAGE
                                SET APP_MSG_SHOW_MESSAGE = 2
                                WHERE CASE_UID = ?
                                AND APP_UID = ?
                                AND APP_MSG_SHOW_MESSAGE != 3", [$this->caseId, $this->app_uid]
        );
    }

}
