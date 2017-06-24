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
class MessageEventRelation
{
    use Validator;

    private $objMysql;

    public function __construct ()
    {
        $this->objMysql = new Mysql2();
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
            $objMessageEventRelations = new MessageDefinitions();
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
            $validator->throwExceptionIfDataIsEmpty ($arrayData, "\$arrayData");
            //Set data
            $arrayData = array_change_key_case ($arrayData, CASE_UPPER);
            unset ($arrayData["MSGER_UID"]);
            unset ($arrayData["PRJ_UID"]);
            //Verify data
            $process->throwExceptionIfNotExistsProcess ($projectUid, $this->arrayFieldNameForException["projectUid"]);
            $this->throwExceptionIfDataIsInvalid ("", $projectUid, $arrayData);
            //Create
            $cnn = \Propel::getConnection ("workflow");
            try {
                $messageEventRelation = new \MessageEventRelation();
                $messageEventRelationUid = \ProcessMaker\Util\Common::generateUID ();
                $messageEventRelation->fromArray ($arrayData, \BasePeer::TYPE_FIELDNAME);
                $messageEventRelation->setMsgerUid ($messageEventRelationUid);
                $messageEventRelation->setPrjUid ($projectUid);
                if ( $messageEventRelation->validate () )
                {
                    $cnn->begin ();
                    $result = $messageEventRelation->save ();
                    $cnn->commit ();
                    //Return
                    return $this->getMessageEventRelation ($messageEventRelationUid);
                }
                else
                {
                    $msg = "";
                    foreach ($messageEventRelation->getValidationFailures () as $validationFailure) {
                        $msg = $msg . (($msg != "") ? "\n" : "") . $validationFailure->getMessage ();
                    }
                    throw new \Exception (\G::LoadTranslation ("ID_RECORD_CANNOT_BE_CREATED") . (($msg != "") ? "\n" . $msg : ""));
                }
            } catch (\Exception $e) {
                $cnn->rollback ();
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
