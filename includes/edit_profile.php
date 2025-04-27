<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
include("../includes/db.php");
include("../includes/user_sidebar.php");

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $errors   = [];

    if ($username === '') {
        $errors[] = "Username cannot be empty.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // Handle profile image upload if provided
    if (!empty($_FILES['profile_image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext     = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Image must be JPG, PNG or GIF.";
        } else {
            $newName = uniqid('prof_', true) . ".$ext";
            $dest    = __DIR__ . '/../assets/uploads/' . $newName;
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                $errors[] = "Failed to upload image.";
            } else {
                $profile_image = $newName;
            }
        }
    }

    if (empty($errors)) {
        // Build update query
        if (isset($profile_image)) {
            $upd = $conn->prepare("
                UPDATE users
                   SET username = ?, email = ?, profile_image = ?
                 WHERE user_id = ?
            ");
            $upd->bind_param("sssi", $username, $email, $profile_image, $user_id);
        } else {
            $upd = $conn->prepare("
                UPDATE users
                   SET username = ?, email = ?
                 WHERE user_id = ?
            ");
            $upd->bind_param("ssi", $username, $email, $user_id);
        }
        $upd->execute();
        // Redirect back to dashboard
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <style>
    body { font-family: Arial, sans-serif;  margin:0; padding:0 }
    .container {
      max-width: 500px; margin: 60px auto; background: whitesmoke;
      padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 { margin-top:0; color:#333; }
    .profile-img {
      display: block; margin: 0 auto 20px;
      width:100px; height:100px; border-radius:50%; object-fit:cover;
    }
    .form-group { margin-bottom:15px; }
    label { display:block; margin-bottom:5px; color:#555; }
    input[type="text"], input[type="email"], input[type="file"] {
      width:100%; padding:8px; border:1px solid #ccc;
      border-radius:4px; box-sizing:border-box;
    }
    .btn {
      display:inline-block; padding:10px 20px; background:#007bff;
      color:#fff; border:none; border-radius:4px; cursor:pointer;
      text-decoration:none; margin-right:10px;
    }
    .btn:hover { background:#0056b3; }
    .btn.gray { background:#6c757d; }
    .errors { background:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; }
    .errors li { margin-left:20px; }
    .back-link { display:block; margin-bottom:20px; color:#007bff; text-decoration:none; }
    .back-link:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <div class="container">
    <a href="../user/dashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>Edit Profile</h1>

    <?php if (!empty($errors)): ?>
      <ul class="errors">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php
      // Show current or default image
      $img = !empty($user['profile_image'])
           ? "../uploads/" . htmlspecialchars($user['profile_image'])
           : "../uploads/default.jpg";
    ?>
    <img src="<?= $img ?>" alt="Profile" class="profile-img">

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="username">Name</label>
        <input type="text"
               id="username"
               name="username"
               required
               placeholder="Enter your name"
               value="<?= htmlspecialchars($user['username']) ?>">
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email"
               id="email"
               name="email"
               required
               placeholder="Enter your email"
               value="<?= htmlspecialchars($user['email']) ?>">
      </div>

      <div class="form-group">
        <label for="profile_image">Profile Image (optional)</label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">
      </div>

      <button type="submit" class="btn">Save Changes</button>
      <a href="profile.php" class="btn gray">Cancel</a>
    </form>
  </div>
</body>
</html>
