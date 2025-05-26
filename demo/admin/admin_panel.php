<?php
session_start();

// Obsługa wylogowania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    header("Location: /demo/login/logout.php");
    exit;
}
?>

<?php include 'admin.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .main-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    h2 {
        font-size: 25px;
        text-align: center;
    }

    .feature {
        background-color: #f9f9f9;
        text-align: center;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .feature img {
        width: 50px;
        margin-bottom: 10px;
    }

    .feature h3 {
        font-size: 22px;
        margin: 10px 0;
        color: #333;
    }

    .feature a {
        color: #ff6600;
        font-weight: bold;
        font-size: 18px;
        text-decoration: none;
    }

    .feature a:hover {
        text-decoration: underline;
    }
</style>

<header>
    <div class="row3">
        <h2>Wybierz sekcję:</h2>
    </div>
</header>

<body>
    <main class="main-section">
        <div class="feature">
            <h3>Produkty</h3>
            <a href="admin_products.php">Zarządzaj</a>
        </div>
        <div class="feature">
            <h3>Promocje</h3>
            <a href="admin_promo.php">Zarządzaj</a>
        </div>
        <div class="feature">
            <h3>Użytkownicy</h3>
            <a href="admin_users.php">Zarządzaj</a>
        </div>
        <div class="feature">
            <h3>Forum</h3>
            <a href="admin_forum.php">Zarządzaj</a>
        </div>
    </main>


</body>
</html>
