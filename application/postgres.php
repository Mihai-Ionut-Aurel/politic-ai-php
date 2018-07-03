<?php

include('../config.php');
class PostgresException extends Exception
{
    function __construct($msg)
    {
        parent::__construct($msg);
    }
}

class DependencyException extends PostgresException
{
    function __construct()
    {
        parent::__construct("deadlock");
    }
}

class pg
{

    public static $connection;

    private static function connect()
    {
        $ip = getenv("DATABASE_IP");
        $port = getenv("DATABASE_PORT");
        $user = getenv("DATABASE_USERNAME");
        $password = getenv('DATABASE_PASSWORD');
        $database_name = getenv("DATABASE_NAME");
        $connStr
            = "host=$ip port=$port dbname=$database_name user=$user password=$password options='--application_name=politic-ai-app' connect_timeout=5";
        self::$connection = @pg_connect(
            $connStr
        );

        if (self::$connection === false) {
            throw(new PostgresException("Can't connect to database server."));
        }
        self::query('SET search_path TO politicalai_ict;');
    }

    public static function query($sql)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }

        $result = @pg_query(self::$connection, $sql);
        if ($result === false) {
            $error = pg_last_error(self::$connection);
            if (stripos($error, "deadlock detected") !== false) {
                throw(new DependencyException());
            }

            throw(new PostgresException($error.": ".$sql));
        }

        $out = array();
        while (($d = pg_fetch_assoc($result)) !== false) {
            $out[] = $d;

        }

        return $out;
    }
}

?>