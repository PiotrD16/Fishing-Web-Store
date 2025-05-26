<?php
session_start();

// Połączenie z bazą danych
$host = 'localhost';
$db = 'webpage';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo "Błąd połączenia z bazą danych: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $quantity]);

    header("Location: admin_panel.php");
    exit;
}
?>
