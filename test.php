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

    define ('PATH_SEP', ($osIsLinux) ? '/' : '\\');

    define ('PATH_HOME', $pathHome);

    define ('PATH_TRUNK', $pathTrunk);

    define ('PATH_OUTTRUNK', $pathOutTrunk);

    define ('PATH_CLASSES', PATH_HOME . PATH_SEP . 'app' . PATH_SEP . 'library' . PATH_SEP);

    define ('SYS_LANG', 'en');

    $e_all = (defined ('E_DEPRECATED')) ? E_ALL & ~E_DEPRECATED : E_ALL;

    $e_all = (defined ('E_STRICT')) ? $e_all & ~E_STRICT : $e_all;

    $e_all = ($arraySystemConfiguration['debug']) ? $e_all : $e_all & ~E_NOTICE;
    
    define("HOME_DIR", "C:/xampp/htdocs/");
    define("DEBUG_LOCATION", HOME_DIR . "core/app/logs/debug.log");
    
    
    require_once 'config.php';
     require_once 'registry.php';
    require_once "Mysql.php";
    require_once 'BusinessModel/Validator.php';
    require_once 'BusinessModel/Cases.php';
     require_once 'Event/BaseTimerEvent.php';
     require_once 'Event/TimerEvents.php';
    require_once 'BusinessModel/TimerEvent.php';

    //Do not change any of these settings directly, use env.ini instead

    ini_set ('display_errors', $arraySystemConfiguration['debug']);

    ini_set ('error_reporting', $e_all);

    ini_set ('short_open_tag', 'On');

    ini_set ('default_charset', 'UTF-8');

    $argvx = '';



    for ($i = 8; $i <= count ($argv) - 1; $i++) {

        /* ----------------------------------********--------------------------------- */

        $argvx = $argvx . (($argvx != '') ? ' ' : '') . $argv[$i];

        /* ----------------------------------********--------------------------------- */
    }
    
      try {

            switch ($cronName) {

                case 'messageeventcron':

                    $messageApplication = new \ProcessMaker\BusinessModel\MessageApplication();



                    $messageApplication->catchMessageEvent(true);

                    break;

                case 'timereventcron':

                    $timerEvent = new TimerEvent();



                    $timerEvent->startContinueCaseByTimerEvent(date('Y-m-d H:i:s'), true);

                    break;

            }

        } catch (Exception $e) {

            echo $e->getMessage() . "\n";



            eprintln('Problem in workspace: ' . $workspace . ' it was omitted.', 'red');

        }


        print_r($argv);

        //eprintln();
    
} catch (Exception $ex) {
    
}

