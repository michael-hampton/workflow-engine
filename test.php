try {
	//Validations
       	if (!isset($_REQUEST['APP_UID'])) {
        	$_REQUEST['APP_UID'] = '';
        }

       if (!isset($_REQUEST['DEL_INDEX'])) {
       		$_REQUEST['DEL_INDEX'] = '';
       }

      if ($_REQUEST['APP_UID'] == '') {
      	throw new Exception('The parameter APP_UID is empty.');
     }

     if ($_REQUEST['DEL_INDEX'] == '') {
     	throw new Exception('The parameter DEL_INDEX is empty.');
    }

   	$_REQUEST['APP_UID'] = urldecode(utf8_encode($_REQUEST['APP_UID']));
        $_REQUEST['DEL_INDEX'] = urldecode(utf8_encode($_REQUEST['DEL_INDEX']));
       	$_REQUEST['FIELD'] = urldecode(utf8_encode($_REQUEST['FIELD']));
        $_REQUEST['VALUE'] = urldecode(utf8_encode($_REQUEST['VALUE']));
        $_REQUEST['ABER'] = urldecode(utf8_encode($_REQUEST['ABER']));

       	$case = new Cases();
	$caseFieldsABE = $case->loadCase($_REQUEST['APP_UID'], $_REQUEST['DEL_INDEX']);

       if (is_null($caseFieldsABE['DEL_FINISH_DATE'])) {
       		$dataField = [];
                $dataField[$_REQUEST['FIELD']] = $_REQUEST['VALUE'];
               	$caseFieldsABE ['APP_DATA'] = array_merge($caseFieldsABE ['APP_DATA'], $dataField);

               	$dataResponses = [];
                $dataResponses['ABE_REQ_UID'] = $_REQUEST['ABER'];
                $dataResponses['ABE_RES_CLIENT_IP'] = $_SERVER['REMOTE_ADDR'];
               	$dataResponses['ABE_RES_DATA'] = serialize($_REQUEST['VALUE']);
                $dataResponses['ABE_RES_STATUS'] = 'PENDING';
                $dataResponses['ABE_RES_MESSAGE'] = '';

               try {
			$abeAbeResponsesInstance = new AbeResponses();
                        $dataResponses['ABE_RES_UID'] = $abeAbeResponsesInstance->createOrUpdate($dataResponses);
               } catch (Exception $e) {
               		throw $e;
               }

              	$case->updateCase($_REQUEST['APP_UID'], $caseFieldsABE);

                $result = $ws->derivateCase(
                	$caseFieldsABE['CURRENT_USER_UID'], $_REQUEST['APP_UID'], $_REQUEST['DEL_INDEX'], true
                );

                $code = (is_array($result))? $result['status_code'] : $result->status_code;

               if ($code != 0) {
              		throw new Exception(
                        	'An error occurred while the application was being processed.<br /><br />
                                Error code: ' . $result->status_code . '<br />
                                Error message: ' . $result->message . '<br /><br />'
                            );
                        }

                        //Update
                        $dataResponses['ABE_RES_STATUS'] = ($code == 0)? 'SENT' : 'ERROR';
                        $dataResponses['ABE_RES_MESSAGE'] = ($code == 0)? '-' : $result->message;

                        try {
                            $abeAbeResponsesInstance = new AbeResponses();
                            $abeAbeResponsesInstance->createOrUpdate($dataResponses);
                        } catch (Exception $e) {
                            throw $e;
                        }

                        $message = '<strong>The answer has been submited. Thank you</strong>';

                        $dataAbeRequests = loadAbeRequest($_REQUEST['ABER']);
                        $dataAbeConfiguration = loadAbeConfiguration($dataAbeRequests['ABE_UID']);

                        if ($dataAbeConfiguration['ABE_CASE_NOTE_IN_RESPONSE'] == 1) {
                            $response = new stdClass();
                            $response->usrUid = $caseFieldsABE['APP_DATA']['USER_LOGGED'];
                            $response->appUid = $_REQUEST['APP_UID'];
                            $response->noteText = 'Check the information that was sent for the receiver: ' .
                                $dataAbeRequests['ABE_REQ_SENT_TO'];

                            postNote($response);
                        }

                        $dataAbeRequests['ABE_REQ_ANSWERED'] = 1;
                        $code == 0 ? uploadAbeRequest($dataAbeRequests) : '';
                    } else {
                        $message = '<strong>The response has already been sent.</strong>';
                    }

                   echo $message;
                } catch (Exception $e) {
                   echo $e->getMessage() . 'Please contact to your system administrator.';
                }



function postNote($httpData)
{
    //extract(getExtJSParams());
    $appUid = (isset($httpData->appUid))? $httpData->appUid : '';

    $usrUid = (isset($httpData->usrUid))? $httpData->usrUid : '' ;

    require_once ( "classes/model/AppNotes.php" );

    $appNotes = new AppNotes();
    $noteContent = addslashes($httpData->noteText);

    $result = $appNotes->postNewNote($appUid, $usrUid, $noteContent, false);
    //return true;

    //die();
    //send the response to client
    @ini_set('implicit_flush', 1);
    ob_start();
    //echo G::json_encode($result);
    @ob_flush();
    @flush();
    @ob_end_flush();
    ob_implicit_flush(1);
    //return true;
    //send notification in background
    $noteRecipientsList = array();
    G::LoadClass('case');
    $oCase = new Cases();

    $p = $oCase->getUsersParticipatedInCase($appUid);

    foreach ($p['array'] as $key => $userParticipated) {
        $noteRecipientsList[] = $key;
    }

    $noteRecipients = implode(",", $noteRecipientsList);

    $appNotes->sendNoteNotification($appUid, $usrUid, $noteContent, $noteRecipients);

}
