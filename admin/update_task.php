<?php
include("../includes/db.php");

$title = $_POST['title'];
$description = $_POST['description'];
$project_id = $_POST['project_id'];
$assigned_to = $_POST['assigned_to'];
$deadline = $_POST['deadline'];
$status = $_POST['status'];

$file_name = "";
if (isset($_FILES['description_file']) && $_FILES['description_file']['error'] == 0) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = time() . '_' . basename($_FILES["description_file"]["name"]);
    $target_file = $target_dir . $file_name;
    move_uploaded_file($_FILES["description_file"]["tmp_name"], $target_file);
}

$query = "INSERT INTO tasks (title, description, project_id, assigned_to, deadline, status, description_file)
          VALUES ('$title', '$description', '$project_id', '$assigned_to', '$deadline', '$status', '$file_name')";

if (mysqli_query($conn, $query)) {
    header("Location: manage_tasks.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
