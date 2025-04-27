<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");
include("../includes/user_sidebar.php");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $upd = $conn->prepare("
        UPDATE tasks 
           SET status = ? 
         WHERE task_id = ?
           AND group_id IN (
               SELECT group_id FROM group_members WHERE user_id = ?
           )
    ");
    $upd->bind_param("sii", $_POST['status'], $_POST['task_id'], $_SESSION['user_id']);
    $upd->execute();

    // Redirect to clear POST and preserve search
    $loc = $_SERVER['PHP_SELF'];
    if (!empty($_GET['search'])) {
        $loc .= '?search=' . urlencode($_GET['search']);
    }
    header("Location: $loc");
    exit();
}

// Build search filter
$search     = $_GET['search'] ?? '';
$searchWild = '%' . $search . '%';

// Fetch tasks assigned via group
$stmt = $conn->prepare("
    SELECT t.task_id, t.title, t.description, t.deadline, t.status
      FROM tasks t
INNER JOIN group_members gm ON t.group_id = gm.group_id
     WHERE gm.user_id = ?
       AND t.title LIKE ?
  ORDER BY t.deadline ASC
");
$stmt->bind_param("is", $_SESSION['user_id'], $searchWild);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Tasks</title>
    <style>
            body { 
                font-family: Arial, sans-serif;  
                margin:0;
            }

        .main { margin-left: 250px; padding: 20px; }
        .search-bar { margin-bottom: 20px; }
        .search-bar input { padding: 8px; width: 200px; }
        .search-bar button { padding: 8px 15px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
        select, button { padding: 6px; }
        form { margin: 0; }
        .badge {
            padding: 5px 10px; border-radius: 12px; color: #fff;
            font-size: 13px; display: inline-block;
        }
        .pending    { background-color: #f39c12; }
        .in_progress{ background-color: #3498db; }
        .completed  { background-color: #2ecc71; }
        select { padding: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button {
            background-color: #3498db; border: none; color: #fff;
            padding: 7px 14px; cursor: pointer; border-radius: 5px;
        }
        button:hover { background-color: #2980b9; }
        a.download-link {
            display: inline-block; padding: 6px 10px;
            background: #2ecc71; color: #fff; text-decoration: none;
            border-radius: 4px; font-size: 13px;
        }
        a.download-link:hover { background: #27ae60; }
    </style>
</head>
<body>

<div class="main">
    <h1>📋 My Tasks</h1>

    <div class="search-bar">
        <form method="get" action="my_tasks.php">
            <input type="text" name="search" placeholder="Search by title…" 
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if ($tasks->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Deadline</th>
                <th>Current Status</th>
                <th>New Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($t = $tasks->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t['title']) ?></td>
                <td>
                    <?php
                        $desc = htmlspecialchars($t['description']);
                        $exts = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','zip','txt'];
                        $found = false;
                        foreach ($exts as $ext) {
                            if (preg_match("/([\w\s\-.]+\.$ext)/i", $t['description'], $m)) {
                                $file = trim($m[1]);
                                $path = "../assets/uploads/" . $file;
                                if (file_exists($path)) {
                                    echo "<a href='$path' class='download-link' download>⬇ Download $file</a><br>";
                                    $found = true;
                                }
                                break;
                            }
                        }
                        if (!$found) echo $desc;
                    ?>
                </td>
                <td><?= htmlspecialchars($t['deadline']) ?></td>
                <td>
                    <span class="badge <?= $t['status'] ?>">
                        <?= ucfirst(str_replace('_',' ',$t['status'])) ?>
                    </span>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="task_id"  value="<?= $t['task_id'] ?>">
                        <select name="status" required>
                            <option value="pending"     <?= $t['status']=='pending'     ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $t['status']=='in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed"   <?= $t['status']=='completed'   ? 'selected' : '' ?>>Completed</option>
                        </select>
                </td>
                <td>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No tasks found<?= $search ? " for \"" . htmlspecialchars($search) . "\"" : "" ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>
