<?php

ini_set ('memory_limit', '512M');
$arraySystemConfiguration = array("debug" => 1, "DEBUG_SQL_LOG" => $_SERVER['DOCUMENT_ROOT'] . "/core/app/logs/debug.log");


try {

    if ( count ($argv) < 8 )
    {

        throw new Exception ('Error: Invalid number of arguments');
    }

    //Set variables

    $osIsLinux = strtoupper (substr (PHP_OS, 0, 3)) != 'WIN';

    $pathHome = $argv[1];

    $pathTrunk = $argv[2];

    $pathOutTrunk = $argv[3];

    $cronName = $argv[4];

    $workspace = $argv[5];

    $dateSystem = $argv[6];

    $sNow = $argv[7]; //$date

    /*     * ********************* Set constants ************************* */

    define ('PATH_SEP', ($osIsLinux) ? '/' : '\\');

    define ('PATH_HOME', $pathHome);

    define ('PATH_TRUNK', $pathTrunk);

    define ('PATH_OUTTRUNK', $pathOutTrunk);

    define ('PATH_CLASSES', PATH_HOME . PATH_SEP . 'app' . PATH_SEP . 'library' . PATH_SEP);

    define ('SYS_LANG', 'en');

    $e_all = (defined ('E_DEPRECATED')) ? E_ALL & ~E_DEPRECATED : E_ALL;

    $e_all = (defined ('E_STRICT')) ? $e_all & ~E_STRICT : $e_all;

    $e_all = ($arraySystemConfiguration['debug']) ? $e_all : $e_all & ~E_NOTICE;


    /*     * *************** Load Classes **************** */
    define ("HOME_DIR", PATH_HOME);
    define ("DEBUG_LOCATION", HOME_DIR . "/core/app/logs/debug.log");

    require_once 'Persistent.php';
    require_once 'config.php';
    require_once 'registry.php';
    require_once "Mysql.php";
     require_once 'BPMN/Flow.php';
    require_once 'BPMN/Task.php';

    require_once 'BusinessModel/Validator.php';
    require_once 'UserClasses/BaseUser.php';
    require_once 'UserClasses/Users.php';
    require_once 'Process/BaseProcess.php';
    require_once 'BusinessModel/Process.php';
    require_once 'WorkflowClasses/Save.php';
    require_once 'BusinessModel/MessageEventRelation.php';
    require_once 'BusinessModel/MessageEventDefinition.php';
    require_once 'BusinessModel/MessageApplication.php';
    require_once 'BusinessModel/Event.php';
    require_once 'WorkflowClasses/Elements.php';
    require_once 'BusinessModel/StepGateway.php';
    require_once 'WorkflowClasses/WorkflowStep.php';
    require_once 'BusinessModel/FieldFactory.php';
    require_once 'BaseVariable.php';
    require_once 'Variable.php';
    require_once 'BusinessModel/StepVariable.php';
    require_once 'BusinessModel/StepPermissions.php';
    require_once 'BusinessModel/ProcessSupervisor.php';
    require_once 'BusinessModel/StepTrigger.php';
    require_once 'Notifications/BaseNotifications.php';
    require_once 'Notifications/Notifications.php';
    require_once 'Notifications/SendNotification.php';
    require_once 'Fields/FieldValidator.php';
    require_once 'WorkflowClasses/Workflow.php';
    require_once 'BusinessModel/UsersFactory.php';

    require_once 'BusinessModel/Cases.php';
    require_once 'Event/BaseTimerEvent.php';
    require_once 'Event/TimerEvent.php';
    require_once 'BusinessModel/TimerEvent.php';

    if ( session_id () === "" )
    {
        session_start ();
    }

    $_SESSION['user']['usrid'] = 2;

    // set error reporting
    //Do not change any of these settings directly, use env.ini instead

    ini_set ('display_errors', $arraySystemConfiguration['debug']);

    ini_set ('error_reporting', $e_all);

    ini_set ('short_open_tag', 'On');

    ini_set ('default_charset', 'UTF-8');

    $argvx = '';


    // build arguments
    for ($i = 8; $i <= count ($argv) - 1; $i++) {

        /* ----------------------------------********--------------------------------- */

        $argvx = $argvx . (($argvx != '') ? ' ' : '') . $argv[$i];

        /* ----------------------------------********--------------------------------- */
    }

    // run cron
    try {

        switch ($cronName) {

            case 'messageeventcron':

                $messageApplication = new \BusinessModel\MessageApplication();



                $messageApplication->catchMessageEvent (true);

                break;

            //php .\test.php C:\xampp\htdocs mike mike timereventcron mike mike mike
            case 'timereventcron':

                $timerEvent = new \BusinessModel\TimerEvent();

                $timerEvent->startContinueCaseByTimerEvent (date ('Y-m-d H:i:s'), true);

                break;
        }
    } catch (Exception $e) {

        echo $e->getMessage () . "\n";



        eprintln ('Problem in workspace: ' . $workspace . ' it was omitted.', 'red');
    }


    //eprintln();
} catch (Exception $ex) {
    
}

