<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

// Check if the user is logged in and if group_id is passed
$user_id  = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("No group selected.");
}

// Fetch Group Details (optional - you can display the group name in the form)
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get project data from the form
    $project_title = $_POST['project_title'];
    $project_description = $_POST['project_description'];
    $project_deadline = $_POST['project_deadline'];
    
    // File Upload Handling
    $upload_dir = "../uploads/projects/";
    $uploaded_file_path = null;

    if (!empty($_FILES['project_file']['name'])) {
        $file_name = basename($_FILES['project_file']['name']);
        $target_file = $upload_dir . time() . "_" . $file_name;

        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move uploaded file to target folder
        if (move_uploaded_file($_FILES['project_file']['tmp_name'], $target_file)) {
            $uploaded_file_path = $target_file;
        } else {
            echo "<p style='color:red;'>Failed to upload file.</p>";
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO projects (group_id, title, description, deadline, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $group_id, $project_title, $project_description, $project_deadline, $uploaded_file_path);
    $stmt->execute();

    // Redirect to the group dashboard
    header("Location: group_dashboard.php?group_id=" . $group_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create New Project</title>
  <style>
    .form-container {
      padding: 30px;
      max-width: 500px;
      margin: 0 auto;
      background:whitesmoke;
      border-radius: 10px;
    }
    .form-container input, .form-container textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .form-container button {
      padding: 10px 15px;
      background-color: #007BFF;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }
    .form-container button:hover {
      background-color: #0056b3;
    }
    body { font-family: Arial, sans-serif; 
     }

  </style>
</head>
<body>

<div class="form-container">
  <h2>Create New Project for Group: <?= htmlspecialchars($group['group_name']) ?></h2>
  <form action="" method="POST" enctype="multipart/form-data">
    <label for="project_title">Project Title:</label>
    <input type="text" name="project_title" id="project_title" required>

    <label for="project_description">Project Description:</label>
    <textarea name="project_description" id="project_description" required></textarea>

    <label for="project_deadline">Project Deadline:</label>
    <input type="date" name="project_deadline" id="project_deadline" required>

    <label for="project_file">Upload Project File (optional):</label>
    <input type="file" name="project_file" id="project_file" accept=".pdf,.doc,.docx,.txt,.zip">

    <button type="submit">Create Project</button>

    <a href="group_dashboard.php?group_id=<?= $group_id ?>" style="display: inline-block; margin-top: 10px; text-decoration: none;">
  <button type="button" style="background-color:rgb(36, 120, 193);">Back</button>
</a>

  </form>
</div>

</body>
</html>
