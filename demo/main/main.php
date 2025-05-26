<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

?>

<?php include '../templates/all.php'; ?> <!-- Włączenie nagłówka -->

<?php
// Sprawdzamy, czy użytkownik jest zalogowany
// if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_name']) || !isset($_SESSION['email'])){
//     session_destroy();
//     header("Location: /demo/login/login.php");
//     exit();
// }

// Obsługa wylogowania
if (isset($_POST['logout'])) {
    session_destroy(); // Zakończenie sesji
    header("Location: /demo/login/login.php"); // Przekierowanie do strony logowania
    exit();
}

// Pobieranie danych użytkownika z sesji
// $user_name = $_SESSION['user_name'];
// $email = $_SESSION['email'];
// ?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtus - Internetowy sklep wędkarski</title>
<style>
    /* Główna sekcja */
    html, body {
        height: 100%; /* Wysokość 100% przeglądarki */
        margin: 0;
        padding: 0;
    }
    .wrapper {
        display: flex;
        flex-direction: column;
        min-height: 73.5%; /* Rozciąga wrapper na pełną wysokość */
    }
    .main-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
        background-color: #fff;
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
        font-size: 20px;
        margin: 10px 0;
        color: #333;
    }

    .feature a {
        color: #ff6600;
        font-weight: bold;
        text-decoration: none;
    }

    .feature a:hover {
        text-decoration: underline;
    }

</style>
</head>
<body>
<div class="wrapper">
    <!-- Główna sekcja -->
    <main class="main-section">
        <div class="feature">
            <h3>Kupujesz = Oszczędzasz!</h3>
            <a href="/demo/program_lojal/program_lojal.php">Sprawdź szczegóły</a>
        </div>
        <div class="feature">
            <h3>Złów najlepsze okazje!</h3>
            <a href="/demo/promo/promo.php">Sprawdź szczegóły</a>
        </div>
        <div class="feature">
            <h3>Rozpoczęcie sezonu tylko z nami!</h3>
            <a href="/demo/nowosci/nowosci.php">Sprawdź</a>
        </div>
    </main>
</div>
<?php include '../templates/footer.php'; ?>

</body>
</html>
