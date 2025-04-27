<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
$task_id  = isset($_GET['task_id'])  ? (int)$_GET['task_id']  : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Task Comments</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; }
    .main { margin-left:250px; padding:30px; }
    h1{ margin-top:0; color:#333; }
    table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; }
    th, td { padding:12px; border:1px solid #ddd; text-align:left; }
    th { background:#007BFF; color:#fff; text-transform: uppercase; }
    tr:hover { background:#f1f1f1; }
    .btn { padding:6px 12px; background:#007BFF; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    .btn:hover { background:#0056b3; }
    .back { text-decoration:none; color:#007BFF; display:inline-block; margin-bottom:10px; }
    .back:hover { text-decoration:underline; }
    textarea { width:100%; height:80px; padding:10px; border:1px solid #ccc; border-radius:4px; resize:vertical; }
    .submit { margin-top:10px; }
    .group-btn { margin-top:20px; }
  </style>
  <script>
    function goToComments(id) {
      window.location.href = 'comment.php?group_id=<?= $group_id ?>&task_id=' + id;
    }
    function goToGroupDashboard() {
      window.location.href = 'group_dashboard.php?group_id=<?= $group_id ?>';
    }
    function backToList() {
      window.location.href = 'group_dashboard.php?group_id=<?= $group_id ?>';
    }
  </script>
</head>
<body>
  <div class="main">
    <h1>💬 Task Comments</h1>
    <div class="group-btn">
        <button class="btn" onclick="goToGroupDashboard()">Go to Group Dashboard</button>
      </div>

    <?php if ($task_id): 
      // ---- Single-task view ----
      // 1) fetch task + project + assigned + status
      $q = $conn->prepare("
        SELECT 
          t.title,
          t.description,
          t.status,
          p.title AS project_name,
          u.username AS assigned_to
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        LEFT JOIN users u ON t.assigned_to = u.user_id
        WHERE t.task_id = ?
      ");
      $q->bind_param("i",$task_id);
      $q->execute();
      $task = $q->get_result()->fetch_assoc();
      if (!$task) {
        echo "<p>Task not found.</p>";
      } else {
        // 2) handle new comment
        if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['comment_text'])) {
          $ins = $conn->prepare("
            INSERT INTO task_comments 
              (task_id, user_id, comment_text, created_at)
            VALUES (?, ?, ?, NOW())
          ");
          $ins->bind_param("iis",$task_id,$user_id, trim($_POST['comment_text']));
          $ins->execute();
          header("Location: comment.php?group_id={$group_id}&task_id={$task_id}");
          exit;
        }

        // 3) fetch existing comments
        $c = $conn->prepare("
          SELECT 
            tc.comment_text,
            tc.created_at,
            u.username
          FROM task_comments tc
          JOIN users u ON tc.user_id = u.user_id
          WHERE tc.task_id = ?
          ORDER BY tc.created_at ASC
        ");
        $c->bind_param("i",$task_id);
        $c->execute();
        $comments = $c->get_result();
    ?>
      <a class="back" href="comment.php?group_id=<?= $group_id ?>">&larr; Back to task list</a>
      <!-- <a class="back" href="my_groups.php">&larr; Back to My Groups</a> -->
       <!-- Added Back to My Groups button -->

      <table>
        <tr><th>Project</th><td><?= htmlspecialchars($task['project_name']) ?></td></tr>
        <tr><th>Task</th><td><?= htmlspecialchars($task['title']) ?></td></tr>
        <tr><th>Assigned To</th><td><?= htmlspecialchars($task['assigned_to'] ?: '—') ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars($task['status']) ?></td></tr>
        <tr><th>Description</th>
          <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
        </tr>
      </table>

      <table>
        <thead>
          <tr><th>Commenter</th><th>When</th><th>Comment</th></tr>
        </thead>
        <tbody>
        <?php while($r = $comments->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td><?= date('M j, Y H:i',strtotime($r['created_at'])) ?></td>
            <td><?= nl2br(htmlspecialchars($r['comment_text'])) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <form method="POST">
        <h3>Add a Comment</h3>
        <textarea name="comment_text" required 
                  placeholder="Type your comment…"></textarea>
        <button class="btn submit" type="submit">Post Comment</button>
      </form>

      <!-- Button to go to group dashboard -->
      <!-- <div class="group-btn">
        <button class="btn" onclick="goToGroupDashboard()">Go to Group Dashboard</button>
      </div> -->

      <!-- Back to List button -->
      <!-- <div class="group-btn">
        <button class="btn" onclick="backToList()">Back to Task List</button>
      </div> -->

    <?php 
      } // end if task found

    // ---- Group-level view ----
    elseif ($group_id):
      $t = $conn->prepare("
        SELECT 
          t.task_id,
          t.title,
          t.status,
          u.username AS assigned_to
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.user_id
        WHERE t.group_id = ?
        ORDER BY t.deadline ASC
      ");
      $t->bind_param("i",$group_id);
      $t->execute();
      $tasks = $t->get_result();
      if ($tasks->num_rows):
    ?>
      <table>
        <thead>
          <tr>
            <th>Task</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Comments</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r = $tasks->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['assigned_to'] ?: '—') ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
              <button class="btn"
                      onclick="goToComments(<?= $r['task_id'] ?>)">
                View &amp; Add
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No tasks in this group.</p>
    <?php endif;

    // ---- Neither selected ----
    else: ?>
      <p>Please select a group first:</p>
      <p><a href="group_dashboard.php">← Back </a></p>
    <?php endif; ?>

  </div>
</body>
</html>
