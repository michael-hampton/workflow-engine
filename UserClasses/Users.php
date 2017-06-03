<?php

class Users extends BaseUser
{

    /**
     * Create User
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new User created
     */
    public function create ($aData)
    {
        try {

            $this->loadObject ($aData);

            if ( $this->validate () )
            {
                $userId = $this->save ();

                return $userId;
            }
            else
            {

                $sMessage = '';

                $aValidationFailures = $this->getValidationFailures ();

                foreach ($aValidationFailures as $message) {

                    $sMessage .= $message . '<br />';
                }

                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

    public function update ($fields)
    {

        try {
            $user = new Users();
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
    public function createUser ($aData = array(), $sRolCode = '')
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


        $sUserUID = $this->create ($aData);



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

    public function updateUser ($aData = array(), $sRolCode = '')

    {

        if (isset( $aData['status'] )) {

            if ($aData['status'] == 'ACTIVE') {

                $aData['status'] = 1;

            }

        }

        $this->update( $aData );

        if ($sRolCode != '') {

            $this->removeRolesFromUser( $aData['USR_UID'] );

            $this->assignRoleToUser( $aData['USR_UID'], $sRolCode );

        }

    }

    /**

     * to put role an user

     *

     * @access public

     * @param string $sUserUID

     * @param string $sRolCode

     * @return void

     */
    public function assignRoleToUser ($sUserUID = '', $sRolCode = '')
    {
//      $role = new Roles($sRolCode);
//
//        $aRol = $this->rolesObj->loadByCode ($sRolCode);
//
//        $this->usersRolesObj->create ($sUserUID, $aRol['ROL_UID']);
//        
//         function create($sUserUID = '', $sRolUID = '') {
//    $oRole  = new UsersRoles();
//    $oRole->setUsrUid($sUserUID);
//    $oRole->setRolUid($sRolUID);
//    $oRole->save();
//  }
    }

    /**

     * remove a role from an user

     *

     * @access public

     * @param array $sUserUID

     * @return void

     */
    public function removeRolesFromUser ($sUserUID = '')
    {

        $oCriteria = new Criteria ('rbac');

        $oCriteria->add (UsersRolesPeer::USR_UID, $sUserUID);

        UsersRolesPeer::doDelete ($oCriteria);
    }

}
