<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");
include("../includes/admin_sidebar.php");

// Get search term if available
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id  = intval($_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    $update_sql = "
        UPDATE users 
        SET username = '$username',
            email    = '$email',
            role     = '$role'
        WHERE user_id = $user_id
    ";
    mysqli_query($conn, $update_sql);
    echo "<script>
            alert('User updated successfully');
            window.location='manage_users.php?search=" . urlencode($search_term) . "';
          </script>";
    exit();
}

// Handle Delete User
if (isset($_GET['delete_user_id'])) {
    $del_id = intval($_GET['delete_user_id']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $del_id");
    echo "<script>
            alert('User deleted successfully');
            window.location='manage_users.php?search=" . urlencode($search_term) . "';
          </script>";
    exit();
}

// Fetch users with search filter
$sql    = "
    SELECT * FROM users 
    WHERE username LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%' 
       OR email    LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%'
    ORDER BY user_id ASC
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Users</title>
  <style>
    .main-content { margin-left: 250px; padding: 20px; }
    table { width:100%; border-collapse:collapse; background:#fff; }
    th,td { padding:12px; border-bottom:1px solid #ccc; text-align:left; }
    th { background:#f4f4f4; }
    .search-bar { margin-bottom:20px; }
    .search-bar input { padding:8px; width:200px; }
    .search-bar button { padding:8px 15px; }
    .edit-form {
      display:none;
      background:#f9f9f9;
      padding:20px;
      border-radius:8px;
      margin-top:20px;
    }
    .edit-form input, .edit-form select {
      width:100%; padding:8px; margin-bottom:10px;
    }
    .edit-form button {
      padding:10px 20px; background:#333; color:#fff; border:none; cursor:pointer;
    }
    .action-btn {
      padding:6px 12px;
      margin-right:4px;
      border:none;
      cursor:pointer;
      color:#fff;
      background:#2980b9;
      border-radius:4px;
    }
    .delete-btn {
      background:#c0392b;
    }
  </style>
  <script>
    function showEditUserForm(id, user, mail, role) {
      document.getElementById('edit-form').style.display = 'block';
      document.getElementById('user_id').value    = id;
      document.getElementById('edit_username').value = user;
      document.getElementById('edit_email').value    = mail;
      document.getElementById('edit_role').value     = role;
      document.getElementById('edit-form').scrollIntoView({ behavior: 'smooth' });
    }
  </script>
</head>
<body>

<div class="main-content">
  <h2>Manage Users</h2>

  <!-- Search Bar -->
  <div class="search-bar">
    <form method="GET" action="manage_users.php">
      <input type="text" name="search" placeholder="Search by Username or Email"
             value="<?php echo htmlspecialchars($search_term); ?>" />
      <button type="submit">Search</button>
    </form>
  </div>

  <!-- Users Table -->
  <table>
    <thead>
      <tr>
        <th>User ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?php echo $row['user_id']; ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo $row['role']; ?></td>
        <td>
          <button class="action-btn"
            onclick="showEditUserForm(
              <?php echo $row['user_id']; ?>,
              '<?php echo addslashes($row['username']); ?>',
              '<?php echo addslashes($row['email']); ?>',
              '<?php echo $row['role']; ?>'
            )">Edit</button>

          <button class="action-btn delete-btn"
            type="button"
            onclick="if(confirm('Delete this user?')) {
              window.location='?delete_user_id=<?php echo $row['user_id']; ?>&search=<?php echo urlencode($search_term); ?>';
            }">Delete</button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Inline Edit Form -->
  <div id="edit-form" class="edit-form">
    <h3>Edit User</h3>
    <form method="POST" action="manage_users.php?search=<?php echo urlencode($search_term); ?>">
      <input type="hidden" name="update_user" value="1">
      <input type="hidden" name="user_id" id="user_id">

      <label>Username:</label>
      <input type="text" name="username" id="edit_username" required>

      <label>Email:</label>
      <input type="email" name="email" id="edit_email" required>

      <label>Role:</label>
      <select name="role" id="edit_role" required>
        <option value="admin">admin</option>
        <option value="manager">manager</option>
        <option value="team_member">team_member</option>
      </select>

      <button type="submit">Update User</button>
    </form>
  </div>
</div>

</body>
</html>
