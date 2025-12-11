<?php
$targetDir = "uploads/";
$filename = time() . "_" . basename($_FILES["food_image"]["name"]);
$targetFile = $targetDir . $filename;

if (move_uploaded_file($_FILES["food_image"]["tmp_name"], $targetFile)) {
    header("Location: detect.php?img=" . $filename);
} else {
    echo "Error uploading file.";
}
?>
