<?php

class Department
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Verify if exists the title of a Department
     *
     * @param string $departmentTitle      Title
     *
     * return bool Return true if exists the title of a Department, false otherwise
     */
    public function checkNameExists ($departmentTitle, $id = null)
    {

        $arrWhere = [];
        $sql = "SELECT * FROM user_management.departments WHERE department = ?";
        $arrWhere[] = $departmentTitle;

        if ( $id !== null )
        {
            $sql .= " AND id != ?";
            $arrWhere[] = $id;
        }

        $result = $this->objMysql->_query ($sql, $arrWhere);

        if ( isset ($result[0]['department']) && !empty ($result[0]['department']) )
        {
            return true;
        }

        return false;
    }

    /**
     * Verify if exists the title of a Department
     *
     * @param string $departmentTitle       Title
     * @param string $departmentUidExclude  Unique id of Department to exclude
     *
     * return void Throw exception if exists the title of a Department
     */
    public function throwExceptionIfExistsTitle ($departmentTitle, $id = null)
    {
        try {
            if ( $this->checkNameExists ($departmentTitle, $id) )
            {
                throw new Exception ("ID_DEPARTMENT_TITLE_ALREADY_EXISTS");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get list for Departments
     *
     * @access public
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return array
     */
    public function getDepartments ()
    {
        $oDepartment = new Departments();
        $aDepts = $oDepartment->getDepartments ('');

        return $aDepts;
    }

    /**
     * Get list for Departments
     * @var string $dep_uid. Uid for Department
     *
     * @access public
     *
     * @return array
     */
    public function getDepartment ($dep_uid)
    {
        $dep_uid = $this->depUid ($dep_uid);
        $result = $this->objMysql->_select ("user_management.departments", [], ["id" => $dep_uid]);

        $oDepaertment = new Departments();
        $record = $oDepaertment->loadDepartmentRecord ($result[0]);


        $oUsers = new UsersFactory();
        $manager = $result[0]['department_manager'];

        if ( $manager != '' && (int) $manager !== 0 )
        {
            $UserUID = $oUsers->getUser ($manager);
            $node['DEP_MANAGER_USERNAME'] = !empty ($UserUID->getUsername ()) ? $UserUID->getUsername () : '';
            $node['DEP_MANAGER_FIRSTNAME'] = !empty ($UserUID->getFirstName ()) ? $UserUID->getFirstName () : '';
            $node['DEP_MANAGER_LASTNAME'] = !empty ($UserUID->getLastName ()) ? $UserUID->getLastName () : '';
        }
        else
        {
            $node['DEP_MANAGER_USERNAME'] = '';
            $node['DEP_MANAGER_FIRSTNAME'] = '';
            $node['DEP_MANAGER_LASTNAME'] = '';
        }

        $record->setDeptManagerFirstName ($node['DEP_MANAGER_FIRSTNAME']);
        $record->setDeptManagerLastName ($node['DEP_MANAGER_LASTNAME']);
        $record->setDeptManagerUsername ($node['DEP_MANAGER_USERNAME']);

        return $record;
    }

    /**
     * Save Department
     * @var string $dep_data. Data for Process
     * @var string $create. Flag for create or update
     *
     * @access public
     *
     * @return array
     */
    public function saveDepartment ($dep_data, $create = true)
    {
        $this->isArray ($dep_data);
        $this->isNotEmpty ($dep_data, '$dep_data');
        $this->isBoolean ($create, '$create');

        if ( $create )
        {
            unset ($dep_data["id"]);
        }
        $oDepartment = new Departments();
        if ( isset ($dep_data['id']) && $dep_data['id'] != '' )
        {
            $this->depUid ($dep_data['id']);
        }

        if ( isset ($dep_data['department_manager']) && $dep_data['department_manager'] != '' )
        {
            $this->validateUserId ($dep_data['department_manager']);
        }
        if ( isset ($dep_data['status']) )
        {
            $this->depStatus ($dep_data['status']);
        }

        if ( !$create )
        {
            if ( isset ($dep_data["department"]) )
            {
                $this->throwExceptionIfExistsTitle ($dep_data["department"], $dep_data["id"]);
                $dep_data["department"] = $dep_data["department"];
            }

            $oDepartment->update ($dep_data);
        }
        else
        {
            if ( isset ($dep_data['department']) )
            {
                $this->throwExceptionIfExistsTitle ($dep_data["department"]);
            }
            else
            {
                throw (new Exception ("DEPARTMENT NAME IS MISSING"));
            }

            $dep_uid = $oDepartment->create ($dep_data);
            $response = $this->getDepartment ($dep_uid);
            return $response;
        }
    }

    /**
     * Delete department
     * @var string $dep_uid. Uid for department
     *
     * @access public
     *
     * @return array
     */
    public function deleteDepartment ($dep_uid)
    {
        $dep_uid = $this->depUid ($dep_uid);
        $oDepartment = new Departments();
        $countUsers = $oDepartment->countUsersInDepartment ($dep_uid);
        if ( $countUsers != 0 )
        {
            throw (new Exception ("ID_CANT_DELETE_DEPARTMENT_HAS_USERS"));
        }

        $oDepartment->remove ($dep_uid);
    }

    /**
     * Get all Users of a Department (Assigned/Available)
     *
     * @param string $departmentUid   Unique id of Department
     * @param string $option          Option (ASSIGNED, AVAILABLE)
     * @param array  $arrayFilterData Data of the filters
     * @param string $sortField       Field name to sort
     * @param string $sortDir         Direction of sorting (ASC, DESC)
     * @param int    $start           Start
     * @param int    $limit           Limit
     * @param bool   $flagRecord      Flag that set the "getting" of record
     * @param bool   $throwException  Flag to throw the exception (This only if the parameters are invalid)
     *                                (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array Return an array with all Users of a Department, ThrowTheException/FALSE otherwise
     */
    public function getUsers (
    $departmentUid, $option, array $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null, $flagRecord = true, $throwException = true
    )
    {
        try {
            $arrayUser = array();
            $numRecTotal = 0;
            //Verify data and Set variables
            $flagFilter = !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData['filter']);

            $arrayDepartmentData = $this->getDepartmentRecordByPk (
                    $departmentUid, ['$departmentUid' => '$departmentUid'], $throwException
            );
            if ( $arrayDepartmentData === false )
            {
                return false;
            }
            //Set variables
            $filterName = 'filter';
            if ( $flagFilter )
            {

                $filterName = (isset ($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : '';
            }

            //Get data
            if ( !is_null ($limit) && (string) ($limit) == '0' )
            {
                return [
                    'total' => $numRecTotal,
                    'start' => (int) ((!is_null ($start)) ? $start : 0),
                    'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                    $filterName => ($flagFilter) ? $arrayFilterData['filter'] : '',
                    'data' => $arrayUser
                ];
            }

            $arrWhere = array();

            //Query
            $criteria = "SELECT usrid, username, firstName, lastName, status FROM user_management.poms_users u";


            $criteria .= " WHERE u.status != 0";

            switch ($option) {
                case 'ASSIGNED':
                    $criteria .= " AND u.dept_id = ?";
                    $arrWhere[] = $departmentUid;
                    break;
                case 'AVAILABLE':
                    $criteria .= " AND u.dept_id = ''";
                    break;
            }
            if ( $flagFilter && trim ($arrayFilterData['filter']) != '' )
            {

                $search = (isset ($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : '';

                $criteria .= " AND (u.username LIKE ? OR u.firstName LIKE ? OR lastName LIKE ?)";
                $arrWhere[] = "%" . $search . "%";
                $arrWhere[] = "%" . $search . "%";
                $arrWhere[] = "%" . $search . "%";
            }
            //Number records total
            $countResult = $this->objMysql->_query ($criteria, $arrWhere);
            $numRecTotal = count ($countResult);

            //Query
            if ( !is_null ($sortField) && trim ($sortField) != '' )
            {
                $sortField = trim ($sortField);
            }
            else
            {
                $sortField = "u.username";
            }
            if ( !is_null ($sortDir) && trim ($sortDir) != '' && strtoupper ($sortDir) == 'DESC' )
            {
                $criteria .= $sortField . " DESC";
            }
            else
            {
                $criteria .= " ORDER BY " . $sortField . " ASC";
            }
            if ( !is_null ($start) )
            {
                $criteria .= " OFFSET " . ((int) ($start));
            }
            if ( !is_null ($limit) )
            {
                $criteria .= " LIMIT " . ((int) ($limit));
            }

            $records = $this->objMysql->_query ($criteria, $arrWhere);

            foreach ($records as $record) {
                switch ($option) {
                    case 'ASSIGNED':
                        $record['USR_SUPERVISOR'] = $record['usrid'] == $arrayDepartmentData->getDepartmentManager ();
                        break;
                    case 'AVAILABLE':
                        break;
                }
                $arrayUser[] = $this->__getUserCustomRecordFromRecord ($record);
            }

            //Return
            return [
                'total' => $numRecTotal,
                'start' => (int) ((!is_null ($start)) ? $start : 0),
                'limit' => (int) ((!is_null ($limit)) ? $limit : 0),
                $filterName => ($flagFilter) ? $arrayFilterData['filter'] : '',
                'data' => $arrayUser
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get custom record
     *
     * @param array $record Record
     *
     * @return array Return an array with custom record
     */
    private function __getUserCustomRecordFromRecord (array $record)
    {
        try {
            $objUser = new Users();
            $objUser->setFirstName ($record['firstName']);
            $objUser->setLastName ($record['lastName']);
            $objUser->setStatus ($record['status']);
            $objUser->setUsername ($record['username']);
            $objUser->setUserId ($record['usrid']);
            $objUser->setSupervisor ($record['USR_SUPERVISOR']);

            return $objUser;


//            $recordc = [
//                'usr_uid'       => $record['USR_UID'],
//                'usr_username'  => $record['USR_USERNAME'],
//                'usr_firstname' => $record['USR_FIRSTNAME'],
//                'usr_lastname'  => $record['USR_LASTNAME'],
//                'usr_status'    => $record['USR_STATUS']
//            ];
//            if (isset($record['USR_SUPERVISOR'])) {
//                $recordc['usr_supervisor'] = $record['USR_SUPERVISOR'];
//            }
//            return $recordc;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Department record
     *
     * @param string $departmentUid                 Unique id of Department
     * @param array  $arrayVariableNameForException Variable name for exception
     * @param bool   $throwException Flag to throw the exception if the main parameters are invalid or do not exist
     *                               (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array Returns an array with Department record, ThrowTheException/FALSE otherwise
     */
    public function getDepartmentRecordByPk (
    $departmentUid, array $arrayVariableNameForException, $throwException = true
    )
    {
        try {
            $objDepartments = new Departments();
            $obj = $objDepartments->retrieveByPK ($departmentUid);
            if ( is_null ($obj) )
            {
                if ( $throwException )
                {
                    throw new Exception ('ID_DEPARTMENT_NOT_EXIST');
                }
                else
                {
                    return false;
                }
            }
            //Return
            return $objDepartments->loadDepartmentRecord ($obj);
        } catch (Exception $e) {
            throw $e;
        }
    }

}
