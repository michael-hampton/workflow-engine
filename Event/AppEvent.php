<?php
/**
 * AppEvent.php
 *
 * @package workflow.engine.classes.model
 */
//require_once 'classes/model/om/BaseAppEvent.php';
/**
 * Skeleton subclass for representing a row from the 'APP_EVENT' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package workflow.engine.classes.model
 */
class AppEvent extends BaseAppEvent
{
    public function load ($sApplicationUID, $iDelegation)
    {
        try {
            $oAppEvent = AppEventPeer::retrieveByPK( $sApplicationUID, $iDelegation );
            if (! is_null( $oAppEvent )) {
                $aFields = $oAppEvent->toArray( BasePeer::TYPE_FIELDNAME );
                $this->fromArray( $aFields, BasePeer::TYPE_FIELDNAME );
                return $aFields;
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }
    public function create ($aData)
    {
        try {
            $oAppEvent = new AppEvent();
            $oAppEvent->loadObject( $aData );
            
            if ($oAppEvent->validate()) {
         
                $oAppEvent->save();
                
                return true;
            } else {
                $sMessage = '';
                $aValidationFailures = $oAppEvent->getValidationFailures();
                
                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure . '<br />';
                }
                throw (new Exception( 'The registry cannot be created!<br />' . $sMessage ));
            }
        } catch (Exception $oError) {
            
            throw ($oError);
        }
    }
    public function update ($aData)
    {
        try {
            $oAppEvent = $this->retrieveByPK( $aData['APP_UID'], $aData['DEL_INDEX'] );
            
            if (! is_null( $oAppEvent )) {
                $oAppEvent->loadArray( $aData );
                
                if ($oAppEvent->validate()) {
                    
                    $iResult = $oAppEvent->save();
                    
                    return $iResult;
                } else {
                    $sMessage = '';
                    $aValidationFailures = $oAppEvent->getValidationFailures();
                    
                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure . '<br />';
                    }
                    throw (new Exception( 'The registry cannot be updated!<br />' . $sMessage ));
                }
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            
            throw ($oError);
        }
    }
    public function remove ($sApplicationUID, $iDelegation, $sEvnUid)
    {
        
        try {
            $oAppEvent = $this->retrieveByPK( $sApplicationUID, $iDelegation, $sEvnUid );
            
            if (! is_null( $oAppEvent )) {
                
                $iResult = $oAppEvent->delete();
                
                return $iResult;
            } else {
                throw (new Exception( 'This row doesn\'t exist!' ));
            }
        } catch (Exception $oError) {
            
            throw ($oError);
        }
    }
}
