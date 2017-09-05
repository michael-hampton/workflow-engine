 function checkReplacedByUser ($user)
    {
        if (is_string( $user )) {
            $userInstance = UsersPeer::retrieveByPK( $user );
        } else {
            $userInstance = $user;
        }
        if (! is_object( $userInstance )) {
            throw new Exception( "The user with the UID '$user' doesn't exist." );
        }
        if ($userInstance->getUsrStatus() == 'ACTIVE') {
            return $userInstance->getUsrUid();
        } else {
            $userReplace = trim( $userInstance->getUsrReplacedBy() );
            if ($userReplace != '') {
                return $this->checkReplacedByUser( UsersPeer::retrieveByPK( $userReplace ) );
            } else {
                return '';
            }
        }
    }


REPORTS TO
 do {
                    $userTasInfo = $this->getDenpendentUser($userTasInfo);
                    $useruid = $this->checkReplacedByUser($userTasInfo);
                    //When the lastManager is INACTIVE/VACATION and does not have a Replace by, the REPORT_TO is himself
                    if($lastManager === $userTasInfo){
                        $useruid = $tasInfo["USER_UID"];
                    } else {
                        $lastManager = $userTasInfo;
                    }
                } while ($useruid === '');

                if (isset( $useruid ) && $useruid != '') {
                    $userFields = $this->getUsersFullNameFromArray( $useruid );
                }

                // if there is no report_to user info, throw an exception indicating this
                if (! isset( $userFields ) || $userFields['USR_UID'] == '') {
                    throw (new Exception( G::LoadTranslation( 'ID_MSJ_REPORSTO' ) )); // "The current user does not have a valid Reports To user.  Please contact administrator.") ) ;
                }
}

/* getDenpendentUser
     *
     * @param   string   $USR_UID
     * @return  string   $aRow['USR_REPORTS_TO']
     */
    function getDenpendentUser ($USR_UID)
    {
        $user = new \ProcessMaker\BusinessModel\User();

        $manager = $user->getUsersManager($USR_UID);

        //Return
        return ($manager !== false)? $manager : $USR_UID;
    }
