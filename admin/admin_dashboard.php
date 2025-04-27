<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>


<div class="main">
    <h1>Welcome, <?php echo $_SESSION["username"]; ?>!</h1>
    <p>This is your admin dashboard.</p>
</div>

</body>
</html>
