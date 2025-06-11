<?php
// reset_password.php - скидання пароля клієнта або водія за ID
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Перевірка ролі: лише адмін може
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$generated = false;
$userType  = '';
$userId    = 0;
$newPlain  = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['user_type'] ?? '';
    $userId   = (int)($_POST['user_id'] ?? 0);

    // Валідація
    if (($userType !== 'customer' && $userType !== 'driver') || $userId <= 0) {
        $error = 'Невірні дані.';
    } else {
        // Генеруємо випадковий пароль
        function generatePassword($length = 10) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $pwd = '';
            for ($i = 0; $i < $length; $i++) {
                $pwd .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $pwd;
        }
        $newPlain = generatePassword(12);
        $newHash  = password_hash($newPlain, PASSWORD_DEFAULT);

        // Оновлюємо у потрібній таблиці
        $table = ($userType === 'customer') ? 'customers' : 'drivers';
        $pk    = ($userType === 'customer') ? 'CustomerID' : 'DriverID';
        $stmt  = $conn->prepare("UPDATE `$table` SET password = ? WHERE `$pk` = ?");
        $stmt->bind_param('si', $newHash, $userId);
        if ($stmt->execute()) {
            $generated = true;
        } else {
            $error = 'Помилка оновлення: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Скидання пароля</title>
    <style>
        body { font-family:Arial,sans-serif; padding:20px; }
        form { max-width:300px; margin:auto; }
        label { display:block; margin:10px 0; }
        input, select, button { width:100%; padding:8px; }
        .result { background:#f0f0f0; padding:10px; margin-top:15px; }
        .error { color:red; }
    </style>
</head>
<body>
    <h1>Скидання пароля користувача</h1>
    <p><a href="admin_dashboard.php">← Назад до панелі</a></p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="user_type">Тип користувача:</label>
        <select id="user_type" name="user_type" required>
            <option value="">-- оберіть --</option>
            <option value="customer"<?= $userType === 'customer' ? ' selected' : '' ?>>Клієнт</option>
            <option value="driver"<?= $userType === 'driver' ? ' selected' : '' ?>>Водій</option>
        </select>

        <label for="user_id">ID користувача:</label>
        <input type="number" id="user_id" name="user_id" value="<?= htmlspecialchars($userId) ?>" required>

        <button type="submit">Згенерувати новий пароль</button>
    </form>

    <?php if ($generated): ?>
        <div class="result">
            <p>Новий пароль для <?= htmlspecialchars($userType) ?> з ID <?= htmlspecialchars($userId) ?>:</p>
            <p><strong><?= htmlspecialchars($newPlain) ?></strong></p>
            <p>Пароль успішно оновлено в базі.</p>
        </div>
    <?php endif; ?>
</body>
</html>
