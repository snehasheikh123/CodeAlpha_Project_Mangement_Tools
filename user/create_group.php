<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['group_name'])) {
    $group_name = trim($_POST['group_name']);
    $user_id    = $_SESSION['user_id'];

    // 1) Create the group in the 'groups' table
    $stmt = $conn->prepare("
        INSERT INTO groups (group_name, created_by)
        VALUES (?, ?)
    ");
    $stmt->bind_param("si", $group_name, $user_id);

    if ($stmt->execute()) {
        // Get the last inserted group_id
        $group_id = $stmt->insert_id;

        // 2) Add creator as admin in the 'group_members' table
        $stmt2 = $conn->prepare("
            INSERT INTO group_members (group_id, user_id, role)
            VALUES (?, ?, 'admin')
        ");
        $stmt2->bind_param("ii", $group_id, $user_id);

        if ($stmt2->execute()) {
            // Redirect after successful group creation
            header("Location: my_groups.php");
            exit();
        } else {
            // Handle error with inserting group member
            $error = "Error adding group member: " . $stmt2->error;
        }
    } else {
        // Handle error with group creation
        $error = "Error creating group: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Group</title>
    <style>
        .form-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-container input,
        .form-container button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .form-container button {
            background: #3498db;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-container button:hover {
            background: #2980b9;
        }
        .error { color: red; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Create New Group</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="group_name">Group Name</label>
        <input type="text" id="group_name" name="group_name" required>
        <button type="submit">Create Group</button>
    </form>
</div>

</body>
</html>
