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
            $this->setStepTo ($aData['step_to']);
            $this->setWorkflowId ($aData['workflow_id']);
            $this->setId ($stepUid);
            $this->setTriggerType ($aData['trigger_type']);
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
    }
