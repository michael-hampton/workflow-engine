<?php

class WorkflowCollectionFactory
{
    use Validator;

    private $objMysql;

    public function __construct ($requestId = null, $objKondor = null)
    {
        $this->objMysql = new Mysql2();
    }

    public function getSystemWorkflowCollections ($strSystemName = null)
    {

        $sql = "SELECT * FROM workflow.request_types r
                INNER JOIN workflow.workflow_systems s ON s.system_id = r.system_id
                WHERE s.system_name = ?";

        $arrResults = $this->objMysql->_query ($sql, array($strSystemName));


        foreach ($arrResults as $result) {

            $arrWorkflowCollectionObjects[$result['request_type']] = new WorkflowCollection ($result['request_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setDeptId ($result['dept_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setName ($result['request_type']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setDescription ($result['description']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setRequestId ($result['request_id']);
            $arrWorkflowCollectionObjects[$result['request_type']]->setParentId ($result['parent_id']);
        }

        return $arrWorkflowCollectionObjects;
    }

    public function existsName ($categoryName)
    {
        try {
            $result = $this->objMysql->_select ("workflow.request_types", array(), array("request_type" => $categoryName));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function retrieveByPk ($pk)
    {
        $result = $this->objMysql->_select ("workflow.request_types", array(), array("request_id" => $pk));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $result;
        }

        return [];
    }

    /**
     * Verify if does not exist the Category in table PROCESS_CATEGORY
     *
     * @param string $categoryUid Unique id of Category
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if does not exist the Category in table PROCESS_CATEGORY
     */
    public function throwExceptionIfNotExistsCategory ($categoryUid)
    {
        try {
            $obj = $this->retrieveByPK ($categoryUid);

            if ( empty ($obj) )
            {
                throw new Exception ("CATEGORY DOES NOT EXIST");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the name of a Category
     *
     * @param string $categoryName Name
     * @param string $fieldNameForException Field name for the exception
     * @param string $categoryUidExclude Unique id of Category to exclude
     *
     * return void Throw exception if exists the name of a Category
     */
    public function throwExceptionIfExistsName ($categoryName)
    {
        try {
            if ( $this->existsName ($categoryName) )
            {
                throw new Exception ("ID_CATEGORY_NAME_ALREADY_EXISTS");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $categoryUid Unique id of Category
     * @param array $arrayData Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid (array $arrayData)
    {
        try {

            if ( !isset ($arrayData['dept_id']) || empty ($arrayData['dept_id']) )
            {
                throw new Exception ("DEPT ID IS MISSING");
            }

            //Verify data
            if ( isset ($arrayData["request_type"]) )
            {
                $this->throwExceptionIfExistsName ($arrayData["request_type"]);
            }
           
            if(empty($arrayData["request_type"]) || !isset($arrayData["request_type"])) {
            
                // throw excption cant be empty
                throw new Exception ("CATEGORY TITLE IS MISSING");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Category
     *
     * @param array $arrayData Data
     *
     * return array Return data of the new Category created
     */
    public function create (array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsEmpty ($arrayData);

            //Verify data
            $this->throwExceptionIfDataIsInvalid ($arrayData);

            //Create
            $category = new WorkflowCollection();

          $category->setNew(true);
            $category->setName ($arrayData["request_type"]);
            $category->setDeptId ($arrayData['dept_id']);

            if ( isset ($arrayData['description']) && !empty ($arrayData['description']) )
            {
                $category->setDescription ($arrayData['description']);
            }

            $category->setSystemId (1);

            $result = $category->save ();
            
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Category
     *
     * @param string $categoryUid Unique id of Category
     * @param array $arrayData Data
     *
     * return array Return data of the Category updated
     */
    public function update ($categoryUid, array $arrayData)
    {
        try {
            //Verify data

            $this->throwExceptionIfDataIsEmpty ($arrayData);

            //Verify data
            $this->throwExceptionIfNotExistsCategory ($categoryUid);

            if ( !isset ($arrayData['dept_id']) || empty ($arrayData['dept_id']) )
            {
                throw new Exception ("DEPT ID IS MISSING");
            }

            if ( !isset ($arrayData['request_type']) || empty ($arrayData['request_type']) )
            {
                throw new Exception ("TITLE IS MISSING");
            }

            //Update
            $category = new WorkflowCollection();

            $category->setNew (false);
            $category->setRequestId ($categoryUid);

            if ( isset ($arrayData["request_type"]) )
            {
                $category->setName ($arrayData["request_type"]);
            }

            $category->setDeptId ($arrayData['dept_id']);

            if ( isset ($arrayData['description']) && !empty ($arrayData['description']) )
            {
                $category->setDescription ($arrayData['description']);
            }

            $result = $category->save ();
            
            return $result;

            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Category
     *
     * @param string $categoryUid Unique id of Category
     *
     * return void
     */
    public function delete ($categoryUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsCategory ($categoryUid);

            $process = new Process();

            $arrayTotalProcessesByCategory = $process->getAllProcessesByCategory ();

            if ( isset ($arrayTotalProcessesByCategory[$categoryUid]) && (int) ($arrayTotalProcessesByCategory[$categoryUid]) > 0 )
            {
                throw new Exception ("ID_MSG_CANNOT_DELETE_CATEGORY");
            }

            //Delete
            $category = new WorkflowCollection();

            $category->setCategoryUid ($categoryUid);
            $category->delete ();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Categories
     *
     * @param array $arrayFilterData Data of the filters
     * @param string $sortField Field name to sort
     * @param string $sortDir Direction of sorting (ASC, DESC)
     * @param int $start Start
     * @param int $limit Limit
     *
     * return array Return an array with all Categories
     */
    public function getCategories (array $arrayFilterData = null, $sortField = null, $sortDir = null, $start = null, $limit = null)
    {
        try {
            $arrayCategory = array();

            //Verify data
            $process = new Process();

            $arrayTotalProcessesByCategory = $process->getAllProcessesByCategory ();

            //SQL
            $sql = "SELECT * FROM workflow.request_types";

            if ( !is_null ($arrayFilterData) && is_array ($arrayFilterData) && isset ($arrayFilterData["filter"]) && trim ($arrayFilterData["filter"]) != "" )
            {

                $sql .= " WHERE request_type LIKE '%" . $arrayFilterData["filter"] . "%'";
            }

            //SQL
            if ( !is_null ($sortField) && trim ($sortField) != "" )
            {

                $sql .= " ORDER BY " . $sortField;
            }

            if ( !is_null ($sortDir) && trim ($sortDir) != "" && strtoupper ($sortDir) == "DESC" )
            {
                $sql .= " DESC";
            }
            else
            {
                $sql .= " ASC";
            }

            if ( !is_null ($start) )
            {
                $sql .= " OFFSET " . (int) ($start);
            }

            if ( !is_null ($limit) )
            {
                $sql .= " LIMIT " . (int) ($limit);
            }

            $results = $this->objMysql->_query ($sql);

//            while ($rsCriteria->next()) {
//                $row = $rsCriteria->getRow();
//
//                $row["CATEGORY_TOTAL_PROCESSES"] = (isset($arrayTotalProcessesByCategory[$row["CATEGORY_UID"]]))? (int)($arrayTotalProcessesByCategory[$row["CATEGORY_UID"]]) : 0;
//
//                $arrayCategory[] = $this->getCategoryDataFromRecord($row);
//            }
//
//            //Return
//            return $arrayCategory;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Category
     *
     * @param string $categoryUid Unique id of Category
     * @param bool $flagGetRecord Value that set the getting
     *
     * return array Return an array with data of a Category
     */
    public function getCategory ($categoryUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsCategory ($categoryUid, $this->arrayFieldNameForException["categoryUid"]);

            //Set variables
            if ( !$flagGetRecord )
            {
                $process = new Process();

                $arrayTotalProcessesByCategory = $process->getAllProcessesByCategory ();
            }

            //Get data
            //SQL
            $result = $sql = "SELECT * FROM workflows.request_types WHERE request_id = ?";

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                return [];
            }

            $row = $result[0];

            if ( !$flagGetRecord )
            {
                $row["CATEGORY_TOTAL_PROCESSES"] = (isset ($arrayTotalProcessesByCategory[$row["CATEGORY_UID"]])) ? (int) ($arrayTotalProcessesByCategory[$row["CATEGORY_UID"]]) : 0;
            }

            //Return
            return (!$flagGetRecord) ? $this->getCategoryDataFromRecord ($row) : $row;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
