<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// 1) GET → Load task or redirect if missing
if (!isset($_GET['task_id'])) {
    header("Location: my_tasks.php");
    exit();
}

$task_id = intval($_GET['task_id']);
$stmt    = $conn->prepare("
    SELECT title, status 
      FROM tasks 
     WHERE task_id = ? 
       AND assigned_to = ?
");
$stmt->bind_param("ii", $task_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div style='margin-left:250px;padding:20px;'><p>Task not found.</p></div>";
    exit();
}

$task = $result->fetch_assoc();

// 2) POST → Update status and redirect back
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $upd = $conn->prepare("
        UPDATE tasks 
           SET status = ? 
         WHERE task_id = ? 
           AND assigned_to = ?
    ");
    $upd->bind_param("sii", $newStatus, $task_id, $_SESSION["user_id"]);
    $upd->execute();

    header("Location: my_tasks.php");
    exit();
}
?>

<div style="margin-left:250px; padding:20px;">
  <h2>✏️ Update Task Status</h2>

  <form method="post">
    <p>
      <strong>Task Name:</strong><br>
      <input 
        type="text" 
        value="<?= htmlspecialchars($task['title']) ?>" 
        disabled 
        style="width:100%;"
      >
    </p>

    <p>
      <strong>Current Status:</strong><br>
      <input 
        type="text" 
        value="<?= htmlspecialchars($task['status']) ?>" 
        disabled 
        style="width:100%;"
      >
    </p>

    <p>
      <strong>New Status:</strong><br>
      <select name="status" required style="width:100%;padding:8px;">
        <option value="Not Started" <?= $task['status']==='Not Started'? 'selected':'' ?>>
          Not Started
        </option>
        <option value="In Progress" <?= $task['status']==='In Progress'? 'selected':'' ?>>
          In Progress
        </option>
        <option value="Completed" <?= $task['status']==='Completed'? 'selected':'' ?>>
          Completed
        </option>
      </select>
    </p>

    <button type="submit" style="padding:10px 20px;">Update Status</button>
  </form>
</div>
