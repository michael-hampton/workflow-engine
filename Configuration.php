<?php
class Configuration extends BaseConfiguration
{
    private $objMysql;
    
    public function __construct ()
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();
    }

        public function create(array $arrayData)
    {
        try {
            $this->setCfgUid($arrayData["CFG_UID"]);
            $this->setObjUid($arrayData["OBJ_UID"]);
            $this->setCfgValue((isset($arrayData["CFG_VALUE"]))? $arrayData["CFG_VALUE"] : "");
            $this->setProUid($arrayData["PRO_UID"]);
            $this->setUsrUid($arrayData["USR_UID"]);
            $this->setAppUid($arrayData["APP_UID"]);
            if ($this->validate()) {
                $result = $this->save();
                //Return
                return $result;
            } else {
                $msg = "";
                foreach ($this->getValidationFailures() as $validationFailure) {
                    $msg = $msg . (($msg != "")? "\n" : "") . $validationFailure;
                }
                throw new Exception("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function update($fields)
    {
        try {
            $this->loadObject($fields);
            if ($this->validate()) {
                $contentResult=0;
                $result=$this->save();
                $result=($result==0)?($contentResult>0?1:0):$result;
                return $result;
            } else {
                throw(new Exception("Failed Validation in class ".get_class($this)."."));
            }
        } catch (Exception $e) {
            throw($e);
        }
    }
    
    public function remove($CfgUid, $ObjUid, $ProUid, $UsrUid, $AppUid)
    {
        try {
            $this->setCfgUid($CfgUid);
            $this->setObjUid($ObjUid);
            $this->setProUid($ProUid);
            $this->setUsrUid($UsrUid);
            $this->setAppUid($AppUid);
            $result=$this->delete();
            return $result;
        } catch (Exception $e) {
            throw($e);
        }
    }
    /**
    * To check if the configuration row exists, by using Configuration Uid data
    */
    public function exists($CfgUid, $ObjUid = "", $ProUid = "", $UsrUid = "", $AppUid = "")
    {
        $result = $this->objMysql->_select("workflow.CONFIGURATION", [], ["CFG_UID" => $CfgUid]);
        
        if(!isset($result[0]) || empty($result[0])) {
            return false;
        }
        $objConfiguration = new Configuration();
        $objConfiguration->setCfgUid($result[0]['CFG_UID']);
        $objConfiguration->setCfgValue($result[0]['CFG_VALUE']);
        
        return $objConfiguration;
        
    }
}
