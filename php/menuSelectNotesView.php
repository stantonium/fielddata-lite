<?php

    // get login file
    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // Prevent SQL injection
    if(isset($_POST['_id'])){
        $id = htmlentities($_POST['_id']);
    }
    if(isset($_POST['_note'])){
        $note = htmlentities($_POST['_note']);
    }
    $queryName = htmlentities($_POST['_query_name']);

    $result = '';

    switch ($queryName) {
        case 'select_note':
            $result = pg_query($dbConn, 
            "select id, note from notes
            order by id");
        break;
        case 'add_note':
            $result = pg_query_params($dbConn, 
            "insert into notes (note)
            values ($1)", array($note));
        break;
        case 'update_note':
            $result = pg_query_params($dbConn, 
            "update notes
            set note = $1
            where id = $2::int", array($note, $id));
        break;
        case 'delete_note':
            $result = pg_query_params($dbConn, 
            "delete from notes
                where id = $1::int", array($id));
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