<?php
require 'config.php';

$username = 'Julia';
$plain    = 'ифдфт2005';

$hash     = $2y$10$VhKfLMUOgOk8OMhxEp8KPux7g..zyUJ5M.gp67k04BpIQqZB3Vbj6;

$stmt = $conn->prepare("INSERT INTO `admins` (`username`,`password`) VALUES (?,?)");
$stmt->bind_param('ss', $username, $hash);
if ($stmt->execute()) {
    echo "Адмін {$username} успішно створено.";
} else {
    echo "Помилка: " . $stmt->error;
}
