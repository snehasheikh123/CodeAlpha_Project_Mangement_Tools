<?php
session_start();
include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm"];

    // Basic validation
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Insert new user with default role 'team_member'
            $hash = hash("sha256", $password);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'team_member')");
            $stmt->bind_param("sss", $username, $email, $hash);
            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    :root {
      --primary: #6C5CE7;
      --primary-dark: #341f97;
      --accent: #00B894;
      --bg: #F0F4F8;
      --text: #2D3436;
    }

    /* Reset & Layout */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Card */
    .container {
      background: #fff;
      border-radius: 12px;
      padding: 36px 28px;
      width: 30%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      animation: fadeIn 0.5s ease-out both;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    h2 {
      text-align: center;
      color: var(--primary-dark);
      font-size: 24px;
      margin-bottom: 24px;
    }

    /* Form Fields */
    .input-group {
      margin-bottom: 16px;
    }
    .input-group label {
      display: block;
      margin-bottom: 6px;
      font-size: 14px;
      color: var(--text);
    }
    .input-group input {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    .input-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(108,92,231,0.2);
    }

    /* Submit Button */
    .btn {
      width: 100%;
      padding: 12px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }
    .btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    /* Error Message */
    .error {
      background: #FFE6E6;
      color: #D63031;
      padding: 8px;
      border-radius: 6px;
      margin-bottom: 16px;
      text-align: center;
      font-size: 14px;
    }

    /* Footer Link */
    .footer {
      text-align: center;
      margin-top: 14px;
      font-size: 14px;
      color: var(--text);
    }
    .footer a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
      transition: text-decoration 0.2s;
    }
    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="input-group">
        <label for="username">👤 Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="input-group">
        <label for="email"> 📧 Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="input-group">
        <label for="password">🔒 Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="input-group">
        <label for="confirm"> 🔒 Confirm Password</label>
        <input type="password" id="confirm" name="confirm" required>
      </div>
      <div class="input-group">

      <label for="profile_image">🖼️ Profile Image (optional)</label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">
      </div>
      <button type="submit" class="btn">Register</button>
    </form>
    <div class="footer">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>
</body>
</html>
