<?php
session_start();

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

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'payment';
    header("Location: /demo/login/login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: /demo/shop/cart.php");
    exit;
}

// Pobieranie punktów użytkownika
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user_points = $stmt->fetchColumn();

// Obliczanie łącznej ceny zamówienia
$total_price = 0;
foreach ($_SESSION['cart'] as $product) {
    $total_price += $product['price'] * $product['quantity'];
}

$discount = 0; // Domyślny rabat na początku
$final_price = $total_price; // Kwota po rabacie

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $delivery_method = $_POST['delivery_method'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $house_number = trim($_POST['house_number'] ?? '');
    $use_points = isset($_POST['use_points']) ? 1 : 0;

    // Walidacja danych
    if (empty($payment_method) || empty($delivery_method) || empty($city) || empty($street) || empty($postal_code) || empty($house_number)) {
        echo "<script>alert('Proszę wypełnić wszystkie pola.');</script>";
    } elseif (!preg_match('/^[0-9]{2}-[0-9]{3}$/', $postal_code)) {
        echo "<script>alert('Wprowadź poprawny kod pocztowy w formacie XX-XXX.');</script>";
    } else {
        // Obliczanie rabatu
        if ($use_points && $user_points > 0) {
            $discount = min($total_price, $user_points); // Użytkownik może wykorzystać max tyle punktów, ile wynosi cena
            $final_price = $total_price - $discount;
        }

        // Kompletowanie pełnego adresu
        $address = "{$street} {$house_number}, {$postal_code} {$city}";

        $pdo->beginTransaction();
        try {
            // Tworzenie zamówienia
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, address, delivery_method, payment_method) 
                                   VALUES (:user_id, :total_price, 'pending', :address, :delivery_method, :payment_method)");
            $stmt->execute([
                'user_id' => $user_id,
                'total_price' => $final_price,
                'address' => $address,
                'delivery_method' => $delivery_method,
                'payment_method' => $payment_method
            ]);
            $order_id = $pdo->lastInsertId();

            // Dodanie produktów do zamówienia
            foreach ($_SESSION['cart'] as $product_id => $product) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                       VALUES (:order_id, :product_id, :quantity, :price)");
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $product_id,
                    'quantity' => $product['quantity'],
                    'price' => $product['price']
                ]);

                // Aktualizacja stanu magazynowego
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id");
                $stmt->execute([
                    'quantity' => $product['quantity'],
                    'product_id' => $product_id
                ]);
            }

            // Rejestracja płatności
            $stmt = $pdo->prepare("INSERT INTO payment_transactions (order_id, status, payment_method, amount) 
                                   VALUES (:order_id, 'pending', :payment_method, :amount)");
            $stmt->execute([
                'order_id' => $order_id,
                'payment_method' => $payment_method,
                'amount' => $final_price
            ]);

            // Zmniejszenie punktów lojalnościowych użytkownika
            if ($discount > 0) {
                $new_points = $user_points - $discount;
                $stmt = $pdo->prepare("UPDATE users SET points = :points WHERE id = :user_id");
                $stmt->execute([
                    'points' => $new_points,
                    'user_id' => $user_id
                ]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: payment_confirmation.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Błąd: " . $e->getMessage();
        }
    }
}

// Opcje płatności i dostawy
$payment_methods = ["Przelew bankowy", "Karta kredytowa", "PayPal"];
$delivery_methods = ["Kurier", "Odbiór osobisty", "Paczkomat"];
?>

<?php include '../templates/all.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność - Virtus Sklep Wędkarski</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Funkcja do dynamicznej zmiany kwoty po rabacie
        function updatePrice() {
            let discountCheckbox = document.getElementById("use_points");
            let totalPrice = <?php echo $total_price; ?>;
            let userPoints = <?php echo $user_points; ?>;
            let discount = 0;
            let finalPrice = totalPrice;

            // Jeśli użytkownik zaznaczył "użyj punktów" i ma punkty
            if (discountCheckbox.checked && userPoints > 0) {
                discount = Math.min(totalPrice, userPoints); // Użytkownik może wykorzystać max tyle punktów, ile wynosi cena
                finalPrice = totalPrice - discount;
            }

            // Zaktualizuj kwotę po rabacie
            document.getElementById("final_price").innerText = finalPrice.toFixed(2) + " PLN";
            document.getElementById("discount").innerText = discount.toFixed(2) + " PLN";
        }
    </script>
</head>
<body>

<header>
    <h1>Płatność</h1>
</header>

<main>
    <h2>Podsumowanie zamówienia</h2>
    <div class="order-summary">
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $product_id => $product): ?>
                <div class="product-summary">
                    <p><strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
                    <p>Cena: <?php echo number_format($product['price'], 2); ?> PLN</p>
                    <p>Ilość: <?php echo $product['quantity']; ?></p>
                    <p>Łączna cena: <?php echo number_format($product['price'] * $product['quantity'], 2); ?> PLN</p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="total-price">
            <p><strong>Łączna cena do zapłaty: 
                <span id="total_price"><?php echo number_format($total_price, 2); ?> PLN</span>
            </strong></p>
            <p>Rabaty za punkty: <span id="discount"><?php echo number_format($discount, 2); ?> PLN</span></p>
            <p><strong>Kwota do zapłaty po rabacie: <span id="final_price"><?php echo number_format($final_price, 2); ?> PLN</span></strong></p>
        </div>
    </div>

    <form action="payment.php" method="POST">
        <!-- Wybór metody płatności -->
        <label for="payment_method">Metoda płatności:</label>
        <select name="payment_method" id="payment_method" required>
            <?php foreach ($payment_methods as $method): ?>
                <option value="<?php echo htmlspecialchars($method); ?>"><?php echo htmlspecialchars($method); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Wybór metody dostawy -->
        <label for="delivery_method">Metoda dostawy:</label>
        <select name="delivery_method" id="delivery_method" required>
            <?php foreach ($delivery_methods as $method): ?>
                <option value="<?php echo htmlspecialchars($method); ?>"><?php echo htmlspecialchars($method); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Wprowadzenie adresu dostawy -->
        <fieldset>
            <legend>Adres dostawy</legend>
            <label for="city">Miasto:</label>
            <input type="text" name="city" id="city" required>

            <label for="street">Ulica:</label>
            <input type="text" name="street" id="street" required>

            <label for="postal_code">Kod pocztowy:</label>
            <input type="text" name="postal_code" id="postal_code" placeholder="XX-XXX" pattern="[0-9]{2}-[0-9]{3}" required>

            <label for="house_number">Numer domu/lokalu:</label>
            <input type="text" name="house_number" id="house_number" required>
        </fieldset>

        <!-- Opcja wykorzystania punktów -->
        <label for="use_points">Użyj punktów lojalnościowych:</label>
        <input type="checkbox" name="use_points" id="use_points" value="1" <?php echo $user_points > 0 ? '' : 'disabled'; ?> onchange="updatePrice()">
        <p>Dostępne punkty: <?php echo $user_points; ?></p>

        <button type="submit" name="confirm_payment">Zamawiam z obowiązkiem zapłaty</button>
    </form>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
