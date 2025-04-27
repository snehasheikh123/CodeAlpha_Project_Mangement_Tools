<?php
session_start();
include("../includes/db.php");

// Make sure the user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id  = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("No group specified.");
}

// Verify current user is an admin of this group
$stmt = $conn->prepare("
    SELECT role 
      FROM group_members 
     WHERE group_id = ? 
       AND user_id  = ?
");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc() || $row['role'] !== 'admin') {
    die("You do not have permission to remove this group.");
}

// Begin transaction
$conn->begin_transaction();

try {
    // 1) Remove all memberships
    $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();

    // 2) (Optionally) remove any related projects/tasks/activity_feed here 
    //    e.g. DELETE FROM tasks WHERE project_id IN (SELECT project_id FROM projects WHERE group_id=?)
    //    and then DELETE FROM projects WHERE group_id=?
    //    and DELETE FROM activity_feed WHERE group_id=?
    //
    //    Add them if you want a full cleanup.

    // 3) Remove the group record
    $stmt = $conn->prepare("DELETE FROM groups WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();

    $conn->commit();

    // Redirect back with a message
    header("Location: my_groups.php?deleted=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Failed to remove group: " . $e->getMessage());
}
