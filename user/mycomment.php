<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch all comments on tasks assigned to this user
$stmt = $conn->prepare("
    SELECT 
      p.title   AS project_name,
      t.title   AS task_title,
      u.username AS commenter,
      c.comment_text,
      c.created_at
    FROM task_comments c
    JOIN tasks t    ON c.task_id = t.task_id
    JOIN projects p ON t.project_id = p.project_id
    JOIN users u    ON c.user_id = u.user_id
    WHERE t.assigned_to = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$comments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Comments on My Tasks</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
    }
    .main {
      margin-left: 250px;
      padding: 30px;
      min-height: 100vh;
    }
    h1 {
      color: #333;
      margin-top: 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #007BFF;
      color: #fff;
      text-transform: uppercase;
      font-size: 0.9em;
      letter-spacing: 0.5px;
    }
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    tr:hover {
      background: #eef3f9;
    }
    .no-data {
      margin-top: 20px;
      font-size: 1em;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="main">
    <h1>💬 Comments on My Tasks</h1>

    <?php if ($comments->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Project</th>
            <th>Task</th>
            <th>Commenter</th>
            <th>Comment</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $comments->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['project_name']) ?></td>
              <td><?= htmlspecialchars($row['task_title']) ?></td>
              <td><?= htmlspecialchars($row['commenter']) ?></td>
              <td><?= nl2br(htmlspecialchars($row['comment_text'])) ?></td>
              <td><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="no-data">No comments have been left on your tasks yet.</p>
    <?php endif; ?>

  </div>
</body>
</html>
