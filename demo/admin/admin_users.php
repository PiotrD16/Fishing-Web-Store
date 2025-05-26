<?php
session_start();
require_once '../classes/Database.php';

$db = (new Database())->connect();

// Pobieranie listy użytkowników (pomijając administratorów)
$stmt = $db->query("SELECT id, username, points FROM users WHERE role != 'admin'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie zdjęć wszystkich użytkowników
$stmt = $db->query("
    SELECT photos.*, users.username 
    FROM photos 
    JOIN users ON photos.user_id = users.id
");
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grupowanie zdjęć według użytkowników
$userPhotos = [];
foreach ($photos as $photo) {
    $userPhotos[$photo['user_id']][] = $photo;
}

// Pobieranie zamówień wszystkich użytkowników
$stmt = $db->query("
    SELECT orders.*, users.username 
    FROM orders 
    JOIN users ON orders.user_id = users.id
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grupowanie zamówień według użytkowników
$userOrders = [];
foreach ($orders as $order) {
    $userOrders[$order['user_id']][] = $order;
}

// Obsługa dodawania punktów
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_points') {
        $userId = $_POST['user_id'];
        $points = $_POST['points'];

        if ($points > 0) {
            $stmt = $db->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $stmt->execute([$points, $userId]);
            $_SESSION['message'] = "Dodano $points punktów!";
        } else {
            $_SESSION['error'] = "Nie można dodać ujemnych punktów!";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Obsługa anulowania zamówień
    if (isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
        $orderId = $_POST['order_id'];

        $stmt = $db->prepare("UPDATE orders SET status = 'Anulowano' WHERE id = ?");
        $stmt->execute([$orderId]);

        $_SESSION['message'] = "Zamówienie zostało anulowane!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<?php include 'admin.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/styles.css">
    <title>Użytkownicy - Panel Administratora</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-image: url("/demo/ryby.jpg");
        color: #151731;
    }
    .row3 {
        background-color: #f9f9f9;
        width: 50%;
        margin: 50px auto;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        text-align: center;
    }
    a {
        color: #ff6600;
        font-weight: bold;
        font-size: 18px;
        text-decoration: none;
    }
    main {
        max-width: auto;
        margin-left: 120px;
        margin-right: 120px;
        padding: 50px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    h2{
        text-align: center;
        font-size: 30px;
        font-weight: bold
    }
    hr {
        border: 0;
        border-top: 2px solid #ccc;
        margin: 20px 0;
    }
</style>

<header>
    <div class="row3">
        <h1>Użytkownicy</h1>
        <a href="admin_panel.php">Powrót do panelu głównego</a>
    </div>
</header>
<body>
    <main>
        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2>Lista użytkowników</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <b><?= htmlspecialchars($user['username']) ?></b> - Punkty: <?= htmlspecialchars($user['points']) ?>
                    <br>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="add_points">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="number" name="points" placeholder="Dodaj punkty" required min="1">
                        <button type="submit">Dodaj punkty</button>
                    </form>

                    <h3>Zdjęcia dodane przez <?= htmlspecialchars($user['username']) ?></h3>
                    <?php if (isset($userPhotos[$user['id']])): ?>
                        <ul>
                            <?php foreach ($userPhotos[$user['id']] as $photo): ?>
                                <li>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($photo['photo_data']) ?>" alt="Zdjęcie użytkownika" width="100">
                                    <p>Długość ryby: <?= htmlspecialchars($photo['fish_length']) ?> cm</p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Brak zdjęć</p>
                    <?php endif; ?>

                    <h3>Zamówienia użytkownika</h3>
                    <?php if (isset($userOrders[$user['id']])): ?>
                        <ul>
                            <?php foreach ($userOrders[$user['id']] as $order): ?>
                                <li>
                                    Zamówienie #<?= htmlspecialchars($order['id']) ?> - <?= htmlspecialchars($order['status']) ?>
                                    <br>
                                    Data: <?= htmlspecialchars($order['order_date']) ?>, Kwota: <?= htmlspecialchars($order['total_price']) ?> PLN
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="cancel_order">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit">Anuluj</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Brak zamówień</p>
                    <?php endif; ?>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    </main>    
</body>
</html>
