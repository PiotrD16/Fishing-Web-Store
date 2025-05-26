<?php
session_start();

// Połączenie z bazą danych
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

// Obsługa dodawania komentarzy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['content'])) {
    $post_id = (int) $_POST['post_id'];
    $content = trim($_POST['content']);

    if (!empty($content) && isset($_SESSION['user_id'])) {
        // Dodaj komentarz do bazy danych
        try {
            $stmt = $pdo->prepare("
                INSERT INTO forum_comments (post_id, user_id, content, created_at, updated_at, likes, dislikes) 
                VALUES (?, ?, ?, NOW(), NOW(), 0, 0)
            ");
            $stmt->execute([$post_id, $_SESSION['user_id'], $content]);
	
// Przekierowanie na stronę forum po dodaniu komentarza
            header("Location: forum.php");
            exit;
        } catch (PDOException $e) {
            die("Błąd wykonania zapytania: " . $e->getMessage());
        }
    } else {
        $error_message = "Komentarz nie może być pusty.";
    }
}

// Pobranie postów na forum
$stmt = $pdo->query("SELECT forum_posts.*, users.username FROM forum_posts JOIN users ON forum_posts.user_id = users.id ORDER BY forum_posts.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obsługa reakcji na post
if (isset($_POST['reaction'], $_POST['post_id'])) {
    $reaction = $_POST['reaction'];  // like lub dislike
    $post_id = (int) $_POST['post_id'];  // ID posta

    if (isset($_SESSION['user_id'])) {
        // Sprawdzamy, czy użytkownik już dodał reakcję na ten post
        $stmt = $pdo->prepare("SELECT * FROM post_reactions WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$_SESSION['user_id'], $post_id]);
        $existing_reaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_reaction) {
            // Jeśli reakcja istnieje, aktualizujemy ją
            $stmt = $pdo->prepare("UPDATE post_reactions SET reaction = ? WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$reaction, $_SESSION['user_id'], $post_id]);
        } else {
            // Jeśli reakcja nie istnieje, dodajemy ją
            $stmt = $pdo->prepare("INSERT INTO post_reactions (user_id, post_id, reaction) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $post_id, $reaction]);
        }
    }
}

// Obsługa reakcji na komentarz
if (isset($_POST['reaction_comment'], $_POST['comment_id'])) {
    $reaction = $_POST['reaction_comment'];  // like lub dislike
    $comment_id = (int) $_POST['comment_id'];  // ID komentarza

    if (isset($_SESSION['user_id'])) {
        // Sprawdzamy, czy użytkownik już dodał reakcję na ten komentarz
        $stmt = $pdo->prepare("SELECT * FROM comment_reactions WHERE user_id = ? AND comment_id = ?");
        $stmt->execute([$_SESSION['user_id'], $comment_id]);
        $existing_reaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_reaction) {
            // Jeśli reakcja istnieje, aktualizujemy ją
            $stmt = $pdo->prepare("UPDATE comment_reactions SET reaction = ? WHERE user_id = ? AND comment_id = ?");
            $stmt->execute([$reaction, $_SESSION['user_id'], $comment_id]);
        } else {
            // Jeśli reakcja nie istnieje, dodajemy ją
            $stmt = $pdo->prepare("INSERT INTO comment_reactions (user_id, comment_id, reaction) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $comment_id, $reaction]);
        }
    }
}

// Pobieranie komentarzy dla każdego posta
function getComments($post_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT forum_comments.*, users.username FROM forum_comments JOIN users ON forum_comments.user_id = users.id WHERE post_id = ? ORDER BY forum_comments.created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobieranie reakcji dla komentarza
function getCommentReactions($comment_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS like_count FROM comment_reactions WHERE comment_id = ? AND reaction = 'like'");
    $stmt->execute([$comment_id]);
    $like_count = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

    $stmt = $pdo->prepare("SELECT COUNT(*) AS dislike_count FROM comment_reactions WHERE comment_id = ? AND reaction = 'dislike'");
    $stmt->execute([$comment_id]);
    $dislike_count = $stmt->fetch(PDO::FETCH_ASSOC)['dislike_count'];

    return ['like' => $like_count, 'dislike' => $dislike_count];
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleComments(postId) {
            var comments = document.getElementById("comments-" + postId);
            var button = document.getElementById("toggle-button-" + postId);
            if (comments.style.display === "none") {
                comments.style.display = "block";
                button.innerHTML = "Zwiń komentarze";
            } else {
                comments.style.display = "none";
                button.innerHTML = "Pokaż komentarze";
            }
        }
    </script>
</head>
<body>

<?php include '../templates/all.php'; ?>

<main>
    <h1>Forum</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="add_post.php">Dodaj nowy post</a>
    <?php else: ?>
        <a href="/demo/login/login.php">Zaloguj się</a>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <p>Brak postów na forum.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="forum-post">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($post['username']); ?></p>

                <!-- Wyświetlanie liczby reakcji -->
                <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) AS like_count FROM post_reactions WHERE post_id = ? AND reaction = 'like'");
                    $stmt->execute([$post['id']]);
                    $like_count = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

                    $stmt = $pdo->prepare("SELECT COUNT(*) AS dislike_count FROM post_reactions WHERE post_id = ? AND reaction = 'dislike'");
                    $stmt->execute([$post['id']]);
                    $dislike_count = $stmt->fetch(PDO::FETCH_ASSOC)['dislike_count'];
                ?>
                <p>Liczba reakcji: <?php echo $like_count; ?> Like, <?php echo $dislike_count; ?> Dislike</p>

                <!-- Formularz reakcji na post -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="forum.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="reaction" value="like">
                        <button type="submit">Like</button>
                    </form>
                    <form action="forum.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="reaction" value="dislike">
                        <button type="submit">Dislike</button>
                    </form>
                <?php endif; ?>

                <!-- Pokaż/Zwiń komentarze -->
                <button id="toggle-button-<?php echo $post['id']; ?>" onclick="toggleComments(<?php echo $post['id']; ?>)">Pokaż komentarze</button>
                
                <div id="comments-<?php echo $post['id']; ?>" style="display: none;">
                    <!-- Wyświetlanie komentarzy -->
                    <h3>Komentarze:</h3>
                    <?php
                        $comments = getComments($post['id']);
                        if (empty($comments)):
                    ?>
                        <p>Brak komentarzy.</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                
                                <!-- Wyświetlanie reakcji na komentarz -->
                                <?php
                                    $reactions = getCommentReactions($comment['id']);
                                ?>
                                <p>Liczba reakcji: <?php echo $reactions['like']; ?> Like, <?php echo $reactions['dislike']; ?> Dislike</p>

                                <!-- Formularz reakcji na komentarz -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="forum.php" method="POST">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="reaction_comment" value="like">
                                        <button type="submit">Like</button>
                                    </form>
                                    <form action="forum.php" method="POST">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="reaction_comment" value="dislike">
                                        <button type="submit">Dislike</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Formularz dodawania komentarza -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="forum.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <textarea name="content" placeholder="Dodaj komentarz..." rows="3" required></textarea>
                            <button type="submit">Dodaj komentarz</button>
                        </form>
                    <?php else: ?>
                        <p><a href="/demo/login/login.php">Zaloguj się, aby dodać komentarz.</a></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>