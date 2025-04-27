<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

// Check if user_id is provided
if (isset($_GET['delete_user_id'])) {
    $user_id = $_GET['delete_user_id'];

    // Delete the user from the database
    $delete_query = "DELETE FROM users WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $delete_query)) {
        // Redirect back to manage users page with success message
        header("Location: manage_users.php?message=User+deleted+successfully");
        exit();
    } else {
        // Redirect back with error message
        header("Location: manage_users.php?message=Error+deleting+user");
        exit();
    }
} else {
    // If no user_id is provided, redirect with error message
    header("Location: manage_users.php?message=No+user+specified");
    exit();
}
?>
