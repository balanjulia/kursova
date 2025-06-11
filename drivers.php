<?php
include 'config.php';

// Обробка форми додавання водія
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_driver'])) {
    $name = $_POST["name"];
    $licenseNumber = $_POST["licenseNumber"];
    $contactNumber = $_POST["contactNumber"];
    $status = $_POST["status"];

    $sql = "INSERT INTO Drivers (Name, LicenseNumber, ContactNumber, Status) VALUES ('$name', '$licenseNumber', '$contactNumber', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('New driver added successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

// Обробка форми оновлення водія
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_driver'])) {
    $driverId = $_POST["driverId"];
    $name = $_POST["name"];
    $licenseNumber = $_POST["licenseNumber"];
    $contactNumber = $_POST["contactNumber"];
    $status = $_POST["status"];

    $sql = "UPDATE Drivers SET Name='$name', LicenseNumber='$licenseNumber', ContactNumber='$contactNumber', Status='$status' WHERE DriverID='$driverId'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Driver updated successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

// Функція для видалення водія
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Drivers WHERE DriverID = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Driver deleted successfully');</script>";
    } else {
        echo "<script>console_log('Error deleting driver: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Водії</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            overflow: hidden;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .table-container {
            width: 100%;
            overflow-y: auto;
            max-height: 400px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .button-container {
            text-align: center;
        }
        .button-container button,
        .button-container a {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .button-container .menu-button {
            background-color: #ff0000;
        }
        .button-container .menu-button:hover {
            background-color: #cc0000;
        }
        .button-container .add-button {
            background-color: #007bff;
        }
        .button-container .add-button:hover {
            background-color: #0056b3;
        }
        .button-container .update-button {
            background-color: #28a745;
        }
        .button-container .update-button:hover {
            background-color: #218838;
        }
        .button-container .delete-button {
            background-color: #dc3545;
        }
        .button-container .delete-button:hover {
            background-color: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .modal button {
            display: inline-block;
            width: auto;
            padding: 10px 20px;
            margin-top: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .modal button:hover {
            background-color: #0056b3;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Водії</h1>
        <div class="table-container">
            <?php
            $sql = "SELECT * FROM Drivers";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Ім'я</th><th>Номер ліцензії</th><th>Номер телефону</th><th>Стан</th><th>Дії</th></tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["Name"] . "</td>";
                    echo "<td>" . $row["LicenseNumber"] . "</td>";
                    echo "<td>" . $row["ContactNumber"] . "</td>";
                    echo "<td>" . $row["Status"] . "</td>";
                    echo "<td class='actions'>
                            <button class='update-button' onclick='openUpdateModal(" . $row["DriverID"] . ", \"" . $row["Name"] . "\", \"" . $row["LicenseNumber"] . "\", \"" . $row["ContactNumber"] . "\", \"" . $row["Status"] . "\")'>Оновити</button>
                            <a href='drivers.php?delete=" . $row["DriverID"] . "' class='delete-button'>Видалити</a>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Немає водіїв";
            }

            $conn->close();
            ?>
        </div>
        <div class="button-container">
            <button class="menu-button" onclick="window.location.href='index.php'">Повернутися до меню</button>
            <button class="add-button" onclick="document.getElementById('addDriverModal').style.display='block'">Додати водія</button>
        </div>
    </div>

    <!-- Модальне вікно для додавання водія -->
    <div id="addDriverModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addDriverModal').style.display='none'">&times;</span>
            <h2>Додати нового водія</h2>
            <form method="post" action="drivers.php">
                <input type="hidden" name="add_driver" value="1">
                <div class="form-group">
                    <label for="name">Ім'я:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="licenseNumber">Номер ліцензії:</label>
                    <input type="text" id="licenseNumber" name="licenseNumber" required>
                </div>
                <div class="form-group">
                    <label for="contactNumber">Номер телефону:</label>
                    <input type="text" id="contactNumber" name="contactNumber" required>
                </div>
                <div class="form-group">
                    <label for="status">Стан:</label>
                    <input type="text" id="status" name="status" required>
                </div>
                <button type="submit">Додати</button>
            </form>
        </div>
    </div>

    <!-- Модальне вікно для оновлення водія -->
    <div id="updateDriverModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('updateDriverModal').style.display='none'">&times;</span>
            <h2>Оновити інформацію водія</h2>
            <form method="post" action="drivers.php">
                <input type="hidden" name="update_driver" value="1">
                <input type="hidden" id="driverId" name="driverId">
                <div class="form-group">
                    <label for="updateName">Ім'я:</label>
                    <input type="text" id="updateName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="updateLicenseNumber">Номер ліцензії:</label>
                    <input type="text" id="updateLicenseNumber" name="licenseNumber" required>
                </div>
                <div class="form-group">
                    <label for="updateContactNumber">Номер телефону:</label>
                    <input type="text" id="updateContactNumber" name="contactNumber" required>
                </div>
                <div class="form-group">
                    <label for="updateStatus">Стан:</label>
                    <input type="text" id="updateStatus" name="status" required>
                </div>
                <button type="submit">Оновити</button>
            </form>
        </div>
    </div>

    <script>
        function console_log(message) {
            console.log(message);
        }

        // Закриття модального вікна при натисканні за межами вікна
        window.onclick = function(event) {
            var addModal = document.getElementById('addDriverModal');
            var updateModal = document.getElementById('updateDriverModal');
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == updateModal) {
                updateModal.style.display = "none";
            }
        }

        function openUpdateModal(driverId, name, licenseNumber, contactNumber, status) {
            document.getElementById('updateDriverModal').style.display = 'block';
            document.getElementById('driverId').value = driverId;
            document.getElementById('updateName').value = name;
            document.getElementById('updateLicenseNumber').value = licenseNumber;
            document.getElementById('updateContactNumber').value = contactNumber;
            document.getElementById('updateStatus').value = status;
        }
    </script>
</body>
</html>
