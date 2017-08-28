<?php

class Form extends BaseForm
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
    }

    /**
     * Creates the Dynaform
     *
     * @param array $aData Fields with :
     * $aData['DYN_UID'] the dynaform id
     * $aData['USR_UID'] the userid
     * @return void
     */
    public function create ($aData, $pmTableUid = '')
    {
        if ( !isset ($aData['PRO_UID']) )
        {
            throw (new Exception ('The dynaform cannot be created. The PRO_UID is empty.'));
        }

        try {
            if ( isset ($aData['DYN_UID']) && $aData['DYN_UID'] == '' )
            {
                unset ($aData['DYN_UID']);
            }


            $dynTitle = isset ($aData['DYN_TITLE']) ? $aData['DYN_TITLE'] : 'Default Dynaform Title';
            $this->setDynTitle ($dynTitle);
            $dynDescription = isset ($aData['DYN_DESCRIPTION']) ? $aData['DYN_DESCRIPTION'] : 'Default Dynaform Description';
            $this->setDynDescription ($dynDescription);
            $this->setProUid ($aData['PRO_UID']);
            $this->setDynType (isset ($aData['DYN_TYPE']) ? $aData['DYN_TYPE'] : 'xmlform' );
            $this->setDynUpdateDate (date ("Y-m-d H:i:s"));

            if ( isset ($aData["DYN_CONTENT"]) )
            {
                $this->setDynContent ($aData["DYN_CONTENT"]);
            }
            else
            {
                $this->setDynContent (json_encode (array(
                    "name" => $aData["DYN_TITLE"],
                    "description" => $aData["DYN_DESCRIPTION"],
                    "items" => array(array(
                            "type" => "form",
                            "variable" => "",
                            "var_uid" => "",
                            "dataType" => "",
                            "id" => $this->getDynUid (),
                            "name" => $aData["DYN_TITLE"],
                            "description" => $aData["DYN_DESCRIPTION"],
                            "mode" => "edit",
                            "script" => "",
                            "language" => "en",
                            "externalLibs" => "",
                            "printable" => false,
                            "items" => array(),
                            "variables" => array()
                        )
                    )
                )));
            }
            if ( isset ($aData["DYN_LABEL"]) )
            {
                $this->setDynLabel ($aData["DYN_LABEL"]);
            }
            if ( !isset ($aData['DYN_VERSION']) )
            {
                $aData['DYN_VERSION'] = 0;
            }
            $this->setDynVersion ($aData['DYN_VERSION']);
            if ( $this->validate () )
            {
                $this->setDynTitleContent ($dynTitle);
                $this->setDynDescriptionContent ($dynDescription);
                $res = $this->save ();

                //Add Audit Log
                $mode = isset ($aData['MODE']) ? $aData['MODE'] : 'Determined by Fields';
                $description = "";

                if ( $pmTableUid != '' )
                {
                    $pmTable = (new AdditionalTables())->retrieveByPK ($pmTableUid);
                    $addTabName = $pmTable->getAddTabName ();
                    $description = "Create from a PM Table: " . $addTabName . ", ";
                }

                //G::auditLog("CreateDynaform", $description."Dynaform Title: ".$aData['DYN_TITLE'].", Type: ".$aData['DYN_TYPE'].", Description: ".$aData['DYN_DESCRIPTION'].", Mode: ".$mode);
            }
            else
            {
                $msg = '';
                foreach ($this->getValidationFailures () as $objValidationFailure) {
                    $msg .= $objValidationFailure . "<br/>";
                }
                throw (new Exception ('The row cannot be created!' . $msg));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function retrieveByPk ($pk)
    {
        $results = $this->objMysql->_select ("workflow.form", [], ["DYN_UID" => $pk]);

        if ( !isset ($results[0]) || empty ($results[0]) )
        {
            return FALSE;
        }

        $objForm = new Form();
        return $objForm;
    }

    /**
     * Load the Dynaform row specified in [dyn_id] column value.
     *
     * @param string $ProUid the uid of the Prolication
     * @return array $Fields the fields
     */
    public function Load ($ProUid)
    {
        try {
            $oPro = $this->retrieveByPk ($ProUid);
            if ( is_object ($oPro) && get_class ($oPro) == 'Form' )
            {
                return $oPro;
            }
            else
            {
                throw (new Exception ("The row '$ProUid' in table Dynaform doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Update the Prolication row
     *
     * @param array $aData
     * @return variant
     *
     */
    public function update ($aData)
    {
        try {
            $oPro = $this->retrieveByPK ($aData['DYN_UID']);

            if ( is_object ($oPro) && get_class ($oPro) == 'Form' )
            {
                $oPro->loadObject ($aData);

                $oPro->setDynUpdateDate (date ("Y-m-d H:i:s"));

                if ( $oPro->validate () )
                {
                    if ( isset ($aData['DYN_TITLE']) )
                    {
                        $oPro->setDynTitleContent ($aData['DYN_TITLE']);
                    }
                    if ( isset ($aData['DYN_DESCRIPTION']) )
                    {
                        $oPro->setDynDescriptionContent ($aData['DYN_DESCRIPTION']);
                    }
                    $res = $oPro->save ();
                    return $res;
                }
                else
                {
                    foreach ($this->getValidationFailures () as $objValidationFailure) {
                        $msg .= $objValidationFailure . "<br/>";
                    }
                    throw (new PropelException ('The row cannot be created! ' . $msg));
                }
            }
            else
            {
                throw (new Exception ("The row '" . $aData['DYN_UID'] . "' in table Dynaform doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Remove the Prolication document registry
     *
     * @param array $aData or string $ProUid
     * @return string
     *
     */
    public function remove ($ProUid)
    {
        if ( is_array ($ProUid) )
        {
            $ProUid = (isset ($ProUid['DYN_UID']) ? $ProUid['DYN_UID'] : '');
        }
        try {
            $oPro = $this->retrieveByPK ($ProUid);
            if ( $oPro === false )
            {
                $title = $oPro->getDynTitle ();
                $type = $oPro->getDynType ();
                $description = $oPro->getDynDescription ();

                $iResult = $oPro->delete ();

                //Add Audit Log
                //G::auditLog ("DeleteDynaform", "Dynaform Title: " . $title . ", Type: " . $type . ", Description: " . $description);


                return $iResult;
            }
            else
            {
                throw (new Exception ("The row '$ProUid' in table Dynaform doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function exists ($DynUid)
    {
        $oPro = $this->retrieveByPk ($DynUid);
        return (is_object ($oPro) && get_class ($oPro) == 'Form');
    }

    /**
     * verify if Dynaform row specified in [DynUid] exists.
     *
     * @param string $sProUid the uid of the Prolication
     */
    public function dynaformExists ($DynUid)
    {
        try {
            $oDyn = $this->retrieveByPk ($DynUid);
            if ( is_object ($oDyn) && get_class ($oDyn) == 'Form' )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function verifyExistingName ($sName, $sProUid, $sDynUid = null)
    {
        $sNameDyanform = urldecode ($sName);
        $sProUid = urldecode ($sProUid);

        $sql = "SELECT DYN_UID FROM workflow.form WHERE PRO_UID = ?";
        $arrWhere = array($sProUid);


        if ( !is_null ($sDynUid) )
        {
            $sql .= " AND DYN_UID = ?";
            $arrWhere[] = $sDynUid;
        }

        $sql .= " AND DYN_TITLE = ?";
        $arrWhere[] = $sNameDyanform;

        $results = $this->objMysql->_query ($sql, [$sProUid]);


        return isset ($results[0]) && !empty ($results[0]) ? true : false;
    }

    /**
     * verify if a dynaform is assigned some dynaform
     *
     * @param string $proUid the uid of the process
     * @param string $dynUid the uid of the dynaform
     *
     * @return array
     */
    public function verifyDynaformAssignDynaform ($dynUid, $proUid)
    {
        $res = array();

        $sql = "SELECT DYN_UID FROM workflow.form WHERE PRO_UID = ? AND DYN_UID != ?";
        $results = $this->objMysql->_query ($sql, [$proUid, $dynUid]);


        return $results;
    }

}
