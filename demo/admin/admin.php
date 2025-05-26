<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtus Pro - Panel Administratora</title>
    <style>
        /* Styl ogólny dla body */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #151731;
        }
        /* Wiersz 1 - górny pasek */
        .row1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #0b4063;
            color: white;
        }

        .row1 > div {
            display: flex;
            /* align-items: center; */
        }

        .row1 a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            margin: 0 15px;
            transition: color 0.3s;
        }

        .row1 a:hover {
            color: #f39c12;
        }

        /* Wiersz 2 - środkowy pasek */
        .row2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f8f8;
            color: #151731;
        }

        .row2 .left, .row2 .right {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 90px;
        }

        .center {
            color: rgb(0, 0, 0);
            font-size: 24px;
            flex-grow: 1;
            text-align: center;
        }

    </style>
</head>
<body>

<!-- Nagłówek: trzy sekcje -->
<header class="top-header">
    <!-- Wiersz 1 (górny pasek) -->
    <div class="row1">
        <div class="left">
            <a href="<?php echo '/demo/shop/shop.php'; ?>">Sklep</a>
        </div>

        <div class="center">
            <a href="<?php echo '/demo/forum/forum.php'; ?>">Forum</a>
        </div>

        <div class="right">
            <?php
            if (isset($_SESSION['user_id'])) {

                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo '<a href="/demo/admin/admin_panel.php">Panel administratora</a>';
                }
                echo '<a href="/demo/login/logout.php" class="logout-btn">Wyloguj się</a>';
            } else {
                echo '<a href="/demo/login/login.php" class="login-btn">Logowanie</a>';
            }
            ?>
        </div>
    </div>

    <!-- Wiersz 2 (środkowy pasek) -->
    <div class="row2">
        <div class="left">
            <a href="/demo/main/main.php">
                <img src="/demo/logo.png" alt="logo" class="logo">
            </a>
        </div>

        <div class="center">
            <h1 class="site-title">Virtus Pro - Panel Administratora</h1>
        </div>

    </div>

</header>

</body>


</html>
