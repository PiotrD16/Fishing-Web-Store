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

// Tworzenie pustego koszyka, jeśli nie istnieje
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Usuwanie produktu z koszyka
if (isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);
}

// Zmniejszanie ilości produktu w koszyku
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Pobranie danych produktu z bazy danych
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jeśli produkt istnieje i ilość nie jest ujemna oraz nie przekracza stanu magazynowego
    if ($product && $quantity > 0 && $quantity <= $product['stock_quantity']) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    } else {
        echo "<script>alert('Ilość nie może być większa niż dostępna w magazynie lub mniejsza niż 1.');</script>";
    }
}

// Obliczanie łącznej ceny
$total_price = 0;
$delivery_cost = 0; // Domyślny koszt dostawy

foreach ($_SESSION['cart'] as $product_id => $product) {
    $stmt = $pdo->prepare("SELECT price, new_price FROM products WHERE id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Używamy ceny promocyjnej (new_price), jeśli jest dostępna, w przeciwnym razie zwykłej ceny
    $price = !empty($product_data['new_price']) ? $product_data['new_price'] : $product_data['price'];

    $total_price += $price * $product['quantity'];
}

// Ustalanie kosztu dostawy
if ($total_price < 300 && !empty($_SESSION['cart'])) {
    $delivery_cost = 22.50;
    $total_price += $delivery_cost; // Koszt dostawy przy kwocie poniżej 300 PLN
}

?>

<?php include '../templates/all.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk - Virtus Sklep Wędkarski</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>

<header>
    <h1>Twój Koszyk</h1>
</header>

<main>
    <h2>Produkty w koszyku</h2>
    <div class="cart-items">
        <?php
        if (empty($_SESSION['cart'])) {
            echo "<p>Twój koszyk jest pusty!</p>";
        } else {
            foreach ($_SESSION['cart'] as $product_id => $product):
                // Pobranie dostępnej ilości produktu
                $stmt = $pdo->prepare("SELECT stock_quantity, price, new_price FROM products WHERE id = :product_id");
                $stmt->execute(['product_id' => $product_id]);
                $product_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $available_stock = $product_data['stock_quantity'];
                $price = !empty($product_data['new_price']) ? $product_data['new_price'] : $product_data['price'];
        ?>
                <div class="cart-item">
                    <p><strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
                    <p>Cena: <?php echo number_format($price, 2); ?> PLN</p>
                    <p>Ilość na magazynie: <?php echo $available_stock; ?></p>
                    <p>W koszyku: <?php echo $product['quantity']; ?></p>
                    <p>Łączna cena: <?php echo number_format($price * $product['quantity'], 2); ?> PLN</p>

                    <!-- Usuwanie produktu -->
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" name="remove_product">Usuń produkt</button>
                    </form>

                    <!-- Zmiana ilości w koszyku -->
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="number" name="quantity" min="1" max="<?php echo $available_stock; ?>" value="<?php echo $product['quantity']; ?>" required>
                        <button type="submit" name="update_quantity">Zaktualizuj ilość</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php } ?>
    </div>

    <!-- Wyświetlanie łącznej ceny -->
    <div class="cart-total" style="text-align: right;">
        <h3>Łączna kwota: <?php echo number_format($total_price, 2); ?> PLN</h3>
        <?php if ($delivery_cost > 0 && !empty($_SESSION['cart'])): ?>
            <h3>Koszt dostawy: <?php echo number_format($delivery_cost, 2); ?> PLN</h3>
        <?php endif; ?>
    </div>

    <!-- Przycisk do płatności -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php $_SESSION['redirect_to'] = 'payment'; ?>
        <p>Musisz być zalogowany, aby przejść do płatności.</p>
        <a href="/demo/login/login.php" class="checkout-button">Zaloguj się</a>
    <?php else: ?>
        <?php if ($total_price > 0): ?>
            <a href="/demo/payment/payment.php" class="checkout-button">Przejdź do płatności</a>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
