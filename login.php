<?php
session_start();
include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if ($user["password"] === hash("sha256", $password)) {
            $_SESSION["user_id"]   = $user["user_id"];
            $_SESSION["username"]  = $user["username"];
            $_SESSION["role"]      = $user["role"];
            // Redirect by role...
            if ($user["role"] === "admin") {
                header("Location: admin/admin_dashboard.php");
            } elseif ($user["role"] === "manager") {
                header("Location: manager/manager_dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #6C5CE7;
      --primary-dark: #341f97;
      --accent: #00B894;
      --fg: #2D3436;
      --card-bg: rgba(255,255,255,0.85);
    }
    * { box-sizing: border-box; margin:0; padding:0; }
    body, html {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      color: var(--fg);
      background: url('assets/images/login.jpg') no-repeat center center/cover;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .container {
      background: var(--card-bg);
      padding: 36px 28px;
      border-radius: 12px;
      width: 360px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      animation: slideIn 0.5s ease-out both;
    }
    @keyframes slideIn {
      from { opacity:0; transform: translateY(-20px); }
      to   { opacity:1; transform: translateY(0); }
    }
    h2 {
      text-align: center;
      margin-bottom: 24px;
      color: var(--primary-dark);
    }
    .input-group {
      margin-bottom: 16px;
    }
    .input-group label {
      display: block;
      margin-bottom: 6px;
      font-size: 14px;
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
    .error {
      background: #FFE6E6;
      color: #D63031;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .footer {
      text-align: center;
      margin-top: 14px;
      font-size: 14px;
    }
    .footer a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
    }
    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🔑 Log In</h2>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="input-group">
        <label for="email">📧 Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="input-group">
        <label for="password">🔒 Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn">Login 🚀</button>
    </form>
    <div class="footer">
      Don’t have an account? <a href="register.php">Sign up here</a>
    </div>
  </div>
</body>
</html>
