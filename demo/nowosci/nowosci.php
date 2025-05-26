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

// Pobierz produkty dodane w ciągu ostatniego tygodnia
$recent_products_stmt = $pdo->query("SELECT * FROM products WHERE created_at >= CURDATE() - INTERVAL 7 DAY");
$recent_products = $recent_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Dodawanie do koszyka
if (isset($_POST['add_to_cart'])) {
    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :product_id");
        $stmt->execute(['product_id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Sprawdzanie, czy w koszyku już jest ten produkt
            if (isset($_SESSION['cart'][$product_id])) {
                // Jeśli produkt już jest w koszyku, dodajemy ilość
                $quantity += $_SESSION['cart'][$product_id]['quantity'];
            }

            // Sprawdzanie dostępnej ilości w magazynie
            $available_stock = $product['stock_quantity'];

            // Jeśli produkt jest w koszyku, odejmujemy ilość z koszyka
            if (isset($_SESSION['cart'][$product_id])) {
                $available_stock -= $_SESSION['cart'][$product_id]['quantity'];
            }

            if ($available_stock >= $quantity) {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'price' => $product['new_price'], // Dodajemy cenę promocyjną
                    'quantity' => $quantity
                ];
            } else {
                echo "<script>alert('Brak wystarczającej ilości w magazynie. Dostępne: $available_stock');</script>";
            }
        }
    } else {
        echo "<script>alert('Produkt lub ilość nie zostały wybrane.');</script>";
    }
}
?>

<?php include '../templates/all.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtus - Sklep Wędkarski</title>
    <link rel="stylesheet" href="nowosci.css">
</head>
<body>

<main>
    <h2>Produkty dodane w ciągu ostatniego tygodnia</h2>
    
    <?php if (empty($recent_products)): ?>
        <p>Brak produktów dodanych w ostatnim tygodniu.</p>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($recent_products as $product): ?>
                <?php 
                    // Oblicz dostępność w magazynie (na stanie - ilość w koszyku)
                    $available_stock = $product['stock_quantity'];
                    if (isset($_SESSION['cart'][$product['id']])) {
                        $available_stock -= $_SESSION['cart'][$product['id']]['quantity'];
                    }
                ?>
                <div class="product-item">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><?php echo $product['description']; ?></p>

                    <?php if ($product['new_price'] != null): ?>
                        <!-- Produkt przeceniony -->
                        <p><del><?php echo number_format($product['price'], 2); ?> PLN</del></p>
                        <p><strong><?php echo number_format($product['new_price'], 2); ?> PLN</strong></p>
                    <?php else: ?>
                        <!-- Produkt bez przeceny -->
                        <p><strong><?php echo number_format($product['price'], 2); ?> PLN</strong></p>
                    <?php endif; ?>

                    <p>W dostępności: <?php echo max($available_stock, 0); ?></p>

                    <?php if ($available_stock > 0): ?>
                        <form action="nowosci.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <a href="../shop/product.php?id=<?php echo htmlspecialchars($product['id']); ?>">Zobacz więcej</a>
                        </form>
                    <?php else: ?>
                        <p class="out-of-stock">Produkt niedostępny</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
