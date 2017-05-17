<?php

class Users
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function getPagePermissions ($strPageName)
    {
        $sql = "SELECT page_id FROM user_management.pages WHERE page_url = ?";
        $arrParameters = array($strPageName);
        $arrResultSet = $this->_query ($sql, $arrParameters);

        if ( isset ($arrResultSet[0]['page_id']) && !empty ($arrResultSet[0]['page_id']) )
        {
            $sql2 = "SELECT perm_id FROM user_management.page_permission_mapping WHERE page_id = ?";
            $arrParameters2 = array($arrResultSet[0]['page_id']);
            $arrResultSet2 = $this->objMysql->_query ($sql2, $arrParameters2);

            if ( !empty ($arrResultSet2) )
            {
                return $arrResultSet2[0]['perm_id'];
            }
        }
    }

    public function getRolesForPage ($intPageId)
    {
        $sql = "SELECT m.perm_id, p.perm_name FROM user_management.page_permission_mapping m 
		INNER JOIN user_management.permissions p ON p.perm_id = m.perm_id
		WHERE m.page_id = ?";

        $arrParameters = array($intPageId);
        $arrResultSet = $this->fetchAll ($sql, $arrParameters);

        return $arrResultSet;
    }

    public function getPages ()
    {
//		$sql = "SELECT * FROM user_management.pages ORDER BY page_name ASC";
//		$result=$this->db->query($sql);
//		$arrResultSet = $result->fetchAll();
//		
//		return $arrResultSet;
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

    public function getAllUsers ()
    {
        $strDbQuery = "SELECT u.*, r.role_name FROM user_management.poms_users u
        LEFT JOIN user_management.user_roles ur ON ur.userId = u.usrid
        LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
        WHERE u.status = 1";
        $arrResultSet = $this->fetchAll ($strDbQuery);


        return $arrResultSet;
    }

    public function getChannelsForUser ($intUserId)
    {
        $strDbQuery = "SELECT c.*, w.websiteName FROM user_management.user_channels c
                        INNER JOIN channels.websites w ON w.websiteId = c.websiteId
                        WHERE c.userId = ?
                        ORDER BY websiteName";
        $arrParameters = array($intUserId);
        $arrResultSet = $this->fetchAll ($strDbQuery, $arrParameters);

        return $arrResultSet;
    }

    public function getRolesForUser ($intUserId)
    {
        $strDbQuery = "SELECT ur.*, r.role_name FROM user_management.user_roles ur
                        INNER JOIN user_management.roles r ON r.role_id = ur.roleId
                        WHERE ur.userId = ?
                        ";

        $arrParameters = array($intUserId);
        $arrResultSet = $this->fetchAll ($strDbQuery, $arrParameters);

        return $arrResultSet;
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

    public function getUsersForSystemRole ($roleId)
    {
        return $this->objMysql->_query ("SELECT u.* FROM user_management.poms_users u 
                                        INNER JOIN user_management.user_roles ur ON ur.userId = u.usrId
                                        WHERE ur.roleId = ?", array($roleId));
    }

}
