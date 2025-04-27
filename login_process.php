<?php
session_start();
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) && $user['role'] == 'admin') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin/admin_dashboard.php');
        } else {
            echo "Invalid credentials or you are not an admin.";
        }
    } else {
        echo "User not found.";
    }
}
?>
