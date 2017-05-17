<?php

class Message
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }
}
