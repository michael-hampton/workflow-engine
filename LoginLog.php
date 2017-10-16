<?php

/**
 * Skeleton subclass for representing a row from the 'LOGIN_LOG' table. 
 *
 * @author michael.hampton
 */
class LoginLog extends BaseLoginLog
{

    private $objMysql;

    public function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function create ($aData)
    {
        try {
            $this->loadObject ($aData);

            if ( $this->validate () )
            {
                $result = $this->save ();
            }
            else
            {
                $e = new Exception ("Failed Validation in class " . get_class ($this) . ".");
                $e->aValidationFailures = $this->getValidationFailures ();
                throw ($e);
            }
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function load ($LogUid)
    {
        try {
            $oRow = $this->retrieveByPK ($LogUid);

            if ( !is_null ($oRow) )
            {
                return $oRow;
            }
            else
            {
                throw (new Exception ("The row '" . $LogUid . "' in table LOGIN_LOG doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function update ($fields)
    {
        try {
            $this->load ($fields['LOG_UID']);
            $this->loadObject ($fields);

            if ( $this->validate () )
            {
                $result = $this->save ();
                return $result;
            }
            else
            {
                throw (new Exception ("Failed Validation in class " . get_class ($this) . "."));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function remove ($LogUid)
    {
        try {
            $this->setWlUid ($LogUid);
            $result = $this->delete ();
            return $result;
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function getLastLoginByUser ($sUID)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $sql = "SELECT LOG_INIT_DATE FROM user_management.LOGIN_LOG WHERE USR_UID = ? LIMIT 1 ORDER BY LOG_INIT_DATE DESC";
        $results = $this->objMysql->_query ($sql, [$sUID]);
        return isset ($results[0]['LOG_INIT_DATE']) ? $results[0]['LOG_INIT_DATE'] : '';
    }

    //Added by Qennix
    public function getLastLoginAllUsers ()
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $sql = "SELECT USR_UID, MAX(LOG_INIT_DATE) AS LAST_LOGIN FROM user_management.LOGIN_LOG GROUP BY USR_UID ";
        $results = $this->objMysql->_query ($sql);

        $aRows = Array();
        foreach ($results as $row) {
            $aRows[$row['USR_UID']] = $row['LAST_LOGIN'];
        }
        return $aRows;
    }

}
