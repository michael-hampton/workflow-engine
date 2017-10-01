<?php

/**
 * Skeleton subclass for representing a row from the 'CASE_CONSOLIDATED' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class CaseConsolidatedCore extends BaseCaseConsolidatedCore
{

    private $objMysql;

    private function getConnection ()
    {
        $this->objMysql = new Mysql2();
    }

    public function retrieveByPk ($pk)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_select ("workflow.case_consolidated", [], ["TAS_UID" => $pk]);

        if ( !isset ($result[0]) || empty ($result[0]) )
        {
            return false;
        }

        $objCaseConsolidated = new CaseConsolidatedCore();
        $objCaseConsolidated->setConStatus ($result[0]['CON_STATUS']);
        $objCaseConsolidated->setDynUid ($result[0]['DYN_UID']);
        $objCaseConsolidated->setRepTabUid ($result[0]['REP_TAB_UID']);
        $objCaseConsolidated->setTasUid ($result[0]['TAS_UID']);

        return $objCaseConsolidated;
    }

    public function delete ($sTasUid)
    {
        if ( $this->objMysql === null )
        {
            $this->getConnection ();
        }

        $result = $this->objMysql->_delete ("workflow.case_consolidated", ["TAS_UID" => $sTasUid]);

        if ( !$result )
        {
            return false;
        }

        return true;
    }

}
