<?php
session_start();
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_POST['group_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO projects (title, description, deadline, created_by, group_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $title, $description, $deadline, $created_by, $group_id);
    
    if ($stmt->execute()) {
        header("Location: group_dashboard.php?group_id=$group_id");
    } else {
        echo "Error creating project.";
    }
}
?>

<!-- Project Creation Form -->
<form method="POST" action="">
  <input type="hidden" name="group_id" value="<?= $_GET['group_id'] ?>">
  <input type="text" name="title" placeholder="Project Title" required><br>
  <textarea name="description" placeholder="Description"></textarea><br>
  <input type="date" name="deadline"><br>
  <button type="submit">Create Project</button>
</form>
