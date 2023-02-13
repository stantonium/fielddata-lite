<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();
    
    $queryName = '';

    // Prevent SQL injection
    if(isset($_POST['_query_name'])){
        $queryName = htmlentities($_POST['_query_name']);
    }

    $result = '';

    switch ($queryName) {
        case 'area_view':
            $result = pg_query($dbConn, 
            "select name from lookup_borders lb
            order by name");
        break;
        case 'plot_view':
            $result = pg_query($dbConn, 
            "select name from lookup_borders_plot lbp
            order by name");
        break;
        case 'report_view':
            $result = pg_query($dbConn, 
            "select report as name from reports
            order by report"); // Select as name so iOS can use the same model
        break;
    }

    // test query
    $result = pg_query($dbConn, 
    "select name from lookup_borders lb
    order by name");

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