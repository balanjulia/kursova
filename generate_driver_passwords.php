<?php
// generate_driver_passwords.php — генерує випадкові паролі для всіх водіїв та оновлює хеші
ini_set('display_errors', 1);
require_once 'config.php';

function generatePassword($len = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pwd = '';
    for ($i = 0; $i < $len; $i++) {
        $pwd .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pwd;
}

echo "Генерація паролів для всіх водіїв...\n";
$res = $conn->query("SELECT DriverID, email FROM drivers");
while ($r = $res->fetch_assoc()) {
    $plain = generatePassword();
    $hash  = password_hash($plain, PASSWORD_DEFAULT);
    $u = $conn->prepare("UPDATE drivers SET password = ? WHERE DriverID = ?");
    $u->bind_param('si', $hash, $r['DriverID']);
    $u->execute();
    echo "DriverID {$r['DriverID']} ({$r['email']}) → {$plain}\n";
}
echo "Готово — після виконання видаліть файл!\n";
?>
