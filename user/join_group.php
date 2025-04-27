<?php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$token = $_GET['token'];

// Validate the token
$stmt = $conn->prepare("SELECT group_id, email FROM group_invitations WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $invitation = $result->fetch_assoc();
    $group_id = $invitation['group_id'];
    $email = $invitation['email'];

    // Add user to the group (assuming the user is logged in)
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();

    // Remove invitation after user accepts
    $stmt = $conn->prepare("DELETE FROM group_invitations WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    echo "Successfully added to the group!";
} else {
    echo "Invalid or expired invitation link.";
}
?>
