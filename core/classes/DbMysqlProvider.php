<?php
require_once ('SafeMySQL.php');

class DbMysqlProvider
{
    protected static $connection;
    public static function getConnection()
    {
        if(self::$connection === null) {
            self::$connection = new SafeMySQL(get_env()["database"]);
            if(!self::$connection) {
                exit('Ошибка MySQL: connection failed');
            }
        }
        return self::$connection;
    }
}
