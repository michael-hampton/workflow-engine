<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupUser
 *
 * @author michael.hampton
 */
class GroupUser
{

    use Validator;
    
    private $objMysql;
    
    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Verify if doesn't exist the User in Group
     *
     * @param string $groupUid              Unique id of Group
     * @param string $userUid               Unique id of User
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if doesn't exist the User in Group
     */
    public function throwExceptionIfNotExistsGroupUser ($groupUid, $userUid, $fieldNameForException)
    {
        try {
            $obj = \GroupUserPeer::retrieveByPK ($groupUid, $userUid);
            if ( !(is_object ($obj) && get_class ($obj) == "GroupUser") )
            {
                throw new \Exception (\G::LoadTranslation ("ID_GROUP_USER_IS_NOT_ASSIGNED", array($fieldNameForException, $userUid)));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve object using using composite pkey values.
     * @param string $grp_uid
     * @param string $usr_uid
     * @param      Connection $con
     * @return     GroupUser
     */
    public function retrieveByPK ($grp_uid, $usr_uid)
    {
      
        $v = $this->objMysql->_select("user_management.poms_users", [], ["team_id" => $grp_uid, "usrid" => $usr_uid]);
        
        return !empty ($v) ? $v[0] : null;
    }

    /**
     * Verify if exists the User in Group
     *
     * @param string $groupUid              Unique id of Group
     * @param string $userUid               Unique id of User
     *
     * return void Throw exception if exists the User in Group
     */
    public function throwExceptionIfExistsGroupUser ($groupUid, $userUid)
    {
        try {
            $obj =  $this->retrieveByPK ($groupUid, $userUid);
            
            if ( $obj !== null )
            {
                throw new Exception ("ID_GROUP_USER_IS_ALREADY_ASSIGNED");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Assign User to Group
     *
     * @param string $groupUid  Unique id of Group
     * @param array  $arrayData Data
     *
     * return array Return data of the User assigned to Group
     */
    public function create ($groupUid, $userId)
    {
        try {

            $group = new Team();
            $group->throwExceptionIfNotExistsGroup ($groupUid);
            $this->validateUserId ($userId);
            $this->throwExceptionIfExistsGroupUser ($groupUid, $userId);
            //Create
            $group = new Teams();
            
            $group->addUserToGroup ($groupUid, $userId);
            //Return
            $arrayData = array("GRP_UID" => $groupUid, "USR_UID" => $userId);

            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /**
     * Unassign User of the Group
     *
     * @param string $groupUid Unique id of Group
     * @param string $userUid  Unique id of User
     *
     * return void
     */
    public function delete ($userUid, $groupUid = null)
    {
        try {
            //Verify data
            $group = new Team();
            if($groupUid !== null) {
                 $group->throwExceptionIfNotExistsGroup ($groupUid); 
                 $this->throwExceptionIfNotExistsGroupUser ($groupUid, $userUid, $this->arrayFieldNameForException["userUid"]);
            }
           
            $this->validateUserId ($userUid);
           
            //Delete
            $groups = new Teams();
            $group->removeUsersFromGroup ($userUid, $groupUid);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
