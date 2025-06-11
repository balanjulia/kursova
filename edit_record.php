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

// Перевірка параметрів
if (!isset($_GET['table'], $_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$table = $_GET['table'];
$id = $_GET['id'];

// Отримання первинного ключа
$pkRes = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
$pkRow = $pkRes->fetch_assoc();
$pk = $pkRow['Column_name'];

// Отримання поточного запису
$stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    echo "Запис не знайдено.";
    exit;
}

// Обробка редагування
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fieldsRes = $conn->query("SHOW COLUMNS FROM `$table`");
    $fields = [];
    $types = '';
    $values = [];

    while ($col = $fieldsRes->fetch_assoc()) {
        if ($col['Extra'] === 'auto_increment') continue;
        $f = $col['Field'];
        $fields[] = "`$f` = ?";
        $types .= 's';
        $values[] = $_POST[$f] ?? '';
    }

    $values[] = $id;
    $types .= 'i';

    $sql = "UPDATE `$table` SET " . implode(',', $fields) . " WHERE `$pk` = ?";
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
  <title>Редагування запису</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #ecf0f1;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    h2 {
      margin-top: 0;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input[type="text"], input[type="email"], input[type="number"] {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      margin-top: 20px;
      padding: 10px 15px;
      background-color: #27ae60;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #219150;
    }
    a.back {
      display: inline-block;
      margin-top: 15px;
      color: #3498db;
      text-decoration: none;
    }
    a.back:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Редагування запису в таблиці: <?= htmlspecialchars($table) ?></h2>
    <form method="post">
      <?php foreach ($row as $field => $value): ?>
        <?php if ($field === $pk) continue; ?>
        <label for="<?= htmlspecialchars($field) ?>"><?= htmlspecialchars($field) ?>:</label>
        <input
          type="text"
          name="<?= htmlspecialchars($field) ?>"
          id="<?= htmlspecialchars($field) ?>"
          value="<?= htmlspecialchars($value) ?>"
          required
        >
      <?php endforeach; ?>
      <button type="submit">Зберегти зміни</button>
    </form>
    <a href="admin_dashboard.php?table=<?= urlencode($table) ?>" class="back">← Назад до таблиці</a>
  </div>
</body>
</html>
