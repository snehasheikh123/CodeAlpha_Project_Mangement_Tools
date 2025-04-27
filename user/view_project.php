<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$group_id = $_GET['group_id'] ?? null;
if (!$group_id) {
    die("No group selected.");
}

// Fetch group name for heading
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) die("Invalid group ID.");

// Fetch projects in this group
$stmt = $conn->prepare(
    "SELECT project_id, title, description, deadline, file_path
     FROM projects WHERE group_id = ?"
);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$projects = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Projects - <?= htmlspecialchars($group['group_name']) ?></title>
  <style>
    body { font-family: Arial, sans-serif;margin: 0; padding: 0; }
    .main { margin-left: 250px; padding: 30px; background: #fff; min-height: 100vh; }
    h1 { color: #333; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
    th { background: #007BFF; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    tr:hover { background: #f1f1f1; }
    .button { background: #007BFF; color: #fff; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
    .button:hover { background: #0056b3; }
    a { text-decoration: none; color: inherit; }
  </style>
</head>
<body>
  <div class="main">
    <h1>Projects in "<?= htmlspecialchars($group['group_name']) ?>"</h1>
    <a href="group_dashboard.php?group_id=<?= $group_id ?>"><button class="button">⬅ Back to Dashboard</button></a>

    <?php if ($projects->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Description</th>
          <th>Deadline</th>
          <th>File</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($p = $projects->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td><?= htmlspecialchars($p['description']) ?></td>
          <td><?= htmlspecialchars($p['deadline']) ?></td>
          <td>
            <?php if (!empty($p['file_path'])): ?>
              <a href="<?= htmlspecialchars($p['file_path']) ?>" download>Download</a>
            <?php else: ?>
              No file
            <?php endif; ?>
          </td>
          <td>
            <a href="project_details1.php?project_id=<?= $p['project_id'] ?>"><button class="button">Details</button></a>
            <a href="project_tasks.php?project_id=<?= $p['project_id'] ?>"><button class="button">Tasks</button></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>No projects found in this group.</p>
    <?php endif; ?>
  </div>
</body>
</html>
