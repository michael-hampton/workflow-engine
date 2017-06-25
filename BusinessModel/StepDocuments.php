<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StepDocument
 *
 * @author michael.hampton
 */
class StepDocuments
{

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    public function create ($aData, $stepId, $docType)
    {
        $assignedDocs = $this->getStepDocuments ($stepId, $docType);

        foreach ($assignedDocs as $assignedDoc) {
            if ( !in_array ($assignedDoc['document_id'], $aData) )
            {
                $this->deleteStepDoc ($stepId, $docType, $assignedDoc['document_id']);
            }
        }

        $objStepDocument = new \StepDocument ($stepId);

        foreach ($aData as $docId) {
            $objStepDocument->setDocumentId ($docId);
            $objStepDocument->setDocumentType ($docType);

            if ( $objStepDocument->validate () )
            {
                $objStepDocument->save ();
            }
        }
    }

    public function getStepDocuments ($stepId, $docType)
    {

        $results = $this->objMysql->_select ("workflow.step_document", [], ["step_id" => $stepId, "document_type" => $docType]);

        return $results;
    }

    public function deleteStepDoc ($stepId, $docType, $docId)
    {
        $objStepDocument = new \StepDocument ($stepId);
        $objStepDocument->setDocumentId ($docId);
        $objStepDocument->setDocumentType ($docType);

        if ( $objStepDocument->validate () )
        {
            $objStepDocument->delete ();
        }
    }

}
