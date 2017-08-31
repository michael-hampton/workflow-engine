<?php
class AppFolder
{
private $objMysql;

    private function getConnection()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     *
     * @param string $folderName
     * @param strin(32) $folderParent
     * @return Ambigous <>|number
     */
    public function createFolder ($folderName, $folderParent = "/", $action = "createifnotexists")
    {
        if($this->objMysql === null) {
            $this->getConnection();
        }

        $validActions = array ("createifnotexists","create","update");
        if (! in_array( $action, $validActions )) {
            $action = "createifnotexists";
        }

        //Clean Folder and Parent names (delete spaces...)
        $folderName = trim( $folderName );
        $folderParent = trim( $folderParent );

        //Try to Load the folder (Foldername+FolderParent)

        $result = $this->objMysql->_select("workflow.APP_FOLDER", [], ["FOLDER_NAME" => $folderName, "FOLDER_PARENT_UID" => $folderParent]);

       if(isset($result[0]) && !empty($result[0])) {
            //Folder exist, then return the ID
            $response['success'] = false;
            $response['message'] = $response['error'] = "CANT_CREATE_FOLDER_A_FOLDER_WITH_SAME_NAME_ALREADY_EXIST"  . $folderName;
            $response['folderUID'] = $result[0]['FOLDER_UID'];
            //return ($aRow ['FOLDER_UID']);
            return ($response);
        } else {
            //Folder doesn't exist. Create and return the ID
            $tr = new AppFolder();
            $tr->setFolderParentUid( $folderParent );
            $tr->setFolderName( $folderName );
            $tr->setFolderCreateDate( 'now' );
            $tr->setFolderUpdateDate( 'now' );

            if ($tr->validate()) {
                // we save it, since we get no validation errors, or do whatever else you like.
                $folderUID = $tr->save();
                $response['success'] = true;
                $response['message'] = "Folder successfully created. <br /> $folderName";
                $response['folderUID'] = $folderUID;
                return ($response);
                //return $folderUID;
            } else {
                // Something went wrong. We can now get the validationFailures and handle them.
                $msg = '';
                $validationFailuresArray = $tr->getValidationFailures();
                foreach ($validationFailuresArray as $objValidationFailure) {
                    $msg .= $objValidationFailure . "<br/>";
                }
                $response['success'] = false;
                $response['message'] = $response['error'] =  "CANT_CREATE_FOLDER_A"  . $msg;
                return ($response);
            }
        }
    }

    /**
     * @param $pk
     */
    public function retrieveByPk($pk)
    {
        if($this->objMysql === null) {
            $this->getConnection();
        }

	$result = $this->objMysql->_select("workflow.APP_FOLDER", [], ["FOLDER_UID" => $pk]);

	if(!isset($result[0]) || empty($result[0])) {
		return false;
	}

	$appFolder = new AppFolder();

	return $appFolder;

    }

    /**
     * Update the application document registry
     *
     * @param array $aData
     * @return string
     *
     */
    public function update ($aData)
    {
        try {

            $oAppFolder = $this->retrieveByPK( $aData['FOLDER_UID'] );
            if ($oAppFolder !== false) {

                if ($oAppFolder->validate()) {
                    if (isset( $aData['FOLDER_NAME'] )) {
                        $oAppFolder->setFolderName( $aData['FOLDER_NAME'] );
                    }
                    if (isset( $aData['FOLDER_UID'] )) {
                        $oAppFolder->setFolderUid( $aData['FOLDER_UID'] );
                    }
                    if (isset( $aData['FOLDER_UPDATE_DATE'] )) {
                        $oAppFolder->setFolderUpdateDate( $aData['FOLDER_UPDATE_DATE'] );
                    }
                    $iResult = $oAppFolder->save();
                    return $iResult;
                } else {
                    $sMessage = '';
                    $aValidationFailures = $oAppFolder->getValidationFailures();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception( 'The registry cannot be updated!<br />' . $sMessage ));
                }
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }


    /**
     *
     * @param string $folderPath
     * @return string Last Folder ID generated
     */
    public function createFromPath ($folderPath)
    {

        $folderPathParsedArray = explode( "/", $folderPath );
        $folderRoot = "/"; //Always starting from Root
        foreach ($folderPathParsedArray as $folderName) {
            if (trim( $folderName ) != "") {
                $response = $this->createFolder( $folderName, $folderRoot );
                $folderRoot = $response['folderUID'];
            }
        }
        return $folderRoot != "/" ? $folderRoot : "";
    }

    public function remove($FolderUid, $rootfolder)
    {
        $appFolder = new AppFolder();
        $appFolder->setFolderUid($FolderUid);
        $appFolder->delete();
    }

}
