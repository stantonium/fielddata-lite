<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // Prevent SQL injection
    $siteName = htmlentities($_POST['_site_name']);

    $result = pg_query_params($dbConn, 
    "SELECT * FROM query_site_center_point_and_zoom($1)", array($siteName));

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