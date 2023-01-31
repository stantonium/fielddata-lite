<?php

// MAY NEED TO ADJUST DEPENDING ON FINAL TABLE FORM AND iOS QUERY RESULT

require_once "login.php";

ini_set("session.cookie_httponly", True); // The following line sets the HttpOnly flag for session cookies - make sure to call it before you call session_start()
session_start();
header("Content-Type: application/json");

//$body = http_get_request_body('php://input');  //another method to get header body contents
$body = file_get_contents('php://input');

$body = htmlentities($body);

// error_log($body);

$dbConn = logIntoPostgreSQL();
$LongLatArray = getLongLatPoint($dbConn, $body);
echo json_encode($LongLatArray);

function getLongLatPoint($dbConn, $body){
    $LongLatArray = array();

    $result = pg_query($dbConn, "select * from routes_android;");

    // error_log('Query result:');
    // error_log(print_r($result, true));

    while ($row = pg_fetch_row($result)) {
    //    array_push($LongLatArray, $row[0]);
        array_push($LongLatArray, $row[1]);
        array_push($LongLatArray, $row[2]);
        array_push($LongLatArray, $row[3]);
    }

    pg_close($dbConn);
    return $LongLatArray;
}

?>

