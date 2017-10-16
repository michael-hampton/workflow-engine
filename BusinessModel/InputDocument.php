<?php

namespace BusinessModel;

class InputDocument
{

    private $objMysql;

    /**
     * 
     * @param type $stepId
     */
    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Get data of an InputDocument
     *
     * @param string $inputDocumentUid Unique id of InputDocument
     *
     * return object Return an object with data of an InputDocument
     */
    public function getInputDocument ($inputDocumentUid = null, $blReturnArray = false)
    {
        try {
            $arrWhere = array();

            if ( $inputDocumentUid !== null )
            {
                $this->throwExceptionIfNotExistsInputDocument ($inputDocumentUid);

                $arrWhere = array("id" => $inputDocumentUid);
            }

            $results = $this->objMysql->_select ("workflow.documents", array(), $arrWhere);

            $arrUnformatted = [];

            foreach ($results as $result) {
                $arrUnformatted[$result['id']] = $result;
            }

            if ( empty ($results) )
            {
                return false;
            }

            $arrDocuments = array();

            if ( $blReturnArray === false )
            {
                foreach ($results as $result) {

                    $arrDocuments[$result['id']] = new \InputDocument ();
                    $arrDocuments[$result['id']]->setDescription ($result['description']);
                    $arrDocuments[$result['id']]->setDestinationPath ($result['destination_path']);
                    $arrDocuments[$result['id']]->setFileType ($result['filetype']);
                    $arrDocuments[$result['id']]->setFilesizeUnit ($result['filesize_unit']);
                    $arrDocuments[$result['id']]->setId ($result['id']);
                    $arrDocuments[$result['id']]->setMaxFileSize ($result['max_filesize']);
                    $arrDocuments[$result['id']]->setTitle ($result['name']);
                    $arrDocuments[$result['id']]->setVersioning ($result['allow_versioning']);
                }

                return $arrDocuments;
            }

            return $arrUnformatted;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of an InputDocument
     *
     * @param string $inputDocumentUid Unique id of InputDocument
     *
     * return array Return InputDocument object
     */
    public function getInputDocumentForStep (\Step $objStep)
    {
        try {
            $results = $this->objMysql->_query ("SELECT * FROM workflow.documents d INNER JOIN workflow.step sd ON sd.STEP_UID_OBJ = d.id WHERE sd.TAS_UID = ? AND STEP_TYPE_OBJ = 'INPUT_DOCUMENT'", [$objStep->getTasUid ()]);

            $arrDocuments = [];

            foreach ($results as $key => $result) {
                $arrDocuments[$key] = new \InputDocument ();
                $arrDocuments[$key]->setDescription ($result['description']);
                $arrDocuments[$key]->setDestinationPath ($result['destination_path']);
                $arrDocuments[$key]->setFileType ($result['filetype']);
                $arrDocuments[$key]->setFilesizeUnit ($result['filesize_unit']);
                $arrDocuments[$key]->setId ($result['id']);
                $arrDocuments[$key]->setMaxFileSize ($result['max_filesize']);
                $arrDocuments[$key]->setTitle ($result['name']);
                $arrDocuments[$key]->setVersioning ($result['allow_versioning']);
            }

            return $arrDocuments;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create InputDocument for a Process
     *
     * @param array  $arrayData  Data
     *
     * return array Return data of the new InputDocument created
     */
    public function create ($arrayData, \Step $objStep)
    {
        try {
            //Verify data
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfNotExistsProcess ($processUid, $this->arrayFieldNameForException["processUid"]);
            //$process->throwExceptionIfDataNotMetFieldDefinition ($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, true);

            if ( !$objStep->stepExists ($objStep->getTasUid ()) )
            {
                throw new \Exception ("Step does not exist");
            }
            $this->throwExceptionIfExistsTitle ($arrayData["INP_DOC_TITLE"]);

            //Flags
            $flagDataDestinationPath = (isset ($arrayData["INP_DOC_DESTINATION_PATH"])) ? 1 : 0;
            $flagDataTags = (isset ($arrayData["INP_DOC_TAGS"])) ? 1 : 0;

            //Create
            $inputDocument = new \InputDocument ();
            $arrayData["PRO_UID"] = $objStep->getProUid ();
            $arrayData["INP_DOC_DESTINATION_PATH"] = ($flagDataDestinationPath == 1) ? $arrayData["INP_DOC_DESTINATION_PATH"] : "";
            $arrayData["INP_DOC_TAGS"] = ($flagDataTags == 1) ? $arrayData["INP_DOC_TAGS"] : "";

            $documentId = $inputDocument->create ($arrayData);
            (new \BusinessModel\Step())->create ($objStep->getTasUid (), $objStep->getProUid (), array('STEP_UID_OBJ' => $documentId,
                'STEP_TYPE_OBJ' => "INPUT_DOCUMENT",
                'STEP_MODE' => "EDIT"));


            //Return
            unset ($arrayData["PRO_UID"]);

            if ( $flagDataDestinationPath == 0 )
            {
                unset ($arrayData["INP_DOC_DESTINATION_PATH"]);
            }

            if ( $flagDataTags == 0 )
            {
                unset ($arrayData["INP_DOC_TAGS"]);
            }

            $arrayData = array_merge (array("INP_DOC_UID" => $documentId), $arrayData);

            return $arrayData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if doesn't exists the InputDocument in table INPUT_DOCUMENT
     *
     * @param string $inputDocumentUid      Unique id of InputDocument
     *
     * return void Throw exception if doesn't exists the InputDocument in table INPUT_DOCUMENT
     */
    public function throwExceptionIfNotExistsInputDocument ($inputDocumentUid)
    {
        try {

            $result = $this->objMysql->_select ("workflow.documents", array(), array("id" => $inputDocumentUid));

            if ( !isset ($result[0]) || empty ($result[0]) )
            {
                throw new \Exception ("ID_INPUT_DOCUMENT_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a InputDocument
     *
     * @param string $processUid              Unique id of Process
     * @param string $inputDocumentTitle      Title
     *
     * return void Throw exception if exists the title of a InputDocument
     */
    public function throwExceptionIfExistsTitle ($inputDocumentTitle)
    {
        try {
            if ( $this->existsTitle ($inputDocumentTitle) )
            {
                //throw new \Exception ("ID_INPUT_DOCUMENT_TITLE_ALREADY_EXISTS");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if the InputDocument it's assigned in other objects
     *
     * @param string $inputDocumentUid      Unique id of InputDocument
     *
     * return void Throw exception if the InputDocument it's assigned in other objects
     */
    public function throwExceptionIfItsAssignedInOtherObjects ($inputDocumentUid)
    {
        try {
            list($flagAssigned) = $this->itsAssignedInOtherObjects ($inputDocumentUid);

            if ( $flagAssigned )
            {
                throw new \Exception ("INPUT DOCUMENT IS ASSIGNED TO A STEP");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update InputDocument
     *
     * @param string $inputDocumentUid Unique id of InputDocument
     * @param array  $arrayData        Data
     *
     * return array Return data of the InputDocument updated
     */
    public function update ($inputDocumentUid, $arrayData)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsInputDocument ($inputDocumentUid);
            //Load InputDocument
            $inputDocument = new \InputDocument ();

            //Verify data

            if ( isset ($arrayData["INP_DOC_TITLE"]) )
            {
                //$this->throwExceptionIfExistsTitle ($this->stepId, $arrayData["INP_DOC_TITLE"], $this->arrayFieldNameForException["inputDocumentTitle"], $inputDocumentUid);
            }

            //Update
            $inputDocument->update ($inputDocumentUid, $arrayData);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete InputDocument
     *
     * @param string $inputDocumentUid Unique id of InputDocument
     *
     * return void
     */
    public function delete ($inputDocumentUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsInputDocument ($inputDocumentUid);
            $this->throwExceptionIfItsAssignedInOtherObjects ($inputDocumentUid);

            $inputDocument = new \InputDocument ();
            $inputDocument->delete ($inputDocumentUid);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if the InputDocument it's assigned in other objects
     *
     * @param string $inputDocumentUid Unique id of InputDocument
     *
     * return array Return array (true if it's assigned or false otherwise and data)
     */
    public function itsAssignedInOtherObjects ($inputDocumentUid)
    {
        try {
            $flagAssigned = false;
            $arrayData = array();

            //Step
            $result = $this->objMysql->_select ("workflow.step", array(), array("STEP_UID_OBJ" => $inputDocumentUid));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                $flagAssigned = true;
                $arrayData[] = $result[0];
            }

            //Return
            return array($flagAssigned, $arrayData);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the title of a InputDocument
     *
     * @param string $processUid              Unique id of Process
     * @param string $inputDocumentTitle      Title
     * @param string $inputDocumentUidExclude Unique id of InputDocument to exclude
     *
     * return bool Return true if exists the title of a InputDocument, false otherwise
     */
    public function existsTitle ($inputDocumentTitle)
    {
        try {
            $result = $this->objMysql->_select ("workflow.documents", array(), array("name" => $inputDocumentTitle));

            if ( isset ($result[0]) && !empty ($result[0]) )
            {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function assignToStep ($assignArr, \Step $objStep)
    {
        try {
            $arrAssignedDocs = $this->getInputDocumentForStep ($objStep);
            $arrAssigned = [];

            foreach ($arrAssignedDocs as $arrAssignedDoc) {
                $arrAssigned[] = $arrAssignedDoc->getId ();
            }

            if ( !isset ($assignArr['selectedDocs']) )
            {
                $assignArr['selectedDocs'] = [];
            }

            foreach ($arrAssigned as $docId) {
                if ( !in_array ($docId, $assignArr['selectedDocs']) )
                {
                    $objSDtepDocument = new \Step();
                    $objSDtepDocument->delete ("INPUT_DOCUMENT", $docId, $objStep->getTasUid ());
                }
            }

            if ( isset ($assignArr['selectedDocs']) && !empty ($assignArr['selectedDocs']) )
            {
                foreach ($assignArr['selectedDocs'] as $docId) {
                    if ( !in_array ($docId, $arrAssigned) )
                    {
                        $objStepDocument = new \BusinessModel\Step();

                        $objStepDocument->create ($objStep->getTasUid (), $objStep->getProUid (), array('STEP_UID_OBJ' => $docId,
                            'STEP_TYPE_OBJ' => "INPUT_DOCUMENT",
                            'STEP_MODE' => "EDIT"));
                    }
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
