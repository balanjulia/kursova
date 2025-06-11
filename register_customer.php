<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name']);
    $email            = trim($_POST['email']);
    $password         = trim($_POST['password']);
    $contactnumber    = trim($_POST['contactnumber']);
    $address          = trim($_POST['address']);

    if (empty($name) || empty($email) || empty($password) || empty($contactnumber) || empty($address)) {
        $error = 'Усі поля обовʼязкові.';
    } elseif (!preg_match('/^\+?\d+$/', $contactnumber)) {
        $error = 'Телефон має містити лише цифри та необов’язковий + на початку.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO customers 
                (name, email, password, Contactnumber, address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssss",
            $name,
            $email,
            $hashedPassword,
            $contactnumber,
            $address
        );

        if ($stmt->execute()) {
            $success = 'Реєстрація успішна! Тепер можете увійти.';
        } else {
            $error = 'Користувач з такою поштою вже існує.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація клієнта</title>
    <style>
        body { font-family: Arial, sans-serif; background: #ecf0f1; padding: 40px; }
        .container { max-width: 400px; margin: auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; color: #2c3e50; }
        input, button { width: 100%; padding: 10px; margin-bottom: 12px; border-radius: 4px; }
        input { border: 1px solid #ccc; }
        button { background: #3498db; color: #fff; border: none; cursor: pointer; }
        .message { margin-top: 10px; padding: 10px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error   { background-color: #f8d7da; color: #721c24; }
        a { display: block; margin-top: 15px; text-align: center; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Реєстрація клієнта</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text"
               name="name"
               placeholder="Ім’я"
               required>

        <input type="email"
               name="email"
               placeholder="Email"
               required>

        <input type="text"
               name="contactnumber"
               placeholder="Телефон (наприклад: +380123456789)"
               pattern="^\+?\d+$"
               title="Лише цифри та + на початку"
               required>

        <input type="text"
               name="address"
               placeholder="Адреса"
               required>

        <input type="password"
               name="password"
               placeholder="Пароль"
               required>

        <button type="submit">Зареєструватися</button>
    </form>

    <a href="index.php">Повернутись на головну</a>
</div>
</body>
</html>
