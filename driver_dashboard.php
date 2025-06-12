<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'driver') {
    header('Location: driver_login.php');
    exit;
}

$driverId    = $_SESSION['user_id'];
$errors      = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = "driver_{$driverId}.{$ext}";
        $dest = __DIR__ . "/uploads/" . $newName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $pstmt = $conn->prepare("UPDATE drivers SET Photo = ? WHERE DriverID = ?");
            $pstmt->bind_param('si', $newName, $driverId);
            $pstmt->execute();
            $success_msg = 'Фото оновлено.';
        } else {
            $errors[] = 'Не вдалося завантажити фото.';
        }
    }
    if (isset($_POST['update_info'])) {
        $fields    = [];
        $params    = [];
        $types     = '';
        $updatable = ['Name','LicenseNumber','ContactNumber','Email','Status'];
        foreach ($updatable as $col) {
            if (isset($_POST[$col])) {
                $fields[]  = "`$col` = ?";
                $params[]  = trim($_POST[$col]);
                $types    .= 's';
            }
        }
        if ($fields) {
            $params[] = $driverId;
            $types   .= 'i';
            $sql      = "UPDATE drivers SET " . implode(', ', $fields) . " WHERE DriverID = ?";
            $stmt     = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success_msg = 'Профіль успішно оновлено.';
            } else {
                $errors[] = 'Помилка оновлення профілю: ' . $stmt->error;
            }
        }
    }
    if (isset($_POST['update_route'])) {
        $start = trim($_POST['start_location'] ?? '');
        $end   = trim($_POST['end_location']   ?? '');
        $dist  = (int)($_POST['distance']      ?? 0);
        $time  = $_POST['estimated_time']      ?? '';
        if (!$start || !$end) {
            $errors[] = 'Поля Звідки та Куди обов\'язкові.';
        } else {
            $upd = $conn->prepare(
                "UPDATE routes SET StartLocation = ?, EndLocation = ?, Distance = ?, EstimatedTime = ? WHERE DriverID = ?"
            );
            $upd->bind_param('ssisi', $start, $end, $dist, $time, $driverId);
            if ($upd->execute()) {
                $success_msg = 'Маршрут успішно оновлено.';
            } else {
                $errors[] = 'Помилка оновлення маршруту: ' . $upd->error;
            }
        }
    }
    if (isset($_POST['change_pass'])) {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $stmt = $conn->prepare("SELECT password FROM drivers WHERE DriverID = ?");
        $stmt->bind_param('i', $driverId);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['password'];
        if (!password_verify($old, $hash)) {
            $errors[] = 'Старий пароль невірний.';
        } elseif (strlen($new) < 6) {
            $errors[] = 'Новий пароль повинен бути не менше 6 символів.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $u = $conn->prepare("UPDATE drivers SET password = ? WHERE DriverID = ?");
            $u->bind_param('si', $newHash, $driverId);
            if ($u->execute()) {
                $success_msg = 'Пароль успішно змінено.';
            } else {
                $errors[] = 'Помилка зміни пароля: ' . $u->error;
            }
        }
    }
}

$stmt = $conn->prepare("SELECT Name, LicenseNumber, ContactNumber, Email, Status, Photo FROM drivers WHERE DriverID = ?");
$stmt->bind_param('i', $driverId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$rstmt = $conn->prepare("SELECT StartLocation, EndLocation, Distance, EstimatedTime FROM routes WHERE DriverID = ? LIMIT 1");
$rstmt->bind_param('i', $driverId);
$rstmt->execute();
$route = $rstmt->get_result()->fetch_assoc() ?: ['StartLocation'=>'','EndLocation'=>'','Distance'=>0,'EstimatedTime'=>'00:00:00'];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Кабінет водія</title>
  <style>
    body {font-family:Arial,sans-serif; max-width:600px; margin:20px auto; padding:0 20px;}
    .logout {float:right;}
    fieldset {margin-bottom:30px; padding:15px; border:1px solid #ccc;}
    legend {font-weight:bold;}
    label {display:block; margin:8px 0;}
    input, select {width:100%; padding:8px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;}
    button {padding:10px 15px; border:none; border-radius:4px; background:#4a90e2; color:#fff; cursor:pointer;}
    .error {color:red;}
    .success {color:green;}
    .photo-preview img {max-width:150px; border-radius:4px;}
  </style>
</head>
<body>
  <h1>Кабінет водія</h1>
  <a href="index.php?logout=1" class="logout">Вийти</a>

  <?php if ($success_msg): ?><p class="success"><?= htmlspecialchars($success_msg) ?></p><?php endif; ?>
  <?php foreach ($errors as $e): ?><p class="error"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

  <fieldset>
    <legend>Фото профілю</legend>
    <div class="photo-preview">
      <?php if (!empty($row['Photo'])): ?>
        <img src="uploads/<?= htmlspecialchars($row['Photo']) ?>" alt="Photo">
      <?php else: ?>
        <p>Фото не завантажено.</p>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <label>Оновити фото:
          <input type="file" name="photo" accept="image/*">
        </label>
        <button type="submit">Завантажити</button>
      </form>
    </div>
  </fieldset>

  <fieldset>
    <legend>Редагування профілю</legend>
    <form method="post">
      <input type="hidden" name="update_info" value="1">
      <label>Ім'я:
        <input type="text" name="Name" value="<?= htmlspecialchars($row['Name']) ?>" required>
      </label>
      <label>Номер посвідчення:
        <input type="text" name="LicenseNumber" value="<?= htmlspecialchars($row['LicenseNumber']) ?>" required>
      </label>
      <label>Контактний номер:
        <input type="text" name="ContactNumber" value="<?= htmlspecialchars($row['ContactNumber']) ?>" required>
      </label>
      <label>Email:
        <input type="email" name="Email" value="<?= htmlspecialchars($row['Email']) ?>" required>
      </label>
      <label>Status:
        <input type="text" name="Status" value="<?= htmlspecialchars($row['Status']) ?>" readonly>
      </label>
      <button type="submit">Зберегти</button>
    </form>
  </fieldset>

  <fieldset>
    <legend>Редагування маршруту</legend>
    <form method="post">
      <input type="hidden" name="update_route" value="1">
      <label>Звідки:
        <input type="text" name="start_location" value="<?= htmlspecialchars($route['StartLocation']) ?>" required>
      </label>
      <label>Куди:
        <input type="text" name="end_location" value="<?= htmlspecialchars($route['EndLocation']) ?>" required>
      </label>
      <label>Відстань (км):
        <input type="number" name="distance" value="<?= htmlspecialchars($route['Distance']) ?>" min="0">
      </label>
      <label>Орієнтовний час (HH:MM:SS):
        <input type="time" name="estimated_time" value="<?= htmlspecialchars($route['EstimatedTime']) ?>">
      </label>
      <button type="submit">Оновити маршрут</button>
    </form>
  </fieldset>

  <fieldset>
    <legend>Зміна пароля</legend>
    <form method="post">
      <input type="hidden" name="change_pass" value="1">
      <label>Старий пароль:
        <input type="password" name="old_password" required>
      </label>
      <label>Новий пароль:
        <input type="password" name="new_password" required>
      </label>
      <button type="submit">Змінити пароль</button>
    </form>
  </fieldset>
</body>
</html>
