<?php

class Login
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function executeLogin ($strUsername, $strPassword)
    {
        $sql = "SELECT * FROM user_management.poms_users WHERE username = ? AND password = ?";
        $arrParameters = array($strUsername, md5 ($strPassword));
        $arrResultSet = $this->objMysql->_query ($sql, $arrParameters);

        if ( !empty ($arrResultSet) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getUserByUsername ($strUsername)
    {
        $strDbQuery = "SELECT u.*, r.role_name, r.role_id FROM user_management.poms_users u
                LEFT JOIN user_management.user_roles ur ON ur.userId = u.usrid
                LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                WHERE u.username = ?";

        $arrParameters = array($strUsername);
        $arrResultSet = $this->objMysql->_query ($strDbQuery, $arrParameters);

        return $arrResultSet;
    }

}
