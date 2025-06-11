<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "transport_logistics_enterprise";

// Створення з'єднання
$conn = new mysqli($servername, $username, $password);

// Перевірка з'єднання
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
console_log("Connected successfully");

// Створення бази даних
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    console_log("Database created successfully");
} else {
    console_log("Error creating database: " . $conn->error);
}   

// Підключення до створеної бази даних
$conn->select_db($dbname);

// Створення таблиці Customers
$sql = "CREATE TABLE IF NOT EXISTS Customers (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    ContactNumber VARCHAR(15),
    Email VARCHAR(255),
    Address TEXT
)";
if ($conn->query($sql) === TRUE) {
    console_log("Table Customers created successfully");
} else {
    console_log("Error creating table Customers: " . $conn->error);
}

// Створення таблиці Vehicles
$sql = "CREATE TABLE IF NOT EXISTS Vehicles (
    VehicleID INT AUTO_INCREMENT PRIMARY KEY,
    VehicleType VARCHAR(255) NOT NULL,
    LicensePlate VARCHAR(50) NOT NULL,
    Capacity INT NOT NULL,
    Status VARCHAR(50)
)";
if ($conn->query($sql) === TRUE) {
    console_log("Table Vehicles created successfully");
} else {
    console_log("Error creating table Vehicles: " . $conn->error);
}

// Створення таблиці Drivers
$sql = "CREATE TABLE IF NOT EXISTS Drivers (
    DriverID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    LicenseNumber VARCHAR(50) NOT NULL,
    ContactNumber VARCHAR(15),
    Status VARCHAR(50)
)";
if ($conn->query($sql) === TRUE) {
    console_log("Table Drivers created successfully");
} else {
    console_log("Error creating table Drivers: " . $conn->error);
}

// Створення таблиці Orders
$sql = "CREATE TABLE IF NOT EXISTS Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    VehicleID INT,
    DriverID INT,
    PickupLocation TEXT,
    DeliveryLocation TEXT,
    OrderDate DATE,
    DeliveryDate DATE,
    Status VARCHAR(50),
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID),
    FOREIGN KEY (VehicleID) REFERENCES Vehicles(VehicleID),
    FOREIGN KEY (DriverID) REFERENCES Drivers(DriverID)
)";
if ($conn->query($sql) === TRUE) {
    console_log("Table Orders created successfully");
} else {
    console_log("Error creating table Orders: " . $conn->error);
}

// Створення таблиці Routes
$sql = "CREATE TABLE IF NOT EXISTS Routes (
    RouteID INT AUTO_INCREMENT PRIMARY KEY,
    VehicleID INT,
    StartLocation TEXT,
    EndLocation TEXT,
    Distance INT,
    EstimatedTime TIME,
    FOREIGN KEY (VehicleID) REFERENCES Vehicles(VehicleID)
)";
if ($conn->query($sql) === TRUE) {
    console_log("Table Routes created successfully");
} else {
    console_log("Error creating table Routes: " . $conn->error);
}


function console_log($message) {
    echo "<script>console.log('".$message."');</script>";
}
?>
