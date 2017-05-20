<?php

class DashboardUser
{

    private $userId;
    private $dashboardId;
    private $orderId;
    private $arrFieldMapping = array(
        "user_id" => array("accessor" => "getUserId", "mutator" => "setUserId", "required" => "true", "type" => "int"),
        "dashboard_id" => array("accessor" => "getDashboardId", "mutator" => "setDashboardId", "required" => "true", "type" => "int"),
        "order_id" => array("accessor" => "getOrderId", "mutator" => "setOrderId", "required" => "true", "type" => "int"),
    );
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * @param array $arrData
     * @return bool
     */
    public function loadObject (array $arrData)
    {
        if ( !empty ($arrData) )
        {
            foreach ($this->arrFieldMapping as $strFieldKey => $arrFields) {
                if ( isset ($arrData[$strFieldKey]) )
                {
                    $strMutatorMethod = $arrFields['mutator'];

                    if ( is_callable (array($this, $strMutatorMethod)) )
                    {
                        call_user_func (array($this, $strMutatorMethod), $arrData[$strFieldKey]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getUserId ()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId ($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getDashboardId ()
    {
        return $this->dashboardId;
    }

    /**
     * @param mixed $dashboardId
     */
    public function setDashboardId ($dashboardId)
    {
        $this->dashboardId = $dashboardId;
    }

    /**
     * @return mixed
     */
    public function getOrderId ()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId ($orderId)
    {
        $this->orderId = $orderId;
    }

}
