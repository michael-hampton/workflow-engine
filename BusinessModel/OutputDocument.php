<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OutputDocuments
 *
 * @author michael.hampton
 */

namespace BusinessModel;

class OutputDocument
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Return output documents of a project
     * @param string $sProcessUID
     * @return array
     *
     * @access public
     */
    public function getOutputDocuments ($blReturnArray = false, $sProcessUID = '')
    {
        try {
            $arrWhere = [];

            if ( trim ($sProcessUID) !== "" )
            {
                $arrWhere['id'] = $sProcessUID;
            }

            $results = $this->objMysql->_select ("workflow.output_document", [], $arrWhere);

            $outputDocArray = array();

            if ( isset ($results[0]) && !empty ($results[0]) )
            {
                foreach ($results as $aRow) {
                    if ( ($aRow['OUT_DOC_TITLE'] == null) || ($aRow['OUT_DOC_TITLE'] == "") )
                    {
                        // There is no transaltion for this Document name, try to get/regenerate the label
                        $outputDocument = new \OutputDocument();
                        $outputDocumentObj = $outputDocument->load ($aRow['OUT_DOC_UID']);
                        $aRow['OUT_DOC_TITLE'] = $outputDocumentObj['OUT_DOC_TITLE'];
                        $aRow['OUT_DOC_DESCRIPTION'] = $outputDocumentObj['OUT_DOC_DESCRIPTION'];
                    }
                    else
                    {
                        if ( $blReturnArray === true )
                        {
                            $outputDocArray[$aRow['id']] = array('out_doc_uid' => $aRow['id'],
                                'out_doc_title' => $aRow['OUT_DOC_TITLE'],
                                'out_doc_description' => $aRow['OUT_DOC_DESCRIPTION'],
                                'out_doc_filename' => $aRow['OUT_DOC_FILENAME'],
                                'out_doc_template' => $aRow['OUT_DOC_TEMPLATE'],
                                'out_doc_report_generator' => $aRow['OUT_DOC_REPORT_GENERATOR'],
                                'out_doc_landscape' => $aRow['OUT_DOC_LANDSCAPE'],
                                'out_doc_media' => $aRow['OUT_DOC_MEDIA'],
                                'out_doc_left_margin' => $aRow['OUT_DOC_LEFT_MARGIN'],
                                'out_doc_right_margin' => $aRow['OUT_DOC_RIGHT_MARGIN'],
                                'out_doc_top_margin' => $aRow['OUT_DOC_TOP_MARGIN'],
                                'out_doc_bottom_margin' => $aRow['OUT_DOC_BOTTOM_MARGIN'],
                                'out_doc_generate' => $aRow['OUT_DOC_GENERATE'],
                                'out_doc_type' => $aRow['OUT_DOC_TYPE'],
                                'out_doc_current_revision' => $aRow['OUT_DOC_CURRENT_REVISION'],
                                'out_doc_field_mapping' => $aRow['OUT_DOC_FIELD_MAPPING'],
                                'out_doc_versioning' => $aRow['OUT_DOC_VERSIONING'],
                                'out_doc_destination_path' => $aRow['OUT_DOC_DESTINATION_PATH'],
                                'out_doc_tags' => $aRow['OUT_DOC_TAGS'],
                                'out_doc_pdf_security_enabled' => $aRow['OUT_DOC_PDF_SECURITY_ENABLED'],
                                'out_doc_pdf_security_permissions' => $aRow['OUT_DOC_PDF_SECURITY_PERMISSIONS'],
                                "out_doc_open_type" => $aRow["OUT_DOC_OPEN_TYPE"]);

                            return $outputDocArray;
                        }

                        $objOutputDocument = new \OutputDocument();
                        $objOutputDocument->setOutDocUid ($aRow['id']);
                        $objOutputDocument->loadObject ($aRow);
                        $outputDocArray[] = $objOutputDocument;
                    }
                }
            }

            return $outputDocArray;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return a single output document of a project
     * @param string $sProcessUID
     * @param string $sOutputDocumentUID
     * @return array
     *
     * @access public
     */
    public function getOutputDocument ($sProcessUID = '', $sOutputDocumentUID = '')
    {
        try {
            $outputDocArray = $this->getOutputDocuments ($sProcessUID);
            return $outputDocArray;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a new output document for a project
     * @param string $sProcessUID
     * @param array  $outputDocumentData
     * @return array
     *
     * @access public
     */
    public function addOutputDocument ($sProcessUID, $outputDocumentData, \Step $objStep)
    {

        $outputDocumentData['out_doc_pdf_security_permissions'] = "print|modify|copy";

        $pemission = $outputDocumentData['out_doc_pdf_security_permissions'];
        $pemission = explode ("|", $pemission);

        foreach ($pemission as $row) {
            if ( $row == "print" || $row == "modify" || $row == "copy" || $row == "forms" || $row == "" )
            {
                $outputDocumentData['out_doc_pdf_security_permissions'] = $outputDocumentData['out_doc_pdf_security_permissions'];
            }
            else
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }
        }
        try {

            $outputDocumentData['PRO_UID'] = $sProcessUID;
            //Verify data
            $objWorkflowStep = new \WorkflowStep();
            if ( !$objWorkflowStep->stepExists ($sProcessUID) )
            {
                throw new \Exception ("Step des not exit");
            }

            if ( $outputDocumentData["OUT_DOC_TITLE"] == "" )
            {
                throw new \Exception ("Title cant be empty");
            }
            if ( isset ($outputDocumentData["OUT_DOC_TITLE"]) && $this->existsTitle ($sProcessUID, $outputDocumentData["OUT_DOC_TITLE"]) )
            {
                throw (new \Exception ("Title already exists"));
            }

            $oOutputDocument = new \OutputDocument();
            if ( isset ($outputDocumentData['OUT_DOC_TITLE']) && $outputDocumentData['OUT_DOC_TITLE'] != '' )
            {
                if ( isset ($outputDocumentData['OUT_DOC_PDF_SECURITY_ENABLED']) && $outputDocumentData['OUT_DOC_PDF_SECURITY_ENABLED'] == "0" )
                {
                    $outputDocumentData['OUT_DOC_PDF_SECURITY_OPEN_PASSWORD'] = "";
                    $outputDocumentData['OUT_DOC_PDF_SECURITY_OWNER_PASSWORD'] = "";
                    $outputDocumentData['OUT_DOC_PDF_SECURITY_PERMISSIONS'] = "";
                }
            }
            if ( isset ($outputDocumentData['OUT_DOC_CURRENT_REVISION']) )
            {
                $oOutputDocument->setOutDocCurrentRevision ($outputDocumentData['OUT_DOC_CURRENT_REVISION']);
            }
            else
            {
                $oOutputDocument->setOutDocCurrentRevision (0);
            }
            if ( isset ($outputDocumentData['OUT_DOC_FIELD_MAPPING']) )
            {
                $oOutputDocument->setOutDocFieldMapping ($outputDocumentData['OUT_DOC_FIELD_MAPPING']);
            }
            else
            {
                $oOutputDocument->setOutDocFieldMapping (null);
            }

            $outDocUid = $oOutputDocument->create ($outputDocumentData);

            $this->updateOutputDocument ($sProcessUID, $outputDocumentData, 1, $outDocUid);
            //Return

            $objStepDocument = new \BusinessModel\Step();

            $objStepDocument->create ($objStep->getTasUid (), $objStep->getProUid (), array('STEP_UID_OBJ' => $outDocUid,
                'STEP_TYPE_OBJ' => "OUTPUT_DOCUMENT",
                'STEP_MODE' => "EDIT"));
            unset ($outputDocumentData["PRO_UID"]);

            $outputDocumentData["out_doc_uid"] = $outDocUid;
            return $outputDocumentData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a output document for a project
     * @param string $sProcessUID
     * @param array  $outputDocumentData
     * @param string $sOutputDocumentUID
     * @param int $sFlag
     *
     * @access public
     */
    public function updateOutputDocument ($sProcessUID, $outputDocumentData, $sFlag, $sOutputDocumentUID = '')
    {
        $outputDocumentData['out_doc_pdf_security_permissions'] = "print|modify|copy";
        $pemission = $outputDocumentData['out_doc_pdf_security_permissions'];
        $pemission = explode ("|", $pemission);
        foreach ($pemission as $row) {
            if ( $row == "print" || $row == "modify" || $row == "copy" || $row == "forms" || $row == "" )
            {
                $outputDocumentData['out_doc_pdf_security_permissions'] = $outputDocumentData['out_doc_pdf_security_permissions'];
            }
            else
            {
                throw new \Exception ("ID_INVALID_VALUE_FOR");
            }
        }
        try {
            $outputDocument = new \OutputDocument();
            $oOutputDocument = $outputDocument->retrieveByPK ($sOutputDocumentUID);

            if ( !empty ($oOutputDocument) && is_object ($oOutputDocument) )
            {
                if ( isset ($outputDocumentData['out_doc_pdf_security_open_password']) && $outputDocumentData['out_doc_pdf_security_open_password'] != "" )
                {
                    $outputDocumentData['out_doc_pdf_security_open_password'] = $this->encrypt ($outputDocumentData['out_doc_pdf_security_open_password'], $sOutputDocumentUID);
                    $outputDocumentData['out_doc_pdf_security_owner_password'] = $this->encrypt ($outputDocumentData['out_doc_pdf_security_owner_password'], $sOutputDocumentUID);
                }
                else
                {
                    unset ($outputDocumentData['out_doc_pdf_security_open_password']);
                    unset ($outputDocumentData['out_doc_pdf_security_owner_password']);
                }
                $oOutputDocument->loadObject ($outputDocumentData);
                if ( $oOutputDocument->validate () )
                {
                    if ( isset ($outputDocumentData['OUT_DOC_TITLE']) )
                    {
                        $uid = $this->titleExists ($outputDocumentData["OUT_DOC_TITLE"]);
                        if ( $uid != '' )
                        {
                            if ( $uid != $sOutputDocumentUID && $sFlag == 0 )
                            {
                                throw (new \Exception ("ID_OUTPUT_NOT_SAVE"));
                            }
                        }
                        $oOutputDocument->setOutDocTitleContent ($outputDocumentData['OUT_DOC_TITLE']);
                    }
                    if ( isset ($outputDocumentData['OUT_DOC_DESCRIPTION']) )
                    {
                        $oOutputDocument->setOutDocDescriptionContent ($outputDocumentData['OUT_DOC_DESCRIPTION']);
                    }
                    if ( isset ($outputDocumentData['OUT_DOC_FILENAME']) )
                    {
                        $oOutputDocument->setOutDocFilenameContent ($outputDocumentData['OUT_DOC_FILENAME']);
                    }
                    if ( isset ($outputDocumentData['OUT_DOC_TEMPLATE']) )
                    {
                        $outputDocumentData['OUT_DOC_TEMPLATE'] = stripslashes ($outputDocumentData['OUT_DOC_TEMPLATE']);
                        $outputDocumentData['OUT_DOC_TEMPLATE'] = str_replace ("@amp@", "&", $outputDocumentData['OUT_DOC_TEMPLATE']);
                        $oOutputDocument->setOutDocTemplate ($outputDocumentData['OUT_DOC_TEMPLATE']);
                        $oOutputDocument->setOutDocTemplateContent ($outputDocumentData['OUT_DOC_TEMPLATE']);
                    }
                    $oOutputDocument->save ();
                }
                else
                {
                    $sMessage = '';
                    $aValidationFailures = $oOutputDocument->getValidationFailures ();
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure;
                    }
                    throw (new \Exception ("ID_REGISTRY_CANNOT_BE_UPDATED" . $sMessage));
                }
            }
            else
            {
                throw new \Exception ("ID_ROW_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a output document of a project
     *
     * @param string $sProcessUID
     * @param string $sOutputDocumentUID
     *
     * @access public
     */
    public function deleteOutputDocument ($sProcessUID, $sOutputDocumentUID)
    {
        try {
            $this->throwExceptionIfItsAssignedInOtherObjects ($sOutputDocumentUID, "outputDocumentUid");
            $oOutputDocument = new \OutputDocument();
            $oOutputDocument->remove ($sOutputDocumentUID);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Checks if the title exists in the OutputDocuments of Process
     *
     * @param string $processUid Unique id of Process
     * @param string $title      Title
     *
     * return bool Return true if the title exists in the OutputDocuments of Process, false otherwise
     */
    public function existsTitle ($processUid, $title)
    {
        try {
            $sql = "SELECT ot.OUT_DOC_TITLE FROM workflow.`step` sd
                    INNER JOIN workflow.output_document ot ON ot.id = sd.`STEP_UID_OBJ`
                    WHERE sd.`STEP_UID` = ? AND ot.OUT_DOC_TITLE = ?";

            $arrParameters = array($processUid, $title);

            $result = $this->objMysql->_query ($sql, $arrParameters);
            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }
            else
            {
                return false;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Checks if the title exists in the OutputDocuments of Process
     *
     * @param string $processUid Unique id of Process
     * @param string $title      Title
     *
     */
    public function titleExists ($title)
    {
        try {
            $sql = "SELECT d.OUT_DOC_TITLE, d.id from workflow.output_document d
                    LEFT JOIN workflow.step sd ON sd.STEP_UID_OBJ = d.id
                    WHERE d.OUT_DOC_TITLE = ?";

            $arrParameters = array($title);

            $result = $this->objMysql->_query ($sql, $arrParameters);

            if ( isset ($result[0]) && !empty ($result[0]) )
            {

                $aResp = $result[0]['id'];
                return $aResp;
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if the OutputDocument it's assigned in other objects
     *
     * @param string $outputDocumentUid Unique id of OutputDocument
     *
     * return array Return array (true if it's assigned or false otherwise and data)
     */
    public function itsAssignedInOtherObjects ($outputDocumentUid)
    {
        try {
            $flagAssigned = false;
            //Step
            $result = $this->objMysql->_query ("SELECT d.* FROM `output_document` d 
                                            INNER JOIN step sd ON sd.STEP_UID_OBJ = d.id
                                            WHERE sd.STEP_TYPE_OBJ = 'OUTPUT_DOCUMENT'
                                            AND sd.STEP_UID_OBJ = ?", [$outputDocumentUid]);


            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                $flagAssigned = true;
            }

            //Return
            return array($flagAssigned, $result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if the OutputDocument it's assigned in other objects
     *
     * @param string $outputDocumentUid      Unique id of OutputDocument
     * @param string $fieldNameForException Field name for the exception
     *
     * return void Throw exception if the OutputDocument it's assigned in other objects
     */
    public function throwExceptionIfItsAssignedInOtherObjects ($outputDocumentUid)
    {
        try {
            list($flagAssigned, $arrayData) = $this->itsAssignedInOtherObjects ($outputDocumentUid);
            if ( $flagAssigned )
            {
                throw new \Exception ("OUTPUT DOCUMENT IT ASSIGNED TO A STEP");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getOutputDocumentsForStep (\Step $objStep)
    {
        try {
            $results = $this->objMysql->_query ("SELECT * FROM workflow.output_document d INNER JOIN workflow.step sd ON sd.STEP_UID_OBJ = d.id WHERE sd.TAS_UID = ? AND sd.STEP_TYPE_OBJ = 'OUTPUT_DOCUMENT'", [$objStep->getTasUid ()]);

            $arrDocuments = [];

            foreach ($results as $result) {
                $oDocument = new \OutputDocument ();
                $arrDocuments[] = $oDocument->retrieveByPk ($result['id']);
            }

            return $arrDocuments;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function assignToStep ($assignArr, \Step $objStep)
    {
        try {

            $arrAssignedDocs = $this->getOutputDocumentsForStep ($objStep);

            $arrAssigned = [];

            foreach ($arrAssignedDocs as $arrAssignedDoc) {
                $arrAssigned[] = $arrAssignedDoc->getOutDocUid ();
            }

            if ( !isset ($assignArr['selectedDocs']) )
            {
                $assignArr['selectedDocs'] = [];
            }

            foreach ($arrAssigned as $docId) {
                if ( !in_array ($docId, $assignArr['selectedDocs']) )
                {
                    $objSDtepDocument = new \Step();
                    $objSDtepDocument->delete ("OUTPUT_DOCUMENT", $docId, $objStep->getTasUid ());
                }
            }

            if ( isset ($assignArr['selectedDocs']) && !empty ($assignArr['selectedDocs']) )
            {
                foreach ($assignArr['selectedDocs'] as $docId) {
                    if ( !in_array ($docId, $arrAssigned) )
                    {
                        $objStepDocument = new \BusinessModel\Step();

                        $objStepDocument->create ($objStep->getTasUid (), $objStep->getProUid (), array('STEP_UID_OBJ' => $docId,
                            'STEP_TYPE_OBJ' => "OUTPUT_DOCUMENT",
                            'STEP_MODE' => "EDIT"));
                    }
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
