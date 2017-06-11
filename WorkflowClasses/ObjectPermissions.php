<?php

class ObjectPermissions extends Permissions
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

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

    public function PermissionExists ($permission, $permissionType)
    {
        try {
            $oPro = $this->retrieveByPk ($permission, $permissionType);
            if ( $oPro !== null && is_array ($oPro) && !empty ($oPro) )
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

    public function deleteAll ($permissionType, $permission)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {

            if ( !$this->PermissionExists ($permission, $permissionType, $stepId = null) )
            {
                throw new Exception ("Permission doesnt exist");
            }

            $arrWhere = array("permission_type" => $permissionType, "permission" => $permission);

            if ( $stepId !== NULL )
            {
                $arrWhere['step_id'] = $stepId;
            }

            $this->objMysql->_delete ("workflow.step_permission", $arrWhere);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function removeObject ($objectType, $permissionType, $permission, $stepId)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        try {
            $this->objMysql->_delete ("workflow.step_permission", array("permission" => $permission, "permission_type" => $permissionType, "access_level" => $objectType, "step_id" => $stepId));
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
