<?php

class UsersFactory
{

    private $objMysql;
    private $userId;
    private $deptId;
    private $permId;
    private $teamId;

    /**
     * 
     * @param type $userId
     * @param type $deptId
     * @param type $permId
     */
    public function __construct ($userId = null, $deptId = null, $permId = null, $teamId = null)
    {
        $this->objMysql = new Mysql2();
        $this->userId = $userId;
        $this->deptId = $deptId;
        $this->permId = $permId;
        $this->teamId = $teamId;
    }

    /**
     * 
     * @param type $searchText
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return type
     */
    public function countUsers ($searchText = null, $pageLimit = 10, $page = 0, $strOrderBy = "u.username", $strOrderDir = "ASC")
    {
        $arrWhere = array();

        $query = "SELECT
                    u.*,
                    r.role_name ,
                    d.department
                    FROM user_management.poms_users u
                    LEFT JOIN user_management.user_roles ur ON ur.`userId` = u.usrid
                    LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                    LEFT join user_management.departments d ON d.id - u.dept_id
                    WHERE 1=1";

        if ( !empty ($searchText) && $searchText !== null )
        {
            $query .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
            $arrWhere[] = "%" . $searchText . "%";
            $arrWhere[] = "%" . $searchText . "%";
        }

        if ( $this->userId !== null )
        {
            $query .= " AND u.usrid = ?";
            $arrWhere[] = $this->userId;
        }


        $query .= " GROUP BY u.usrid";
        $query .= " ORDER BY " . $strOrderBy . " " . $strOrderDir;

        $arrUsers = $this->objMysql->_query ($query, $arrWhere);

        return count ($arrUsers);
    }

    /**
     * 
     * @param type $searchText
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return \Users
     */
    public function getUsers ($searchText = null, $pageLimit = 10, $page = 0, $strOrderBy = "u.username", $strOrderDir = "ASC")
    {
        $totalRows = $this->countUsers ($searchText, $pageLimit, $page, $strOrderBy, $strOrderDir);

        $arrWhere = array();

        $query = "SELECT
                    u.*,
                    r.role_name ,
                    d.department
                    FROM user_management.poms_users u
                    LEFT JOIN user_management.user_roles ur ON ur.`userId` = u.usrid
                    LEFT JOIN user_management.roles r ON r.role_id = ur.roleId
                    LEFT join user_management.departments d ON d.id - u.dept_id
                    WHERE 1=1";

        if ( !empty ($searchText) && $searchText !== null )
        {
            $query .= " AND (u.username LIKE ? OR u.user_email LIKE ?)";
            $arrWhere[] = "%" . $searchText . "%";
            $arrWhere[] = "%" . $searchText . "%";
        }

        if ( $this->userId !== null )
        {
            $query .= " AND u.usrid = ?";
            $arrWhere[] = $this->userId;
        }

        $query .= " GROUP BY u.usrid";

        $query .= " ORDER BY " . $strOrderBy . " " . $strOrderDir;

        ///////////////////////////////////////////////////////////////////////////////////////////////
        //
        //      Pagination
        //

        
        //all rows
        $_SESSION["pagination"]["total_counter"] = $totalRows;

        $current_page = $page;
        $startwith = $pageLimit * $page;
        $total_pages = $totalRows / $pageLimit;
        $_SESSION["pagination"]["current_page"] = $current_page;

        // calculating displaying pages
        $_SESSION["pagination"]["total_pages"] = (int) ($totalRows / $pageLimit);
        if ( fmod ($totalRows, $pageLimit) > 0 )
            $_SESSION["pagination"]["total_pages"] ++;

        $query .= " LIMIT " . $page . ", " . $pageLimit;

        $arrUsers = $this->objMysql->_query ($query, $arrWhere);

        $arrAllUsers = array();


        if ( !empty ($arrUsers) )
        {
            foreach ($arrUsers as $key => $arrUser) {
                $objUser = new Users ($arrUser['usrid']);
                $objUser->loadObject ($arrUser);
                $arrAllUsers[$key] = $objUser;
            }

            return $arrAllUsers;
        }

        return [];
    }

    /**
     * 
     * @return \Departments
     */
    public function getDepartments ()
    {
        $arrWhere = array();

        if ( $this->deptId !== null )
        {
            $arrWhere['id'] = $this->deptId;
        }

        $arrDepartments = $this->objMysql->_select ("user_management.departments", array(), $arrWhere);

        $arrAllDepartments = array();

        if ( !empty ($arrDepartments) )
        {
            foreach ($arrDepartments as $key => $arrDepartment) {

                $objDepartments = new Departments();
                $objDepartments->loadObject ($arrDepartment);
                $arrAllDepartments[$key] = $objDepartments;
            }

            return $arrAllDepartments;
        }
        
        return [];
    }

    /**
     * 
     * @param type $pageLimit
     * @param type $page
     * @param type $strOrderBy
     * @param type $strOrderDir
     * @return \Teams
     */
    public function getTeams ($pageLimit = 10, $page = 0, $strOrderBy = "team_name", $strOrderDir = "ASC")
    {
        $arrWhere = array();

        if ( $this->teamId !== null )
        {
            $arrWhere['team_id'] = $this->teamId;
        }

        $arrTeams = $this->objMysql->_select ("user_management.teams", array(), $arrWhere, array($strOrderBy => $strOrderDir), (int) $pageLimit, (int) $page);

        $arrAllTeams = array();

        if ( !empty ($arrTeams) )
        {
            foreach ($arrTeams as $key => $arrTeam) {

                $objTeams = new Teams();
                $objTeams->loadObject ($arrTeam);
                $arrAllTeams[$key] = $objTeams;
            }

            return $arrAllTeams;
        }

        return [];
    }

}
