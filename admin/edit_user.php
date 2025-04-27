<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

if (isset($_GET['edit_user_id'])) {
    $user_id = $_GET['edit_user_id'];
    $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $update_query = "UPDATE users SET username='$username', email='$email', role='$role' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('User updated successfully'); window.location.href='manage_users.php';</script>";
        } else {
            echo "<script>alert('Error updating user: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>

<h2>Edit User</h2>
<form method="POST" action="">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

    <label for="role">Role:</label>
    <select name="role" required>
        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
    </select>

    <button type="submit">Update User</button>
</form>

</body>
</html>
