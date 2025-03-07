<?php

    /* Create variables for incoming POST data for 
       _device_uuid, _trip_name, _point_uuid, _latitude, _longitude, _horizontal_accuracy, _altitude, _created_on, _scanned_text. 
        Apply htmlentities() to each POST variable.
    */
    $deviceUUID = htmlentities($_POST["_device_uuid"]);
    $tripName = htmlentities($_POST["_trip_name"]);
    $pointUUID = htmlentities($_POST["_point_uuid"]);
    $latitude = htmlentities($_POST["_latitude"]);
    $longitude = htmlentities($_POST["_longitude"]);
    $horizontalAccuracy = htmlentities($_POST["_horizontal_accuracy"]);
    $altitude = htmlentities($_POST["_altitude"]);
    $createdOn = htmlentities($_POST["_created_on"]);
    $scannedText = htmlentities($_POST["_scanned_text"]);

    // Print the variables to the php log
    // error_log("deviceUUID: " . $deviceUUID . ". tripName: " . $tripName . ". pointUUID: " . $pointUUID . 
    // ". latitude: " . $latitude . ". longitude: " . $longitude . ". horizontalAccuracy: " . $horizontalAccuracy . 
    // ". altitude: " . $altitude . ". createdOn: " . $createdOn);

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    $result = pg_query_params($dbConn, "call fern.update_fern_tables($1, $2, $3, $4, $5, $6, $7, $8, $9)",
        array($deviceUUID, $tripName, $pointUUID, $horizontalAccuracy, $longitude, $latitude, $altitude, $scannedText, $createdOn));

    // If $result is 1, echo it
    if ($result == 1) {
        echo "1";
    } 


?>