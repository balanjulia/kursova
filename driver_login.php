<?php
// driver_login.php — вхід для водіїв за Email і паролем
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Шукаємо водія за Email
    $stmt = $conn->prepare("SELECT DriverID, password FROM drivers WHERE Email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Успішний вхід
            $_SESSION['user_id'] = $row['DriverID'];
            $_SESSION['role']    = 'driver';
            header('Location: driver_dashboard.php');
            exit;
        }
    }
    $error = 'Невірна пошта або пароль.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід для водіїв</title>
    <style>
        body{font-family:Arial,sans-serif;padding:20px;}
        form{max-width:300px;margin:auto;}
        input,button{width:100%;padding:8px;margin:5px 0;}
        .error{color:red;text-align:center;}
    </style>
</head>
<body>
    <h2>Вхід для водіїв</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif;?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Увійти</button>
    </form>
    <p><a href="index.php">Повернутися до основного входу</a></p>
</body>
</html>
