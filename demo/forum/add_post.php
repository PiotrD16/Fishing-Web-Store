<?php
// Rozpoczęcie sesji, aby sprawdzić, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Jeśli użytkownik nie jest zalogowany, przekierowanie do logowania
    exit();
}

// Połączenie z bazą danych za pomocą PDO
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

// Po przesłaniu formularza, zapisujemy dane do bazy
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pobieramy dane z formularza
    $title = htmlspecialchars($_POST['title']); // Zabezpieczenie przed XSS
    $content = htmlspecialchars($_POST['content']); // Zabezpieczenie przed XSS
    $user_id = $_SESSION['user_id']; // Użytkownik musi być zalogowany

    // Wstawianie postu do bazy danych
    $query = "INSERT INTO forum_posts (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $title, $content]);

    if ($stmt->rowCount()) {
        // Przekierowanie po pomyślnym dodaniu postu
        header("Location: forum.php");
        exit();
    } else {
        // Jeśli nie udało się dodać postu, wyświetl komunikat
        echo "<p>Wystąpił błąd podczas dodawania postu. Spróbuj ponownie.</p>";
    }
}
?>
<?php include '../templates/all.php'; ?> <!-- Włączenie nagłówka -->

<!-- Formularz dodawania postu -->
<h1>Dodaj nowy post</h1>
<form action="add_post.php" method="POST">
    <label for="title">Tytuł:</label>
    <input type="text" id="title" name="title" required>

    <label for="content">Treść:</label>
    <textarea id="content" name="content" rows="4" required></textarea>

    <button type="submit">Dodaj post</button>
</form>
<?php include '../templates/footer.php'; ?>