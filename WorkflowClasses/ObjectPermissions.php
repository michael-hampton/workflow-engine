<?php

class ObjectPermissions extends Permissions
{

    public function create ($permission)
    {
        try {
            $this->setPermissionType (trim ($permission['objectType']));
            $this->setAccessLevel (trim ($permission['permissionType']));
            
            if ( $permission['objectType'] == "team" )
            {
                $this->setTeamId ($permission['id']);
            }
            else
            {
                $this->setUserId ($permission['id']);
            }

            $result = $this->save ();
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function Exists ($Uid)
    {
        try {
            $oPro = ObjectPermissionPeer::retrieveByPk ($Uid);
            if ( is_object ($oPro) && get_class ($oPro) == 'ObjectPermission' )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function remove ($Uid)
    {
        $con = Propel::getConnection (ObjectPermissionPeer::DATABASE_NAME);
        try {
            $oObjPer = ObjectPermissionPeer::retrieveByPK ($Uid);
            if ( is_object ($oObjPer) && get_class ($oObjPer) == 'ObjectPermission' )
            {
                $con->begin ();
                $iResult = $oObjPer->delete ();
                $con->commit ();
                return $iResult;
            }
            else
            {
                throw (new Exception ("The row '" . $Uid . "' in table CaseTrackerObject doesn't exist!"));
            }
        } catch (exception $e) {
            $con->rollback ();
            throw ($e);
        }
    }

    public function update ($aFields)
    {
        $oConnection = Propel::getConnection (ObjectPermissionPeer::DATABASE_NAME);
        try {
            $oConnection->begin ();
            $this->load ($aFields['OP_UID']);
            $this->fromArray ($aFields, BasePeer::TYPE_FIELDNAME);
            if ( $this->validate () )
            {
                $iResult = $this->save ();
                $oConnection->commit ();
                return $iResult;
            }
            else
            {
                $oConnection->rollback ();
                throw (new Exception ('Failed Validation in class ' . get_class ($this) . '.'));
            }
        } catch (Exception $e) {
            $oConnection->rollback ();
            throw ($e);
        }
    }

}
