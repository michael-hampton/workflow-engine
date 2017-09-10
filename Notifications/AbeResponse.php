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
                $AbeResponsesInstance = new AbeResponses();
            }
            else
            {
                $AbeResponsesInstance = $this->retrieveByPK ($data['ABE_RES_UID']);
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
