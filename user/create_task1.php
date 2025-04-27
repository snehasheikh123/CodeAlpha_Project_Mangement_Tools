<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id  = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("No group selected.");
}

// Fetch Group Details
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

// Fetch Projects in the group
$projects = $conn->prepare("SELECT * FROM projects WHERE group_id = ?");
$projects->bind_param("i", $group_id);
$projects->execute();
$project_result = $projects->get_result();

// ✅ Fetch only group members for assignment
$users = $conn->prepare("
    SELECT u.user_id, u.username 
    FROM users u 
    JOIN group_members gm ON u.user_id = gm.user_id 
    WHERE gm.group_id = ?
");
$users->bind_param("i", $group_id);
$users->execute();
$user_result = $users->get_result();

// Handle task form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_title = $_POST['title'];
    $task_description = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $project_id = $_POST['project_id'];
    $status = $_POST['status'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, assigned_to, project_id, status, deadline, group_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiisi", $task_title, $task_description, $assigned_to, $project_id, $status, $deadline, $group_id);

    if ($stmt->execute()) {
        echo "<script>alert('Task created successfully!'); window.location.href='group_dashboard.php?group_id=" . $group_id . "';</script>";
    } else {
        echo "<script>alert('Error creating task. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  
  <title>Create New Task</title>
  <style>
    body { 
                font-family: Arial, sans-serif;  
            }

    .main { margin-left: 230px; padding: 10px;   min-height: 90vh; }
    form { background: whitesmoke; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    label { display: block; margin-bottom: 8px; font-weight: bold; }
    input, textarea, select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    button { background-color: #007BFF; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    button:hover { background-color: #0056b3; }
    h1{ color: #333; }
  </style>
</head>
<body>
<div class="main">
<h1>Create New Task for <?= htmlspecialchars($group['group_name']) ?></h1>

  <form method="POST">
    <label for="title">Task Title</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Task Description</label>
    <textarea name="description" id="description" rows="4" required></textarea>

    <label for="assigned_to">Assign To</label>
    <select name="assigned_to" id="assigned_to" required>
      <option value="">Select User</option>
      <?php while ($user = $user_result->fetch_assoc()): ?>
        <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="project_id">Select Project</label>
    <select name="project_id" id="project_id" required>
      <option value="">Select Project</option>
      <?php while ($project = $project_result->fetch_assoc()): ?>
        <option value="<?= $project['project_id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="status">Status</label>
    <select name="status" id="status" required>
      <option value="Not Started">Not Started</option>
      <option value="In Progress">In Progress</option>
      <option value="Completed">Completed</option>
    </select>

    <label for="deadline">Deadline</label>
    <input type="date" name="deadline" id="deadline" required>

    <button type="submit">Create Task</button>
    
    <a href="group_dashboard.php?group_id=<?= $group_id ?>" style="display: inline-block; margin-top: 10px; text-decoration: none;">
  <button type="button" style="background-color:rgb(36, 120, 193);">Back</button>
  </form>
</div>
</body>
</html>
