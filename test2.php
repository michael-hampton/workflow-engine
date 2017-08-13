<?php

define ("HOME_DIR", "C:/xampp/htdocs/");
define ("DEBUG_LOCATION", HOME_DIR . "/core/app/logs/db.log");

require_once 'Persistent.php';
//require_once 'config.php';
//require_once 'registry.php';
require_once "Mysql.php";

require_once 'BusinessModel/Validator.php';
require_once 'BPMN/BaseTask.php';
require_once 'BPMN/Task.php';
require_once 'Calendar/BaseCalendarDefinition.php';
require_once 'Calendar/CalendarDefinition.php';

require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarBusinessHours.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarBusinessHours.php';

require_once HOME_DIR . '/core/app/library/Log.php';
require_once("CalendarFunctions.php");

require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarAssignment.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarAssignment.php';

require_once HOME_DIR . '/core/app/library/BPMN/BaseTask.php';
require_once HOME_DIR . '/core/app/library/BPMN/Task.php';
require_once HOME_DIR . '/core/app/library/Process/BaseTaskUser.php';
require_once HOME_DIR . '/core/app/library/Process/TaskUser.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Task.php';

define ("LOG_FILE", "C:/xampp/htdocs/core/app/logs/easyflow.log");

$objMysql = new Mysql2();
$results = $objMysql->_select ("workflow.workflow_data", [], []);

foreach ($results as $result) {
    $auditData = json_decode ($result['audit_data'], true);
    $workflowData = json_decode ($result['workflow_data'], true);

    foreach ($workflowData['elements'] as $elementId => $element) {

        $results2 = $objMysql->_select ("workflow.status_mapping", [], ["id" => $element['current_step']]);
        $conditions = json_decode ($results2[0]['step_condition'], true);

        if ( isset ($conditions['claimStep']) && strtolower ($conditions['claimStep']) === "yes" )
        {
            $appAudit = $auditData['elements'][$elementId];
            $step = end ($appAudit['steps']);

            $calendar = new CalendarFunctions();

            if ( $calendar->pmCalendarUid == '' )
            {
                $appcacheProUid = $element['workflow_id'];
                $taskUid = $results2[0]['TAS_UID'];
                $flag = false;

                $calendar->getCalendar (null, $appcacheProUid, $taskUid);

                $calendar->getCalendarData ();

                $date = $step['dateCompleted'];

                $appcacheDelDelegateDate = date ('Y-m-d H:i:s', strtotime ($date . ' -3 days'));

                $objTask = (new Task())->load ($taskUid);
                $taskSelfServiceTime = $objTask->getTasSelfserviceTime ();
                $taskSelfServiceTimeUnit = $objTask->getTasSelfserviceTimeUnit ();

                $dueDate = $calendar->calculateDate (
                        $appcacheDelDelegateDate, $taskSelfServiceTime, $taskSelfServiceTimeUnit //HOURS|DAYS|MINUTES
                        //1
                );
                
                $appcacheAppNumber = $elementId;
                $projectId = $result['object_id'];

                if ( time () > $dueDate["DUE_DATE_SECONDS"] && $flag == false )
                {
                    //Execute Trigger
                    ECHO "EXECUTE TRIGGER NOW";

                    saveLog ("unassignedCase", "action", "OK Executed tigger to the case $appcacheAppNumber");
                }
                else
                {
                    die ("2");
                }
            }
        }
    }
}

function saveLog ($sSource, $sType, $sDescription)
{

    try {

        global $sObject;

        global $isDebug;

        if ( $isDebug )
        {

            print date ("H:i:s") . " ($sSource) $sType $sDescription <br />\n";
        }



        (new \Log (LOG_FILE))->log (
                array(
            "message" => $sDescription,
            'source_id' => $sSource,
            'type' => $sType
                ), \Log::NOTICE);
    } catch (Exception $e) {

        //CONTINUE
    }
}

function setExecutionMessage ($m)
{

    $len = strlen ($m);

    $linesize = 60;

    $rOffset = $linesize - $len;



    eprint ("* $m");



    for ($i = 0; $i < $rOffset; $i++) {

        eprint ('.');
    }
}
