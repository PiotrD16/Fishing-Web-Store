<?php
// Połączenie z bazą danych
$host = "localhost";
$username = "root";
$password = "";
$database = "webpage";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obsługa formularza zmiany ceny
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_price'])) {
        $product_id = intval($_POST['product_id']);
        $new_price = floatval($_POST['new_price']);

        $stmt = $conn->prepare("UPDATE products SET new_price = ? WHERE id = ?");
        $stmt->bind_param("di", $new_price, $product_id);
        $stmt->execute();
        $stmt->close();

    } elseif (isset($_POST['remove_promotion'])) {
        $product_id = intval($_POST['product_id']);

        $stmt = $conn->prepare("UPDATE products SET new_price = NULL WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();
    }

    // Przekierowanie po zakończeniu operacji na tę samą stronę
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Pobieranie produktów z bazy danych
$result = $conn->query("SELECT id, name, price, new_price, description FROM products");
?>

<?php include 'admin.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Promocje</title>
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
    table{
        margin: 0 auto;
    }

</style>

<header>
    <div class="row3">
        <h1>Promocje</h1>
        <a href="admin_panel.php">Powrót do panelu głównego</a>
    </div>
</header>
<body>

<main>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Produkt</th>
            <th>Opis</th>
            <th>Stara cena</th>
            <th>Nowa cena</th>
            <th>Akcje</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                <td><?php echo number_format($row['price'], 2); ?> zł</td>
                <td>
                    <?php if ($row['new_price'] !== null): ?>
                        <?php echo number_format($row['new_price'], 2); ?> zł
                    <?php else: ?>
                        Brak promocji
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['new_price'] === null): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="new_price" placeholder="Nowa cena" required>
                            <button type="submit" name="update_price">Zaktualizuj cenę</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="remove_promotion">Usuń promocję</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php $conn->close(); ?>
</main>
</body>
</html>
