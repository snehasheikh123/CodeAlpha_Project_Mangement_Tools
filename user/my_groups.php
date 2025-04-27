<?php
// my_groups.php
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

$error   = "";
$success = "";

// Handle Create Group
if (isset($_POST['create_group'])) {
    $name = trim($_POST['create_group_name']);
    if ($name === "") {
        $error = "Group name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO groups (group_name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $new_group_id = $stmt->insert_id;
            // Make creator admin
            $stmt2 = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
            $stmt2->bind_param("ii", $new_group_id, $user_id);
            $stmt2->execute();
            $success = "✅ Group “{$name}” created.";
        } else {
            $error = "❌ Could not create group.";
        }
    }
}

// Handle Invite User
if (isset($_POST['invite_user'])) {
    $invite_group = (int)$_POST['invite_group_id'];
    $email        = trim($_POST['invite_email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // lookup
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $error = "❌ No user found with that email.";
        } else {
            $row = $res->fetch_assoc();
            $new_uid = $row['user_id'];
            // already member?
            $chk = $conn->prepare("SELECT 1 FROM group_members WHERE group_id=? AND user_id=?");
            $chk->bind_param("ii", $invite_group, $new_uid);
            $chk->execute();
            if ($chk->get_result()->num_rows) {
                $error = "⚠️ User already in that group.";
            } else {
                $ins = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')");
                $ins->bind_param("ii", $invite_group, $new_uid);
                if ($ins->execute()) {
                    $success = "✅ “{$email}” invited to group.";
                } else {
                    $error = "❌ Could not add user.";
                }
            }
        }
    }
}

// Fetch groups
$stmt = $conn->prepare("
    SELECT g.group_id, g.group_name, g.created_at, gm.role
      FROM groups g
      JOIN group_members gm ON g.group_id = gm.group_id
     WHERE gm.user_id = ?
     ORDER BY g.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$groups = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Groups</title>
  <style>
    body { font-family: Arial, sans-serif;  margin:0;   }
        .main { margin-left: 250px; padding: 20px; }
    h1 { margin-top:0; }
    .group-list { list-style:none; padding:0; }
    .group-list li {
      background:#f4f4f9; margin-bottom:12px; padding:16px;
      border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.05);
      display:flex; justify-content:space-between; align-items:center;
    }
    .group-info a { font-weight:bold; color:#3498db; text-decoration:none; }
    .group-info a:hover { text-decoration:underline; }
    .group-info small { display:block; color:#777; font-size:0.9em; }

    .btn {
      border:none; border-radius:4px; padding:6px 10px;
      cursor:pointer; font-size:0.9em; display:inline-flex; align-items:center; gap:4px;
      transition:background 0.2s,transform 0.1s;
    }
    .btn-create { background:#007bff; color:#fff; margin-bottom:20px; }
    .btn-create:hover { background:#0056b3; }
    .btn-invite { background:#f1c40f; color:#fff; }
    .btn-invite:hover { background:#d4ac0d; }
    .btn-remove { background:#e74c3c; color:#fff; }
    .btn-remove:hover { background:#c0392b; }
    .alerts { max-width:500px; margin-bottom:20px; }
    .alert { padding:12px; border-radius:4px; margin-bottom:10px; }
    .alert.error { background:#fdecea; color:#c0392b; }
    .alert.success { background:#e9f7ef; color:#27ae60; }

    .modal {
      display:none; position:fixed; top:0; left:0; width:100%; height:100%;
      background:rgba(0,0,0,0.4); align-items:center; justify-content:center;
      z-index:1000;
    }
    .modal.active { display:flex; }
    .modal-content {
      background:#fff; padding:20px; border-radius:6px; width:90%; max-width:400px;
      box-shadow:0 4px 12px rgba(0,0,0,0.1);
    }
    .modal-content h3 { margin-top:0; }
    .modal-content input[type=text],
    .modal-content input[type=email] {
      width:100%; padding:8px; margin:10px 0 0; border:1px solid #ccc; border-radius:4px;
    }
    .modal-content .btn-submit {
      margin-top:12px; background:#28a745; color:#fff; width:100%;
    }
    .modal-content .btn-cancel {
      margin-top:8px; background:#dc3545; color:#fff; width:100%;
    }
  </style>
</head>
<body>
  <div class="main">
    <h1>📁 My Groups</h1>
   
    <button class="btn btn-create" onclick="showCreate()">➕ Create Group</button>
    <ul class="group-list">
      <?php while ($g = $groups->fetch_assoc()): ?>
        <li>
          <div class="group-info">
            <a href="group_dashboard.php?group_id=<?= $g['group_id'] ?>"><?= htmlspecialchars($g['group_name']) ?></a>
            <small>Role: <?= ucfirst($g['role']) ?> • <?= date('M j, Y', strtotime($g['created_at'])) ?></small>
          </div>
          <div>
            <button class="btn btn-invite" onclick="showInvite(<?= $g['group_id'] ?>)">👤 Invite</button>
            <a href="remove_group.php?group_id=<?= $g['group_id'] ?>" class="btn btn-remove"
               onclick="return confirm('Remove this group?');">❌ Remove</a>
          </div>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>

  <!-- Create Group Modal -->
  <div id="modalCreate" class="modal">
    <div class="modal-content">
      <h3>Create New Group</h3>
      <form method="post">
        <input type="text" name="create_group_name" placeholder="Group Name" required>
        <button type="submit" name="create_group" class="btn-submit btn">Create</button>
        <button type="button" class="btn-cancel btn" onclick="hideCreate()">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Invite User Modal -->
  <div id="modalInvite" class="modal">
    <div class="modal-content">
      <h3>Invite to Group</h3>
      <form method="post">
        <input type="hidden" name="invite_group_id" id="inviteGroupId" value="">
        <input type="email" name="invite_email" placeholder="user@example.com" required>
        <button type="submit" name="invite_user" class="btn-submit btn">Add Member</button>
        <button type="button" class="btn-cancel btn" onclick="hideInvite()">Cancel</button>
      </form>
    </div>
  </div>

  <script>
    function showCreate() {
      document.getElementById('modalCreate').classList.add('active');
    }
    function hideCreate() {
      document.getElementById('modalCreate').classList.remove('active');
    }
    function showInvite(gid) {
      document.getElementById('inviteGroupId').value = gid;
      document.getElementById('modalInvite').classList.add('active');
    }
    function hideInvite() {
      document.getElementById('modalInvite').classList.remove('active');
    }
  </script>
</body>
</html>
