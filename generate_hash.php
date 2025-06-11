<?php
// generate_client_passwords.php - Генерація випадкових паролів для всіх клієнтів та оновлення хешів
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Функція для генерації випадкового пароля
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $pwd = '';
    for ($i = 0; $i < $length; $i++) {
        $pwd .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pwd;
}

echo "Генерація паролів для клієнтів...\n";

// Отримуємо список клієнтів
$result = $conn->query("SELECT CustomerID, Email FROM customers");
if (!$result) {
    die("Помилка: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $id    = (int)$row['CustomerID'];
    $email = $row['Email'];
    $plain = generatePassword(12); // довжина 12
    $hash  = password_hash($plain, PASSWORD_DEFAULT);

    // Оновлюємо хеш у БД
    $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE CustomerID = ?");
    $stmt->bind_param('si', $hash, $id);
    if ($stmt->execute()) {
        echo "Клієнт (ID={$id}, email={$email}) - пароль: {$plain}\n";
    } else {
        echo "Помилка оновлення для ID={$id}: " . $stmt->error . "\n";
    }
}

echo "Готово. Після виконання видаліть цей файл!\n";
