<?php  //code from https://jmsliu.com/3224/ios-upload-image-from-gallery-or-camera-to-server-side.html

$fileSavePath = "";
$fileName = "";
$sourceHash = "";

if (!empty($_POST["fileSavePath"]) && !empty($_POST["fileName"])) {
    $fileSavePath = htmlentities($_POST["fileSavePath"]);
    $fileName = htmlentities($_POST["fileName"]);
}

if (!empty($_POST["sourceHash"])) {
    $sourceHash = htmlentities($_POST["sourceHash"]);
}

// Insert into DB
if (!empty($_POST["insertIntoDB"])) {
    if(htmlentities($_POST["insertIntoDB"]) == "true"){
        $command = escapeshellcmd('python3 /var/www/html/fielddata/code/python/fern/process_trips.py');
        $output = shell_exec($command);
        echo $output;
        exit;
    }
}

$uploadFolder = "./upload";

processFile($fileSavePath, $fileName, $uploadFolder, $sourceHash);

function processFile($fileSavePath, $fileName, $uploadFolder, $sourceHash) {

    // Create folder if it doesn't exist
    $uploadFile = $uploadFolder . "/" . $fileSavePath;
    if (!file_exists($uploadFile)) {
        mkdir($uploadFile, 0744, true);
    }
    $uploadFile = $uploadFile . "/" . $fileName;

    doesFileExist($uploadFile, $sourceHash);
}

 // See if file already exists on the server
function doesFileExist($uploadFile, $sourceHash) {
    // See if filename has "Scoring" in it. If so, don't check for file existence. May need to add the initial trip CSV as well.
    if (strpos($uploadFile, "Scoring") == true) {
        uploadFile($uploadFile, $sourceHash);
    }
    else {
        if (file_exists($uploadFile)) {

            echo json_encode(["Message" => "file exists!",
                                "Status" => "Error"]);
            exit;

        } else {
            uploadFile($uploadFile, $sourceHash);
        }
    }
}

function uploadFile($uploadFile, $sourceHash) {
    if (!empty($_FILES["file"])) {
        if (is_array($_FILES["file"])) {

            $numberOfFiles = 1; // count($_FILES["file"]["name"]);

            for ($i = 0; $i < $numberOfFiles; $i++) { //ignore this comment >

                $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

                // $lat = $_POST["lat"];
                // $long = $_POST["long"];

            // if (!(getimagesize($_FILES["file"]["tmp_name"][$i]) !== false)) {
            //     echo "Sorry, your image is invalid";
            //     exit;
            // }

                if ($_FILES["file"]["size"] > 50000000) {

                    echo "Sorry, your file is too large.";
                    exit;

                }

                // if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && #imageFileType !=".csv" && imageFileType !=".heic") {
                //    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                //    exit;
                // }

                // Move from PHP temp folder to the file save folder
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadFile)) {

                    // Check sha256 hash of the file
                    $hash = hash_file('sha256', $uploadFile);

                    // If not equal, delete the file
                    if ($hash != $sourceHash) {

                        unlink($uploadFile);

                        echo json_encode(["Message" => "Hashes do not match!",
                                        "Status" => "Error"]);
                        exit;

                    } else {
                        echo json_encode(["Message" => "Upload image ".basename($_FILES["file"]["name"])." successfully!",
                                        "Status" => "OK" //,
                                        // "lat" => $_REQUEST["lat"],
                                        // "long" => $_REQUEST["long"]
                                        ]);
                    }

                } else {

                    echo "Sorry, there was an error uploading your file.";

                }
            }
        }
    }
}

?>