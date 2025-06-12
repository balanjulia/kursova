<?php
session_start();
require_once 'config.php';

$col_from = 'StartLocation';
$col_to   = 'EndLocation';
$col_id   = 'RouteID';

$from = $_POST['from'] ?? '';
$to   = $_POST['to']   ?? '';

$direct       = [];
$withTransfer = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $from && $to) {
    $sql1 = "
      SELECT * 
      FROM routes 
      WHERE `$col_from` = ? 
        AND `$col_to`   = ?
    ";
    $stmt = $conn->prepare($sql1);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $direct = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($direct)) {
        $sql2 = "
          SELECT
            r1.`$col_id`   AS leg1_id,
            r1.`$col_from` AS leg1_from,
            r1.`$col_to`   AS leg1_to,
            r2.`$col_id`   AS leg2_id,
            r2.`$col_from` AS leg2_from,
            r2.`$col_to`   AS leg2_to
          FROM routes AS r1
          JOIN routes AS r2
            ON r1.`$col_to` = r2.`$col_from`
          WHERE r1.`$col_from` = ? 
            AND r2.`$col_to`   = ?
        ";
        $stmt2 = $conn->prepare($sql2);
        if (!$stmt2) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt2->bind_param("ss", $from, $to);
        $stmt2->execute();
        $withTransfer = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Пошук маршруту</title>
</head>
<body>
  <h1>Пошук маршруту</h1>
  <form method="post">
    <label>Звідки:
      <input name="from" value="<?= htmlspecialchars($from) ?>" placeholder="Львів" required>
    </label>
    <label>Куди:
      <input name="to"   value="<?= htmlspecialchars($to)   ?>" placeholder="Одеса" required>
    </label>
    <button type="submit">Знайти</button>
  </form>

  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <?php if (!empty($direct)): ?>
      <h2>Прямий маршрут</h2>
      <ul>
        <?php foreach($direct as $r): ?>
          <li>
            <?= "{$r[$col_from]} → {$r[$col_to]} (рейс №{$r[$col_id]})" ?>
          </li>
        <?php endforeach; ?>
      </ul>

    <?php elseif (!empty($withTransfer)): ?>
      <h2>Маршрут з однією пересадкою</h2>
      <ul>
        <?php foreach($withTransfer as $r): ?>
          <li>
            Етап 1: <?= "{$r['leg1_from']} → {$r['leg1_to']} (рейс №{$r['leg1_id']})" ?><br>
            Етап 2: <?= "{$r['leg2_from']} → {$r['leg2_to']} (рейс №{$r['leg2_id']})" ?>
          </li>
        <?php endforeach; ?>
      </ul>

    <?php else: ?>
      <p>Маршрут не знайдено.</p>
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>
