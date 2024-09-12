<?php

    require_once "login.php";

    // log into DB
    $dbConn = logIntoPostgreSQLroutes();

    // if $_FILES["fileToUpload"]["tmp_name"] is not empty
    if(isset($_FILES["fileToUpload"]["tmp_name"])) {
        // get the body of the POST request. enctype is multipart/form-data. input type is file. the file should be a CSV file.
        // $body = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

        // if the file is not a CSV file, show javascript alert and PHP script will exit
        $fileType = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION);
        if ($fileType != "csv") {
            echo "<script type='text/javascript'>alert('File is not a CSV file!');</script>";
            exit();
        }
    } else {
        echo "<script type='text/javascript'>alert('No file found!');</script>";
        exit();
    }

    // if POST tripname is not empty
    if (!empty($_POST["tripname"])) {
        // get the body of the POST request. enctype is multipart/form-data. input type is text. the text should be the tripname.
        $tripname = htmlentities($_POST["tripname"]);
        // if trimmed tripname has no length, show javascript alert and PHP script will exit
        if (trim($tripname) == "") {
            echo "<script type='text/javascript'>alert('No tripname found!');</script>";
            exit();
        }
    } else {
        echo "<script type='text/javascript'>alert('No tripname found!');</script>";
        exit();
    }

    // Split the CSV file into an array of arrays
    $csv = array_map('str_getcsv', file($_FILES["fileToUpload"]["tmp_name"]));

    // truncate postgres temp_route_submission_1 table
    pg_query($dbConn, "TRUNCATE TABLE public.temp_route_submission_1");

    $counter = 0;

    $missingHeaderMsg = "All headers are not present in the CSV file. Headers include:";

    $incorrectHeaderMsg = "Headers are not the correct name. Headers are:";

    $columnListMsg = "\\n\\ngeographic_area, organism_name, latitude, longitude,\\nh_pos_err, planting_date, block, column, row,\\ngenotype, score_type, score, score_datetime";

    // Loop through the array of arrays and insert each row into a postgres table, skipping the header row
    foreach ($csv as $row) {
        if ($counter == 0) {

            // If all headers are not present, show javascript alert and PHP script will exit
            if (count($row) != 13) {
                echo "<script type='text/javascript'>alert('". $missingHeaderMsg . $columnListMsg . "');</script>";
                exit();
            }

            // If headers are not the correct name, show javascript alert and PHP script will exit (geographic_area always is returning != geographic_area??)
            if (strpos($row[0], "geographic_area") != 0 || strpos($row[1], "organism_name") != 0 || strpos($row[2], "latitude") != 0 || 
                strpos($row[3], "longitude") != 0 || strpos($row[4], "h_pos_err") != 0 || strpos($row[5], "planting_date") != 0 ||
                strpos($row[6], "block") != 0 || strpos($row[7], "column") != 0 || strpos($row[8], "row") != 0 || strpos($row[9], "genotype") != 0 || 
                strpos($row[10], "score_type") != 0 || strpos($row[11], "score") != 0 || strpos($row[12], "score_datetime") != 0) {
                echo "<script type='text/javascript'>alert('". $incorrectHeaderMsg . $columnListMsg . "');</script>";
                exit();
            }
        } else {

            // if organism_name, latitude, and longitude are empty, show javascript alert and PHP script will exit
            if (trim($row[1]) == "" || trim($row[2]) == "" || trim($row[3]) == "") {
                echo "<script type='text/javascript'>alert('Row " . $counter + 1 . " is missing organism_name, latitude, or longitude!');</script>";
                exit();
            }

            // if latitude or longitude is not decimal degrees, show javascript alert and PHP script will exit
            if (strpos($row[2], "°") != FALSE || strpos($row[3], "°") != FALSE || strpos($row[2], "'") != FALSE || strpos($row[3], "'") != FALSE ||
                strpos($row[2], "\"") != FALSE || strpos($row[3], "\"") != FALSE || stripos($row[2], "N") != FALSE || stripos($row[3], "W") != FALSE || 
                stripos($row[2], "S") != FALSE || stripos($row[3], "E") != FALSE) {
                echo "<script type='text/javascript'>alert('Row " . $counter + 1 . " latitude or longitude is not in decimal degrees!');</script>";
                exit();
            }

            // set h_pos_err, block, column, row, and score to null if they are empty
            if (trim($row[4]) == "") {$row[4] = null;}
            if (trim($row[6]) == "") {$row[6] = null;}
            if (trim($row[7]) == "") { $row[7] = null;}
            if (trim($row[8]) == "") {$row[8] = null;}
            if (trim($row[11]) == "") {$row[11] = null;}

            // set score_datetime to 1/1/1900 if it is empty
            if (trim($row[12]) == "") {$row[12] = "1900-01-01 00:00:00+01";}

            // Insert the row into the postgres table
            pg_query_params($dbConn, 'INSERT INTO public.temp_route_submission_1 (geographic_area, organism_name, latitude, longitude,
                        h_pos_err, planting_date, "block", "column", "row", genotype, score_type, score, score_datetime)
                        VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)',
                        array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12])
            );
        }
        $counter++;
    }

    // append UTC time to the end of $tripname, formatted to YYYY-MM-DD HH:MM:SS
    $tripname = $tripname . " " . gmdate("Y-m-d H:i:s");

    // Call postgres function public.query_route_run_through_all(_route_name text, _user_id text). If return value is 1, show javascript alert that the trip was successfully uploaded
    $result = pg_query_params($dbConn, 'SELECT public.query_route_run_through_all($1, $2)', array($tripname, '0'));
    $row = pg_fetch_row($result);
    if ($row[0] == 1) {
        echo "<script type='text/javascript'>alert('Trip successfully uploaded!');</script>";
    }

    // Close the connection to the database
    pg_close($dbConn);
    
?>