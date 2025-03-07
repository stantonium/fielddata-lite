<?php

    /* Create variables for incoming POST data for 
       _device_uuid, _trip_name, _point_uuid, _latitude, _longitude, _horizontal_accuracy, _altitude, _created_on, _scanned_text, _image_objects.
    */
    $deviceUUID = $_POST["_device_uuid"];
    $tripName = $_POST["_trip_name"];
    $pointUUID = $_POST["_point_uuid"];
    $latitude = $_POST["_latitude"];
    $longitude = $_POST["_longitude"];
    $horizontalAccuracy = $_POST["_horizontal_accuracy"];
    $altitude = $_POST["_altitude"];
    $createdOn = $_POST["_created_on"];
    // $scannedText = $_POST["_scanned_text"];
    $scannedText = "";
    $imageObjects = $_POST["_image_objects"];

    // change [ and ] to { and } in imageObjects
    $imageObjects = str_replace("[", "{", $imageObjects);
    $imageObjects = str_replace("]", "}", $imageObjects);

    // Print the variables to the php log
    error_log("deviceUUID: " . $deviceUUID . ". tripName: " . $tripName . ". pointUUID: " . $pointUUID . 
    ". latitude: " . $latitude . ". longitude: " . $longitude . ". horizontalAccuracy: " . $horizontalAccuracy . 
    ". altitude: " . $altitude . ". createdOn: " . $createdOn . ". scannedText: " . $scannedText . ". imageObjects: " . $imageObjects);

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    $result = pg_query_params($dbConn, "call fern.update_fern_tables($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
        array($deviceUUID, $tripName, $pointUUID, $horizontalAccuracy, $longitude, $latitude, $altitude, $scannedText, $createdOn, $imageObjects));

    // If $result is 1, echo it
    if ($result == 1) {
        echo "1";
    } 

?>