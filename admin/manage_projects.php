<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");
include("../includes/admin_sidebar.php");

// Fetch projects
$query = "SELECT p.*, u.username AS creator FROM projects p
          JOIN users u ON p.created_by = u.user_id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Projects</title>
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
        }

        .add-form input, .add-form textarea {
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

    </style>
</head>
<body>

<div class="main">
    <h2>Manage Projects</h2>

    <!-- Project List -->
    <table>
        <tr>
            <th>Project ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Created By</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row["project_id"]; ?></td>
            <td><?php echo $row["title"]; ?></td>
            <td><?php echo $row["description"]; ?></td>
            <td><?php echo $row["deadline"]; ?></td>
            <td><?php echo $row["creator"]; ?></td>
        </tr>
        <?php } ?>
    </table>

    <!-- Add Project Form -->
    <div class="add-form">
        <h3>Add New Project</h3>
        <form method="post" action="add_project.php">
            <input type="text" name="title" placeholder="Project Title" required>
            <textarea name="description" placeholder="Project Description" required></textarea>
            <input type="date" name="deadline" required>
            <button type="submit">Add Project</button>
        </form>
    </div>
</div>

</body>
</html>
