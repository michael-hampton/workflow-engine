<?php

class Dashboard
{

    private $title;
    private $description;
    private $columns;
    private $chart_type;
    private $function;
    private $id;
    private $algorithm;
    private $arrFieldMapping = array(
        "title" => array("accessor" => "getTitle", "mutator" => "setTitle", "required" => "true", "type" => "string"),
        "description" => array("accessor" => "getDescription", "mutator" => "setDescription", "required" => "true", "type" => "string"),
        "columns" => array("accessor" => "getColumns", "mutator" => "setColumns", "required" => "true", "type" => "string"),
        "chart_type" => array("accessor" => "getChartType", "mutator" => "setChartType", "required" => "false", "type" => "string"),
        "function_name" => array("accessor" => "getFunction", "mutator" => "setFunction", "required" => "true", "type" => "string")
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
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle ($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getColumns ()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     */
    public function setColumns ($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return mixed
     */
    public function getChartType ()
    {
        return $this->chart_type;
    }

    /**
     * @param mixed $chart_type
     */
    public function setChartType ($chart_type)
    {
        $this->chart_type = $chart_type;
    }

    /**
     * @return mixed
     */
    public function getFunction ()
    {
        return $this->function;
    }

    /**
     * @param mixed $function
     */
    public function setFunction ($function)
    {
        $this->function = $function;
    }

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }
    
    public function getAlgorithm ()
    {
        return $this->algorithm;
    }

    public function setAlgorithm ($algorithm)
    {
        $this->algorithm = $algorithm;
    }

}
