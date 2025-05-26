<?php
session_start();
require_once '../classes/Database.php';

$db = (new Database())->connect();

// Pobieranie zdjęć użytkownika
$userId = $_GET['user_id'];
$stmt = $db->prepare("SELECT * FROM photos WHERE user_id = ?");
$stmt->execute([$userId]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/styles.css">
    <title>Zdjęcia użytkownika</title>
</head>
<body>
    <h1>Zdjęcia użytkownika</h1>

    <!-- Powrót do panelu użytkowników -->
    <a href="admin_users.php">Powrót do użytkowników</a>

    <ul>
        <?php foreach ($photos as $photo): ?>
            <li>
                <img src="data:image/jpeg;base64,<?= base64_encode($photo['photo_data']) ?>" alt="Zdjęcie użytkownika" width="100">
                <p>Długość ryby: <?= htmlspecialchars($photo['fish_length']) ?> cm</p>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
