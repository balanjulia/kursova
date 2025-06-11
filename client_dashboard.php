<?php
// client_dashboard.php — особистий кабінет клієнта з редагуванням та пошуком водіїв
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Перевірка ролі
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: client_login.php');
    exit;
}

$customerId = $_SESSION['user_id'];
$errors     = [];
$success    = '';

// Оновлення профілю
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $fields = [];
    $params = [];
    $types  = '';
    foreach (['Name','ContactNumber','Email','Address'] as $col) {
        $fields[] = "`$col` = ?";
        $params[] = trim($_POST[$col] ?? '');
        $types   .= 's';
    }
    $params[] = $customerId;
    $types   .= 'i';
    $sql      = "UPDATE customers SET " . implode(', ', $fields) . " WHERE CustomerID = ?";
    $stmt     = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $success = 'Профіль оновлено.';
    } else {
        $errors[] = 'Помилка оновлення профілю: ' . $stmt->error;
    }
}

// Зміна пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $stmt = $conn->prepare("SELECT password FROM customers WHERE CustomerID = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'];

    if (!password_verify($old, $hash)) {
        $errors[] = 'Старий пароль невірний.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'Новий пароль має містити мінімум 6 символів.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upStmt  = $conn->prepare("UPDATE customers SET password = ? WHERE CustomerID = ?");
        $upStmt->bind_param('si', $newHash, $customerId);
        if ($upStmt->execute()) {
            $success = 'Пароль успішно змінено.';
        } else {
            $errors[] = 'Помилка зміни пароля: ' . $upStmt->error;
        }
    }
}

// Отримуємо дані клієнта
$stmt = $conn->prepare("SELECT Name, ContactNumber, Email, Address FROM customers WHERE CustomerID = ?");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// Параметри пошуку водіїв
$from    = $_GET['from'] ?? '';
$to      = $_GET['to']   ?? '';
$drivers = [];

if ($from && $to) {
    $search = $conn->prepare(
        "SELECT d.Name, d.ContactNumber, r.StartLocation, r.EndLocation
         FROM drivers d
         JOIN routes r ON d.DriverID = r.DriverID
         WHERE r.StartLocation = ? AND r.EndLocation = ?"
    );
    $search->bind_param('ss', $from, $to);
    $search->execute();
    $drivers = $search->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Кабінет клієнта</title>
  <style>
    body { background:#f0f2f5; font-family:Arial,sans-serif; margin:0; padding:0; }
    .header { background:#4a90e2; color:#fff; padding:20px; text-align:center; position:relative; }
    .logout { position:absolute; right:20px; top:20px; color:#fff; text-decoration:none; }
    .container { max-width:800px; margin:30px auto; padding:0 20px; }
    fieldset { background:#fff; border:1px solid #ddd; border-radius:4px; margin-bottom:20px; padding:20px; }
    legend { font-weight:bold; }
    label { display:block; margin:10px 0 5px; }
    input { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
    button { padding:10px 15px; background:#4a90e2; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    .error { color:#e74c3c; }
    .success { color:#27ae60; }
    .search-form { margin-bottom:15px; }
    .search-results table { width:100%; border-collapse:collapse; margin-top:10px; }
    .search-results th, .search-results td { border:1px solid #ccc; padding:8px; text-align:left; }
    .search-results th { background:#e0e4e8; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Кабінет клієнта</h1>
    <a href="index.php?logout=1" class="logout">Вийти</a>
  </div>
  <div class="container">
    <?php if ($success): ?>
      <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?>
      <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <fieldset>
      <legend>Профіль</legend>
      <form method="post">
        <input type="hidden" name="update_info" value="1">
        <label>Ім'я:<input type="text" name="Name" value="<?= htmlspecialchars($row['Name']) ?>" required></label>
        <label>Телефон:<input type="text" name="ContactNumber" value="<?= htmlspecialchars($row['ContactNumber']) ?>" required></label>
        <label>Email:<input type="email" name="Email" value="<?= htmlspecialchars($row['Email']) ?>" required></label>
        <label>Адреса:<input type="text" name="Address" value="<?= htmlspecialchars($row['Address']) ?>" required></label>
        <button type="submit">Зберегти зміни</button>
      </form>
    </fieldset>

    <fieldset>
      <legend>Змінити пароль</legend>
      <form method="post">
        <input type="hidden" name="change_pass" value="1">
        <label>Старий пароль:<input type="password" name="old_password" required></label>
        <label>Новий пароль:<input type="password" name="new_password" required></label>
        <button type="submit">Змінити пароль</button>
      </form>
    </fieldset>

    <fieldset>
      <legend>Пошук водіїв</legend>
      <form method="get" class="search-form">
        <label>Звідки:<input type="text" name="from" value="<?= htmlspecialchars($from) ?>"></label>
        <label>Куди:<input type="text" name="to" value="<?= htmlspecialchars($to) ?>"></label>
        <button type="submit">Шукати</button>
      </form>
      <?php if ($from && $to): ?>
        <div class="search-results">
          <?php if (count($drivers)): ?>
            <table>
              <tr><th>Водій</th><th>Телефон</th><th>Звідки</th><th>Куди</th></tr>
              <?php foreach ($drivers as $d): ?>
                <tr>
                  <td><?= htmlspecialchars($d['Name']) ?></td>
                  <td><?= htmlspecialchars($d['ContactNumber']) ?></td>
                  <td><?= htmlspecialchars($d['StartLocation']) ?></td>
                  <td><?= htmlspecialchars($d['EndLocation']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php else: ?>
            <p>Водіїв не знайдено.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </fieldset>
  </div>
</body>
</html>
