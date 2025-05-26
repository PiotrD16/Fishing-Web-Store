<?php
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Nieprawidłowy identyfikator posta.";
    exit;
}

$host = 'localhost'; 
$db = 'webpage'; 
$user = 'root'; 
$pass = ''; 
$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo "Błąd połączenia z bazą danych: " . $e->getMessage();
    exit;
}

$post_id = $_GET['id'];

// Pobranie danych posta
$stmt = $pdo->prepare("SELECT forum_posts.*, users.username FROM forum_posts JOIN users ON forum_posts.user_id = users.id WHERE forum_posts.id = :post_id");
$stmt->execute(['post_id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Post nie istnieje.";
    exit;
}

// Pobranie komentarzy do posta
$stmt = $pdo->prepare("SELECT forum_comments.*, users.username FROM forum_comments JOIN users ON forum_comments.user_id = users.id WHERE forum_comments.post_id = :post_id ORDER BY forum_comments.created_at ASC");
$stmt->execute(['post_id' => $post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Virtus Sklep Wędkarski</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo-container">
        <img src="image.png" alt="Virtus Sklep Wędkarski">
        <h1>Forum - <?php echo htmlspecialchars($post['title']); ?></h1>
    </div>
</header>

<main>
    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
    <p><strong>Autor:</strong> <?php echo htmlspecialchars($post['username']); ?> - <strong>Data:</strong> <?php echo $post['created_at']; ?></p>
    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

    <h3>Komentarze</h3>
    <?php if (empty($comments)): ?>
        <p>Brak komentarzy.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    <p><strong>Data:</strong> <?php echo $comment['created_at']; ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3>Dodaj komentarz</h3>
    <form action="add_comment.php" method="POST">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <textarea name="content" placeholder="Twój komentarz..." required></textarea>
        <button type="submit">Dodaj komentarz</button>
    </form>
</main>

<footer>
    <p>&copy; 2024 Virtus - Sklep Wędkarski</p>
</footer>

</body>
</html>