<?php
include("../includes/db.php");

$title = $_POST['title'];
$description = $_POST['description'];
$project_id = $_POST['project_id'];
$assigned_to = $_POST['assigned_to'];
$deadline = $_POST['deadline'];
$status = $_POST['status'];

$query = "INSERT INTO tasks (project_id, title, description, assigned_to, deadline, status) 
          VALUES ('$project_id', '$title', '$description', '$assigned_to', '$deadline', '$status')";

if (mysqli_query($conn, $query)) {
    header("Location: manage_tasks.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
