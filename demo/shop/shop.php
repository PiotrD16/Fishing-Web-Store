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

// Pobierz wszystkie kategorie
$category_stmt = $pdo->query("SELECT * FROM categories");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobierz produkty z wybranej kategorii, jeśli jest ustawiona
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

if ($category_id) {
    $product_stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id");
    $product_stmt->execute(['category_id' => $category_id]);
    $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $product_stmt = $pdo->query("SELECT * FROM products");
    $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
                    'price' => $product['price'],
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main>
    
        <h2>Produkty w sklepie</h2>
        
        <?php if (empty($products)): ?>
            <p>Brak produktów w tej kategorii.</p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
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

                        <?php if (!empty($product['new_price']) && $product['new_price'] < $product['price']): ?>
                            <p><del class="old-price"><?php echo number_format($product['price'], 2); ?> PLN</del></p>
                            <p><strong class="new-price"><?php echo number_format($product['new_price'], 2); ?> PLN</strong></p>
                        <?php else: ?>
                            <p><strong><?php echo number_format($product['price'], 2); ?> PLN</strong></p>
                        <?php endif; ?>

                        <p>W dostępności: <?php echo max($available_stock, 0); ?></p>

                        <?php if ($available_stock > 0): ?>
                            <form action="shop.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <a href="product.php?id=<?php echo htmlspecialchars($product['id']); ?>">Zobacz więcej</a>
                            </form>
                        <?php else: ?>
                            <p>Produkt niedostępny</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<?php include '../templates/footer.php'; ?>

</body>
</html>

