<?php

class Department extends BaseDepartment
{

    private $objMysql;

    public function __construct ($deptId = null)
    {
        parent::__construct ($deptId);
        $this->objMysql = new Mysql2();
    }

    /**
     * Create the Department
     *
     * @param array $aData
     * @return void
     */
    public function create ($aData)
    {
        try {
            if ( isset ($aData['DEP_UID']) )
            {
                $this->setDepUid ($aData['DEP_UID']);
            }


            if ( isset ($aData['department_manager']) )
            {
                $this->setDepartmentManager ($aData['department_manager']);
            }
            else
            {
                $this->setDepartmentManager ("");
            }

            if ( isset ($aData['status']) )
            {
                $this->setStatus ($aData['status']);
            }
            else
            {
                $this->setStatus (1);
            }

            if ( isset ($aData['department']) )
            {
                $this->setDepartment ($aData['department']);
            }
            else
            {
                $this->setDepartment ('');
            }
            if ( $this->validate () )
            {
                $this->save ();
                return $this->getId ();
            }
            else
            {
                $msg = '';
                foreach ($this->getValidationFailures () as $message) {
                    $msg .= $message . "<br/>";
                }
                throw (new Exception (" The Department row cannot be created $msg "));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     Department
     */
    public function retrieveByPK ($pk)
    {

        $result = $this->objMysql->_select ("user_management.departments", [], ["id" => $pk]);

        return !empty ($result[0]) > 0 ? $result[0] : null;
    }

    public function getDepartmentObject ($deptUid)
    {
        $oPro = $this->loadDepartmentRecord ($this->retrieveByPK ($deptUid));
        return $oPro;
    }

    /**
     * Update the Dep row
     *
     * @param array $aData
     * @return variant
     *
     */
    public function update ($aData)
    {
        try {
            $oPro = $this->loadDepartmentRecord ($this->retrieveByPK ($aData['id']));

            if ( is_object ($oPro) && get_class ($oPro) == 'Departments' )
            {
                $oPro->loadObject ($aData);
                if ( $oPro->validate () )
                {
                    if ( isset ($aData['department']) )
                    {
                        $oPro->setDepartment ($aData['department']);
                    }
                    if ( isset ($aData['status']) )
                    {
                        $oPro->setStatus ($aData['status']);
                    }

                    if ( isset ($aData['department_manager']) )
                    {
                        $oPro->setDepartmentManager ($aData['department_manager']);
                    }
                    $res = $oPro->save ();
                    return $res;
                }
                else
                {
                    $msg = '';
                    foreach ($this->getValidationFailures () as $message) {
                        $msg .= $message . "<br/>";
                    }
                    throw (new Exception ('The Department row cannot be created! ' . $msg));
                }
            }
            else
            {
                throw (new Exception ("The row '" . $aData['DEP_UID'] . "' in table Department doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Remove the row
     *
     * @param array $aData or string $ProUid
     * @return string
     *
     */
//    public function remove ($ProUid)
//    {
//        if ( is_array ($ProUid) )
//        {
//            $ProUid = (isset ($ProUid['DEP_UID']) ? $ProUid['DEP_UID'] : '');
//        }
//        try {
//            $oCriteria = new Criteria ('workflow');
//            $oCriteria->addSelectColumn (UsersPeer::USR_UID);
//            $oCriteria->add (UsersPeer::DEP_UID, $ProUid, Criteria::EQUAL);
//            $oDataset = UsersPeer::doSelectRS ($oCriteria);
//            $oDataset->setFetchmode (ResultSet::FETCHMODE_ASSOC);
//            $oDataset->next ();
//            $aFields = array();
//            while ($aRow = $oDataset->getRow ()) {
//                $aFields['USR_UID'] = $aRow['USR_UID'];
//                $aFields['DEP_UID'] = '';
//                $oDepto = UsersPeer::retrieveByPk ($aFields['USR_UID']);
//                if ( is_object ($oDepto) && get_class ($oDepto) == 'UsersPeer' )
//                {
//                    return true;
//                }
//                else
//                {
//                    $oDepto = new Users();
//                    $oDepto->update ($aFields);
//                }
//                $oDataset->next ();
//            }
//            $oPro = DepartmentPeer::retrieveByPK ($ProUid);
//            if ( !is_null ($oPro) )
//            {
//                $dptoTitle = $this->Load ($oPro->getDepUid ());
//                Content::removeContent ('DEPO_TITLE', '', $oPro->getDepUid ());
//                Content::removeContent ('DEPO_DESCRIPTION', '', $oPro->getDepUid ());
//                G::auditLog ("DeleteDepartament", "Departament Name: " . $dptoTitle['DEP_TITLE'] . " Departament ID: (" . $oPro->getDepUid () . ") ");
//                return $oPro->delete ();
//            }
//            else
//            {
//                throw (new Exception ("The row '$ProUid' in table Group doesn't exist!"));
//            }
//        } catch (Exception $oError) {
//            throw ($oError);
//        }
//    }
    // select departments
    // this function is used to draw the hierachy tree view
    public function getDepartments ()
    {
        try {
            $result = array();
            $objects = $this->objMysql->_select ("user_management.departments");

            $oUsers = new \BusinessModel\UsersFactory();

            foreach ($objects as $oDepartment) {
                $node = array();

                $record = $this->loadDepartmentRecord ($oDepartment);

                $manager = $oDepartment['department_manager'];
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

                $count = $this->objMysql->_select ("user_management.poms_users", [], ["dept_id" => $oDepartment['id']]);
                $node['DEP_MEMBERS'] = count ($count);

                $result[] = $record;
            }

            return $result;
        } catch (exception $e) {
            throw $e;
        }
    }

    /**
     * Load the Department row specified in [depo_id] column value.
     *
     * @param string $ProUid the uid of the Prolication
     * @return array $Fields the fields
     */
    public function existsDepartment ($DepUid)
    {
        $result = $this->objMysql->_select ("user_management.departments", [], ["id" => $DepUid]);
        
        $oPro = $this->loadDepartmentRecord ($result[0]);
        if ( is_object ($oPro) && get_class ($oPro) == 'Department' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function loadDepartmentRecord ($record)
    {
        $objDepartment = new Department();
        $objDepartment->setDepartment ($record['department']);
        $objDepartment->setDepartmentManager ($record['department_manager']);
        $objDepartment->setId ($record['id']);
        $objDepartment->setStatus ($record['status']);

        return $objDepartment;
    }

    public function addUserToDepartment (Department $objDepartment, Users $objUser)
    {
        try {
            
            $objUser->setDept_id ($objDepartment->getId ());
            $objUser->save ();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function updateDepartmentManager ($deptUid, $deptManager)
    {
        try {
            $this->setId ($deptUid);
            $this->setDepartmentManager ($deptManager);
            $this->save ();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function removeUserFromDepartment ($depUid, $userUid)
    {
        try {
            $objUser = new Users();
            $objUser->setDept_id ($depUid);
            $objUser->setUserId ($userUid);
        } catch (Exception $ex) {
            throw $ex;
        }

        $objUser->save ();
    }

}
