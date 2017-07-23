<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SaveReport
 *
 * @author michael.hampton
 */
class SaveReport
{

    private $fields;
    private $tableName;
    private $objMysql;
    private $datebaseName = "task_manager";
    private $appUid;
    private $projectId;
    private $blUpdate = false;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    public function setTableName ($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getAppUid ()
    {
        return $this->appUid;
    }

    public function setAppUid ($appUid)
    {
        $this->appUid = $appUid;
        $this->fields['APP_UID'] = $appUid;
        $this->fields['APP_STATUS'] = 'ACTIVE';
    }

    public function getProjectId ()
    {
        return $this->projectId;
    }

    public function setProjectId ($projectId)
    {
        $this->projectId = $projectId;
        $this->fields['PRO_UID'] = $projectId;
    }

    public function setVariable ($fieldName, $fieldValue)
    {
        $this->fields[$fieldName] = $fieldValue;
    }

    public function getBlUpdate ()
    {
        return $this->blUpdate;
    }

    public function setBlUpdate ($blUpdate)
    {
        $this->blUpdate = $blUpdate;
    }

    public function save ()
    {
        if ( $this->blUpdate === FALSE )
        {
            $this->objMysql->_insert ($this->datebaseName . "." . $this->tableName, $this->fields);
        }
        else
        {
            $this->objMysql->_update ($this->datebaseName . "." . $this->tableName, $this->fields, [
                "pro_uid" => $this->projectId,
                "app_id" => $this->appUid
                    ]
            );
        }
    }

    //put your code here
}
