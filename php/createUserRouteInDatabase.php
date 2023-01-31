<?php

// WILL NEED TO BE TWEAKED (Original intention was from object selection in CesiumJS):

require_once "login.php";

ini_set("session.cookie_httponly", True); // The following line sets the HttpOnly flag for session cookies - make sure to call it before you call session_start()
session_start();
header("Content-Type: application/json");

$body = file_get_contents('php://input');


$dbConn = logIntoPostgreSQL();
createUserRoute($dbConn, $body);


function createUserRoute($dbConn, $body){

    // error_log($body);

    $obj_array = json_decode($body, true);
    // error_log(print_r($obj_array, true));
    //get pickedObjects
    $pickedObjects = $obj_array[0];

    //user's DB id
    $userDbId = $obj_array[1];

    // user's route name
    $routeName = htmlentities($obj_array[2]);
    $charsToReplace = array("'", "\"", "/", "\\", "$", "&", "*", "#", "@", "%", "!", "^", "~", "`",
        "(", ")", ",", "<", ">", "?", ":", ";", "=", "+", "[", "]", "{", "}", "|");
    $cleanedRouteName = str_replace($charsToReplace,"", $routeName);
    $cleanedRouteName = trim($cleanedRouteName);

    //clear items
    pg_query($dbConn, "DELETE FROM public.temp_selected_bsd_site_ids");

    //insert cesium's selection
    foreach ($pickedObjects as $itm){
//        pg_query($dbConn, "INSERT INTO public.temp_selected_bsd_site_ids (id) VALUES(" . $itm . ")");
        pg_query_params($dbConn, "INSERT INTO public.temp_selected_bsd_site_ids (id) VALUES($1)",
            array($itm));
    }

    // create the user's base route data. pass user's route name and their DB id
    pg_query_params($dbConn, "select query_route_create_user_base_route($1, $2)"
        , array($cleanedRouteName, $userDbId));

    pg_close($dbConn);
}


?>