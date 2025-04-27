<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch user (we assume no profile_image column yet, so use null coalesce)
$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Determine which image to show
$profileImage = $user['profile_image'] ?? '';
if (!$profileImage || !file_exists(__DIR__ . "/../assets/uploads/$profileImage")) {
    $profileImage = 'default.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      /* background: #f4f4f4; */
      margin: 0; padding: 0;
    }
    .container {
      width: 400px;
      margin: 60px auto;
      background: whitesmoke;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .profile-img {
      width: 100px; height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
      border: 2px solid #ddd;
    }
    h1 { margin: 0; color: #333; }
    p { color: #555; margin: 8px 0; }
    .edit-btn {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      transition: background 0.3s;
    }
    .edit-btn:hover { background: #0056b3; }
  </style>
</head>
<body>
  <div class="container">
    <img src="../uploads/<?= htmlspecialchars($profileImage) ?>"
         alt="Profile Image"
         class="profile-img">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <a href="edit_profile.php" class="edit-btn">✏️ Edit Profile</a>
  </div>
</body>
</html>
