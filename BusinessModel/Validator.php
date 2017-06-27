<?php
namespace BusinessModel;

trait Validator
{

    private $connection;

    /**
     * Validate project id
     *
     * @param string $project_uid, Uid for application
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    public function projectUid ($project_uid)
    {
        if ( $this->connection === null )
        {
            $this->connection = new \Mysql2();
        }

        $project_uid = trim ($project_uid);
        if ( $project_uid == '' )
        {
            throw (new \Exception ("PROJECT DOES NOT EXIST"));
        }

        $result = $this->connection->_select ("task_manager.projects", array(), array("id" => $project_uid));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return $project_uid;
        }

        throw (new \Exception ("PROJECT DOES NOT EXIST"));
    }

    /**
     * validates a user id
     * @param type $userId
     * @return boolean
     */
    public function validateUsername ($username)
    {
        if ( $this->connection === null )
        {
            $this->connection = new \Mysql2();
        }

        $result = $this->connection->_select ("user_management.poms_users", array(), array("username" => $username));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return FALSE;
    }

    /**
     * validates a user id
     * @param type $userId
     * @return boolean
     */
    public function validateUserId ($userId)
    {
        if ( $this->connection === null )
        {
            $this->connection = new \Mysql2();
        }

        $result = $this->connection->_select ("user_management.poms_users", array(), array("usrid" => $userId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return FALSE;
    }

    /**
     * validates a team id
     * @param type $teamId
     * @return boolean
     */
    public function validateTeamId ($teamId)
    {
        if ( $this->connection === null )
        {
            $this->connection = new \Mysql2();
        }

        $result = $this->connection->_select ("user_management.teams", array(), array("team_id" => $teamId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return FALSE;
    }

    /**
     * validates a depsrtment id
     * @param type $deptId
     * @return boolean
     */
    public function validateDeptId ($deptId)
    {
        if ( $this->connection === null )
        {
            $this->connection = new \Mysql2();
        }

        $result = $this->connection->_select ("user_management.departments", array(), array("id" => $deptId));

        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }

        return FALSE;
    }

    /**
     * Validate date
     *
     * @param string $date, Date for validate
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    public function isDate ($date, $format = 'Y-m-d H:i:s')
    {
        $date = trim ($date);
        if ( $date == '' )
        {
            throw (new \Exception ("ID_DATE_NOT_VALID"));
        }
        $d = \DateTime::createFromFormat ($format, $date);
        if ( !($d && $d->format ($format) == $date) )
        {
            throw (new \Exception ("ID_DATE_NOT_VALID"));
        }
        return $date;
    }

    /**
     * Validate is string
     * @var array $field. Field type string
     *
     * @access public
     *
     * @return void
     */
    public function isString ($field, $nameField)
    {
        if ( !is_string ($field) )
        {
            throw (new \Exception ("ID_INVALID_VALUE_STRING"));
        }
    }

    /**
     * Validate is integer
     * @var array $field. Field type integer
     *
     * @access public
     *
     * @return void
     */
    public function isInteger ($field, $nameField)
    {
        if ( !is_integer ($field) )
        {
            throw (new \Exception ("ID_INVALID_VALUE_INTEGER"));
        }
    }

    /**
     * Validate is boolean
     * @var boolean $field. Field type boolean
     *
     * @access public
     *
     * @return void
     */
    public function isBoolean ($field)
    {
        if ( !is_bool ($field) )
        {
            throw (new \Exception ("ID_INVALID_VALUE_BOOLEAN"));
        }
    }

    /**
     * Validate dep_status
     * @var string $dep_status. Status for Departament
     *
     * @access public
     *
     * @return string
     */
    public function depStatus ($dep_status)
    {
        $dep_status = (string) trim ($dep_status);
        $values = array('0', '1');
        if ( !in_array ($dep_status, $values) )
        {
            throw (new \Exception ("ID_DEPARTMENT_NOT_EXIST"));
        }
        return $dep_status;
    }

    /**
     * Validate is boolean
     * @var boolean $field. Field type boolean
     *
     * @access public
     *
     * @return void
     */
    public function isNotEmpty ($field, $nameField)
    {
        if ( empty ($field) )
        {
            throw (new \Exception ("ID_INVALID_VALUE_IS_EMPTY"));
        }
    }

    /**
     * Verify if data is array
     *
     * @param string $data                 Data
     * @param string $dataNameForException Data name for the exception
     *
     * return void Throw exception if data is not array
     */
    public function throwExceptionIfDataIsNotArray ($data)
    {
        try {
            if ( !is_array ($data) )
            {
                throw new \Exception ("ID_INVALID_VALUE_THIS_MUST_BE_ARRAY");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if data is empty
     *
     * @param string $data                 Data
     * @param string $dataNameForException Data name for the exception
     *
     * return void Throw exception if data is empty
     */
    public function throwExceptionIfDataIsEmpty ($data)
    {
        try {
            if ( empty ($data) )
            {
                throw new \Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate is array
     * @var array $field. Field type array
     *
     * @access public
     *
     * @return void
     */
    public function isArray ($field)
    {
        if ( !is_array ($field) )
        {
            throw (new \Exception ("ID_INVALID_VALUE_ARRAY"));
        }
    }

    /**
     * Validate pro_uid
     *
     * @param string $pro_uid, Uid for process
     *
     * @access public
     *
     * @return string
     */
    public function proUid ($pro_uid)
    {
        $pro_uid = trim ($pro_uid);
        if ( $pro_uid == '' )
        {
            throw (new \Exception ("ID_PROCESS_NOT_EXIST"));
        }
        $oProcess = new \BusinessModel\Process();
        if ( !($oProcess->processExists ($pro_uid)) )
        {
            throw (new \Exception ("ID_PROCESS_NOT_EXIST"));
        }
        return $pro_uid;
    }

    /**
     * Validate cat_uid
     *
     * @param string $cat_uid, Uid for category
     *
     * @access public
     *
     * @return string
     */
    public function catUid ($cat_uid)
    {
        $cat_uid = trim ($cat_uid);
        if ( $cat_uid == '' )
        {
            throw (new \Exception ("ID_CATEGORY_NOT_EXIST"));
        }
        $oCategory = new \BusinessModel\WorkflowCollectionFactory();

        if ( empty ($oCategory->retrieveByPk ($cat_uid)) )
        {
            throw (new \Exception ("ID_CATEGORY_NOT_EXIST"));
        }
        return $cat_uid;
    }

    public function dateIsBetween ($from, $to, $date)
    {
        $date = new \DateTime ($date);
        $from = new \DateTime ($from);
        $to = new \DateTime ($to);
        if ( $date >= $from && $date <= $to )
        {
            return true;
        }

        return false;
    }

    /**
     * Validate dep_uid
     * @var string $dep_uid. Uid for Departament
     * @var string $nameField. Name of field for message
     *
     * @access public
     *
     * @return string
     */
    public function depUid ($dep_uid, $nameField = 'dep_uid')
    {
        $dep_uid = trim ($dep_uid);
        if ( $dep_uid == '' )
        {
            throw (new \Exception ("ID_DEPARTMENT_NOT_EXIST"));
        }
        $oDepartment = new \Department();
        if ( !($oDepartment->existsDepartment ($dep_uid)) )
        {
            throw (new \Exception ("ID_DEPARTMENT_NOT_EXIST"));
        }
        return $dep_uid;
    }

    /**
     * getIpAddress
     * @return string $ip
     */
    public function getIpAddress ()
    {
        if ( getenv ('HTTP_CLIENT_IP') )
        {
            $ip = getenv ('HTTP_CLIENT_IP');
        }
        elseif ( getenv ('HTTP_X_FORWARDED_FOR') )
        {
            $ip = getenv ('HTTP_X_FORWARDED_FOR');
        }
        else
        {
            $ip = getenv ('REMOTE_ADDR');
        }
        return $ip;
    }

    /**
     * Stores a message in the log file, if the file size exceeds
     * specified log file is renamed and a new one is created.
     *
     * @param type $message
     * @param type $pathData
     * @param type $file
     */
    public static function writeLog ($message, $file = 'cron.log')
    {
        $path = HOME_DIR . "/core/app/logs/" . $file;
        $f = (file_exists ($path)) ? fopen ($path, "a+") : fopen ($path, "w+");
        fwrite ($f, $message);
        fclose ($f);
        chmod ($path, 0777);
    }

}
