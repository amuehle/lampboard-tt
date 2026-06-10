<?php
require_once "../config/database.php";

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin["password"])) {

        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["username"];

        header("Location: /admin/index.php");
        exit;

    } else {
        $error = "Invalid login credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        .login-box {
            width: 320px;
        }

        .login-box input,
        .login-box button {
            width: 100%;
            box-sizing: border-box;
        }

        .login-box input {
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body style="display:flex;justify-content:center;align-items:center;height:100vh;">

<div class="modal-content login-box">

    <h2>Admin Login</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button class="btn-come" type="submit">
            Login
        </button>

    </form>

</div>

</body>
</html>
