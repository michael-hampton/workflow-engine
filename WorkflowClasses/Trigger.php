<?php

class Trigger extends BaseTrigger
{

    /**
     * Assign Trigger to a Step
     *
     * @param string $stepUid    Unique id of Step
     * @param array  $aData  Data
     *
     * return array Data of the Trigger assigned to a Step
     */
    public function create ($stepUid, $aData)
    {
        try {
            $triTitle = isset ($aData['title']) ? $aData['title'] : '';
            $this->setTitle ($triTitle);

            $this->setNew (TRUE);

            $triDescription = isset ($aData['description']) ? $aData['description'] : '';
            $template = isset($aData['template_name']) ? $aData['template_name'] : '';
            $this->setDescription ($triDescription);

            $this->setStepTo ($aData['step_to']);
            $this->setWorkflowId ($aData['workflow_id']);
            $this->setId ($stepUid);
            $this->setTriggerType ($aData['trigger_type']);
            $this->setEventType ($aData['event_type']);
            $this->setWorkflowTo ($aData['workflow_id']);
            $this->setTemplate($template);
            if ( $this->validate () )
            {
                $result = $this->save ();
                return $result;
            }
            else
            {
                print_r($this->getArrayValidationErrors());
                die;
                throw (new Exception ("Failed Validation in class " . get_class ($this) . "."));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function update ($fields)
    {
        try {
            $triTitle = isset ($fields['title']) ? $fields['title'] : '';
            $this->setTitle ($triTitle);

            $triDescription = isset ($fields['description']) ? $fields['description'] : '';
            $template = isset($aData['template_name']) ? $aData['template_name'] : '';

            $this->setTemplate($template);
            $this->setDescription ($triDescription);
            $this->setId ($fields['step_id']);
            $this->setTriggerType ($fields['trigger_type']);
            $this->setWorkflowId ($fields['workflow_id']);
            $this->setStepTo ($fields['step_to']);
            $this->setTriggerId ($fields['triggerId']);
            $this->setEventType ($fields['event_type']);
            $this->setWorkflowTo ($fields['workflow_id']);
            if ( $this->validate () )
            {
                $this->setNew (false);
                $result = $this->save ();
                return $result;
            }
            else
            {
                $validationE = new Exception ("Failed Validation in class " . get_class ($this) . ".");
                $validationE->aValidationFailures = $this->getArrayValidationErrors ();
                throw($validationE);
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function load ($TriUid)
    {
        try {
            $oRow = $this->retrieveByPK ($TriUid);
            if ( !is_null ($oRow) )
            {

                return $oRow;
            }
            else
            {
                throw( new Exception ("The row '$TriUid' in table TRIGGERS doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw($oError);
        }
    }

    public function remove ($TriUid)
    {
        try {
            $result = false;
            $oTri = $this->retrieveByPK ($TriUid);
            if ( !is_null ($oTri) )
            {
                $result = $this->delete ($TriUid);
            }
            return $result;
        } catch (Exception $e) {
            throw($e);
        }
    }

}
