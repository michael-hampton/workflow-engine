<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbeResponse
 *
 * @author michael.hampton
 */
class AbeResponse extends BaseAbeResponse
{

    private $objMysql;
    private $filterThisFields = array('ABE_RES_UID', 'ABE_REQ_UID', 'ABE_RES_CLIENT_IP', 'ABE_RES_DATA',
        'ABE_RES_DATE', 'ABE_RES_STATUS', 'ABE_RES_MESSAGE');

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function load ($abeResponsesUid)
    {
        try {
            $abeResponsesInstance = AbeResponsesPeer::retrieveByPK ($abeResponsesUid);
            $fields = $abeResponsesInstance->toArray (BasePeer::TYPE_FIELDNAME);
            return $fields;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function retrieveByPK ($pk)
    {

        $result = $this->objMysql->_select ("workflow.ABE_RESPONSE", [], ["ABE_RES_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $abeResponse = new AbeResponse();
        $abeResponse->setAbeReqUid ($result[0]['ABE_REQ_UID']);
        $abeResponse->setAbeResClientIp ($result[0]['ABE_RES_CLIENT_IP']);
        $abeResponse->setAbeResData ($result[0]['ABE_RES_DATA']);
        $abeResponse->setAbeResDate ($result[0]['ABE_RES_DATE']);
        $abeResponse->setAbeResMessage ($result[0]['ABE_RES_MESSAGE']);
        $abeResponse->setAbeResStatus ($result[0]['ABE_RES_STATUS']);
        $abeResponse->setAbeResUid ($result[0]['ABE_RES_UID']);

        return $abeResponse;
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
            if ( !isset ($data['ABE_RES_UID']) )
            {
                $data['ABE_RES_UID'] = '';
            }
            if ( $data['ABE_RES_UID'] == '' )
            {
                $data['ABE_RES_DATE'] = date ('Y-m-d H:i:s');
                $AbeResponsesInstance = new AbeResponse();
            }
            else
            {
                $AbeResponsesInstance = $this->retrieveByPK ($data['ABE_RES_UID']);
            }


            if ( $AbeResponsesInstance === false )
            {
                throw new Exception ("Row cannot be found");
            }
            //$data['ABE_RES_UPDATE'] = date('Y-m-d H:i:s');
            $AbeResponsesInstance->loadObject ($data);

            if ( $AbeResponsesInstance->validate () )
            {
                $id = $AbeResponsesInstance->save ();
                return $id;
            }
            else
            {
                $message = '';
                $validationFailures = $AbeResponsesInstance->getValidationFailures ();

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
