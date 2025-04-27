<?php
session_start();
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST["title"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $deadline = $_POST["deadline"];
    $created_by = $_SESSION["user_id"];

    $query = "INSERT INTO projects (title, description, deadline, created_by)
              VALUES ('$title', '$description', '$deadline', '$created_by')";

    if (mysqli_query($conn, $query)) {
        header("Location: manage_projects.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
