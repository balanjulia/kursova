<?php
// client_dashboard.php — особистий кабінет клієнта з редагуванням та пошуком маршрутів
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

// --- Оновлення профілю і пароль — без змін ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    // ... ваш код оновлення профілю ...
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
    // ... ваш код зміни пароля ...
}
$stmt = $conn->prepare("SELECT Name, ContactNumber, Email, Address FROM customers WHERE CustomerID = ?");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// --- Нова частина: пошук маршрутів із 1 пересадкою ---
$from    = $_GET['from']        ?? '';
$to      = $_GET['to']          ?? '';
$routes  = [];
$error   = '';

// Щоб у формі були варіанти міст, читаємо всі унікальні
$cities = [];
$res = $conn->query("
    SELECT DISTINCT StartLocation AS city FROM routes
    UNION
    SELECT DISTINCT EndLocation FROM routes
");
while ($r = $res->fetch_assoc()) {
    $cities[] = $r['city'];
}

// Якщо вказано обидва параметри — шукаємо дволосний маршрут
if ($from && $to) {
    $stmt = $conn->prepare(
        "SELECT
           r1.StartLocation AS from1,
           r1.EndLocation   AS via,
           r2.EndLocation   AS to2,
           d1.Name          AS drv1_name,
           d1.ContactNumber AS drv1_contact,
           d2.Name          AS drv2_name,
           d2.ContactNumber AS drv2_contact
         FROM routes AS r1
         JOIN routes AS r2
           ON r1.EndLocation = r2.StartLocation
         JOIN drivers AS d1
           ON d1.DriverID = r1.DriverID
         JOIN drivers AS d2
           ON d2.DriverID = r2.DriverID
         WHERE r1.StartLocation = ?
           AND r2.EndLocation   = ?"
    );
    $stmt->bind_param('ss', $from, $to);
    if ($stmt->execute()) {
        $routes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = $stmt->error;
    }
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
    input, select { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
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
        <label>Телефон:<input type="text" name="ContactNumber" value="<?= htmlspecialchars($row['ContactNumber'] ?? '') ?>" required pattern="^\+?\d+$" title="Лише цифри та +"></label>
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
        <label>
          Звідки:
          <select name="from" required>
            <option value="">– виберіть –</option>
            <?php foreach($cities as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"
                <?= $c === $from ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Куди:
          <select name="to" required>
            <option value="">– виберіть –</option>
            <?php foreach($cities as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"
                <?= $c === $to ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit">Шукати</button>
      </form>

      <?php if (isset($error) && $error): ?>
        <p class="error">SQL-помилка: <?= htmlspecialchars($error) ?></p>
      <?php elseif ($from && $to): ?>
        <div class="search-results">
          <?php if (count($routes)): ?>
            <table>
              <tr>
                <th>Маршрут</th>
                <th>Водій 1</th><th>Контакт 1</th>
                <th>Водій 2</th><th>Контакт 2</th>
              </tr>
              <?php foreach($routes as $r): ?>
                <tr>
                  <td><?= htmlspecialchars("{$r['from1']} → {$r['via']} → {$r['to2']}") ?></td>
                  <td><?= htmlspecialchars($r['drv1_name']) ?></td>
                  <td><?= htmlspecialchars($r['drv1_contact']) ?></td>
                  <td><?= htmlspecialchars($r['drv2_name']) ?></td>
                  <td><?= htmlspecialchars($r['drv2_contact']) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php else: ?>
            <p class="error">Маршрут не знайдено.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </fieldset>

  </div>
</body>
</html>
