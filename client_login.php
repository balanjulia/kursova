<?php
// client_login.php — вхід клієнта через Email і пароль
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Шукаємо клієнта за Email
    $stmt = $conn->prepare("SELECT CustomerID, password FROM customers WHERE Email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Успішний вхід
            $_SESSION['user_id'] = $row['CustomerID'];
            $_SESSION['role']    = 'customer';
            header('Location: client_dashboard.php');
            exit;
        }
    }
    $err = 'Невірна пошта або пароль.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Вхід клієнта</title>
  <style>
    body{font-family:Arial,sans-serif;padding:20px;}
    form{max-width:300px;margin:auto;}
    input,button{width:100%;padding:8px;margin:5px 0;}
    .error{color:red;text-align:center;}
  </style>
</head>
<body>
  <h2>Вхід для клієнтів</h2>
  <?php if ($err): ?><p class="error"><?= htmlspecialchars($err) ?></p><?php endif; ?>
  <form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button type="submit">Увійти</button>
  </form>
  <p><a href="index.php">Повернутися до загального входу</a></p>
</body>
</html>
