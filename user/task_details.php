<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['task_id'])) {
    echo "<p>Task ID is missing.</p>";
    exit();
}

$task_id = $_GET['task_id'];

$sql = "SELECT * FROM tasks WHERE task_id = ? AND assigned_to = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();
?>

<div style="margin-left: 250px; padding: 20px;">
    <h2>📌 Task Details</h2>
    <p><strong>Title:</strong> <?= htmlspecialchars($task['title']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($task['description'])) ?></p>
    <p><strong>Deadline:</strong> <?= htmlspecialchars($task['deadline']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($task['status']) ?></p>
    <p><strong>Due Date:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
</div>

<?php
} else {
    echo "<div style='margin-left:250px;padding:20px;'><p>Task not found or you don't have permission to view it.</p></div>";
}
?>
