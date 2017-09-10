<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbeRequest
 *
 * @author michael.hampton
 */
class AbeRequest extends BaseAbeRequest
{

    private $objMysql;
    private $filterThisFields = array('ABE_REQ_UID', 'ABE_UID', 'APP_UID', 'DEL_INDEX',
        'ABE_REQ_SENT_TO', 'ABE_REQ_SUBJECT', 'ABE_REQ_BODY',
        'ABE_REQ_DATE', 'ABE_REQ_STATUS', 'ABE_REQ_ANSWERED');

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function retrieveByPK ($pk)
    {
        $result = $this->objMysql->_select ("workflow.ABE_REQUEST", [], ["ABE_REQ_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $abeRequest = new AbeRequest();
        $abeRequest->setAbeReqAnswered ($result[0]['ABE_REQ_ANSWERED']);
        $abeRequest->setAbeReqBody ($result[0]['ABE_REQ_BODY']);
        $abeRequest->setAbeReqDate ($result[0]['ABE_REQ_DATE']);
        $abeRequest->setAbeReqSentTo ($result[0]['ABE_REQ_SENT_TO']);
        $abeRequest->setAbeReqStatus ($result[0]['ABE_REQ_STATUS']);
        $abeRequest->setAbeReqSubject ($result[0]['ABE_REQ_SUBJECT']);
        $abeRequest->setAbeReqUid ($result[0]['ABE_REQ_UID']);
        $abeRequest->setAbeUid ($result[0]['ABE_UID']);
        $abeRequest->setAppUid ($result[0]['APP_UID']);
        $abeRequest->setDelIndex ($result[0]['DEL_INDEX']);
        
        return $abeRequest;
    }

    public function load ($abeRequestUid)
    {
        try {
            $abeRequestInstance = $this->retrieveByPK ($abeRequestUid);
            return $abeRequestInstance;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function createOrUpdate ($data)
    {
        $additionalFields = array();
        foreach ($data as $field => $value) {
            if ( !in_array ($field, $this->filterThisFields) )
            {
                $additionalFields[$field] = $value;
                unset ($data[$field]);
            }
        }
        try {
            if ( !isset ($data['ABE_REQ_UID']) )
            {
                $data['ABE_REQ_UID'] = '';
            }
            if ( $data['ABE_REQ_UID'] == '' )
            {
                $data['ABE_REQ_DATE'] = date ('Y-m-d H:i:s');
                $AbeRequestsInstance = new AbeRequest();
            }
            else
            {
                $AbeRequestsInstance = $this->retrieveByPK ($data['ABE_REQ_UID']);
            }

            $AbeRequestsInstance->loadObject ($data);

            if ( $AbeRequestsInstance->validate () )
            {
                $id = $AbeRequestsInstance->save ();
                return $id;
            }
            else
            {
                $message = '';
                $validationFailures = $AbeRequestsInstance->getValidationFailures ();
                foreach ($validationFailures as $validationFailure) {
                    $message .= $validationFailure . '. ';
                }
                throw(new Exception ('Error trying to update: ' . $message));
            }
        } catch (Exception $error) {
            throw $error;
        }
    }

}
