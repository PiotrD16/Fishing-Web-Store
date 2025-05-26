<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtus - Internetowy sklep wędkarski</title>
    <link rel="stylesheet" href="/demo/templates/styles.css">
</head>

<!-- pasek na samej gorze -->
<header class="top">
    <nav>
        <ul>
            <li><a href="<?php echo '/demo/shop/shop.php'; ?>">Sklep</a></li>
            
            <li><a href="<?php echo '/demo/forum/forum.php'; ?>">Forum</a></li>
            
            <?php
            // Sprawdzenie, czy użytkownik jest zalogowany
            if (isset($_SESSION['user_id'])) { 
                // Jeśli użytkownik jest zalogowany, pokaż panel użytkownika i wylogowanie
                echo '<li><a href="/demo/login/profile_user.php">Panel użytkownika</a></li>';
                echo '<li class="logout-btn"><a href="/demo/login/logout.php">Wyloguj się</a></li>';
            } else {
                // Jeśli użytkownik nie jest zalogowany, pokaż link do logowania
                echo '<li class="login-btn"><a href="/demo/login/login.php">Logowanie</a></li>';
            }
            ?>
        </ul>
    </nav>
    
</header>

<!-- srodek -->
<header class="bar">

    <div class="left">
        <a href="/demo/main/main.php">
            <img src="/demo/logo.png" alt="logo" class="logo">
        </a>
    </div>

    <div class="center">
    <h1 class="site-title">Virtus - Internetowy sklep wędkarski</h1>
    </div>

    <div class="right">
        <div class="koszyk">
        <img src="/demo/koszyk.png" alt="koszyk" class="koszyk" width="25" height="25">
        <a href="/demo/shop/cart.php">Koszyk 
            <?php
            $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
            echo '(' . $cart_count . ')';  // Pokazuje liczbę przedmiotów w koszyku
            ?>
        </a>
    </div>

</header>
    
<!-- nawigacja -->
<nav class="menu">
    <ul class="jol">
        <li><a href="#">Kategorie</a></li>
        <li><a href="#">Produkty</a></li>
        <li><a href="#">Promocje</a></li>
        <li><a href="#">Program lojalnościowy</a></li>
        
    </ul>
</nav>
</body>
</html>
