<?php
session_start();
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("INSERT INTO tasks (project_id, title, description, assigned_to, deadline, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issis", $project_id, $title, $description, $assigned_to, $deadline);
    
    if ($stmt->execute()) {
        echo "Task created successfully.";
    } else {
        echo "Error.";
    }
}
?>

<!-- Task Creation Form -->
<form method="POST" action="">
  <input type="hidden" name="project_id" value="<?= $_GET['project_id'] ?>">
  <input type="text" name="title" placeholder="Task Title" required><br>
  <textarea name="description" placeholder="Description"></textarea><br>
  <select name="assigned_to">
    <?php
    $users = $conn->query("SELECT user_id, username FROM users");
    while ($u = $users->fetch_assoc()) {
        echo "<option value='{$u['user_id']}'>{$u['username']}</option>";
    }
    ?>
  </select><br>
  <input type="date" name="deadline"><br>
  <button type="submit">Create Task</button>
</form>
