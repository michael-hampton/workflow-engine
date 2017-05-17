<?php

class RolesFactory
{

    private $objMysql;

    public function __construct ($permId = null)
    {
        $this->objMysql = new Mysql2();
        $this->permId = $permId;
    }

    public function getRoles ($pageLimit = 10, $page = 0, $strOrderBy = "role_name", $strOrderDir = "ASC")
    {

        $arrRoles = $this->objMysql->_select ("user_management.roles", array(), array(), array($strOrderBy => $strOrderDir), (int) $pageLimit, (int) $page);

        $arrAllRoles = [];

        foreach ($arrRoles as $key => $arrRole) {
            $objRoles = new Roles();
            $objRoles->loadObject ($arrRole);
            $arrAllRoles[$key] = $objRoles;
        }

        return $arrAllRoles;
    }

    public function getPermissions ()
    {
        $arrWhere = array();

        if ( $this->permId !== null )
        {
            $arrWhere['perm_id'] = $this->permId;
        }

        $arrPermissions = $this->objMysql->_select ("user_management.permissions", array(), $arrWhere);

        $arrAllPermissions = array();

        foreach ($arrPermissions as $key => $arrPermission) {

            $objPermissions = new Roles();
            $objPermissions->loadObject ($arrPermission);
            $arrAllPermissions[$key] = $objPermissions;
        }

        return $arrAllPermissions;
    }

    public function getPermissionsWithRoles ()
    {
        $arrPermissions = $this->objMysql->_query ("SELECT * FROM user_management.`role_perms` rp
                                                    INNER JOIN user_management.permissions p ON p.perm_id = rp.`perm_id`
                                                    INNER JOIN user_management.roles r ON r.role_id = rp.`role_id");

        $arrAllPermissions = array();

        foreach ($arrPermissions as $key => $arrPermission) {

            $objPermissions = new Roles();
            $objPermissions->loadObject ($arrPermission);
            $arrAllPermissions[$key] = $objPermissions;
        }

        return $arrAllPermissions;
    }

    public function getAllRoles ()
    {
        $roles = $this->objMysql->_select ("user_management.roles", array(), array());

        $arrAllRoles = [];

        foreach ($roles as $key => $role) {
            $objPermissions = new Roles();
            $objPermissions->loadObject ($role);
            $arrAllRoles[$key] = $objPermissions;
        }

        return $arrAllRoles;
    }

}
