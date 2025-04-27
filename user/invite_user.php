<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id  = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("No group specified.");
}

// Fetch group name
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) {
    die("Invalid group.");
}

$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Lookup user by email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $error = "❌ No user found with that email.";
    } else {
        $new_user = $res->fetch_assoc();
        $new_id   = $new_user['user_id'];
        
        // Already a member?
        $stmt = $conn->prepare(
            "SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?"
        );
        $stmt->bind_param("ii", $group_id, $new_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows) {
            $error = "⚠️ That user is already in this group.";
        } else {
            // Add to group
            $role = 'member';
            $stmt = $conn->prepare(
                "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iis", $group_id, $new_id, $role);
            if ($stmt->execute()) {
                $success = "✅ “{$email}” has been added as a member.";
            } else {
                $error = "❌ Database error. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invite User to “<?= htmlspecialchars($group['group_name']) ?>”</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f9; }
    .main {
      margin-left: 250px;
      padding: 30px;
      min-height: 100vh;
      background: #fff;
    }
    h2 { margin-top: 0; color: #333; }
    .card {
      background: #fafafa;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      max-width: 500px;
    }
    .card label { display: block; margin: 12px 0 6px; font-weight: bold; }
    .card input[type="email"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .card button {
      margin-top: 15px;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1em;
    }
    .btn-submit { background: #2ecc71; color: #fff; }
    .btn-submit:hover { background: #27ae60; }
    .btn-back {
      background: #3498db;
      color: #fff;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
      padding: 8px 14px;
      border-radius: 4px;
    }
    .btn-back:hover { background: #2980b9; }
    .alert {
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 15px;
      font-size: 0.95em;
    }
    .alert.error { background: #fdecea; color: #d64545; }
    .alert.success { background: #e9f7ef; color: #27ae60; }
  </style>
</head>
<body>
  <div class="main">
    <h2>Invite to: “<?= htmlspecialchars($group['group_name']) ?>”</h2>
    <div class="card">
      <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <label for="email">User Email</label>
        <input type="email" id="email" name="email" placeholder="user@example.com" required>
        <button type="submit" class="btn-submit">➕ Add to Group</button>
      </form>
    </div>

    <a href="my_groups.php" class="btn-back">⬅️ Back to My Groups</a>
  </div>
</body>
</html>
