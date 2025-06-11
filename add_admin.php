<?php
require 'config.php';
$sql = "INSERT INTO `admins` (`username`,`password`)
        VALUES ('Julia','$2y$10$VhKfLMUOgOk8OMhxEp8KPux7g..zyUJ5M.gp67k04BpIQqZB3Vbj6')";
if ($conn->query($sql)) {
    echo 'OK';
} else {
    echo 'Error: ' . $conn->error;
}
