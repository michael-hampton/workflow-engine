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

            //echo $objDashboard->getChartType () . " " . $objDashboard->getTitle () . " ";

            $functionName = $objDashboard->getFunction ();

            $arrDashboardData = [];

            if ( !empty ($functionName) )
            {
                $arrDashboardData = call_user_func (array($objDashboardInstance, $functionName));
            }



            $html .= $this->buildDashboard ($objDashboard, $arrDashboardData);
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

    private function random_color_part ()
    {
        return str_pad (dechex (mt_rand (0, 255)), 2, '0', STR_PAD_LEFT);
    }

    private function random_color ()
    {
        return $this->random_color_part () . $this->random_color_part () . $this->random_color_part ();
    }

    private function buildDashboard (Dashboard $objDashboard, $arrData)
    {
        $html = "";
        $type = "bar";

        $html .= '<div class="col-lg-4">';

        $html .= '<div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>' . $objDashboard->getTitle () . '</h5>
                    </div>';

        $html .= '<div class="ibox-content">';

        echo $objDashboard->getChartType ();

        switch (trim ($objDashboard->getChartType ())) {

            case "table":

                reset ($arrData);
                $first_key = key ($arrData);

                $keys = array_keys ($arrData[$first_key]);

                $html .= "<table>";
                $html .= "<thead>";


                foreach ($keys as $key) {
                    $html .= "<th>" . $key . "</th>";
                }

                $html .= "</thead>";

                foreach ($arrData as $columnName => $data) {
                    $html .= "<tr>";
                    if ( is_array ($data) )
                    {
                        foreach ($data as $column) {
                              $html .= "<td>" . $column . "</td>";
                        }
                    }
                    else
                    {

                        $html .= "<td>" . $data . "</td>";
                    }

                    $html .= "</tr>";
                }

                $html .= "<tbody>";

                $html .= "</tbody>";

                $html .= "</table>";

                break;


            case "text":
                if ( !is_array ($arrData) )
                {
                    $html .= $arrData;
                }


                break;

            case "pie":

                $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
                $title = str_replace (":", "", $title);
                $hash = $title . rand (0, 10);

                $html .= '<div id="' . $hash . '"></div>';

                $html .= '<script>';

                $html .= "c3.generate ({
                    bindto: '#" . $hash . "',
                    data: {";


                $count = 0;
                $colours = '';
                $rows = '';
                foreach ($arrData as $key => $value) {
                    $colour = $this->random_color ();
                    $colours .= "data" . $count . ": '#" . $colour . "',";
                    $rows .= "['" . $key . "', " . $value . "],";

                    $count++;
                }

                $html .= "  columns: [";
                $html .= rtrim ($rows, ",");
                $html .= "],";

                $html .= " colors: {";
                $html .= rtrim ($colours, ",");
                $html .= "},";

                $html .= "type: 'pie'
                    }
                });";

                $html .= '</script>';
                break;

            case "bar":

                $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
                $title = str_replace (":", "", $title);
                $hash = $title . rand (0, 10);

                $html .= '<div id="' . $hash . '"></div>';

                $html .= "<script>";

                $html .= "c3.generate ({
                        bindto: '#" . $hash . "',
                        data: {
                            columns: [ ";
                $count = 0;
                $rows = '';
                foreach ($arrData as $key => $value) {

                    $rows .= "['" . $key . "', " . $value . "],";

                    $count++;
                }

                $html .= rtrim ($rows, ',');
                $html .= "],
                colors: {";

                $count = 0;
                $groups = '';
                $colours = '';
                foreach ($arrData as $key => $value) {
                    $colour = $this->random_color ();
                    $colours .= "'data" . $count . "': '#" . $colour . "',";
                    $groups .= "'data" . $count . "',";
                    $count++;
                }
                $html .= rtrim ($colours, ',');

                $html .= "},
                type: 'bar',
                groups: [
                    [" . rtrim ($groups, ',') . "]
                ]
            }
        });";

                $html .= "</script>";
                break;

            case "gauge":
                $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
                $title = str_replace (":", "", $title);
                $hash = $title . rand (0, 10);

                $html .= '<div id="' . $hash . '"></div>';

                $html .= "<script>";

                $html .= "c3.generate ({
                     bindto: '#" . $hash . "',
                     data: {
                         columns: [
                             ['data', " . $arrData . "]
                         ],
                         type: 'gauge'
                     },
                     color: {
                         pattern: ['#1ab394', '#BABABA']

                     }
                 });";

                $html .= "</script>";
                break;
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

}
