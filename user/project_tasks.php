<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("Project ID missing.");
}

$stmt = $conn->prepare("
  SELECT t.title AS task_title, t.status, t.deadline, u.username AS assigned_to
  FROM tasks t
  LEFT JOIN users u ON t.assigned_to = u.user_id
  WHERE t.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Project Task List</title>
  <style>
    /* General Styles */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
    }

    .main {
      margin-left: 250px;
      padding: 20px;
      font-family: Arial, sans-serif;
    }

    .search-bar {
      margin-bottom: 20px;
    }

    .search-bar input {
      padding: 8px;
      width: 200px;
    }

    .search-bar button {
      padding: 8px 15px;
    }

    /* Table Styles */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: white;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
        background-color: #007BFF;
        text-transform: uppercase;
      color: #333;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    /* Status Badge */
    .status {
      padding: 6px 12px;
      border-radius: 12px;
      font-size: 13px;
      color: #fff;
      display: inline-block;
    }

    .pending {
      background-color: #f39c12;
    }

    .in-progress {
      background-color: #3498db;
    }

    .completed {
      background-color: #2ecc71;
    }

    .overdue {
      background-color: #e74c3c;
    }

    /* Button Styles */
    button {
      background-color: #3498db;
      border: none;
      color: #fff;
      padding: 7px 14px;
      cursor: pointer;
      border-radius: 5px;
    }

    button:hover {
      background-color: #2980b9;
    }

    /* Link Styles */
    a.download-link {
      display: inline-block;
      padding: 6px 10px;
      background: #2ecc71;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      font-size: 13px;
    }

    a.download-link:hover {
      background: #27ae60;
    }

    .back-button {
      text-decoration: none;
      color: #3498db;
      font-size: 16px;
    }

    .back-button:hover {
      color: #2980b9;
    }

  </style>
</head>
<body>
  <div class="main">
    <h2>Project Task List</h2>
    <a class="back-button" href="javascript:history.back()">⬅️ Go Back</a>

    <?php if ($result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Task Title</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Deadline</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['task_title']) ?></td>
              <td><?= htmlspecialchars($row['assigned_to']) ?: 'Unassigned' ?></td>
              <td>
                <?php
                  // Get the status value from the database
                  $status_class = strtolower(str_replace(' ', '-', $row['status']));

                  // Check if the task is overdue
                  if ($row['status'] == 'Pending' && strtotime($row['deadline']) < time()) {
                      $status_class = 'overdue'; // Change status to overdue if deadline is passed
                  }
                  elseif ($row['status'] == 'In Progress') {
                      $status_class = 'in-progress'; // Apply the correct class for 'In Progress' status
                  }
                ?>
                <span class="status <?= $status_class ?>"><?= htmlspecialchars($row['status']) ?></span>
              </td>
              <td><?= htmlspecialchars($row['deadline']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No tasks found for this project.</p>
    <?php endif; ?>
  </div>
</body>
</html>
