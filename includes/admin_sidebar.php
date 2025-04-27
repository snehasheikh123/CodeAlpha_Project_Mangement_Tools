<!-- includes/admin_sidebar.php -->
<div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_projects.php">Manage Projects</a>
    <a href="manage_tasks.php">Manage Tasks</a>
    <a href="../logout.php">Logout</a>
</div>

<style>
    .sidebar {
        width: 200px;
        height: 100vh;
        background-color: #222;
        padding: 20px;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
        margin: 10px 0;
    }

    .main {
        margin-left: 220px;
        padding: 20px;
    }
</style>
