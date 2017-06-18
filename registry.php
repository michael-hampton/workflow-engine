<?php

use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlConnection;
use Phalcon\DI\FactoryDefault;

class Registry
{

    private $some;
    private $config;
    private $adapter;
    public $dbConfig;
    public $connection;
    public $di;

    public function __construct ($adapter)
    {
        require_once HOME_DIR . '/core/app/library/config.php';
        $dbConfig = getConfig ();
        $this->dbConfig = $dbConfig;

        $this->adapter = $adapter;
        $this->di = new Phalcon\DI();
    }

    /**
     * Creates a connection only once and returns it
     */
    public function getSharedConnection ()
    {
        if ( $this->connection == NULL )
        {
            $this->connection = $this->_createConnection ();
        }

        return $this->di;
    }

    /**
     * Always returns a new connection
     */
    public function getNewConnection ()
    {
        $this->connection = $this->_createConnection ();
        return $this->di;
    }

    /**
     * Returns the connection
     */
    protected function _createConnection ()
    {
        try {
            // Register a "db" service in the container
            $this->di->set ('db', function () {
                $connection = new \Phalcon\Db\Adapter\Pdo\Mysql (array(
                    "host" => $this->dbConfig[$this->adapter]['host'],
                    "username" => $this->dbConfig[$this->adapter]['username'],
                    "password" => $this->dbConfig[$this->adapter]['password'],
                    "dbname" => $this->dbConfig[$this->adapter]['dbname'],
                    "options" => array(
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
                    )
                ));
                
                return $connection;
            }, true);
        } catch (Exception $ex) {
            die("CATCH");
        }
    }

}
