<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");
include("../includes/admin_sidebar.php");

$project_filter = isset($_GET['project_id']) ? $_GET['project_id'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT t.*, p.title AS project_title, u.username AS assignee
          FROM tasks t
          JOIN projects p ON t.project_id = p.project_id
          JOIN users u ON t.assigned_to = u.user_id
          WHERE 1";

if ($project_filter != '') {
    $query .= " AND t.project_id = '$project_filter'";
}
if ($status_filter != '') {
    $query .= " AND t.status = '$status_filter'";
}

$result = mysqli_query($conn, $query);
$projects_filter = mysqli_query($conn, "SELECT * FROM projects");
$projects_form = mysqli_query($conn, "SELECT * FROM projects");
$users = mysqli_query($conn, "SELECT * FROM users WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Tasks</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .add-form {
            margin-top: 30px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        .add-form input, .add-form textarea, .add-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        .add-form button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }
        .filters {
            margin-bottom: 20px;
        }
        .filters select {
            padding: 10px;
            margin-right: 10px;
        }
        .toggle-btn {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function toggleForm() {
            var form = document.getElementById("addTaskForm");
            form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
        }
    </script>
</head>
<body>

<div class="main">
    <h2>Manage Tasks</h2>

    <!-- Filter -->
    <div class="filters">
        <form action="manage_tasks.php" method="GET">
            <label for="project_id">Project:</label>
            <select name="project_id" id="project_id">
                <option value="">Select Project</option>
                <?php while ($p = mysqli_fetch_assoc($projects_filter)) { ?>
                    <option value="<?php echo $p['project_id']; ?>" <?php echo ($project_filter == $p['project_id']) ? 'selected' : ''; ?>>
                        <?php echo $p['title']; ?>
                    </option>
                <?php } ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="">Select Status</option>
                <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo ($status_filter == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <button class="toggle-btn" onclick="toggleForm()">Add New Task</button>

    <!-- Task Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Project</th>
            <th>Title</th>
            <th>Description</th>
            <th>File</th>
            <th>Assigned To</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['task_id']; ?></td>
                <td><?php echo $row['project_title']; ?></td>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>
                    <?php if (!empty($row['description_file'])) { ?>
                        <a href="../uploads/<?php echo $row['description_file']; ?>" target="_blank">View File</a>
                    <?php } else { echo "No File"; } ?>
                </td>
                <td><?php echo $row['assignee']; ?></td>
                <td><?php echo $row['deadline']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td>
                    <a href="edit_task.php?task_id=<?php echo $row['task_id']; ?>">Edit</a> |
                    <a href="delete_task.php?task_id=<?php echo $row['task_id']; ?>" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Add Task Form -->
    <div id="addTaskForm" class="add-form">
        <h3>Add New Task</h3>
        <form action="save_task.php" method="POST" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" required>

            <label>Description:</label>
            <textarea name="description" required></textarea>

            <label>Attach File (Optional):</label>
            <input type="file" name="description_file" accept=".pdf,.doc,.docx,.jpg,.png">

            <label>Project:</label>
            <select name="project_id" required>
                <option value="">Select Project</option>
                <?php while ($p = mysqli_fetch_assoc($projects_form)) { ?>
                    <option value="<?php echo $p['project_id']; ?>"><?php echo $p['title']; ?></option>
                <?php } ?>
            </select>

            <label>Assign To:</label>
            <select name="assigned_to" required>
                <option value="">Select User</option>
                <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                    <option value="<?php echo $u['user_id']; ?>"><?php echo $u['username']; ?> (<?php echo $u['role']; ?>)</option>
                <?php } ?>
            </select>

            <label>Deadline:</label>
            <input type="date" name="deadline" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>

            <button type="submit">Add Task</button>
        </form>
    </div>
</div>

</body>
</html>
