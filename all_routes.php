<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Всі маршрути</title>
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
            max-width: 1000px;
            overflow: hidden;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .table-container {
            width: 100%;
            overflow-y: auto;
            max-height: 500px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Всі маршрути</h1>
        <div class="table-container">
            <?php
            $sql = "
                SELECT 
                    Routes.RouteID, 
                    Vehicles.LicensePlate AS VehicleLicensePlate, 
                    Routes.StartLocation, 
                    Routes.EndLocation, 
                    Routes.Distance, 
                    Routes.EstimatedTime
                FROM Routes
                JOIN Vehicles ON Routes.VehicleID = Vehicles.VehicleID
            ";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Транспортний засіб</th>
                        <th>Місце початку</th>
                        <th>Місце кінця</th>
                        <th>Відстань (км)</th>
                        <th>Орієнтовний час</th>
                      </tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["VehicleLicensePlate"] . "</td>";
                    echo "<td>" . $row["StartLocation"] . "</td>";
                    echo "<td>" . $row["EndLocation"] . "</td>";
                    echo "<td>" . $row["Distance"] . "</td>";
                    echo "<td>" . $row["EstimatedTime"] . "</td>";
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
        </div>
    </div>
</body>
</html>
