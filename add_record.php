<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['table'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$table = $_GET['table'];

$columnsRes = $conn->query("SHOW COLUMNS FROM `$table`");

$fields = [];
foreach ($columnsRes as $col) {
    if ($col['Extra'] === 'auto_increment') continue;
    $fields[] = $col['Field'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $types = str_repeat('s', count($fields));
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $fieldList = '`' . implode('`,`', $fields) . '`';
    $values = [];

    foreach ($fields as $f) {
        $values[] = $_POST[$f] ?? '';
    }

    $sql = "INSERT INTO `$table` ($fieldList) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    header("Location: admin_dashboard.php?table=" . urlencode($table) . "&success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Додати запис</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #ecf0f1; padding: 20px; }
    .container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input[type="text"], input[type="email"], input[type="number"] {
      width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;
    }
    button {
      margin-top: 20px;
      padding: 10px 15px;
      background-color: #2ecc71;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover { background-color: #27ae60; }
    a.back { display: inline-block; margin-top: 15px; color: #3498db; text-decoration: none; }
    a.back:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Додати новий запис у «<?= htmlspecialchars($table) ?>»</h2>
    <form method="post">
      <?php foreach ($fields as $field): ?>
        <label for="<?= htmlspecialchars($field) ?>"><?= htmlspecialchars($field) ?>:</label>
        <input type="text" name="<?= htmlspecialchars($field) ?>" id="<?= htmlspecialchars($field) ?>" required>
      <?php endforeach; ?>
      <button type="submit">Зберегти</button>
    </form>
    <a href="admin_dashboard.php?table=<?= urlencode($table) ?>" class="back">← Назад до таблиці</a>
  </div>
</body>
</html>
