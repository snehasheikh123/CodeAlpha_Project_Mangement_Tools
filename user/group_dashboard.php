<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id  = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null;
if (!$group_id) {
    die("No group selected.");
}

// Fetch Group Name
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) {
    die("Invalid group ID.");
}

// Fetch total Projects
$total_projects = $conn->prepare("SELECT COUNT(*) AS total FROM projects WHERE group_id = ?");
$total_projects->bind_param("i", $group_id);
$total_projects->execute();
$total_projects_result = $total_projects->get_result()->fetch_assoc();

// Fetch Projects
$projects = $conn->prepare("SELECT project_id, title, description, deadline, file_path FROM projects WHERE group_id = ?");
$projects->bind_param("i", $group_id);
$projects->execute();
$project_result = $projects->get_result();

// Fetch total Tasks
$total_tasks = $conn->prepare("SELECT COUNT(*) AS total FROM tasks t JOIN projects p ON t.project_id = p.project_id WHERE p.group_id = ?");
$total_tasks->bind_param("i", $group_id);
$total_tasks->execute();
$total_tasks_result = $total_tasks->get_result()->fetch_assoc();

// Fetch Tasks
$tasks = $conn->prepare("SELECT t.title, p.title AS project_name, u.username AS assigned_name, t.status, t.deadline FROM tasks t JOIN projects p ON t.project_id = p.project_id LEFT JOIN users u ON t.assigned_to = u.user_id WHERE p.group_id = ?");
$tasks->bind_param("i", $group_id);
$tasks->execute();
$task_result = $tasks->get_result();

// Fetch total Group Members
$total_members = $conn->prepare("SELECT COUNT(*) AS total FROM group_members WHERE group_id = ?");
$total_members->bind_param("i", $group_id);
$total_members->execute();
$total_members_result = $total_members->get_result()->fetch_assoc();

// Fetch Group Members
$members = $conn->prepare("SELECT u.username, u.email FROM users u JOIN group_members gm ON u.user_id = gm.user_id WHERE gm.group_id = ?");
$members->bind_param("i", $group_id);
$members->execute();
$members_result = $members->get_result();

// Fetch Activity Feed
$activity = $conn->prepare("SELECT activity_text, created_at FROM activity_feed WHERE group_id = ? ORDER BY created_at DESC LIMIT 10");
$activity->bind_param("i", $group_id);
$activity->execute();
$activity_result = $activity->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($group['group_name']) ?> - Group Dashboard</title>
  <style>
    html { scroll-behavior: smooth; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 0;}
    .main { margin-left: 250px; padding: 30px; background: #fff; min-height: 100vh; }
    .all-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .button { background: #007BFF; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
    .button:hover { background: #0056b3; }
    h1, h3 { color: #333; margin-bottom: 15px; }
    ul { list-style: none; padding-left: 0; }
    ul li { background: #f9f9f9; margin-bottom: 10px; padding: 10px; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
    th { background: #007BFF; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }
    tr:hover { background: #f1f1f1; }
    a { text-decoration: none; color: #007BFF; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="main">
    <h1 id="top"><?= htmlspecialchars($group['group_name']) ?> - Group Dashboard</h1>
    <div class="all-buttons">
      <a href="view_project.php?group_id=<?= $group_id ?>"><button class="button">📁 View Projects</button></a>
      <a href="view_group_members.php?group_id=<?= $group_id ?>"><button class="button">👥 View Members</button></a>
      <a href="create_task1.php?group_id=<?= $group_id ?>"><button class="button">➕ New Task</button></a>
      <a href="create_project1.php?group_id=<?= $group_id ?>"><button class="button">➕ New Project</button></a>
      <a href="comment.php?group_id=<?= $group_id ?>"><button class="button">➕ Add Comment</button></a>

    </div>

    <h3 id="projects">Projects (Total: <?= $total_projects_result['total'] ?>)</h3>
    <ul>
      <?php while ($project = $project_result->fetch_assoc()) { ?>
        <li><?= htmlspecialchars($project['title']) ?> - Deadline: <?= htmlspecialchars($project['deadline']) ?></li>
      <?php } ?>
    </ul>

    <h3 id="tasks">Tasks (Total: <?= $total_tasks_result['total'] ?>)</h3>
    <ul>
      <?php while ($task = $task_result->fetch_assoc()) { ?>
        <!-- <li><strong><?= htmlspecialchars($task['title']) ?></strong> - Assigned to: <?= htmlspecialchars($task['assigned_name']) ?> - Status: <?= htmlspecialchars($task['status']) ?> - Deadline: <?= htmlspecialchars($task['deadline']) ?></li> -->
      <?php } ?>
    </ul>

    <h3 id="members">Group Members (Total: <?= $total_members_result['total'] ?>)</h3>
    <ul>
      <?php while ($member = $members_result->fetch_assoc()) { ?>
        <!-- <li><?= htmlspecialchars($member['username']) ?> - Email: <?= htmlspecialchars($member['email']) ?></li> -->
      <?php } ?>
    </ul>

    <h3 id="activity">Activity Feed</h3>
    <ul>
      <?php while ($feed = $activity_result->fetch_assoc()) { ?>
        <li><strong><?= htmlspecialchars($feed['created_at']) ?>:</strong> <?= htmlspecialchars($feed['activity_text']) ?></li>
      <?php } ?>
    </ul>
  </div>
</body>
</html>
