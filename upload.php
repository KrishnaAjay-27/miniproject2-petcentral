<?php
require('connection.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an actual image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (optional)
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats (optional)
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // If all is well, move the uploaded file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Call Python script to classify the image
            $output = shell_exec("python3 predict_breed.py " . escapeshellarg($target_file));
            echo "Predicted Breed: " . $output;

            // Now search in the database (productpet table)
            

            if ($con->connect_error) {
                die("Connection failed: " . $con->connect_error);
            }

            $sql = "SELECT * FROM productpet WHERE name LIKE '%" . $output . "%'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "Breed Name: " . $row['name'] . "<br>";
                }
            } else {
                echo "No results found for breed: " . $output;
            }

            $con->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
