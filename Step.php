<?php

class Step extends BaseStep
{

    private $objMysql;

    public function __construct ($stepId = null)
    {
        parent::__construct ();
        $this->objMysql = new Mysql2();

        if ( $stepId !== null )
        {
            $this->setStepUid ($stepId);
        }
    }

    /*
     * update the step information using an array with all values
     * @param array $fields
     * @return variant
     */

    public function update ($fields)
    {
        try {

            $this->loadObject ($fields);
            if ( $this->validate () )
            {
                $result = $this->save ();
                return $result;
            }
            else
            {
                $messages = $this->getValidationFailures ();
                $strMessage = '';

                foreach ($messages as $message) {
                    $strMessage .= $message . "</br>";
                }

                throw (new Exception ("Could not save step " . $strMessage));
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    public function delete ($type, $documentId, $task)
    {
        $this->setTasUid ($task);
        $this->setStepTypeObj ($type);
        $this->setStepUidObj ($documentId);
        $this->doDelete ();
    }

    public function stepExists ($stepId)
    {

        $result = $this->objMysql->_select ("workflow.task", [], ["TAS_UID" => $stepId]);
        if ( isset ($result[0]) && !empty ($result[0]) )
        {
            return true;
        }
        return false;
    }

}
