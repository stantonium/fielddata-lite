<?php

// APPEARS TO HAVE BEEN FOR GETTING A USER-SAVED ROUTE

require_once "login.php";

ini_set("session.cookie_httponly", True); // The following line sets the HttpOnly flag for session cookies - make sure to call it before you call session_start()
session_start();
//header("Content-Type: application/json");

//$body = http_get_request_body('php://input');  //another method to get header body contents
$body = file_get_contents('php://input');

$body = htmlentities($body);

// error_log($body);

$dbConn = logIntoPostgreSQL();
$RoutesArray = getRouteNames($dbConn, $body);
//echo json_encode($LongLatArray);
$routeNamesJson = json_encode($RoutesArray);
echo $routeNamesJson;


function getRouteNames($dbConn, $body){
//    $LongLatArray = array();
    $UserId = "";

    $result = pg_query($dbConn, "select user_id from lookup_users where google_id = $body;"); // replace with google id variable (get from android http request)
//    echo $result;

    // error_log('Query result:');
    // error_log(print_r($result, true));
/*
    while ($row = pg_fetch_row($result)) {
        array_push($LongLatArray, $row[0]);
        array_push($LongLatArray, $row[1]);
    }
*/
    while ($row = pg_fetch_row($result)) {
    //    error_log(print_r($row, true));
    //    error_log(print_r(pg_fetch_result($result, $row, 0), true));
    //    error_log(print_r(pg_fetch_result($result, $row, 1), true));
        // build array
//        array_push($LongLatArray, $row);
        $UserId = $row[0];
    }

    $routeNames = array();
    $result = pg_query($dbConn, "select * from lookup_routes where user_id = $UserId;");
    while ($row = pg_fetch_row($result)) {
        array_push($routeNames, $row[1]);
    }

    pg_close($dbConn);
    return $routeNames;
}

?>

