<?php
session_start();
require_once '../classes/Database.php';

$db = (new Database())->connect();

// Pobieranie produktów
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie kategorii
$stmt = $db->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obsługa dodawania, edycji i usuwania produktów
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add_product') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $stock_quantity = $_POST['stock_quantity'];

        // Walidacja, aby ilość produktów nie była ujemna
        if ($stock_quantity < 0) {
            $_SESSION['message'] = "Ilość produktów nie może być ujemna.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = file_get_contents($_FILES['image']['tmp_name']); // Odczytanie obrazu jako BLOB
        }

        $stmt = $db->prepare("INSERT INTO products (name, price, description, category_id, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $category_id, $stock_quantity, $image]);
    } elseif ($action === 'delete_product') {
        $productId = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
    } elseif ($action === 'edit_product') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $category_id = $_POST['category'];
        $stock_quantity = $_POST['quantity'];

        // Walidacja, aby ilość produktów nie była ujemna
        if ($stock_quantity < 0) {
            $_SESSION['message'] = "Ilość produktów nie może być ujemna.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = file_get_contents($_FILES['image']['tmp_name']); // Odczytanie obrazu jako BLOB
        }

        $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = ?, stock_quantity = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $category_id, $stock_quantity, $image, $id]);
    }

    header("Location: admin_products.php");
    exit;
}
?>

<?php include 'admin.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="../css/styles.css">
<title>Produkty - Panel Administratora</title>
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
    .main-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .product-container {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
    }
    .product-info {
        flex-grow: 1;
        margin-right: 10px; /* Mniejszy odstęp między opisem a zdjęciem */
    }
    .product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin-left: 10px; /* Mniejszy odstęp od opisu */
    }
    .action-buttons {
        display: inline-flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .edit-form {
        display: none;
    }
    .form-group {
        margin-bottom: 10px;
    }
</style>
</head>
<header>
    <div class="row3">
        <h1>Produkty</h1>
        <a href="admin_panel.php">Powrót do panelu głównego</a>
    </div>
</header>

<body>

    <main>
        <h2>Lista produktów</h2>
        <ul>
            <?php foreach ($products as $product): ?>
                <li class="product-container">
                    <!-- Opis produktu -->
                    <div class="product-info">
                        <b><?= htmlspecialchars($product['name']) ?></b> 
                        - Cena: <?= $product['price'] ?> PLN 
                        - Ilość: <?= $product['stock_quantity'] ?>
                        <br>
                        <?= htmlspecialchars($product['description']) ?>
                        <br>
                        <?php
                            // Pobieranie nazwy kategorii na podstawie category_id
                            $categoryStmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
                            $categoryStmt->execute([$product['category_id']]);
                            $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        Kategoria: <?= htmlspecialchars($category['name'] ?? 'Brak kategorii') ?>
                    </div>

                    <!-- Zdjęcie i przyciski edycji i usuwania -->
                    <div class="action-buttons">
                        <div>
                            <?php if ($product['image']): ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($product['image']) ?>" alt="Product Image" class="product-image">
                            <?php else: ?>
                                <p>Brak zdjęcia</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="action" value="delete_product">
                            <button type="submit">Usuń</button>
                        </form>
                        <button onclick="toggleEditForm(<?= $product['id'] ?>)">Edytuj</button>
                    </div>

                    <!-- Formularz edycji -->
                    <form method="POST" enctype="multipart/form-data" class="edit-form" id="edit-form-<?= $product['id'] ?>">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="action" value="edit_product">
                        <div class="form-group">
                            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
                        </div>
                        <div class="form-group">
                            <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <select name="category" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="number" name="quantity" value="<?= $product['stock_quantity'] ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <input type="file" name="image">
                        </div>
                        <div class="form-group">
                            <button type="submit">Zapisz</button>
                        </div>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Dodaj Produkt</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_product">
            <input type="text" name="name" placeholder="Nazwa produktu" required>
            <input type="number" step="0.01" name="price" placeholder="Cena" required>
            <textarea name="description" placeholder="Opis produktu"></textarea>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="stock_quantity" placeholder="Ilość" min="0" required>
            <input type="file" name="image">
            <button type="submit">Dodaj</button>
        </form>
    </main>
    
    <script>
        function toggleEditForm(id) {
            const form = document.getElementById('edit-form-' + id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
