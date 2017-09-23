<?php

class Users extends BaseUser
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Create User
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new User created
     */
    public function create ($aData, Users $objAuditUser)
    {
        try {
            
            $objUser = new Users(null, $objAuditUser);

            $objUser->loadObject ($aData);

            if ( $objUser->validate () )
            {
                $userId = $objUser->save ();

                return $userId;
            }
            else
            {
                $sMessage = '';

                $aValidationFailures = $objUser->getValidationFailures ();

                foreach ($aValidationFailures as $message) {

                    $sMessage .= $message . '<br />';
                }

                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

    public function update ($fields, Users $objAuditUser)
    {
        
        try {
            $user = new Users(null, $objAuditUser);
            $user->loadObject ($fields);
            
            if ( $user->validate () )
            {
                $result = $user->save ();
                return $result;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $user->getValidationFailures ();

                foreach ($aValidationFailures as $message) {
                    $sMessage .= $message . '<br />';
                }
                throw (new Exception ('The user cannot be updated!<br />' . $sMessage));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function remove ($UsrUid)
    {
        try {
            $user = new Users();
            $user->setUserId ($UsrUid);
            $result = $user->disableUser ();
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**

     * to create an user

     *

     * @access public

     * @param array $aData

     * @param string $sRolCode

     * @return $sUserUID

     */
    public function createUser (Users $objAuditUser, $aData = array(), $sRolCode = '')
    {

        if ( $aData["status"] . "" == "1" )
        {

            $aData["status"] = "ACTIVE";
        }



        if ( $aData["status"] . "" == "0" )
        {

            $aData["status"] = "INACTIVE";
        }

        if ( $aData['status'] == 'ACTIVE' )
        {

            $aData['status'] = 1;
        }

        if ( $aData['status'] == 'INACTIVE' )
        {

            $aData['status'] = 0;
        }

        $sUserUID = $this->create ($aData, $objAuditUser);

        if ( $sRolCode != '' )
        {

            $this->assignRoleToUser ($sUserUID, $sRolCode);
        }

        return $sUserUID;
    }

    /**

     * updated an user

     *

     * @access public

     * @param array $aData

     * @param string $sRolCode

     * @return void

     */
    public function updateUser (Users $objAuditUser, $aData = array(), $sRolCode = '')
    {

        if ( isset ($aData['status']) )
        {

            if ( $aData['status'] == 'ACTIVE' )
            {

                $aData['status'] = 1;
            }
        }

        $this->update ($aData, $objAuditUser);

        if ( $sRolCode != '' )
        {

            $this->removeRolesFromUser ($aData['USR_UID']);

            $this->assignRoleToUser ($aData['USR_UID'], $sRolCode);
        }
    }

    public function retrieveByPk ($usrUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("user_management.poms_users", array(), array("usrid" => $usrUid));

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }
        
        return $this->loadObject ($result[0]);
    }
}
