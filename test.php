case 'SELF_SERVICE':
                $to = '';
                $cc = '';
		$arrWhere = [];

                //Query
               $sql "SELECT usrid, username, firstName, lastName, email_address FROM user_management.poms_users WHERE status != 0";

                if (trim($task->getTasGroupVariable()) != '') {
                    //SELF_SERVICE_VALUE
                    $variable = trim($task->getTasGroupVariable(), ' @#%?$=');

                    //Query
                    if (isset($arrayData[$variable])) {
                        $data = $arrayData[$variable];

                        switch (gettype($data)) {
                            case 'string':
				$sql .= " AND team_id = ?";
				$arrWhere[] = $data;

                                $results = $this->objMysql->_query($sql, $arrWhere);

                                break;
                            case 'array':
				$sql .= " AND usrid IN( ".implode(",", $data)." )";
				$results = $this->objMysql->_query($sql, $arrWhere);
                                break;
                        }
                    }
                } else {
                    //SELF_SERVICE
                    $arrayTaskUser = [];

                    $arrayAux1 = $tasks->getGroupsOfTask($taskUid, 1);

                    foreach ($arrayAux1 as $arrayGroup) {
                        $arrayAux2 = $group->getUsersOfGroup($arrayGroup['GRP_UID']);

                        foreach ($arrayAux2 as $arrayUser) {
                            $arrayTaskUser [] = $arrayUser ['USR_UID'];
                        }
                    }

                    $arrayAux1 = $tasks->getUsersOfTask($taskUid, 1);

                    foreach ($arrayAux1 as $arrayUser) {
                        $arrayTaskUser[] = $arrayUser['USR_UID'];
                    }


                    //Query
		    $sql .= " AND usrid IN( ".implode(",", $arrayTaskUser)." )";

                    $results = $this->objMysql->_query($sql, $arrWhere);
                }

                if (isset($results[0]) && !empty($results[0])) {

                    foreach($results as $record) {

                        $toAux = (($record['firstName'] != '' || $record['lastName'] != '')? $record['firstName'] . ' ' . $record['lastName'] . ' ' : '') . '<' . $record['email_address'] . '>';

                        if ($to == '') {
                            $to = $toAux;
                        } else {
                            $cc .= (($cc != '')? ',' : '') . $toAux;
                        }
                    }
                }

                $arrayResp['to'] = $to;
                $arrayResp['cc'] = $cc;



		if(trim($this->template) !== "") {

			$pathEmail = PATH_DATA_SITE . "mailTemplates" . PATH_SEP . $aTaskInfo["PRO_UID"] . PATH_SEP;
			$fileTemplate = $pathEmail . $this->template;
                	$sBody = file_get_contents($fileTemplate);
		}
