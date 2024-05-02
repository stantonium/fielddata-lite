<?php

    # File needs to be renamed to insertSinglePointForTrip.php

    $tripName = $picUUID = $gps = $hdop = $longtitude = $latitude = "";
    $altitude = $scannedText = $timeStamp = $notes = "";

    $tripName = htmlentities($_POST["_name"]);
    $picUUID = htmlentities($_POST["_pic_uuid"]);
    $gps = htmlentities($_POST["_gps"]);
    $hdop = htmlentities($_POST["_hdop"]);
    $longtitude = htmlentities($_POST["_long"]);
    $latitude = htmlentities($_POST["_lat"]);
    $altitude = htmlentities($_POST["_alt"]);
    $scannedText = htmlentities($_POST["_scanned_text"]);
    $timeStamp = htmlentities($_POST["_time"]);
    $notes = htmlentities($_POST["_notes"]);

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    $result = pg_query_params($dbConn, 
    "call fern.insert_row_into_trip($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
    array($tripName, $picUUID, $gps, $hdop, $longtitude, $latitude, $altitude, $scannedText, $timeStamp, $notes));

    // variable for result
    $data = array();

    // get rows. Do not use pg_fetch_row(). Do not include numerical indices.
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC))
    {
        array_push($data, $row);
    }

    // send JSON response
    echo json_encode($data);

?>