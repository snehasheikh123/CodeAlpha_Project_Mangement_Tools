<?php
session_start();

// Redirect user if not logged in as team_member
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "team_member") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php"); // To fetch user data including the profile image
include("../includes/user_sidebar.php");

// Fetch the current user's data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .main {
            text-align: center;
            margin-top: 50px;
            /* background: #fff; */
            padding: 20px;
            /* border-radius: 8px; */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            color: #333;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
        }

        p {
            color: #555;
        }
    </style>
</head>
<body>

<div class="main">
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?> 👋</h1>

    <!-- Display profile image -->
    <?php if (!empty($user['profile_image'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-img">
    <?php else: ?>
        <img src="../uploads/default.jpg" alt="Default Profile Image" class="profile-img">
    <?php endif; ?>

    <p>This is your dashboard. Here you can track your tasks and progress.</p>
</div>

</body>
</html>
