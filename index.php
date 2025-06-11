<?php
// index.php — універсальна сторінка входу для адміністратора, клієнта та водія
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$loginError = '';
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? '';
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');

    switch ($loginType) {
        case 'admin':
            $table     = 'admins';
            $userField = 'username';
            $passField = 'password';
            $idField   = 'id';
            $redirect  = 'admin_dashboard.php';
            break;
        case 'customer':
            $table     = 'customers';
            $userField = 'Email';
            $passField = 'password';
            $idField   = 'CustomerID';
            $redirect  = 'client_dashboard.php';
            break;
        case 'driver':
            $table     = 'drivers';
            $userField = 'email';
            $passField = 'password';
            $idField   = 'DriverID';
            $redirect  = 'driver_dashboard.php';
            break;
        default:
            $loginError = 'Будь ласка, виберіть тип користувача.';
    }

    if (!$loginError) {
        $sql  = "SELECT `$idField`, `$passField` FROM `$table` WHERE `$userField` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row[$passField])) {
                $_SESSION['user_id'] = $row[$idField];
                $_SESSION['role']    = $loginType;
                header("Location: $redirect");
                exit;
            } else {
                $loginError = 'Невірний пароль.';
            }
        } else {
            $loginError = 'Користувача не знайдено.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
        }
        .description-box {
            background: rgba(255, 255, 255, 0.75);
            padding: 15px 20px;
            border-radius: 6px;
            max-width: 600px;
            margin: 0 auto 20px;
            color: #333;
            text-align: center;
            line-height: 1.5;
        }
        form {
            background: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 8px;
            max-width: 320px;
            margin: 20px auto;
        }
        label { display: inline-block; margin-right: 10px; }
        input, button {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .error {
            color: yellow;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .register-link a:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <h1>Транспортно-логістичне підприємство</h1>
    <div class="description-box">
        Ласкаво просимо до Транспортно-логістичного підприємства — вашого рішення для
        ефективного планування маршрутів, відстеження вантажів і контролю за доставкою в режимі реального часу.
        Розпочніть роботу, обравши свою роль, і отримайте доступ до потрібних інструментів!
    </div>

    <form method="post">
        <div style="text-align:center; margin-bottom:15px;">
            <label><input type="radio" name="login_type" value="admin" required> Адміністратор</label>
            <label style="margin-left:10px;"><input type="radio" name="login_type" value="customer"> Клієнт</label>
            <label style="margin-left:10px;"><input type="radio" name="login_type" value="driver"> Водій</label>
        </div>
        <input type="text" name="username" placeholder="Логін або Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Увійти</button>
    </form>

    <?php if ($loginError): ?>
        <p class="error"><?= htmlspecialchars($loginError) ?></p>
    <?php endif; ?>

    <div class="register-link">
        <a href="register_customer.php">Реєстрація клієнта</a>
    </div>
</body>
</html>
