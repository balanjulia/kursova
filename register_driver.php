<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$errors = [];
$success = '';


$uploadDir = __DIR__ . '/uploads/drivers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $licenseNumber  = trim($_POST['license_number'] ?? '');
    $phone          = trim($_POST['contact_number'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $password       = $_POST['password'] ?? '';
    $vehicleType    = trim($_POST['vehicle_type'] ?? '');
    $licensePlate   = trim($_POST['license_plate'] ?? '');
    $capacity       = trim($_POST['capacity'] ?? '');
    $startLocation  = trim($_POST['start_location'] ?? '');
    $endLocation    = trim($_POST['end_location'] ?? '');


    $photoPath = '';
    if (!empty($_FILES['photo']['name'])) {
        $ext       = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg','jpeg','png'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Непідтримуваний формат фото. Допустимо: jpg, jpeg, png.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Фото має бути не більше 2МБ.';
        } else {
            $newName = uniqid('drv_') . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newName)) {
                $photoPath = 'uploads/drivers/' . $newName;
            } else {
                $errors[] = 'Помилка завантаження фото.';
            }
        }
    }

    if (!$name || !$licenseNumber || !$phone || !$email || !$password
        || !$vehicleType || !$licensePlate || !$capacity
        || !$startLocation || !$endLocation) {
        $errors[] = "Усі поля обов'язкові.";
    } elseif (!preg_match('/^\+?\d+$/', $phone)) {
        $errors[] = "Телефон може містити лише цифри й необов'язковий '+'";
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль повинен містити мінімум 6 символів.';
    } elseif (!is_numeric($capacity) || intval($capacity) <= 0) {
        $errors[] = 'Місткість має бути числом більше за 0.';
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $drv = $conn->prepare(
            "INSERT INTO drivers
             (Name, LicenseNumber, ContactNumber, Email, password, Photo, Status)
             VALUES (?, ?, ?, ?, ?, ?, 'active')"
        );
        $drv->bind_param(
            'ssssss',
            $name,
            $licenseNumber,
            $phone,
            $email,
            $passwordHash,
            $photoPath
        );
        $drv->execute();
        $driverId = $conn->insert_id;

        $veh = $conn->prepare(
            "INSERT INTO vehicles
             (VehicleType, LicensePlate, Capacity, Status)
             VALUES (?, ?, ?, 'active')"
        );
        $veh->bind_param('ssi', $vehicleType, $licensePlate, $capacity);
        $veh->execute();
        $vehicleId = $conn->insert_id;
        $route = $conn->prepare(
            "INSERT INTO routes
             (VehicleID, StartLocation, EndLocation, Distance, EstimatedTime, DriverID)
             VALUES (?, ?, ?, 0, '00:00:00', ?)"
        );
        $route->bind_param('issi', $vehicleId, $startLocation, $endLocation, $driverId);
        $route->execute();

        $success = 'Водія успішно зареєстровано з фото!';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Реєстрація водія</title>
  <style>
    body { background:#f7f7f7; font-family:Arial,sans-serif; padding:40px; }
    .container { background:#fff; max-width:600px; margin:auto; padding:20px; border-radius:6px; box-shadow:0 0 8px rgba(0,0,0,0.1); }
    h2 { text-align:center; }
    label { display:block; margin:10px 0 5px; }
    input[type=text], input[type=email], input[type=password], input[type=number], input[type=file], input[type=time] {
      width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;
    }
    button { width:100%; padding:12px; background:#4a90e2; color:#fff; border:none; border-radius:4px; cursor:pointer; margin-top:20px; }
    .error { color:#e74c3c; }
    .success { color:#27ae60; }
    .back { display:inline-block; margin-bottom:15px; text-decoration:none; color:#555; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Реєстрація водія</h2>
    <a href="index.php" class="back">← На головну</a>
    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php foreach($errors as $e): ?><p class="error"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <label>Фото водія (jpg, png ≤2МБ): <input type="file" name="photo"></label>
      <label>Ім'я: <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></label>
      <label>Номер посвідчення: <input type="text" name="license_number" value="<?= htmlspecialchars($_POST['license_number'] ?? '') ?>" required></label>
      <label>Телефон: <input type="text" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" required></label>
      <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></label>
      <label>Пароль: <input type="password" name="password" required></label>
      <hr>
      <h3>Дані про транспорт</h3>
      <label>Тип ТЗ: <input type="text" name="vehicle_type" value="<?= htmlspecialchars($_POST['vehicle_type'] ?? '') ?>" required></label>
      <label>Номерний знак: <input type="text" name="license_plate" value="<?= htmlspecialchars($_POST['license_plate'] ?? '') ?>" required></label>
      <label>Місткість (тонн): <input type="number" name="capacity" min="1" value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>" required></label>
      <hr>
      <h3>План маршруту</h3>
      <label>Звідки: <input type="text" name="start_location" value="<?= htmlspecialchars($_POST['start_location'] ?? '') ?>" required></label>
      <label>Куди: <input type="text" name="end_location" value="<?= htmlspecialchars($_POST['end_location'] ?? '') ?>" required></label>
      <button type="submit">Зареєструватися</button>
    </form>
  </div>
</body>
</html>
