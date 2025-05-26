<?php
session_start();
$order_id = $_GET['order_id'] ?? null;

if ($order_id === null) {
    echo "Brak numeru zamówienia.";
    exit;
}

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

// Pobierz szczegóły zamówienia
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id");
$stmt->execute(['order_id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Nie znaleziono zamówienia.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie zamówienia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include '../templates/all.php'; ?>

<main>
    <h2>Twoje zamówienie zostało przyjęte!</h2>
    <p>Numer zamówienia: <?php echo htmlspecialchars($order['id']); ?></p>
    <p>Dziękujemy za złożenie zamówienia. Przejdź do płatności, klikając poniższy przycisk:</p>
    <a href="#" class="checkout-button">Przejdź do banku</a>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
