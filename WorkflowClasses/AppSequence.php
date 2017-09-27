<?php
/**
 * Skeleton subclass for representing a row from the 'APP_SEQUENCE' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class AppSequence extends BaseAppSequence {
    /**
     * Get an Set new sequence number
     *
     * @return mixed
     * @throws Exception
     */
    public function sequenceNumber()
    {
        try {
            //UPDATE SEQUENCES SET SEQ_VALUE = LAST_INSERT_ID(SEQ_VALUE + 1);
            $sql = "UPDATE workflow.APP_SEQUENCE SET ID=LAST_INSERT_ID(ID+1)";
            $result = $this->objMysql->_query($sql);
            
            if(!$result) {
                return false;
            }
            
            //SELECT LAST_INSERT_ID()
            $sql = "SELECT LAST_INSERT_ID()";
            $row = $this->objMysql->_query($sql);
            
            if(!isset($row[0]) || empty($row[0])) {
                return false;
            }
            
            $result = $row[0]['LAST_INSERT_ID()'];
        } catch (\Exception $e) {
            throw ($e);
        }
        return $result;
    }
    
    /**
     * Update sequence number
     *
     * @return mixed
     * @throws Exception
     */
    public function updateSequenceNumber($number)
    {
        try {
   
           $sql = "SELECT (MAX(ID) + 1) FROM workflow.APP_SEQUENCE WHERE id = ?";
           $result = $this->objMysql->_query($sql, [$number]):
           
           if(isset($result[0]) && !empty($result[0])) {
                $sql = "UPDATE APP_SEQUENCE SET ID=LAST_INSERT_ID('$number')";
            } else {
                $sql = "INSERT INTO APP_SEQUENCE (ID) VALUES ('$number');";
            }
            $this->objMysql->_query($sql);
        } catch (\Exception $e) {
            throw ($e);
        }
    }
}
