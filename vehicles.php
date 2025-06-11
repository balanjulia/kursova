<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_route'])) {
    $vehicleID = $_POST["vehicleID"];
    $startLocation = $_POST["startLocation"];
    $endLocation = $_POST["endLocation"];
    $distance = $_POST["distance"];
    $estimatedTime = $_POST["estimatedTime"];

    $sql = "INSERT INTO Routes (VehicleID, StartLocation, EndLocation, Distance, EstimatedTime) VALUES ('$vehicleID', '$startLocation', '$endLocation', '$distance', '$estimatedTime')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('New route added successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_route'])) {
    $routeID = $_POST["routeID"];
    $vehicleID = $_POST["vehicleID"];
    $startLocation = $_POST["startLocation"];
    $endLocation = $_POST["endLocation"];
    $distance = $_POST["distance"];
    $estimatedTime = $_POST["estimatedTime"];

    $sql = "UPDATE Routes SET VehicleID='$vehicleID', StartLocation='$startLocation', EndLocation='$endLocation', Distance='$distance', EstimatedTime='$estimatedTime' WHERE RouteID='$routeID'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Route updated successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Routes WHERE RouteID = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Route deleted successfully');</script>";
    } else {
        echo "<script>console_log('Error deleting route: " . $conn->error . "');</script>";
    }
}

$vehicles = $conn->query("SELECT VehicleID, LicensePlate FROM Vehicles");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Маршрути</title>
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
        .form-group input,
        .form-group select {
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
        <h1>Маршрути</h1>
        <div class="table-container">
            <?php
            $sql = "SELECT * FROM Routes";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Транспортний засіб</th><th>Місце початку</th><th>Місце кінця</th><th>Відстань</th><th>Орієнтовний час</th><th>Дії</th></tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["VehicleID"] . "</td>";
                    echo "<td>" . $row["StartLocation"] . "</td>";
                    echo "<td>" . $row["EndLocation"] . "</td>";
                    echo "<td>" . $row["Distance"] . "</td>";
                    echo "<td>" . $row["EstimatedTime"] . "</td>";
                    echo "<td class='actions'>
                            <button class='update-button' onclick='openUpdateModal(" . $row["RouteID"] . ", " . $row["VehicleID"] . ", \"" . $row["StartLocation"] . "\", \"" . $row["EndLocation"] . "\", " . $row["Distance"] . ", \"" . $row["EstimatedTime"] . "\")'>Оновити</button>
                            <a href='routes.php?delete=" . $row["RouteID"] . "' class='delete-button'>Видалити</a>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Немає маршрутів";
            }

            $conn->close();
            ?>
        </div>
        <div class="button-container">
            <button class="menu-button" onclick="window.location.href='index.php'">Повернутися до меню</button>
            <button class="add-button" onclick="document.getElementById('addRouteModal').style.display='block'">Додати маршрут</button>
        </div>
    </div>

    <div id="addRouteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addRouteModal').style.display='none'">&times;</span>
            <h2>Додати новий маршрут</h2>
            <form method="post" action="routes.php">
                <input type="hidden" name="add_route" value="1">
                <div class="form-group">
                    <label for="vehicleID">Транспортний засіб:</label>
                    <select id="vehicleID" name="vehicleID" required>
                        <?php while($row = $vehicles->fetch_assoc()) { ?>
                            <option value="<?= $row['VehicleID'] ?>"><?= $row['LicensePlate'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="startLocation">Місце початку:</label>
                    <input type="text" id="startLocation" name="startLocation" required>
                </div>
                <div class="form-group">
                    <label for="endLocation">Місце кінця:</label>
                    <input type="text" id="endLocation" name="endLocation" required>
                </div>
                <div class="form-group">
                    <label for="distance">Відстань (км):</label>
                    <input type="number" id="distance" name="distance" required>
                </div>
                <div class="form-group">
                    <label for="estimatedTime">Орієнтовний час (год:хв):</label>
                    <input type="time" id="estimatedTime" name="estimatedTime" required>
                </div>
                <button type="submit">Додати</button>
            </form>
        </div>
    </div>

    <div id="updateRouteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('updateRouteModal').style.display='none'">&times;</span>
            <h2>Оновити інформацію маршруту</h2>
            <form method="post" action="routes.php">
                <input type="hidden" name="update_route" value="1">
                <input type="hidden" id="routeID" name="routeID">
                <div class="form-group">
                    <label for="updateVehicleID">Транспортний засіб:</label>
                    <select id="updateVehicleID" name="vehicleID" required>
                        <?php while($row = $vehicles->fetch_assoc()) { ?>
                            <option value="<?= $row['VehicleID'] ?>"><?= $row['LicensePlate'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="updateStartLocation">Місце початку:</label>
                    <input type="text" id="updateStartLocation" name="startLocation" required>
                </div>
                <div class="form-group">
                    <label for="updateEndLocation">Місце кінця:</label>
                    <input type="text" id="updateEndLocation" name="endLocation" required>
                </div>
                <div class="form-group">
                    <label for="updateDistance">Відстань (км):</label>
                    <input type="number" id="updateDistance" name="distance" required>
                </div>
                <div class="form-group">
                    <label for="updateEstimatedTime">Орієнтовний час (год:хв):</label>
                    <input type="time" id="updateEstimatedTime" name="estimatedTime" required>
                </div>
                <button type="submit">Оновити</button>
            </form>
        </div>
    </div>

    <script>
        function console_log(message) {
            console.log(message);
        }

        window.onclick = function(event) {
            var addModal = document.getElementById('addRouteModal');
            var updateModal = document.getElementById('updateRouteModal');
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == updateModal) {
                updateModal.style.display = "none";
            }
        }

        function openUpdateModal(routeID, vehicleID, startLocation, endLocation, distance, estimatedTime) {
            document.getElementById('updateRouteModal').style.display = 'block';
            document.getElementById('routeID').value = routeID;
            document.getElementById('updateVehicleID').value = vehicleID;
            document.getElementById('updateStartLocation').value = startLocation;
            document.getElementById('updateEndLocation').value = endLocation;
            document.getElementById('updateDistance').value = distance;
            document.getElementById('updateEstimatedTime').value = estimatedTime;
        }
    </script>
</body>
</html>
        