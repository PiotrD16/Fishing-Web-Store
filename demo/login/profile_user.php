<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Połączenie z bazą danych
$host = 'localhost'; 
$db = 'webpage'; 
$user = 'root'; 
$pass = ''; 
$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Pobranie danych użytkownika
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Pobranie zdjęć użytkownika
$photos_stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = :user_id");
$photos_stmt->execute(['user_id' => $_SESSION['user_id']]);
$photos = $photos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobranie zamówień użytkownika
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id");
$orders_stmt->execute(['user_id' => $_SESSION['user_id']]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../templates/all.php'; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil użytkownika</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        h1, h2 {
            color: #444;
        }
        h1 {
            text-align: center;
            font-size: 35px;
            font-weight: bold;
        }
        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        section {
            margin-bottom: 20px;
        }
        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .photo {
            flex: 1 1 calc(33.333% - 10px);
            box-sizing: border-box;
        }
        .photo img {
    width: 100%; 
    height: 150px; 
    object-fit: cover; 
    border-radius: 5px;
}

        table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    border: 1px solid #ddd;
}

table th, table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

table th {
    background-color: #f4f4f4;
    font-weight: bold;
    text-align: center;
}

    </style>
</head>
<body>
    <h1>Twój profil</h1>
    <main>
        <section class="profile-info">
            <h2>Informacje o użytkowniku</h2>
            <p><strong>Nazwa użytkownika:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Adres e-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Punkty:</strong> <?php echo (int)$user['points']; ?></p>
        </section>
        <section class="user-photos">
            <h2>Twoje zdjęcia</h2>
            <?php if (empty($photos)): ?>
                <p>Nie dodałeś jeszcze żadnych zdjęć.</p>
            <?php else: ?>
                <div class="photo-gallery">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($photo['photo_data']); ?>" alt="Zdjęcie użytkownika" />
                            <p>Punkty za zdjęcie: <?php echo (int)$photo['points']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <section class="user-orders">
            <h2>Twoje zamówienia</h2>
            <?php if (empty($orders)): ?>
                <p>Nie masz jeszcze żadnych zamówień.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Zamówienia</th>
                            <th>Data zamówienia</th>
                            <th>Cena łączna</th>
                            <th>Metoda dostawy</th>
                            <th>Status płatności</th>
                            <th>Metoda płatności</th>
                            <th>Adres zamówienia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo (int)$order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($order['total_price'], 2, ',', ' ')); ?> PLN</td>
                                <td><?php echo htmlspecialchars($order['delivery_method']); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td><?php echo htmlspecialchars($order['address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
