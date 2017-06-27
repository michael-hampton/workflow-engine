//case 'webEntry_generate':
//            include (PATH_METHODS . 'processes/processes_webEntryGenerate.php');
//            break;
//        // add this event to validate de data to create a Web Entry
//        case 'webEntry_validate':
//            include (PATH_METHODS . 'processes/processes_webEntryValidate.php');
//            break;
//        case 'webEntry_delete':
//            G::LoadSystem('inputfilter');
//            $filter = new InputFilter();
//            $form = $_REQUEST;
//            if(file_exists(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $form['PRO_UID'] . PATH_SEP . $form['FILENAME'])) {
//                unlink($filter->validateInput(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" .
//                    PATH_SEP . $form['PRO_UID'] . PATH_SEP . $form['FILENAME'], 'path'));
//            }
//            if(file_exists(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $form['PRO_UID'] . PATH_SEP . str_replace(".php", "Post", $form['FILENAME']) . ".php")) {
//                unlink($filter->validateInput(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" .
//                    PATH_SEP . $form['PRO_UID'] . PATH_SEP . str_replace(".php", "Post", $form['FILENAME']) . ".php",
//                'path'));
//            }
//            $oProcessMap->webEntry($_REQUEST['PRO_UID']);
//            G::auditLog('WebEntry','Delete web entry ('.$form['FILENAME'].') in process "'.$resultProcess['PRO_TITLE'].'"');
//            break;
//        case 'webEntry_new':
//            $oProcessMap->webEntry_new($oData->PRO_UID);
//            break;