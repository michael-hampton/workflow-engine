<?php

class DashboardFactory
{

    private $userId;
    private $dashboardId;
    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    private function getDashboardsForUser ()
    {
        $result = $this->objMysql->_select ("dashboard.user_dashboard", array(), array("user_id" => $this->userId), array("order_id" => "ASC"));

        if ( !empty ($result) )
        {
            return $result;
        }

        return [];
    }

    public function loadDashboards ($userId, $arrProjects)
    {
        $this->userId = $userId;
        $arrUserDashboards = $this->getDashboardsForUser ();

        $html = '';

        foreach ($arrUserDashboards as $arrUserDashboard) {

            $this->dashboardId = $arrUserDashboard['dashboard_id'];
            $objDashboard = $this->getDashbaordById ();

            $objDashboardInstance = new DashboardInstance ($arrProjects);

            $functionName = $objDashboard->getFunction ();

            $arrDashboardData = [];

            if ( !empty ($functionName) )
            {
                $arrDashboardData = call_user_func (array($objDashboardInstance, $functionName));
            }

            $objDashboardBuilder = new DashboardBuilder();

            $html .= $objDashboardBuilder->buildDashboard ($objDashboard, $arrDashboardData);
        }

        return $html;
    }

    private function getDashbaordById ()
    {
        $result = $this->objMysql->_select ("dashboard.dashboard", array(), array("id" => $this->dashboardId));

        if ( empty ($result) )
        {
            return [];
        }

        $objDashboard = new Dashboard();
        $objDashboard->setChartType ($result[0]['chart_type']);
        $objDashboard->setColumns ($result[0]['columns']);
        $objDashboard->setDescription ($result[0]['description']);
        $objDashboard->setFunction ($result[0]['function_name']);
        $objDashboard->setId ($result[0]['id']);
        $objDashboard->setTitle ($result[0]['title']);
        $objDashboard->setAlgorithm ($result[0]['algorithm']);

        return $objDashboard;
    }
}
