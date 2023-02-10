<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // Prevent SQL injection
    if(isset($_POST['_column_name'])){
        $colName = htmlentities($_POST['_column_name']);
        $orgName = htmlentities($_POST['_org_name']);
    }   
    $colValue = htmlentities($_POST['_column_value']);
    $queryName = htmlentities($_POST['_query_name']);

    $result = '';


    switch ($queryName) {
        case 'query_search_org_name_by_site':
            $result = pg_query_params($dbConn, 
            "SELECT * FROM query_search_org_name_by_site($1, $2, $3)", array($colName, $colValue,
            $orgName));
    break;
        case 'query_get_route_for_app':
            $result = pg_query_params($dbConn, 
            "SELECT * FROM query_get_route_for_app($1)", array($colValue));
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