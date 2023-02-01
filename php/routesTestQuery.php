<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // test query
    $result = pg_query_params($dbConn, "select id, type from lookup_site_type where id > $1", array(0));

    // variable for result
    $data = array();

    // get rows
    while ($row = pg_fetch_row($result))
    {
        // array_push($data, $row);
        $data[] = $row;
    }

    // send JSON response
    error_log(print_r(json_encode($data), true));
    echo json_encode($data);

?>