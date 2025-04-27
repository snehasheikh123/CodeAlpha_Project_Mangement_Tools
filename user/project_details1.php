<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("No project selected.");
}

$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    die("Invalid project ID.");
}

$stmt = $conn->prepare("
    SELECT u.username, u.email 
    FROM users u 
    JOIN tasks t ON u.user_id = t.assigned_to 
    WHERE t.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$assigned_users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📁 Project Details</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f8;
    }

    .main-container {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 250px;
      background-color: #1f2937;
      position: fixed;
      top: 0;
      bottom: 0;
      padding-top: 30px;
    }

    .sidebar a {
      color: #fff;
      padding: 15px 20px;
      display: block;
      text-decoration: none;
    }

    .sidebar a:hover {
      background-color: #374151;
    }

    .content {
      margin-left: 250px;
      padding: 40px;
      width: 100%;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      padding: 25px 30px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
      max-width: 800px;
      margin: auto;
    }

    .card h2 {
      font-size: 26px;
      color: #1f2937;
      margin-bottom: 20px;
    }

    .card p {
      font-size: 16px;
      color: #374151;
      margin-bottom: 12px;
    }

    .download-link {
      display: inline-block;
      background-color: #3b82f6;
      color: white;
      padding: 10px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      margin-top: 10px;
    }

    .download-link:hover {
      background-color: #2563eb;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 14px;
      text-align: left;
    }

    th {
      background-color: #1f2937;
      color: #fff;
    }

    tr:nth-child(even) {
      background-color: #f3f4f6;
    }

    tr:hover {
      background-color: #e5e7eb;
    }

    .section-title {
      margin-top: 30px;
      font-size: 20px;
      font-weight: bold;
      color: #1f2937;
    }

    .go-button {
      display: inline-block;
      margin-top: 25px;
      background-color: #10b981; /* Emerald green */
      color: white;
      padding: 12px 20px;
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    .go-button:hover {
      background-color: #059669;
    }

    .back-button {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #ff4d4d; /* Red color for back button */
      color: white;
      padding: 10px 15px;
      border-radius: 50%;
      font-size: 18px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .back-button:hover {
      background-color: #e12d39;
    }
  </style>
</head>
<body>

<div class="main-container">
  <div class="sidebar">
    <?php include("../includes/user_sidebar.php"); ?>
  </div>

  <div class="content">
    <div class="card">
      <h2>📌 Project: <?= htmlspecialchars($project['title']) ?></h2>
      
      <p>📝 <strong>Description:</strong> <?= nl2br(htmlspecialchars($project['description'])) ?></p>
      <p>📅 <strong>Deadline:</strong> <?= htmlspecialchars($project['deadline']) ?></p>

      <p>📂 <strong>Project File:</strong>
        <?php if (!empty($project['file_path'])): ?>
          <a href="<?= htmlspecialchars($project['file_path']) ?>" class="download-link" download>⬇️ Download File</a>
        <?php else: ?>
          No file uploaded.
        <?php endif; ?>
      </p>

      <div class="section-title">👥 Assigned Users:</div>
      <?php if ($assigned_users->num_rows > 0): ?>
        <table>
          <tr>
            <th>🙍 Username</th>
            <th>📧 Email</th>
          </tr>
          <?php while ($user = $assigned_users->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>No users assigned to this project.</p>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- Back Button -->
<a href="javascript:history.back()" class="back-button">←</a>

</body>
</html>
