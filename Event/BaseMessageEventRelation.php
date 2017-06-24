<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseMessageEventRelation
 *
 * @author michael.hampton
 */
abstract class BaseMessageEventRelation implements Persistent
{

    private $MSGER_UID;
    private $EVN_UID_THROW;
    private $EVN_UID_CATCH;

    public function getMSGER_UID ()
    {
        return $this->MSGER_UID;
    }

    public function getEVN_UID_THROW ()
    {
        return $this->EVN_UID_THROW;
    }

    public function getEVN_UID_CATCH ()
    {
        return $this->EVN_UID_CATCH;
    }

    public function setMSGER_UID ($MSGER_UID)
    {
        $this->MSGER_UID = $MSGER_UID;
    }

    public function setEVN_UID_THROW ($EVN_UID_THROW)
    {
        $this->EVN_UID_THROW = $EVN_UID_THROW;
    }

    public function setEVN_UID_CATCH ($EVN_UID_CATCH)
    {
        $this->EVN_UID_CATCH = $EVN_UID_CATCH;
    }
    
    public function loadObject (array $arrData)
    {
        
    }
    
    public function validate ()
    {
        return true;
    }
    
    public function save ()
    {
        
    }

}
