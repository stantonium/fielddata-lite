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
        case 'area_name': // Use the same iOS variable used for searching organism name by site
            $result = pg_query($dbConn, 
            "select name from lookup_borders lb
            order by name");
        break;
        case 'plot_name':
            $result = pg_query($dbConn, 
            "select name from lookup_borders_plot lbp
            order by name");
        break;
        case 'report_view':
            $result = pg_query($dbConn, 
            "select report as name from reports
            order by report"); // Select as name so iOS can use the same model as the area and plot
        break;
        case 'report_route_total_distance':
            $result = pg_query($dbConn, 
            "select *
             from query_rpt_route_total_distance()"); // set to * for possible measurement unit additions
        break;
        case 'trips_in_db_view':
            $result = pg_query($dbConn, 
            "select name from fern.lookup_trip
            where id in (select lookup_trip_id from fern.trip
            group by lookup_trip_id)
            and is_active = 'Y'
            order by name");
        break;
    }

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