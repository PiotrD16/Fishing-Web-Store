<?php
session_start();
require_once '../classes/Database.php';

$db = (new Database())->connect();

// Usuwanie wpisu na forum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    $postId = $_POST['post_id'];

    $stmt = $db->prepare("DELETE FROM forum_posts WHERE id = ?");
    $stmt->execute([$postId]);
    $_SESSION['message'] = "Wpis został usunięty.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Usuwanie komentarza na forum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    $commentId = $_POST['comment_id'];

    $stmt = $db->prepare("DELETE FROM forum_comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $_SESSION['message'] = "Komentarz został usunięty.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Pobieranie użytkowników
$stmt = $db->query("SELECT id, username, points FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie wpisów na forum z informacją o użytkownikach
$stmt = $db->query("
    SELECT forum_posts.*, users.username 
    FROM forum_posts 
    LEFT JOIN users ON forum_posts.user_id = users.id
");
$forumPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie komentarzy na forum z informacją o użytkownikach
$stmt = $db->query("
    SELECT forum_comments.*, users.username, forum_comments.post_id
    FROM forum_comments 
    LEFT JOIN users ON forum_comments.user_id = users.id
");
$forumComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grupowanie komentarzy według postów
$commentsByPost = [];
foreach ($forumComments as $comment) {
    $commentsByPost[$comment['post_id']][] = $comment;
}
?>

<?php include 'admin.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/styles.css">
    <title>Panel Administratora</title>
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
    hr {
        border: 0;
        border-top: 2px solid #ccc;
        margin: 20px 0;
    }

</style>

<header>
    <div class="row3">
        <h1>Forum</h1>
        <a href="admin_panel.php">Powrót do panelu głównego</a>
    </div>
</header>
<body>
    <main>
        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Sekcja forum -->
        <h2>Wpisy na forum</h2>
        <ul>
            <?php foreach ($forumPosts as $post): ?>
                <li>
                    <b><?= htmlspecialchars($post['title']) ?></b> - <?= htmlspecialchars($post['content']) ?>
                    <br>
                    <small><i>Dodane przez: <?= htmlspecialchars($post['username'] ?? 'Nieznany użytkownik') ?></i></small>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit">Usuń wpis</button>
                    </form>

                    <!-- Wyświetlanie komentarzy pod wpisem -->
                    <h3>Komentarze:</h3>
                    <?php if (isset($commentsByPost[$post['id']]) && count($commentsByPost[$post['id']]) > 0): ?>
                        <ul>
                            <?php foreach ($commentsByPost[$post['id']] as $comment): ?>
                                <li>
                                    <?= htmlspecialchars($comment['content']) ?>
                                    <br>
                                    <small><i>Dodane przez: <?= htmlspecialchars($comment['username'] ?? 'Nieznany użytkownik') ?></i></small>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <button type="submit">Usuń komentarz</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Brak komentarzy.</p>
                    <?php endif; ?>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    </main>
</body>

</html>