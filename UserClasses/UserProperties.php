<?php

class UserProperties extends BaseUserProperties
{

    public $fields = null;
    public $usrID = '';
    public $lang = 'en';

    public function __construct ()
    {
        $this->lang = defined ('SYS_LANG') ? SYS_LANG : 'en';
    }

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function retrieveByPk ($userUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $results = $this->objMysql->_select ("user_management.USER_PROPERTIES", [], ["USR_UID" => $userUid]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return false;
        }

        $userProperties = new UserProperties();
        $userProperties->setUsrLastUpdateDate ($results[0]['USR_LAST_UPDATE_DATE']);
        $userProperties->setUsrLoggedNextTime ($results[0]['USR_LOGGED_NEXT_TIME']);
        $userProperties->setUsrPasswordHistory ($results[0]['USR_PASSWORD_HISTORY']);
        $userProperties->setUsrUid ($results[0]['USR_UID']);

        return $userProperties;
    }

    public function UserPropertyExists ($sUserUID)
    {
        $oUserProperty = $this->retrieveByPk ($sUserUID);

        if ( !is_null ($oUserProperty) && is_object ($oUserProperty) && get_class ($oUserProperty) == 'UserProperties' )
        {
            $oUserProperty->doUpdate = true;
            $this->fields = $oUserProperty;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function load ($sUserUID)
    {
        $oUserProperty = $this->retrieveByPK ($sUserUID);

        if ( !is_null ($oUserProperty) )
        {

            $this->loadObject ($aFields);
            return $aFields;
        }
        else
        {
            throw new Exception ("User with $sUserUID does not exist!");
        }
    }

    public function create ($aData)
    {
        try {
            $oUserProperty = new UserProperties();
            $oUserProperty->loadObject ($aData);

            if ( $oUserProperty->validate () )
            {

                $oUserProperty->save ();

                return true;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $oUserProperty->getValidationFailures ();

                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw (new Exception ('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {

            throw ($oError);
        }
    }

    public function update ($aData)
    {

        try {
            $oUserProperty = $this->retrieveByPK ($aData['USR_UID']);
            
            if ( !is_null ($oUserProperty) )
            {
                $oUserProperty->loadObject ($aData);
                $oUserProperty->doUpdate = true;

                if ( $oUserProperty->validate () )
                {
                    $iResult = $oUserProperty->save ();

                    return $iResult;
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oUserProperty->getValidationFailures ();

                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception ('The registry cannot be updated!<br />' . $sMessage));
                }
            }
            else
            {
                throw (new Exception ('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function loadOrCreateIfNotExists ($sUserUID, $aUserProperty = array())
    {
        if ( !$this->UserPropertyExists ($sUserUID) )
        {
            $aUserProperty['USR_UID'] = $sUserUID;
            if ( !isset ($aUserProperty['USR_LAST_UPDATE_DATE']) )
            {
                $aUserProperty['USR_LAST_UPDATE_DATE'] = date ('Y-m-d H:i:s');
            }
            if ( !isset ($aUserProperty['USR_LOGGED_NEXT_TIME']) )
            {
                $aUserProperty['USR_LOGGED_NEXT_TIME'] = 0;
            }
            $this->create ($aUserProperty);
        }
        else
        {
            $aUserProperty = $this->fields;
        }
        return $aUserProperty;
    }

    public function validatePassword ($sPassword, $sLastUpdate, $iChangePasswordNextTime, $nowLogin = false)
    {
        if ( !defined ('PPP_MINIMUM_LENGTH') )
        {
            define ('PPP_MINIMUM_LENGTH', 5);
        }
        if ( !defined ('PPP_MAXIMUM_LENGTH') )
        {
            define ('PPP_MAXIMUM_LENGTH', 20);
        }
        if ( !defined ('PPP_NUMERICAL_CHARACTER_REQUIRED') )
        {
            define ('PPP_NUMERICAL_CHARACTER_REQUIRED', 0);
        }
        if ( !defined ('PPP_UPPERCASE_CHARACTER_REQUIRED') )
        {
            define ('PPP_UPPERCASE_CHARACTER_REQUIRED', 0);
        }
        if ( !defined ('PPP_SPECIAL_CHARACTER_REQUIRED') )
        {
            define ('PPP_SPECIAL_CHARACTER_REQUIRED', 0);
        }
        if ( !defined ('PPP_EXPIRATION_IN') )
        {
            define ('PPP_EXPIRATION_IN', 0);
        }
        if ( function_exists ('mb_strlen') )
        {
            $iLength = mb_strlen ($sPassword);
        }
        else
        {
            $iLength = strlen ($sPassword);
        }
        $aErrors = array();
        if ( $iLength < PPP_MINIMUM_LENGTH || $nowLogin )
        {
            $aErrors[] = 'ID_PPP_MINIMUM_LENGTH';
        }
        if ( $iLength > PPP_MAXIMUM_LENGTH || $nowLogin )
        {
            $aErrors[] = 'ID_PPP_MAXIMUM_LENGTH';
        }
        if ( PPP_NUMERICAL_CHARACTER_REQUIRED == 1 )
        {
            if ( preg_match_all ('/[0-9]/', $sPassword, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) == 0 || $nowLogin )
            {
                $aErrors[] = 'ID_PPP_NUMERICAL_CHARACTER_REQUIRED';
            }
        }
        if ( PPP_UPPERCASE_CHARACTER_REQUIRED == 1 )
        {
            if ( preg_match_all ('/[A-Z]/', $sPassword, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) == 0 || $nowLogin )
            {
                $aErrors[] = 'ID_PPP_UPPERCASE_CHARACTER_REQUIRED';
            }
        }
        if ( PPP_SPECIAL_CHARACTER_REQUIRED == 1 )
        {
            if ( preg_match_all ('/[��\\!|"@�#$~%�&�\/()=\'?��*+\-_.:,;]/', $sPassword, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) == 0 || $nowLogin )
            {
                $aErrors[] = 'ID_PPP_SPECIAL_CHARACTER_REQUIRED';
            }
        }
        if ( PPP_EXPIRATION_IN > 0 )
        {
            $oCalendar = new CalendarFunctions();
            if ( $oCalendar->pmCalendarUid == '' )
            {
                $oCalendar->pmCalendarUid = '00000000000000000000000000000001';
                $oCalendar->getCalendarData ();
            }
            $fDays = $oCalendar->calculateDuration (date ('Y-m-d H:i:s'), $sLastUpdate);
            if ( $fDays > (PPP_EXPIRATION_IN * 24) || $nowLogin )
            {
                $aErrors[] = 'ID_PPP_EXPIRATION_IN';
            }
        }
        if ( $iChangePasswordNextTime == 1 )
        {
            $aErrors[] = 'ID_PPP_CHANGE_PASSWORD_AFTER_NEXT_LOGIN';
        }
        return $aErrors;
    }

}
