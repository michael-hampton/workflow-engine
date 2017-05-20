<?php

trait Validator
{

    private $connection;

    /**
     * validates a user id
     * @param type $userId
     * @return boolean
     */
    public function validateUsername ($username)
    {
        if ( $this->connection === null )
        {
            $this->connection = new Mysql2();
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
            $this->connection = new Mysql2();
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
            $this->connection = new Mysql2();
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
            $this->connection = new Mysql2();
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
            throw (new Exception ("ID_DATE_NOT_VALID"));
        }
        $d = \DateTime::createFromFormat ($format, $date);
        if ( !($d && $d->format ($format) == $date) )
        {
            throw (new Exception ("ID_DATE_NOT_VALID"));
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
            throw (new Exception ("ID_INVALID_VALUE_STRING"));
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
            throw (new Exception ("ID_INVALID_VALUE_INTEGER"));
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
    public function isBoolean ($field, $nameField)
    {
        if ( !is_bool ($field) )
        {
            throw (new Exception ("ID_INVALID_VALUE_BOOLEAN"));
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
    public function isNotEmpty ($field, $nameField)
    {
        if ( empty ($field) )
        {
            throw (new Exception ("ID_INVALID_VALUE_IS_EMPTY"));
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
    public function throwExceptionIfDataIsNotArray ($data, $dataNameForException)
    {
        try {
            if ( !is_array ($data) )
            {
                throw new Exception ("ID_INVALID_VALUE_THIS_MUST_BE_ARRAY");
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
                throw new Exception ("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
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
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return void
     */
    public function isArray ($field)
    {
        if ( !is_array ($field) )
        {
            throw (new Exception ("ID_INVALID_VALUE_ARRAY"));
        }
    }

    /**
     * Validate pro_uid
     *
     * @param string $pro_uid, Uid for process
     * @param string $nameField . Name of field for message
     *
     * @access public
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return string
     */
    public function proUid ($pro_uid)
    {
        $pro_uid = trim ($pro_uid);
        if ( $pro_uid == '' )
        {
            throw (new Exception ("ID_PROCESS_NOT_EXIST"));
        }
        $oProcess = new Process();
        if ( !($oProcess->processExists ($pro_uid)) )
        {
            throw (new Exception ("ID_PROCESS_NOT_EXIST"));
        }
        return $pro_uid;
    }

    /**
     * Validate cat_uid
     *
     * @param string $cat_uid, Uid for category
     * @param string $nameField . Name of field for message
     *
     * @access public
     * @author Brayan Pereyra (Cochalo) <brayan@colosa.com>
     * @copyright Colosa - Bolivia
     *
     * @return string
     */
    public function catUid ($cat_uid)
    {
        $cat_uid = trim ($cat_uid);
        if ( $cat_uid == '' )
        {
            throw (new Exception ("ID_CATEGORY_NOT_EXIST"));
        }
        $oCategory = new WorkflowCollectionFactory();

        if ( empty ($oCategory->retrieveByPk ($cat_uid)) )
        {
            throw (new Exception ("ID_CATEGORY_NOT_EXIST"));
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

}
