<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageEventRelation
 *
 * @author michael.hampton
 */

namespace BusinessModel;

class MessageEventRelation
{

    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new \Mysql2();
    }

    /**
     * Get data of a Message-Event-Relation
     *
     * @param array $arrayCondition Conditions
     * @param bool  $flagGetRecord  Value that set the getting
     *
     * return array Return an array with data of a Message-Event-Relation, otherwise null
     */
    public function getMessageEventRelationWhere (array $arrayCondition, $flagGetRecord = false)
    {
        try {
            //Get data
            $criteria = $this->getMessageEventRelationCriteria ();

            if ( !empty ($arrayCondition) )
            {
                $criteria .= " WHERE 1=1";
                foreach ($arrayCondition as $key => $value) {
                    if ( is_array ($value) )
                    {
                        $criteria->add ($key, $value[0], $value[1]);
                    }
                    else
                    {
                        $criteria .= " AND " . $key . " = " . $value;
                    }
                }
            }


            $row = $this->objMysql->_query ($criteria);

            if ( isset ($row[0]) && !empty ($row[0]) )
            {
                //Return
                return (!$flagGetRecord) ? $this->getMessageEventRelationDataFromRecord ($row[0]) : $row[0];
            }
            else
            {
                //Return
                return null;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Message-Event-Relation
     *
     * @param string $messageEventRelationUid Unique id of Message-Event-Relation
     *
     * return bool Return true if exists the Message-Event-Relation, false otherwise
     */
    public function exists ($messageEventRelationUid)
    {
        try {
            $result = $this->objMysql->_select ("workflow.message_event_relation", [], ["MSGER_UID" => $messageEventRelationUid]);
            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if does not exists the Message-Event-Relation
     *
     * @param string $messageEventRelationUid Unique id of Message-Event-Relation
     * @param string $fieldNameForException   Field name for the exception
     *
     * return void Throw exception if does not exists the Message-Event-Relation
     */
    public function throwExceptionIfNotExistsMessageEventRelation ($messageEventRelationUid)
    {
        try {
            if ( !$this->exists ($messageEventRelationUid) )
            {
                throw new \Exception ("ID_MESSAGE_EVENT_RELATION_DOES_NOT_EXIST");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Message-Event-Relation
     *
     * @param string $messageEventRelationUid Unique id of Message-Event-Relation
     * @param bool   $flagGetRecord           Value that set the getting
     *
     * return array Return an array with data of a Message-Event-Relation
     */
    public function getMessageEventRelation ($messageEventRelationUid, $flagGetRecord = false)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsMessageEventRelation ($messageEventRelationUid);
            //Get data
            $criteria = $this->getMessageEventRelationCriteria ();
            $criteria .= " WHERE MSGER_UID = ?";
            $result = $this->objMysql->_query ($criteria, [$messageEventRelationUid]);

            $objMessageEventRelation = new \MessageEventRelation();
            $objMessageEventRelation->setEVN_UID_CATCH ($result[0]['EVN_UID_CATCH']);
            $objMessageEventRelation->setEVN_UID_THROW ($result[0]['EVN_UID_THROW']);
            $objMessageEventRelation->setPrjUid ($result[0]['PRJ_UID']);
            $objMessageEventRelation->setMSGER_UID ($result[0]['MSGER_UID']);

            //Return
            return $objMessageEventRelation;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get criteria for Message-Event-Relation
     *
     * return object
     */
    public function getMessageEventRelationCriteria ()
    {
        try {
            $criteria = "SELECT MSGER_UID,  PRJ_UID, EVN_UID_THROW, EVN_UID_CATCH FROM workflow.message_event_relation";
            return $criteria;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a Message-Event-Relation from a record
     *
     * @param array $record Record
     *
     * return array Return an array with data Message-Event-Relation
     */
    public function getMessageEventRelationDataFromRecord (array $record)
    {
        try {
            $objMessageEventRelations = new \MessageDefinition();
            return array(
                $objMessageEventRelations->setPrjUid ($record["MSGER_UID"]),
                //$objMessageEventRelations $record["EVN_UID_THROW"],
                $this->getFieldNameByFormatFieldName ("EVN_UID_CATCH") => $record["EVN_UID_CATCH"]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if exists the Event-Relation of a Message-Event-Relation
     *
     * @param string $projectUid                       Unique id of Project
     * @param string $eventUidThrow                    Unique id of Event (throw)
     * @param string $eventUidCatch                    Unique id of Event (catch)
     * @param string $messageEventRelationUidToExclude Unique id of Message-Event-Relation to exclude
     *
     * return bool Return true if exists the Event-Relation of a Message-Event-Relation, false otherwise
     */
    public function existsEventRelation ($projectUid, $eventUidThrow, $eventUidCatch, $messageEventRelationUidToExclude = "")
    {
        try {

            $sql = "SELECT * FROM workflow.message_event_relation
                    WHERE PRJ_UID = ?";
            $arrParameters = array($projectUid);


            if ( $messageEventRelationUidToExclude != "" )
            {
                $sql .= " AND MSGER_UID != ?";
                $arrParameters[] = $messageEventRelationUidToExclude;
            }

            $sql .= " AND EVN_UID_THROW = ?";
            $arrParameters[] = $eventUidThrow;

            $sql .= " AND EVN_UID_CATCH = ?";
            $arrParameters[] = $eventUidCatch;

            $result = $this->objMysql->_query ($sql, $arrParameters);

            return isset ($result[0]) && !empty ($result[0]) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if is registered the Event-Relation
     *
     * @param string $projectUid                       Unique id of Project
     * @param string $eventUidThrow                    Unique id of Event (throw)
     * @param string $eventUidCatch                    Unique id of Event (catch)
     * @param string $messageEventRelationUidToExclude Unique id of Message-Event-Relation to exclude
     *
     * return void Throw exception if is registered the Event-Relation
     */
    public function throwExceptionIfEventRelationIsRegistered ($projectUid, $eventUidThrow, $eventUidCatch, $messageEventRelationUidToExclude = "")
    {
        try {
            if ( $this->existsEventRelation ($projectUid, $eventUidThrow, $eventUidCatch, $messageEventRelationUidToExclude) )
            {
                throw new \Exception ("ID_MESSAGE_EVENT_RELATION_ALREADY_REGISTERED");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate the data if they are invalid (INSERT and UPDATE)
     *
     * @param string $messageEventRelationUid Unique id of Message-Event-Relation
     * @param string $projectUid              Unique id of Project
     * @param array  $arrayData               Data
     *
     * return void Throw exception if data has an invalid value
     */
    public function throwExceptionIfDataIsInvalid ($messageEventRelationUid, $projectUid, array $arrayData)
    {
        try {
            //Set variables
            $arrayMessageEventRelationData = ($messageEventRelationUid == "") ? array() : $this->getMessageEventRelation ($messageEventRelationUid, true);
            $arrayFinalData = array_merge ($arrayMessageEventRelationData, $arrayData);
            //Verify data - Field definition
            //Verify data
            if ( isset ($arrayData["EVN_UID_THROW"]) || isset ($arrayData["EVN_UID_CATCH"]) )
            {
                $this->throwExceptionIfEventRelationIsRegistered ($projectUid, $arrayFinalData["EVN_UID_THROW"], $arrayFinalData["EVN_UID_CATCH"], $messageEventRelationUid);
            }

            if ( isset ($arrayData["EVN_UID_THROW"]) || isset ($arrayData["EVN_UID_CATCH"]) )
            {
                //Flow

                $bpmnFlow = $this->objMysql->_select ("workflow.status_mapping", [], ["step_from" => $arrayFinalData["EVN_UID_THROW"], "step_to" => $arrayFinalData["EVN_UID_CATCH"]]);

                if ( !isset ($bpmnFlow[0]) || empty ($bpmnFlow[0]) )
                {
                    throw new \Exception ("ID_MESSAGE_EVENT_RELATION_DOES_NOT_EXIST_MESSAGE_FLOW");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Message-Event-Relation for a Project
     *
     * @param string $projectUid Unique id of Project
     * @param array  $arrayData  Data
     *
     * return array Return data of the new Message-Event-Relation created
     */
    public function create ($projectUid, array $arrayData)
    {
        try {

            //Verify data
            $this->throwExceptionIfDataIsNotArray ($arrayData, "\$arrayData");
            $this->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            unset ($arrayData["MSGER_UID"]);
            unset ($arrayData["PRJ_UID"]);
            //Verify data
            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);

            //Create
            try {

                $messageEventRelation = new \MessageEventRelation();
                $messageEventRelation->loadObject ($arrayData);
                $messageEventRelation->setPrjUid ($projectUid);

                if ( $messageEventRelation->validate () )
                {
                    $messageEventRelationUid = $messageEventRelation->save ();
                    //Return
                    return $this->getMessageEventRelation ($messageEventRelationUid);
                }
                else
                {
                    $msg = "";
                    foreach ($messageEventRelation->getValidationFailures () as $message) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $message;
                    }
                    throw new \Exception ("ID_RECORD_CANNOT_BE_CREATED" . $msg != "" ? "\n" . $msg : "");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
