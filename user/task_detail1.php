<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$task_id = $_GET['task_id'] ?? null;
if (!$task_id) die("Task ID not provided.");

$stmt = $conn->prepare("
    SELECT t.*, u.username AS assigned_name, p.title AS project_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    LEFT JOIN projects p ON t.project_id = p.project_id
    WHERE t.task_id = ?
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Task Details</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f6f8;
      padding: 30px;
      margin-left: 230px;
    }
    .card {
      background-color: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      max-width: 700px;
      margin: auto;
    }
    h2 {
      color: #2c3e50;
      margin-bottom: 20px;
    }
    p {
      margin: 10px 0;
      font-size: 16px;
    }
    strong {
      color: #34495e;
    }
    .label {
      font-weight: bold;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>📝 Task: <?= htmlspecialchars($task['title']) ?></h2>
    <p><span class="label">Project:</span> <?= htmlspecialchars($task['project_name']) ?></p>
    <p><span class="label">Assigned To:</span> <?= htmlspecialchars($task['assigned_name']) ?></p>
    <p><span class="label">Status:</span> <?= htmlspecialchars($task['status']) ?></p>
    <p><span class="label">Deadline:</span> <?= htmlspecialchars($task['deadline']) ?></p>
    <p><span class="label">Description:</span><br><?= nl2br(htmlspecialchars($task['description'])) ?></p>
  </div>
</body>
</html>
