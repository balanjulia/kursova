<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Авторизація
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Якщо таблиця не обрана — показати список таблиць
if (!isset($_GET['table'])) {
    $res = $conn->query("SHOW TABLES");
    ?>
    <!DOCTYPE html>
    <html lang="uk">
    <head>
      <meta charset="UTF-8">
      <title>Панель адміністратора</title>
      <style>
        body { font-family: Arial, sans-serif; background-color: #ecf0f1; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; }
        .table-link {
          display: block;
          padding: 12px;
          margin: 8px 0;
          background: #3498db;
          color: white;
          text-decoration: none;
          border-radius: 4px;
          text-align: center;
          font-size: 16px;
          font-weight: bold;
        }
        .table-link:hover { background: #2980b9; }
        .menu-link {
          display: inline-block;
          margin-bottom: 15px;
          padding: 10px 15px;
          background-color: #e74c3c;
          color: white;
          text-decoration: none;
          border-radius: 4px;
          font-weight: bold;
        }
        .menu-link:hover {
          background-color: #c0392b;
        }
      </style>
    </head>
    <body>
      <div class="container">
        <h1>Панель адміністратора</h1>
        <a href="index.php" class="menu-link">← На головне меню</a>
        <?php while ($row = $res->fetch_array()): ?>
          <a class="table-link" href="?table=<?= htmlspecialchars($row[0]) ?>"><?= htmlspecialchars($row[0]) ?></a>
        <?php endwhile; ?>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Якщо обрана таблиця ---
$table = $_GET['table'];
$pkRes = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
$pkRow = $pkRes->fetch_assoc();
$pk = $pkRow['Column_name'];

// Обробка POST-запитів (видалення)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table  = $_POST['table']  ?? '';
    $pk     = $_POST['pk']     ?? '';
    $id     = $_POST['id']     ?? '';

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header("Location: admin_dashboard.php?table=" . urlencode($table));
        exit;
    }
}

// Отримати записи з таблиці
$dataRes = $conn->query("SELECT * FROM `$table`");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Адмін-панель: <?= htmlspecialchars($table) ?></title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #ecf0f1; margin: 0; padding: 20px; }
    .container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background-color: #3498db; color: #fff; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    a.button, button {
      padding: 6px 12px;
      background-color: #3498db;
      color: white;
      border-radius: 4px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      display: inline-block;
    }
    a.button:hover, button:hover { background-color: #2980b9; }
    .add-button {
      background-color: #2ecc71;
    }
    .add-button:hover {
      background-color: #27ae60;
    }
  </style>
  <script>
    function confirmDelete() {
      return confirm('Дійсно видалити запис?');
    }
  </script>
</head>
<body>
  <div class="container">
    <?php if (isset($_GET['success'])): ?>
      <div style="color: green; margin-bottom: 10px;">✅ Дані успішно змінені!</div>
    <?php endif; ?>

    <h1>Таблиця: <?= htmlspecialchars($table) ?></h1>

    <a class="button add-button" href="add_record.php?table=<?= urlencode($table) ?>" style="margin-bottom: 15px;">➕ Додати новий запис</a>

    <table>
      <tr>
        <?php foreach ($dataRes->fetch_fields() as $c): ?>
          <th><?= htmlspecialchars($c->name) ?></th>
        <?php endforeach; ?>
        <th>Дії</th>
      </tr>
      <?php foreach ($dataRes as $row): ?>
        <tr>
          <?php foreach ($row as $v): ?>
            <td><?= htmlspecialchars($v) ?></td>
          <?php endforeach; ?>
          <td>
            <a class="button" href="edit_record.php?table=<?= urlencode($table) ?>&id=<?= urlencode($row[$pk]) ?>">Редагувати</a>
            <form method="post" style="display:inline;" onsubmit="return confirmDelete();">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
              <input type="hidden" name="pk" value="<?= htmlspecialchars($pk) ?>">
              <input type="hidden" name="id" value="<?= htmlspecialchars($row[$pk]) ?>">
              <button type="submit">Видалити</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <br>
    <a class="button" href="admin_dashboard.php">← Назад до списку таблиць</a>
    <a class="button" href="index.php" style="background-color:#e74c3c;">← Вихід на головне меню</a>
  </div>
</body>
</html>
