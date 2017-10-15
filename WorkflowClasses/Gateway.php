<?php

/**
 * Description of Gateway
 *
 * @author michael.hampton
 */
class Gateway extends BaseGateway
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function create ($aData)
    {
        try {

            $this->setNew (true);
            $this->setCondition ($aData['condition']);
            $this->setConditionValue ($aData['conditionValue']);
            $this->setElse ($aData['else']);
            $this->setField ($aData['field']);
            $this->setStep_to ($aData['next_step']);
            $this->setTriggerType ($aData['trigger_type']);
            $this->setWorkflowId ($aData['workflow_id']);

            $title = isset ($aData['title']) && trim ($aData['title']) !== "" ? $aData['title'] : '';
            $this->setTitle ($title);

            $description = isset ($aData['description']) && trim ($aData['description']) !== "" ? $aData['description'] : '';
            $this->setDescription ($description);

            if ( $this->validate () )
            {
                $iResult = $this->save ();
                return $iResult;
            }
            else
            {
                $sMessage = '';
                $aValidationFailures = $this->getArrayValidationErrors ();
                foreach ($aValidationFailures as $strMessage) {
                    $sMessage .= $strMessage . '<br />';
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

            $this->setNew (false);
            $this->setCondition ($aData['condition']);
            $this->setConditionValue ($aData['conditionValue']);
            $this->setElse ($aData['else']);
            $this->setField ($aData['field']);
            $this->setStep_to ($aData['next_step']);
            $this->setTriggerType ($aData['trigger_type']);
            $this->setWorkflowId ($aData['workflow_id']);
            $this->setGatewayId ($aData['gatewayId']);

            $title = isset ($aData['title']) && trim ($aData['title']) !== "" ? $aData['title'] : '';
            $this->setTitle ($title);

            $description = isset ($aData['description']) && trim ($aData['description']) !== "" ? $aData['description'] : '';
            $this->setDescription ($description);

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

    public function remove ($GatewayUid)
    {
        try {
            $oGateWay = $this->retrieveByPK ($GatewayUid);
            if ( !is_null ($oGateWay) )
            {
                
                $this->delete ();
                return true;
            }
            else
            {
                throw (new Exception ('This row does not exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param      mixed $pk the primary key.
     * @param      Connection $con the connection to use
     * @return     Gateway
     */
    public function retrieveByPK ($pk)
    {

        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $v = $this->objMysql->_select ("workflow.gateways", [], ["id" => $pk]);

        if ( isset ($v[0]) && !empty ($v[0]) )
        {
            $this->setGatewayId ($pk);
            return $v[0];
        }

        return null;
    }

    public function load ($GatewayUid)
    {
        try {
            $oRow = $this->retrieveByPK ($GatewayUid);
            if ( !is_null ($oRow) )
            {
                return $oRow;
            }
            else
            {
                throw (new Exception ("The row '" . $GatewayUid . "' in table Gateway doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

}
