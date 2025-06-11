<?php
// driver_dashboard.php — особистий кабінет водія з переглядом та редагуванням інформації й пароля
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Перевірка ролі
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: driver_login.php');
    exit;
}

$driverId    = $_SESSION['user_id'];
$errors      = [];
$success_msg = '';

// Обробка POST-запиту на оновлення профілю чи пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Оновлення інформації
    if (isset($_POST['update_info'])) {
        $fields    = [];
        $params    = [];
        $types     = '';
        $updatable = ['Name', 'Phone', 'Email', 'Vehicle']; // приклад полів
        foreach ($updatable as $col) {
            if (isset($_POST[$col])) {
                $fields[] = "`$col` = ?";
                $params[] = trim($_POST[$col]);
                $types   .= 's';
            }
        }
        if ($fields) {
            $params[] = $driverId;
            $types   .= 'i';
            $sql      = "UPDATE `drivers` SET " . implode(', ', $fields) . " WHERE DriverID = ?";
            $stmt     = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success_msg = 'Профіль оновлено.';
            } else {
                $errors[] = 'Помилка оновлення профілю: ' . $stmt->error;
            }
        }
    }
    // Зміна пароля
    if (isset($_POST['change_pass'])) {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        // Отримуємо поточний хеш
        $stmt = $conn->prepare("SELECT password FROM drivers WHERE DriverID = ?");
        $stmt->bind_param('i', $driverId);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['password'];
        if (!password_verify($old, $hash)) {
            $errors[] = 'Старий пароль невірний.';
        } elseif (strlen($new) < 6) {
            $errors[] = 'Новий пароль має бути не менше 6 символів.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $u = $conn->prepare("UPDATE drivers SET password = ? WHERE DriverID = ?");
            $u->bind_param('si', $newHash, $driverId);
            if ($u->execute()) {
                $success_msg = 'Пароль змінено успішно.';
            } else {
                $errors[] = 'Помилка зміни пароля: ' . $u->error;
            }
        }
    }
}

// Отримуємо дані водія
$stmt = $conn->prepare("SELECT Name, Phone, Email, Vehicle FROM drivers WHERE DriverID = ?");
$stmt->bind_param('i', $driverId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Кабінет водія</title>
  <style>
    body{font-family:Arial,sans-serif;padding:20px;max-width:600px;margin:auto;}
    fieldset{margin-bottom:30px;padding:15px;border:1px solid #ccc;}
    legend{font-weight:bold;}
    label{display:block;margin:8px 0;}
    input{width:100%;padding:8px;margin-bottom:10px;}
    button{padding:10px 15px;}
    .error{color:red;} .success{color:green;}
    .logout{position:absolute;top:20px;right:20px;}
  </style>
</head>
<body>
  <h1>Кабінет водія</h1>
  <a class="logout" href="index.php?logout=1">Вийти</a>

  <?php if ($success_msg): ?><p class="success"><?= htmlspecialchars($success_msg) ?></p><?php endif; ?>
  <?php foreach ($errors as $e): ?><p class="error"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

  <fieldset>
    <legend>Редагування профілю</legend>
    <form method="post">
      <input type="hidden" name="update_info" value="1">
      <label>Ім'я:<input type="text" name="Name" value="<?= htmlspecialchars($row['Name']) ?>" required></label>
      <label>Телефон:<input type="text" name="Phone" value="<?= htmlspecialchars($row['Phone']) ?>" required></label>
      <label>Email:<input type="email" name="Email" value="<?= htmlspecialchars($row['Email']) ?>" required></label>
      <label>Автомобіль:<input type="text" name="Vehicle" value="<?= htmlspecialchars($row['Vehicle']) ?>" required></label>
      <button type="submit">Зберегти</button>
    </form>
  </fieldset>

  <fieldset>
    <legend>Зміна пароля</legend>
    <form method="post">
      <input type="hidden" name="change_pass" value="1">
      <label>Старий пароль:<input type="password" name="old_password" required></label>
      <label>Новий пароль:<input type="password" name="new_password" required></label>
      <button type="submit">Змінити пароль</button>
    </form>
  </fieldset>
</body>
</html>
