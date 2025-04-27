<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<style>
    .sidebar {
        height: 100vh;
        width: 220px;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #2c3e50;
        padding-top: 20px;
        color: white;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 22px;
    }

    .sidebar a {
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        color: white;
        transition: background 0.3s;
    }

    .sidebar a:hover {
        background-color: #34495e;
    }

    .main {
        margin-left: 230px;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <h2>Team Member</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="../user/my_tasks.php">📋 My Tasks</a>
    <!-- <a href="../user/update_status.php">📋 update status</a> -->
    <a href="../user/my_groups.php">📁	My Groups</a>
    <!-- <a href="../user/create_group.php">
    ➕	Create Groups</a> -->
    <!-- <a href="#">📁	Group Tasks	</a> -->
    <a href="../user/mycomment.php">💬	Task Comments</a>
    <!-- <a href="#">  👥	Group Members	</a> -->


    <a href="../includes/profile.php">👤 Profile</a>
    <a href="../logout.php">🚪 Logout</a>
</div>
