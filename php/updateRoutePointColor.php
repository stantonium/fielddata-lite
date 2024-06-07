<?php

    $routeID = $pointOrder = "";

    $routeID = htmlentities($_POST["_route_id"]);
    $pointOrder = htmlentities($_POST["_point_order"]);

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    $result = pg_query_params($dbConn, "call public.update_route_point_color($1, $2)",
        array($routeID, $pointOrder));

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