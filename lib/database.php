<?php

class Database
{
    //private $dbName = 'bbgest';
    private static $dbHost = 'localhost';
    private static $dbUsername = 'root';
    private static $dbUserPassword = '4321Mnbv';

    private static $bbgestUsername = 'root';//bbgest
    private static $bbgestUserPassword = '4321Mnbv';//7y6t5r4e

    private static $cont = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    public static function connect($db = 'presu14')
    {
        // One connection through whole application
        if (null == self::$cont) {
            try {
                if($db == 'bbgest') {
                    $user = self::$bbgestUsername;
                    $pass = self::$bbgestUserPassword;
                }
                else {
                    $user = self::$dbUsername;
                    $pass = self::$dbUserPassword;
                }

                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES latin1',
                );

                self::$cont = new PDO("mysql:host=" . self::$dbHost . ";" . "dbname=" . $db . ";charset=utf8", $user, $pass, $options);

            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
}