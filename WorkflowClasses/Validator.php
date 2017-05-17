<?php

trait Validator
{

    /**
     * validates a user id
     * @param type $userId
     * @return boolean
     */
    public function validateUserId ($userId)
    {
        $result = $this->objMysql->_select ("user_management.poms_users", array(), array("usrid" => $userId));

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
        $result = $this->objMysql->_select ("user_management.teams", array(), array("team_id" => $teamId));

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
        $result = $this->objMysql->_select ("user_management.departments", array(), array("id" => $deptId));

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
    public function isDate($date, $format = 'Y-m-d H:i:s')
    {
        $date = trim($date);
        if ($date == '') {
            throw (new Exception("ID_DATE_NOT_VALID"));
        }
        $d = \DateTime::createFromFormat($format, $date);
        if (!($d && $d->format($format) == $date)) {
            throw (new Exception("ID_DATE_NOT_VALID"));
        }
        return $date;
    }
    /**
     * Validate is array
     * @var array $field. Field type array
     *
     * @access public
     *
     * @return void
     */
    public function isArray($field, $nameField)
    {
        if (!is_array($field)) {
            throw (new Exception("ID_INVALID_VALUE_ARRAY"));
        }
    }
    /**
     * Validate is string
     * @var array $field. Field type string
     *
     * @access public
     *
     * @return void
     */
    public function isString($field, $nameField)
    {
        if (!is_string($field)) {
            throw (new Exception("ID_INVALID_VALUE_STRING"));
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
    public function isInteger($field, $nameField)
    {
        if (!is_integer($field)) {
            throw (new Exception("ID_INVALID_VALUE_INTEGER"));
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
    public function isBoolean($field, $nameField)
    {
        if (!is_bool($field)) {
            throw (new Exception("ID_INVALID_VALUE_BOOLEAN"));
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
    public function isNotEmpty($field, $nameField)
    {
        if (empty($field)) {
            throw (new Exception("ID_INVALID_VALUE_IS_EMPTY"));
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
    public function throwExceptionIfDataIsNotArray($data, $dataNameForException)
    {
        try {
            if (!is_array($data)) {
                throw new Exception("ID_INVALID_VALUE_THIS_MUST_BE_ARRAY");
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
    public function throwExceptionIfDataIsEmpty($data, $dataNameForException)
    {
        try {
            if (empty($data)) {
                throw new Exception("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
