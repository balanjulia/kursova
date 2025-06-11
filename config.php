<?php
// === config.php ===

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "transport_logistics_enterprise";

// Створюємо з'єднання та одразу вказуємо базу
$conn = new mysqli($servername, $username, $password, $dbname);

// Перевірка з'єднання
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Встановлюємо кодування
$conn->set_charset('utf8mb4');

function console_log($message) {
    echo "<script>console.log('". addslashes($message) ."');</script>";
}
