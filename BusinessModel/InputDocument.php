<?php
namespace BusinessModel;

class InputDocument
{

    private $objMysql;
    private $stepId;
    private $documentId;

    /**
     * 
     * @param type $stepId
     */
    public function __construct (\Task $objTask)
    {

        if ( $objTask !== null )
        {
            $this->stepId = $objTask->getStepId ();
        }

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
                    $arrDocuments[$result['id']] = new \InputDocument ($this->stepId);
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
    public function getInputDocumentForStep ()
    {
        try {
            $results = $this->objMysql->_query ("SELECT * FROM workflow.documents d INNER JOIN workflow.step_document sd ON sd.document_id = d.id WHERE sd.step_id = ? AND sd.document_type = 2", [$this->stepId]);

            $arrDocuments = [];

            foreach ($results as $key => $result) {
                $arrDocuments[$key] = new \InputDocument ($this->stepId);
                $arrDocuments[$key]->setDescription ($result['description']);
                $arrDocuments[$key]->setDestinationPath ($result['destination_path']);
                $arrDocuments[$key]->setFileType ($result['filetype']);
                $arrDocuments[$key]->setFilesizeUnit ($result['filesize_unit']);
                $arrDocuments[$key]->setId ($result['document_id']);
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
    public function create ($arrayData)
    {
        try {
            //Verify data
            //$process = new \ProcessMaker\BusinessModel\Process();
            //$process->throwExceptionIfNotExistsProcess ($processUid, $this->arrayFieldNameForException["processUid"]);
            //$process->throwExceptionIfDataNotMetFieldDefinition ($arrayData, $this->arrayFieldDefinition, $this->arrayFieldNameForException, true);

            $objStep = new \WorkflowStep();
            if ( !$objStep->stepExists ($this->stepId) )
            {
                throw new \Exception ("Step does not exist");
            }
            $this->throwExceptionIfExistsTitle ($this->stepId, $arrayData["INP_DOC_TITLE"]);

            //Flags
            $flagDataDestinationPath = (isset ($arrayData["INP_DOC_DESTINATION_PATH"])) ? 1 : 0;
            $flagDataTags = (isset ($arrayData["INP_DOC_TAGS"])) ? 1 : 0;

            //Create
            $inputDocument = new \InputDocument ($this->stepId);
            $arrayData["PRO_UID"] = $this->stepId;
            $arrayData["INP_DOC_DESTINATION_PATH"] = ($flagDataDestinationPath == 1) ? $arrayData["INP_DOC_DESTINATION_PATH"] : "";
            $arrayData["INP_DOC_TAGS"] = ($flagDataTags == 1) ? $arrayData["INP_DOC_TAGS"] : "";

            $documentId = $inputDocument->create ($arrayData);

            $inputDocument->saveStepDocument ($documentId);

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

            $arrayData = array_merge (array("INP_DOC_UID" => $this->documentId), $arrayData);

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
    public function throwExceptionIfExistsTitle ($processUid, $inputDocumentTitle)
    {
        try {
            if ( $this->existsTitle ($processUid, $inputDocumentTitle) )
            {
                throw new \Exception ("ID_INPUT_DOCUMENT_TITLE_ALREADY_EXISTS");
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
            list($flagAssigned, $arrayData) = $this->itsAssignedInOtherObjects ($inputDocumentUid);
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
            $inputDocument = new \InputDocument ($this->stepId);

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

            $objWorkflowStep = new \WorkflowStep();

            if ( !$objWorkflowStep->stepExists ($this->stepId) )
            {
                throw new \Exception ("Step doews not exist");
            }

            $this->throwExceptionIfNotExistsInputDocument ($inputDocumentUid);
            $this->throwExceptionIfItsAssignedInOtherObjects ($inputDocumentUid);

            $inputDocument = new \InputDocument ($this->stepId);
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
            $result = $this->objMysql->_select ("workflow.step_document", array(), array("document_id" => $inputDocumentUid));

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
    public function existsTitle ($processUid, $inputDocumentTitle, $inputDocumentUidExclude = "")
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

    public function assignToStep ($assignArr)
    {
        $inputDocument = new \InputDocument ($this->stepId);
        try {
            $inputDocument = new \InputDocument ($this->stepId);
            $arrAssignedDocs = $this->getInputDocumentForStep ();
            $arrAssigned = [];

            foreach ($arrAssignedDocs as $arrAssignedDoc) {
                $arrAssigned[] = $arrAssignedDoc->getId ();
            }

            foreach ($arrAssigned as $docId) {
                if ( !in_array ($docId, $assignArr) )
                {
                    $this->objMysql->_delete ("workflow.step_document", array("document_id" => $docId));
                }
            }

            foreach ($assignArr as $docId) {
                if ( !in_array ($docId, $arrAssigned) )
                {
                    $inputDocument->saveStepDocument ($docId);
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage ();
            die;
        }
    }

}
