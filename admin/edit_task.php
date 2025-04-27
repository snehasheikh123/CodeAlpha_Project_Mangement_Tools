<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

// Get the task ID from the URL
$task_id = isset($_GET['task_id']) ? $_GET['task_id'] : 0;
if ($task_id == 0) {
    header("Location: manage_tasks.php");
    exit();
}

// Fetch task details
$query = "SELECT * FROM tasks WHERE task_id = '$task_id'";
$result = mysqli_query($conn, $query);
$task = mysqli_fetch_assoc($result);

// Fetch projects and users for dropdown
$projects = mysqli_query($conn, "SELECT * FROM projects");
$users = mysqli_query($conn, "SELECT * FROM users WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <style>
        /* Add styling for form */
        .form-container {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .form-container input, .form-container textarea, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="main">
    <h2>Edit Task</h2>
    <div class="form-container">
        <form action="update_task.php" method="POST">
            <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">

            <label>Title:</label>
            <input type="text" name="title" value="<?php echo $task['title']; ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?php echo $task['description']; ?></textarea>

            <label>Project:</label>
            <select name="project_id" required>
                <option value="">Select Project</option>
                <?php while ($p = mysqli_fetch_assoc($projects)) { ?>
                    <option value="<?php echo $p['project_id']; ?>" <?php echo ($p['project_id'] == $task['project_id']) ? 'selected' : ''; ?>>
                        <?php echo $p['title']; ?>
                    </option>
                <?php } ?>
            </select>

            <label>Assign To:</label>
            <select name="assigned_to" required>
                <option value="">Select User</option>
                <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                    <option value="<?php echo $u['user_id']; ?>" <?php echo ($u['user_id'] == $task['assigned_to']) ? 'selected' : ''; ?>>
                        <?php echo $u['username']; ?> (<?php echo $u['role']; ?>)
                    </option>
                <?php } ?>
            </select>

            <label>Deadline:</label>
            <input type="date" name="deadline" value="<?php echo $task['deadline']; ?>" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo ($task['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit">Update Task</button>
        </form>
    </div>
</div>

</body>
</html>
