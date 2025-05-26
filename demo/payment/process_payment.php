<?php
session_start();

// Jeśli użytkownik nie jest zalogowany, przekieruj na stronę logowania
if (!isset($_SESSION['user_id'])) {
    header('Location: /demo/login/login.php');
    exit;
}

// Sprawdzenie, czy dane zostały przesłane
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];
    $delivery_method = $_POST['delivery_method'];
    $address = $_POST['address'];

    // Połączenie z bazą danych
    $host = 'localhost'; 
    $db = 'your_database'; 
    $user = 'your_username'; 
    $pass = 'your_password'; 
    $dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

    try {
        $pdo = new PDO($dsn, $user, $pass);
    } catch (PDOException $e) {
        echo "Błąd połączenia z bazą danych: " . $e->getMessage();
        exit;
    }

    // Zapisanie płatności i dostawy w bazie
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, payment_method, delivery_method, address, status) 
                           VALUES (:user_id, :payment_method, :delivery_method, :address, 'oczekująca')");
    $stmt->execute([
        ':user_id' => $user_id,
        ':payment_method' => $payment_method,
        ':delivery_method' => $delivery_method,
        ':address' => $address,
    ]);

    // Przekierowanie użytkownika na stronę z potwierdzeniem płatności
    header('Location: payment_confirmation.php');
    exit;
}
?>
