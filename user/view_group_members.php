<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id  = $_SESSION['user_id'] ?? null;
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("No group selected.");
}

// Fetch group name
$stmt = $conn->prepare("SELECT group_name FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) {
    die("Invalid group ID.");
}

// Check if current user is admin
$stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$user_role = $stmt->get_result()->fetch_assoc()['role'] ?? null;
$is_admin  = ($user_role === 'admin');

// Handle modal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['role'])) {
    $email = trim($_POST['email']);
    $role  = $_POST['role'];

    // Lookup user_id by email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $new_user_id = $user['user_id'];
        // Ensure not already a member
        $stmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $new_user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $group_id, $new_user_id, $role);
            $stmt->execute();
            $message = "✅ Member added successfully!";
        } else {
            $error = "⚠️ This user is already a member.";
        }
    } else {
        $error = "❌ No user found with that email.";
    }
}

// Fetch group members
$stmt = $conn->prepare("
    SELECT u.user_id, u.username, u.email, gm.role 
      FROM group_members gm 
      JOIN users u ON gm.user_id = u.user_id 
     WHERE gm.group_id = ?
");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$members = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Members – <?= htmlspecialchars($group['group_name']) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0; padding: 0;
    }
    .main {
      margin-left: 250px;
      padding: 30px;
      background: #fff;
      min-height: 100vh;
    }
    h1 {
      color: #333;
      margin-top: 0;
    }
    .button {
      background-color: #007BFF;
      color: #fff;
      border: none;
      padding: 10px 15px;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 10px;
      transition: background 0.3s;
    }
    .button:hover {
      background-color: #0056b3;
    }
    .add-btn {
      background-color: #28a745;
    }
    .add-btn:hover {
      background-color: #218838;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #007BFF;
      color: #fff;
    }
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    tr:hover {
      background: #f1f1f1;
    }
    .action-btn {
      background-color: #e74c3c;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .action-btn:hover {
      background-color: #c0392b;
    }
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 6px;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      position: relative;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    .modal-header h3 {
      margin: 0;
      font-size: 1.25em;
    }
    .close {
      font-size: 1.5em;
      cursor: pointer;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1em;
    }
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #007BFF;
    }
    .modal-footer {
      text-align: right;
    }
    .alert {
      margin-top: 15px;
      padding: 10px;
      border-radius: 4px;
    }
    .alert.error { background: #f8d7da; color: #721c24; }
    .alert.success { background: #d4edda; color: #155724; }
  </style>
</head>
<body>
  <div class="main">
    <h1>Group: <?= htmlspecialchars($group['group_name']) ?> – Members</h1>
    <a href="group_dashboard.php?group_id=<?= $group_id ?>">
      <button class="button">⬅ Back to Dashboard</button>
    </a>
    <?php if ($is_admin): ?>
      <button class="button add-btn" id="openModal">➕ Add New Member</button>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($message)): ?>
      <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($members->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($m['username']) ?></td>
              <td><?= htmlspecialchars($m['email']) ?></td>
              <td><?= htmlspecialchars(ucfirst($m['role'])) ?></td>
              <?php if ($is_admin): ?>
                <td>
                  <?php if ($m['user_id'] != $user_id): ?>
                    <a href="remove_member.php?group_id=<?= $group_id ?>&user_id=<?= $m['user_id'] ?>"
                       onclick="return confirm('Remove this member?');">
                      <button type="button" class="action-btn">Remove</button>
                    </a>
                  <?php else: ?>
                    <span style="color:#aaa;">(You)</span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No members in this group.</p>
    <?php endif; ?>
  </div>

  <!-- Add Member Modal -->
  <?php if ($is_admin): ?>
  <div id="memberModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Add New Member</h3>
        <span class="close" id="closeModal">&times;</span>
      </div>
      <form method="POST">
        <div class="form-group">
          <label for="email">User Email</label>
          <input type="email" name="email" id="email" placeholder="user@example.com" required>
        </div>
        <div class="form-group">
          <label for="role">Role</label>
          <select name="role" id="role" required>
            <option value="member">Member</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="button" id="cancelBtn">Cancel</button>
          <button type="submit" class="button add-btn">Add Member</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script>
    // Modal toggle
    const modal = document.getElementById('memberModal');
    document.getElementById('openModal').onclick = () => modal.style.display = 'flex';
    document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
    document.getElementById('cancelBtn').onclick = () => modal.style.display = 'none';
    window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; }
  </script>
</body>
</html>
