<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id  = $_SESSION['user_id'];
$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    die("No project selected.");
}

// Fetch project details
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

// Fetch tasks for this project
$task_query = $conn->prepare("SELECT t.*, u.username AS assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.user_id WHERE t.project_id = ?");
$task_query->bind_param("i", $project_id);
$task_query->execute();
$tasks_result = $task_query->get_result();

// Fetch project members
$member_query = $conn->prepare("SELECT u.username FROM project_members pm JOIN users u ON pm.user_id = u.user_id WHERE pm.project_id = ?");
$member_query->bind_param("i", $project_id);
$member_query->execute();
$members_result = $member_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Details</title>
    <style>
        .main { margin-left: 230px; padding: 30px; font-family: Arial; background: #f5f5f5; min-height: 100vh; }
        table, th, td { border: 1px solid #ccc; border-collapse: collapse; padding: 8px; }
        th { background-color: #eee; }
        input, select, textarea { padding: 8px; width: 100%; margin-bottom: 10px; }
        form { background: #fff; padding: 20px; border: 1px solid #ccc; margin-top: 20px; }
    </style>
</head>
<body>
<div class="main">
    <h2><?= htmlspecialchars($project['title']) ?> - Project Page</h2>
    <p><?= htmlspecialchars($project['description']) ?></p>

    <h3>Project Members</h3>
    <ul>
        <?php while ($m = $members_result->fetch_assoc()): ?>
            <li><?= htmlspecialchars($m['username']) ?></li>
        <?php endwhile; ?>
    </ul>

    <h3>Create New Task</h3>
    <form action="create_task.php" method="POST">
        <input type="hidden" name="project_id" value="<?= $project_id ?>">
        <label>Task Title:</label>
        <input type="text" name="title" required>

        <label>Description:</label>
        <textarea name="description"></textarea>

        <label>Assign To:</label>
        <select name="assigned_to">
            <option value="">-- Select Member --</option>
            <?php
            $member_query->execute();
            $members_result = $member_query->get_result();
            while ($m = $members_result->fetch_assoc()): ?>
                <option value="<?= $m['username'] ?>"><?= $m['username'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Deadline:</label>
        <input type="date" name="deadline">

        <input type="submit" value="Create Task">
    </form>

    <h3>Tasks</h3>
    <table>
        <tr>
            <th>Title</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Deadline</th>
            <th>Details</th>
        </tr>
        <?php while ($t = $tasks_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t['title']) ?></td>
                <td><?= htmlspecialchars($t['assigned_name']) ?></td>
                <td><?= htmlspecialchars($t['status']) ?></td>
                <td><?= htmlspecialchars($t['deadline']) ?></td>
                <td><a href="task_details.php?task_id=<?= $t['task_id'] ?>">View</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="create_task.php?project_id=<?= $project['project_id'] ?>">➕ Add Task</a>
<a href="task_details.php?task_id=<?= $task['task_id'] ?>">🔍 View Task</a>

</div>
</body>
</html>
