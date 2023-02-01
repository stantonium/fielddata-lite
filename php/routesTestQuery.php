<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // test query
    $result = pg_query_params($dbConn, "select type from lookup_site_type where id > $1", array(0));

    // variable for result
    $data = array();

    // get rows
    while ($row = pg_fetch_row($result))
    {
        array_push($data, $row);
    }

    // send JSON response
    echo json_encode($data);

?>