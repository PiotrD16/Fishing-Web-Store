<?php 
// Połączenie z bazą danych
$host = 'localhost'; 
$db = 'webpage'; 
$db_user = 'root';  // Zmieniono nazwę zmiennej
$db_pass = ''; 
$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
} catch (PDOException $e) {
    echo "Błąd połączenia z bazą danych: " . $e->getMessage();
    exit;
}

// Pobierz wszystkie kategorie
$category_stmt = $pdo->query("SELECT * FROM categories");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobierz wszystkie emaile użytkowników
$user_stmt = $pdo->query("SELECT email FROM users");
$emails = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtus - Internetowy sklep wędkarski</title>
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
            flex-grow: 1;
            text-align: center;
        }

        .koszyk {
            display: flex;
            align-items: center;
        }

        .koszyk img {
            margin-right: 5px;
        }

        .koszyk a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            transition: color 0.3s;
        }

        .koszyk a:hover {
            color: #007bff;
        }

        /* Wiersz 3 - dolny pasek (nawigacja) */
        .row3 {
            background-color: #0b4063;
            color: #fff;
            padding: 10px 20px;
        }

        nav {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            position: relative;
            margin: 0 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #f39c12; /* Kolor po najechaniu */
        }

        /* rozwijane menu */
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #0b4063;
            top: 100%;
            left: 0;
            list-style: none;
            margin: 0;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            min-width: 180px;
        }

        .dropdown-menu li {
            padding: 10px 15px;
        }

        .dropdown-menu li a {
            color: white;
            text-decoration: none;
            font-weight: normal;
            display: block;
        }

        .dropdown-menu li a:hover {
            text-decoration: underline;
            color: white;
        }

        /* Pokaż menu po najechaniu */
        .dropdown:hover .dropdown-menu {
            display: block;
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
                else{
                    echo '<a href="/demo/login/profile_user.php">Panel użytkownika</a>';
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
        </div>
    </div>

    <!-- Wiersz 3 (dolny pasek) - nawigacja -->
    <div class="row3">
        <nav class="menu">
            <ul>
                <li class="dropdown">
                    <a href="/demo/shop/shop.php">Produkty</a>
                    <ul class="dropdown-menu">
                        <li><a href="/demo/shop/shop.php">Wszystko</a></li>
                        <?php foreach ($categories as $category): ?>
    			<?php if ($category['name'] !== 'Promocje' && $category['name'] !== 'Nowości'): ?>
    			    <li><a href="shop.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
  			 <?php endif; ?>
			<?php endforeach; ?>

                    </ul>
                </li>
		        <li><a href="../nowosci/nowosci.php">Nowości</a></li>
                <li><a href="/demo/promo/promo.php">Promocje</a></li>
                <li><a href="/demo/program_lojal/program_lojal.php">Program lojalnościowy</a></li>
            </ul>
        </nav>
    </div>
</header>

</body>


</html>
