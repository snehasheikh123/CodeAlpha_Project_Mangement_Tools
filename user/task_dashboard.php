<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Fetch tasks for the logged-in user
$sql = "SELECT * FROM tasks WHERE assigned_to = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$res = $stmt->get_result();

?>

<div style="margin-left: 250px; padding: 20px;">
    <h2>My Task Dashboard</h2>

    <?php
    if ($res->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='width: 100%;'>";
        echo "<tr><th>Task Title</th><th>Status</th><th>Update Status</th></tr>";

        while ($task = $res->fetch_assoc()) {
            ?>
            <tr>
                <td><?= htmlspecialchars($task['title']) ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td>
                    <form method="POST" action="update_status.php">
                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                        <select name="status">
                            <option value="Pending" <?= ($task['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?= ($task['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?= ($task['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
            </tr>
            <?php
        }
        echo "</table>";
    } else {
        echo "<p>No tasks found.</p>";
    }
    ?>
</div>
