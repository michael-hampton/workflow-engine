<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author michael.hampton
 */
interface Persistent
{

    /**
     * 
     */
    public function save ();
    
    /**
     * 
     * @param array $arrData
     */
    public function loadObject(array $arrData);
    
    /**
     * 
     */
    public function validate();
}
