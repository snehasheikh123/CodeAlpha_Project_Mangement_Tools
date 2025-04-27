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
    <h2>My Tasks</h2>

    <?php if ($res->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>Title</th>
                    <th>Description</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($task = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['description']) ?></td>
                        <td><?= htmlspecialchars($task['deadline']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td><?= htmlspecialchars($task['due_date']) ?></td>
                        <td>
                            <a href="task_details.php?task_id=<?= $task['task_id'] ?>" style="text-decoration: none; color: blue;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tasks found.</p>
    <?php endif; ?>
</div>
