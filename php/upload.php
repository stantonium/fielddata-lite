<?php  //code from https://jmsliu.com/3224/ios-upload-image-from-gallery-or-camera-to-server-side.html

$uploadFolder = "./upload";

if (!file_exists($uploadFolder)) {

    mkdir($uploadFolder);

}

 

if (is_array($_FILES["file"])) {

    $numberOfFiles = 1; // count($_FILES["file"]["name"]);

    for ($i = 0; $i < $numberOfFiles; $i++) { //ignore this comment >

        $path_parts = pathinfo($_FILES["file"]["tmp_name"]);

        // Create device and trip subfolders
        $uploadFile = $uploadFolder . "/" . htmlentities($_POST["fileSavePath"]);

        if (!file_exists($uploadFile)) {

            mkdir($uploadFile, 0744, true);

        }

        // Create save path and filename
        $uploadFile = $uploadFile . "/" .  basename($_FILES["file"]["name"]);

        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

        // $lat = $_POST["lat"];
        // $long = $_POST["long"];

       // if (!(getimagesize($_FILES["file"]["tmp_name"][$i]) !== false)) {
       //     echo "Sorry, your image is invalid";
       //     exit;
       // }

        if ($_FILES["file"]["size"] > 10000000) {

            echo "Sorry, your file is too large.";
            exit;

        }

        // if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && #imageFileType !=".csv" && imageFileType !=".heic") {
        //    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        //    exit;
        // }

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadFile)) {

            echo json_encode(["Message" => "Upload image ".basename($_FILES["file"]["name"])." successfully!",
                                 "Status" => "OK" //,
                                // "lat" => $_REQUEST["lat"],
                                // "long" => $_REQUEST["long"]
                                ]);

        } else {

            echo "Sorry, there was an error uploading your file.";

        }
    }
}

?>
