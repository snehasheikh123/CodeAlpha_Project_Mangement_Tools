<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

// Get the task ID from the URL
$task_id = isset($_GET['task_id']) ? $_GET['task_id'] : 0;
if ($task_id == 0) {
    header("Location: manage_tasks.php");
    exit();
}

// Delete the task from the database
$query = "DELETE FROM tasks WHERE task_id = '$task_id'";

if (mysqli_query($conn, $query)) {
    header("Location: manage_tasks.php");
} else {
    echo "Error deleting task: " . mysqli_error($conn);
}
?>
