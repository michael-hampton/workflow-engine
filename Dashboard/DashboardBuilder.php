<?php

class DashboardBuilder
{

    public function buildDashboard (Dashboard $objDashboard, $arrData)
    {
        $html = "";

        $html .= '<div class="col-lg-'.$objDashboard->getColumns ().'">';

        $html .= '<div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>' . $objDashboard->getTitle () . '</h5>
                    </div>';

        $html .= '<div class="ibox-content">';

        switch (trim ($objDashboard->getChartType ())) {

            case "table":
                $html .= $this->buildTable ($arrData);
                break;


            case "text":
                if ( !is_array ($arrData) )
                {
                    $html .= $arrData;
                }
                break;

            case "pie":
                $html .= $this->buildPieChart ($objDashboard, $arrData);
                break;

            case "bar":
                $html .= $this->buildBarChart ($objDashboard, $arrData);
                break;

            case "gauge":
                $html .= $this->buildGauge ($objDashboard, $arrData);
                break;
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    private function buildTable ($arrData)
    {
        reset ($arrData);
        $first_key = key ($arrData);

        $keys = array_keys ($arrData[$first_key]);

        $html = "<table>";
        $html .= "<thead>";


        foreach ($keys as $key) {
            $html .= "<th>" . $key . "</th>";
        }

        $html .= "</thead>";

        foreach ($arrData as $data) {
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

        return $html;
    }

    private function buildPieChart (Dashboard $objDashboard, $arrData)
    {
        $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
        $title = str_replace (":", "", $title);
        $hash = $title . rand (0, 10);

        $html = '<div id="' . $hash . '"></div>';

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

        return $html;
    }

    private function buildBarChart (Dashboard $objDashboard, $arrData)
    {
        $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
        $title = str_replace (":", "", $title);
        $hash = $title . rand (0, 10);

        $html = '<div id="' . $hash . '"></div>';

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

        return $html;
    }

    private function buildGauge (Dashboard $objDashboard, $arrData)
    {
        $title = str_replace (" ", "", strtolower ($objDashboard->getTitle ()));
        $title = str_replace (":", "", $title);
        $hash = $title . rand (0, 10);

        $html = '<div id="' . $hash . '"></div>';

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

        return $html;
    }

    private function random_color_part ()
    {
        return str_pad (dechex (mt_rand (0, 255)), 2, '0', STR_PAD_LEFT);
    }

    private function random_color ()
    {
        return $this->random_color_part () . $this->random_color_part () . $this->random_color_part ();
    }

}
