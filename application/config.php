<?php
$ip = getenv("DATABASE_IP");
$port = getenv("DATABASE_PORT");
$user = getenv("DATABASE_USERNAME");
$password = getenv('DATABASE_PASSWORD');
$database_name = getenv("DATABASE_NAME");

$connStr
    = "host=$ip port=$port dbname=$database_name user=$user password=$password options='--application_name=politic-ai-app' connect_timeout=5";
try {
    $link = pg_connect($connStr);

    if ($link) {

    } else {
        exit("Connect failed: $link");
    }
    pg_query($link,'SET search_path TO politicalai_ict;');

}
Catch (Exception $e) {
    Echo $e->getMessage();
}

$url_microservice = "192.168.99.100:5777/"
?>