<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');


require_once 'config.php';


$origin      = $_GET['origin']      ?? '';
$destination = $_GET['destination'] ?? '';


$cities = [];
$res = $conn->query("SELECT DISTINCT StartLocation FROM routes UNION SELECT DISTINCT EndLocation FROM routes");
while ($r = $res->fetch_row()) {
    $cities[] = $r[0];
}


$results = [];
if ($origin && $destination) {
    $o = $conn->real_escape_string($origin);
    $d = $conn->real_escape_string($destination);

    $sql = "
      SELECT
        r1.StartLocation AS from1,
        r1.EndLocation   AS via,
        r2.EndLocation   AS to2,
        r1.DriverID      AS drv1,
        r2.DriverID      AS drv2
      FROM routes AS r1
      JOIN routes AS r2
        ON r1.EndLocation = r2.StartLocation
      WHERE r1.StartLocation = '$o'
        AND r2.EndLocation   = '$d'
    ";
    $q = $conn->query($sql);
    if ($q) {
        while ($row = $q->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Пошук маршруту з пересадкою</title>
</head>
<body>
  <h1>Пошук маршруту</h1>


  <form method="GET">
    <label>
      Звідки:
      <select name="origin" required>
        <option value="">– виберіть –</option>
        <?php foreach($cities as $c): ?>
          <option value="<?=htmlspecialchars($c)?>" <?=($c===$origin?'selected':'')?>><?=htmlspecialchars($c)?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Куди:
      <select name="destination" required>
        <option value="">– виберіть –</option>
        <?php foreach($cities as $c): ?>
          <option value="<?=htmlspecialchars($c)?>" <?=($c===$destination?'selected':'')?>><?=htmlspecialchars($c)?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Пошук</button>
  </form>

  <?php if (isset($error)): ?>
    <p style="color:red;">SQL-помилка: <?=$error?></p>
  <?php elseif ($origin && $destination): ?>
    <?php if (count($results)): ?>
      <h2>Знайдено маршрутів: <?=count($results)?></h2>
      <ul>
        <?php foreach($results as $r): ?>
          <li>
            <?=$r['from1']?> → <strong><?=$r['via']?></strong> → <?=$r['to2']?>
            (водії: <?=$r['drv1']?>, <?=$r['drv2']?>)
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p style="color:gray;">Маршрут не знайдено.</p>
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>
