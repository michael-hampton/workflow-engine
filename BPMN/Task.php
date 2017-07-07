<?php

class Task
{

    private $stepName;
    private $objMysql;
    private $stepId;

    public function __construct ($stepId = null)
    {
        $this->objMysql = new Mysql2();

        if ( $stepId !== null )
        {
            $this->stepId = $stepId;
        }
    }

    public function getStepName ()
    {
        return $this->stepName;
    }

    public function setStepName ($stepName)
    {
        $this->stepName = $stepName;
    }

    public function getStepId ()
    {
        return $this->stepId;
    }

    public function setStepId ($stepId)
    {
        $this->stepId = $stepId;
    }

    public function save ()
    {
        $id = $this->objMysql->_insert ("workflow.steps", array("step_name" => $this->stepName));
        return $id;
    }

    public function removeTask ()
    {
        $this->objMysql->_delete ("workflow.steps", array("step_id" => $this->stepId));
    }

    public function getTask ($step)
    {
        $check = $this->objMysql->_select ("workflow.steps", array(), array("step_id" => $step));

        return $check;
    }
    
    /**
     * create a new Task
     *
     * @param      array $aData with new values
     * @return     string
     */
    public function create($aData)
    {

        try {
           
            $sTaskUID = $aData['TAS_UID'];
 

            $this->setProUid($aData['PRO_UID']);
            $this->setTasUid($sTaskUID);
            $this->setTasTitle((isset($aData['TAS_TITLE']) ? $aData['TAS_TITLE']: ''));
            $this->setTasDescription((isset($aData['TAS_DESCRIPTION']) ? $aData['TAS_DESCRIPTION']: ''));
            $this->setTasDefTitle("");
            $this->setTasDefDescription("");
            $this->setTasDefProcCode("");
            $this->setTasDefMessage("");
            $this->setTasDefSubjectMessage("");
            $this->setTasType("NORMAL");
            $this->setTasDuration("1");
            $this->setTasDelayType("");
            $this->setTasTypeDay("");
            $this->setTasTimeunit("DAYS");
            $this->setTasPriorityVariable("");
            $this->setTasAssignType("BALANCED");
            $this->setTasAssignVariable("@@SYS_NEXT_USER_TO_BE_ASSIGNED");
            $this->setTasAssignLocation("FALSE");
            $this->setTasAssignLocationAdhoc("FALSE");
            $this->setTasLastAssigned("0");
            $this->setTasUser("0");
            $this->setTasCanUpload("FALSE");
            $this->setTasViewUpload("FALSE");
            $this->setTasViewAdditionalDocumentation("FALSE");
            $this->setTasCanCancel("FALSE");
            $this->setTasOwnerApp("FALSE");
            $this->setStgUid("");
            $this->setTasCanPause("FALSE");
            $this->setTasCanSendMessage("TRUE");
            $this->setTasCanDeleteDocs("FALSE");
            $this->setTasSelfService("FALSE");
            $this->setTasStart("FALSE");
            $this->setTasToLastUser("FALSE");
            $this->setTasSendLastEmail("FALSE");

            $this->setTasGroupVariable("");
  
                $this->setTasId($aData['TAS_ID']);
         
            $this->loadObject($aData);

            if ($this->validate()) {
                $this->setTasTitleContent((isset($aData['TAS_TITLE']) ? $aData['TAS_TITLE']: ''));
                $this->setTasDescriptionContent((isset($aData['TAS_DESCRIPTION']) ? $aData['TAS_DESCRIPTION']: ''));
                
		$this->save();
      
                return $sTaskUID;
            } else {

                $e = new Exception("Failed Validation in class " . get_class($this) . ".");
                $e->aValidationFailures=$this->getValidationFailures();

                throw ($e);
            }
        } catch (Exception $e) {


            throw ($e);
        }
    }

/**
     * Get the tas_title column value.
     * @return     string
     */
    public function getTasTitleContent()
    {
        if ($this->getTasUid() == "") {
            throw (new Exception("Error in getTasTitle, the getTasUid() can't be blank"));
        }


        return $this->tas_title_content;
    }


    /**
     * Set the tas_title column value.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setTasTitleContent($v)
    {
        if ($this->getTasUid() == "") {
            throw (new Exception("Error in setTasTitle, the getTasUid() can't be blank"));
        }

        $v = isset($v)? ((string)$v) : '';


        if ($v === "") {
            $this->tas_title_content = $v;

        }

    }

    /**
     * This value goes in the content table
     * @var        string
     */
    protected $tas_description_content = '';

    /**
     * Get the tas_description column value.
     * @return     string
     */
    public function getTasDescriptionContent()
    {
        if ($this->getTasUid() == "") {
            throw (new Exception( "Error in getTasDescription, the getTasUid() can't be blank"));
        }


        return $this->tas_description_content;
    }


    /**
     * Set the tas_description column value.
     *
     * @param      string $v new value
     * @return     void
     */
    public function setTasDescriptionContent($v)
    {
        if ($this->getTasUid() == "") {
            throw (new Exception("Error in setTasDescription, the getTasUid() can't be blank"));
        }

        $v = isset($v)? ((string)$v) : '';


        if ($v === "") {
            $this->tas_description_content = $v;

        }

    }

}
