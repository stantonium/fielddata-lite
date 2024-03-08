<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // Prevent SQL injection
    $startLong = htmlentities($_POST['_start_long']);
    $startLat = htmlentities($_POST['_start_lat']);
    $endLong = htmlentities($_POST['_end_long']);
    $endLat = htmlentities($_POST['_end_lat']);

    $result = '';

    $result = pg_query_params($dbConn, 
    "SELECT distance, bearing from public.query_route_get_dist_and_bear($1, $2, $3, $4)", array($startLong, $startLat, $endLong, $endLat));

    // variable for fetch
    $data = array();

    // get rows. Do not use pg_fetch_row(). Do not include numerical indices.
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC))
    {
        array_push($data, $row);
    }

    // send JSON response
    echo json_encode($data);

?>