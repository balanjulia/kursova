<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_order'])) {
    $customerID = $_POST["customerID"];
    $vehicleID = $_POST["vehicleID"];
    $driverID = $_POST["driverID"];
    $pickupLocation = $_POST["pickupLocation"];
    $deliveryLocation = $_POST["deliveryLocation"];
    $orderDate = $_POST["orderDate"];
    $deliveryDate = $_POST["deliveryDate"];
    $status = $_POST["status"];

    $sql = "INSERT INTO Orders (CustomerID, VehicleID, DriverID, PickupLocation, DeliveryLocation, OrderDate, DeliveryDate, Status) VALUES ('$customerID', '$vehicleID', '$driverID', '$pickupLocation', '$deliveryLocation', '$orderDate', '$deliveryDate', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('New order added successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $orderID = $_POST["orderID"];
    $customerID = $_POST["customerID"];
    $vehicleID = $_POST["vehicleID"];
    $driverID = $_POST["driverID"];
    $pickupLocation = $_POST["pickupLocation"];
    $deliveryLocation = $_POST["deliveryLocation"];
    $orderDate = $_POST["orderDate"];
    $deliveryDate = $_POST["deliveryDate"];
    $status = $_POST["status"];

    $sql = "UPDATE Orders SET CustomerID='$customerID', VehicleID='$vehicleID', DriverID='$driverID', PickupLocation='$pickupLocation', DeliveryLocation='$deliveryLocation', OrderDate='$orderDate', DeliveryDate='$deliveryDate', Status='$status' WHERE OrderID='$orderID'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Order updated successfully');</script>";
    } else {
        echo "<script>console_log('Error: " . $sql . " " . $conn->error . "');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Orders WHERE OrderID = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>console_log('Order deleted successfully');</script>";
    } else {
        echo "<script>console_log('Error deleting order: " . $conn->error . "');</script>";
    }
}

$customers = $conn->query("SELECT CustomerID, Name FROM Customers");
$vehicles = $conn->query("SELECT VehicleID, LicensePlate FROM Vehicles");
$drivers = $conn->query("SELECT DriverID, Name FROM Drivers");
$routes = $conn->query("SELECT RouteID, StartLocation, EndLocation FROM Routes");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Замовлення</title>
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
        <h1>Замовлення</h1>
        <div class="table-container">
            <?php
            $sql = "SELECT * FROM Orders";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Клієнт</th><th>Транспортний засіб</th><th>Водій</th><th>Місце завантаження</th><th>Місце розвантаження</th><th>Дата замовлення</th><th>Дата доставки</th><th>Статус</th><th>Дії</th></tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["CustomerID"] . "</td>";
                    echo "<td>" . $row["VehicleID"] . "</td>";
                    echo "<td>" . $row["DriverID"] . "</td>";
                    echo "<td>" . $row["PickupLocation"] . "</td>";
                    echo "<td>" . $row["DeliveryLocation"] . "</td>";
                    echo "<td>" . $row["OrderDate"] . "</td>";
                    echo "<td>" . $row["DeliveryDate"] . "</td>";
                    echo "<td>" . $row["Status"] . "</td>";
                    echo "<td class='actions'>
                            <button class='update-button' onclick='openUpdateModal(" . $row["OrderID"] . ", " . $row["CustomerID"] . ", " . $row["VehicleID"] . ", " . $row["DriverID"] . ", \"" . $row["PickupLocation"] . "\", \"" . $row["DeliveryLocation"] . "\", \"" . $row["OrderDate"] . "\", \"" . $row["DeliveryDate"] . "\", \"" . $row["Status"] . "\")'>Оновити</button>
                            <a href='orders.php?delete=" . $row["OrderID"] . "' class='delete-button'>Видалити</a>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Немає замовлень";
            }

            $conn->close();
            ?>
        </div>
        <div class="button-container">
            <button class="menu-button" onclick="window.location.href='index.php'">Повернутися до меню</button>
            <button class="add-button" onclick="document.getElementById('addOrderModal').style.display='block'">Додати замовлення</button>
        </div>
    </div>

    <div id="addOrderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addOrderModal').style.display='none'">&times;</span>
            <h2>Додати нове замовлення</h2>
            <form method="post" action="orders.php">
                <input type="hidden" name="add_order" value="1">
                <div class="form-group">
                    <label for="customerID">Клієнт:</label>
                    <select id="customerID" name="customerID" required>
                        <?php while($row = $customers->fetch_assoc()) { ?>
                            <option value="<?= $row['CustomerID'] ?>"><?= $row['Name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="vehicleID">Транспортний засіб:</label>
                    <select id="vehicleID" name="vehicleID" required>
                        <?php while($row = $vehicles->fetch_assoc()) { ?>
                            <option value="<?= $row['VehicleID'] ?>"><?= $row['LicensePlate'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="driverID">Водій:</label>
                    <select id="driverID" name="driverID" required>
                        <?php while($row = $drivers->fetch_assoc()) { ?>
                            <option value="<?= $row['DriverID'] ?>"><?= $row['Name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pickupLocation">Місце завантаження:</label>
                    <input type="text" id="pickupLocation" name="pickupLocation" required>
                </div>
                <div class="form-group">
                    <label for="deliveryLocation">Місце розвантаження:</label>
                    <input type="text" id="deliveryLocation" name="deliveryLocation" required>
                </div>
                <div class="form-group">
                    <label for="orderDate">Дата замовлення:</label>
                    <input type="date" id="orderDate" name="orderDate" required>
                </div>
                <div class="form-group">
                    <label for="deliveryDate">Дата доставки:</label>
                    <input type="date" id="deliveryDate" name="deliveryDate" required>
                </div>
                <div class="form-group">
                    <label for="status">Статус:</label>
                    <input type="text" id="status" name="status" required>
                </div>
                <button type="submit">Додати</button>
            </form>
        </div>
    </div>

    <div id="updateOrderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('updateOrderModal').style.display='none'">&times;</span>
            <h2>Оновити інформацію замовлення</h2>
            <form method="post" action="orders.php">
                <input type="hidden" name="update_order" value="1">
                <input type="hidden" id="orderID" name="orderID">
                <div class="form-group">
                    <label for="updateCustomerID">Клієнт:</label>
                    <select id="updateCustomerID" name="customerID" required>
                        <?php while($row = $customers->fetch_assoc()) { ?>
                            <option value="<?= $row['CustomerID'] ?>"><?= $row['Name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="updateVehicleID">Транспортний засіб:</label>
                    <select id="updateVehicleID" name="vehicleID" required>
                        <?php while($row = $vehicles->fetch_assoc()) { ?>
                            <option value="<?= $row['VehicleID'] ?>"><?= $row['LicensePlate'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="updateDriverID">Водій:</label>
                    <select id="updateDriverID" name="driverID" required>
                        <?php while($row = $drivers->fetch_assoc()) { ?>
                            <option value="<?= $row['DriverID'] ?>"><?= $row['Name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="updatePickupLocation">Місце завантаження:</label>
                    <input type="text" id="updatePickupLocation" name="pickupLocation" required>
                </div>
                <div class="form-group">
                    <label for="updateDeliveryLocation">Місце розвантаження:</label>
                    <input type="text" id="updateDeliveryLocation" name="deliveryLocation" required>
                </div>
                <div class="form-group">
                    <label for="updateOrderDate">Дата замовлення:</label>
                    <input type="date" id="updateOrderDate" name="orderDate" required>
                </div>
                <div class="form-group">
                    <label for="updateDeliveryDate">Дата доставки:</label>
                    <input type="date" id="updateDeliveryDate" name="deliveryDate" required>
                </div>
                <div class="form-group">
                    <label for="updateStatus">Статус:</label>
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

        window.onclick = function(event) {
            var addModal = document.getElementById('addOrderModal');
            var updateModal = document.getElementById('updateOrderModal');
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == updateModal) {
                updateModal.style.display = "none";
            }
        }

        function openUpdateModal(orderID, customerID, vehicleID, driverID, pickupLocation, deliveryLocation, orderDate, deliveryDate, status) {
            document.getElementById('updateOrderModal').style.display = 'block';
            document.getElementById('orderID').value = orderID;
            document.getElementById('updateCustomerID').value = customerID;
            document.getElementById('updateVehicleID').value = vehicleID;
            document.getElementById('updateDriverID').value = driverID;
            document.getElementById('updatePickupLocation').value = pickupLocation;
            document.getElementById('updateDeliveryLocation').value = deliveryLocation;
            document.getElementById('updateOrderDate').value = orderDate;
            document.getElementById('updateDeliveryDate').value = deliveryDate;
            document.getElementById('updateStatus').value = status;
        }
    </script>
</body>
</html>
