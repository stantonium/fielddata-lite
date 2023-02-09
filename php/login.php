<?php

	// get functions for ENV vars
    require_once "dotEnvLoader.php";

    //(new DotEnv(__DIR__ . '/../../../fielddata.env'))->load();
    (new DotEnv(__DIR__ . '/opt/fielddata-lite/fielddata.env'))->load();

 
	// get vars
    $routesUN = getenv('ROUTES_UN');
    $routesPW = getenv('ROUTES_PW');
    $pgDNS = getenv('PG_DNS');
    $pgDNS = getenv('PG_DNS');

	// set connection info
    $connTimeout = "connect_timeout=5";

    $dnsConnTimeout = $pgDNS . " " . $connTimeout;

    $routes_connection_string = $dnsConnTimeout . " user=" . $routesUN . " password=" . $routesPW;

	// log into DB
    function logIntoPostgreSQLroutes(){
        global $routes_connection_string;
        $dbConn = pg_connect($routes_connection_string);
        return checkConnection($dbConn);
    }
	
	// validate connection
	function checkConnection($dbConn){
        if ($dbConn) {
            error_log('Connection attempt succeeded.');
            return $dbConn;
        } else {
            error_log('Connection attempt failed. Exiting PHP file.');
            exit();
        }
    }
?>
