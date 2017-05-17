<?php
function getConfig()
{


    $dbConfig = array(
        "task_manager" => array(
                "adapter" => "Mysql",
                "host" => "localhost",
                "username" => "phalcon",
                "password" => "Password123",
                "dbname" => "task_manager"
        ),
        "shared" => array(
            "adapter" => "Mysql",
            "host" => "localhost",
            "username" => "phalcon",
            "password" => "Password123",
            "dbname" => "pcms_shared"
        )
     );
    
    return $dbConfig;
}

