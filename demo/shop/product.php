<?php
session_start();
// Połączenie z bazą danych
$conn = new mysqli('localhost', 'root', '', 'webpage');
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// Pobierz ID produktu z URL-a i upewnij się, że to liczba
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


// Zabezpieczone zapytanie SQL (prepared statement)
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id); // "i" oznacza, że parametr to liczba całkowita (integer)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $product_image = $product['image'];
} else {
    die("Produkt nie został znaleziony!");
}

// Zamknięcie zapytania
$stmt->close();
?>

<?php include '../templates/all.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Sklep Internetowy</title>
    <link rel="stylesheet" href="product.css">
</head>
<body>
    <main>
        <a href="/demo/shop/shop.php" class="back-link">Powrót do sklepu</a>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="product-container">
            <div class="product-image">
            <img src="data:image/jpeg;base64,<?= base64_encode($product_image); ?>" alt="Zdjęcie produktu" width="400"/>
            </div>
            <div class="product-details">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p>
                    Cena: 
                    <?php if (!empty($product['new_price']) && $product['new_price'] < $product['price']): ?>
                        <span class="old-price"><?php echo number_format($product['price'], 2); ?> PLN</span>
                        <span class="new-price"><?php echo number_format($product['new_price'], 2); ?> PLN</span>
                    <?php else: ?>
                        <?php echo number_format($product['price'], 2); ?> PLN
                    <?php endif; ?>
                </p>
                <p>W dostępności: <?php echo $product['stock_quantity']; ?></p>
                <form action="shop.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <label for="quantity">Ilość:</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                    <button type="submit" name="add_to_cart" class="add-to-cart">Dodaj do koszyka</button>
                </form>
            </div>
        </div>
    </main>
</body>

</html>
<?php
$conn->close();
?>
