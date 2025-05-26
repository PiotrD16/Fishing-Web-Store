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

// Obsługa AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $response = ['success' => false, 'message' => '', 'html' => ''];

    switch ($_POST['ajax_action']) {
        case 'add_comment':
            if (isset($_POST['post_id'], $_POST['content']) && isset($_SESSION['user_id'])) {
                $post_id = (int)$_POST['post_id'];
                $content = trim($_POST['content']);

                if (!empty($content)) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO forum_comments (post_id, user_id, content, created_at, updated_at, likes, dislikes) 
                            VALUES (?, ?, ?, NOW(), NOW(), 0, 0)
                        ");
                        $stmt->execute([$post_id, $_SESSION['user_id'], $content]);

                        ob_start();
                        displayComments($post_id);
                        $response['html'] = ob_get_clean();

                        $response['success'] = true;
                        $response['message'] = 'Komentarz dodany pomyślnie.';
                    } catch (PDOException $e) {
                        $response['message'] = "Błąd wykonania zapytania: " . $e->getMessage();
                    }
                } else {
                    $response['message'] = 'Treść komentarza nie może być pusta.';
                }
            }
            break;

        case 'add_reaction':
            if (isset($_POST['id'], $_POST['reaction'], $_POST['type']) && isset($_SESSION['user_id'])) {
                $id = (int)$_POST['id'];
                $reaction = $_POST['reaction'];
                $type = $_POST['type'];

                try {
                    if ($type === 'post') {
                        $table = 'post_reactions';
                        $reaction_field = 'post_id';
                    } elseif ($type === 'comment') {
                        $table = 'comment_reactions';
                        $reaction_field = 'comment_id';
                    } else {
                        throw new Exception("Nieznany typ: $type");
                    }

                    $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ? AND $reaction_field = ?");
                    $stmt->execute([$_SESSION['user_id'], $id]);
                    $existing_reaction = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing_reaction) {
                        $stmt = $pdo->prepare("UPDATE $table SET reaction = ? WHERE user_id = ? AND $reaction_field = ?");
                        $stmt->execute([$reaction, $_SESSION['user_id'], $id]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO $table (user_id, $reaction_field, reaction) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $id, $reaction]);
                    }

                    $like_count = getReactionCount($table, $reaction_field, $id, 'like');
                    $dislike_count = getReactionCount($table, $reaction_field, $id, 'dislike');

                    $response['html'] = json_encode(['like' => $like_count, 'dislike' => $dislike_count]);
                    $response['success'] = true;
                } catch (Exception $e) {
                    $response['message'] = "Błąd: " . $e->getMessage();
                }
            }
            break;

        case 'load_comments':
            if (isset($_POST['post_id'])) {
                $post_id = (int)$_POST['post_id'];
                ob_start();
                displayComments($post_id);
                $response['html'] = ob_get_clean();
                $response['success'] = true;
            }
            break;

        default:
            $response['message'] = 'Nieznana akcja.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Funkcje pomocnicze
function getComments($post_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT forum_comments.*, users.username FROM forum_comments JOIN users ON forum_comments.user_id = users.id WHERE post_id = ? ORDER BY forum_comments.created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function displayComments($post_id) {
    $comments = getComments($post_id);
    if (empty($comments)) {
        echo '<p>Brak komentarzy.</p>';
    } else {
        foreach ($comments as $comment) {
            $reactions = getCommentReactions($comment['id']);
            echo '
                <div class="comment">
                    <p><strong>' . htmlspecialchars($comment['username']) . ':</strong> ' . nl2br(htmlspecialchars($comment['content'])) . '</p>
                    <div class="reactions">
                        <button onclick="addReaction(' . $comment['id'] . ', \'like\', \'comment\')">👍</button>
                        <span id="comment-likes-' . $comment['id'] . '">' . $reactions['like'] . '</span>
                        <button onclick="addReaction(' . $comment['id'] . ', \'dislike\', \'comment\')">👎</button>
                        <span id="comment-dislikes-' . $comment['id'] . '">' . $reactions['dislike'] . '</span>
                    </div>
                </div>';
        }
    }
}

function getCommentReactions($comment_id) {
    return getReactionCountData('comment_reactions', 'comment_id', $comment_id);
}

function getReactionCount($table, $field, $id, $reaction) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM $table WHERE $field = ? AND reaction = ?");
    $stmt->execute([$id, $reaction]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function getReactionCountData($table, $field, $id) {
    return [
        'like' => getReactionCount($table, $field, $id, 'like'),
        'dislike' => getReactionCount($table, $field, $id, 'dislike')
    ];
}

// Obsługa dodawania postów
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO forum_posts (user_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $title, $content]);

            header("Location: forum.php");
            exit();
        } catch (PDOException $e) {
            echo "Błąd wykonania zapytania: " . $e->getMessage();
        }
    } else {
        echo "Tytuł i treść postu nie mogą być puste.";
    }
}

$stmt = $pdo->query("SELECT forum_posts.*, users.username FROM forum_posts JOIN users ON forum_posts.user_id = users.id ORDER BY forum_posts.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../templates/all.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        main {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .forum-post, .add-post, .comments-container {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .forum-post h2 {
            margin-bottom: 10px;
        }

        .forum-post p {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .forum-post .reactions, .comments-container .reactions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .forum-post button, .add-post button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #ff6600;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .forum-post button:hover, .add-post button:hover {
            background-color: #e55c00;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin: 10px 0;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        hr {
            margin: 20px 0;
            border: 1px solid #ddd;
        }

        .comment {
            padding: 10px;
            border-top: 1px solid #ddd;
        }

        .comment p {
            margin: 5px 0;
        }

        .reactions span {
            display: inline-block;
            min-width: 20px;
        }
    </style>
    <script>
        async function addReaction(id, reaction, type) {
            const response = await fetch('forum.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ ajax_action: 'add_reaction', id, reaction, type })
            });
            const result = await response.json();
            if (result.success) {
                const counts = JSON.parse(result.html);
                document.querySelector(`#${type}-likes-${id}`).innerText = counts.like;
                document.querySelector(`#${type}-dislikes-${id}`).innerText = counts.dislike;
            }
        }
    </script>
</head>
<body>
<main>
    <h1>Forum</h1>

    <!-- Dodaj nowy post -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="add-post">
        <h2>Dodaj nowy post</h2>
        <form method="POST" action="forum.php">
            <input type="text" name="title" placeholder="Tytuł" required><br>
            <textarea name="content" placeholder="Treść" required></textarea><br>
            <button type="submit" name="add_post">Dodaj post</button>
        </form>
    </div>
    <?php else: ?>
    <a href="/demo/login/login.php">Zaloguj się</a>
    <?php endif; ?>

    <!-- Wyświetlanie postów -->
    <?php if (empty($posts)): ?>
    <p>Brak postów na forum.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <div class="forum-post">
            <h2><?= htmlspecialchars($post['title']); ?></h2>
            <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>
            <p><strong>Autor:</strong> <?= htmlspecialchars($post['username']); ?></p>

            <!-- Reakcje dla postów -->
            <div class="reactions">
                <button onclick="addReaction(<?= $post['id']; ?>, 'like', 'post')">👍</button>
                <span id="post-likes-<?= $post['id']; ?>"><?= getReactionCount('post_reactions', 'post_id', $post['id'], 'like'); ?></span>
                <button onclick="addReaction(<?= $post['id']; ?>, 'dislike', 'post')">👎</button>
                <span id="post-dislikes-<?= $post['id']; ?>"><?= getReactionCount('post_reactions', 'post_id', $post['id'], 'dislike'); ?></span>
            </div>

            <!-- Komentarze -->
            <button id="toggle-button-<?= $post['id']; ?>" onclick="toggleComments(<?= $post['id']; ?>)">Pokaż komentarze</button>
            <div id="comments-<?= $post['id']; ?>" style="display: none;"></div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <textarea id="comment-input-<?= $post['id']; ?>" placeholder="Dodaj komentarz..."></textarea>
            <button onclick="addComment(<?= $post['id']; ?>)">Dodaj komentarz</button>
            <?php endif; ?>
        </div>
        <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script>
    function toggleComments(postId) {
        const commentsDiv = document.getElementById(`comments-${postId}`);
        const button = document.getElementById(`toggle-button-${postId}`);
        if (commentsDiv.style.display === "none") {
            commentsDiv.style.display = "block";
            button.innerText = "Ukryj komentarze";
            loadComments(postId);
        } else {
            commentsDiv.style.display = "none";
            button.innerText = "Pokaż komentarze";
        }
    }

    async function loadComments(postId) {
        const response = await fetch('forum.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ ajax_action: 'load_comments', post_id: postId })
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById(`comments-${postId}`).innerHTML = result.html;
        }
    }

    async function addComment(postId) {
        const content = document.getElementById(`comment-input-${postId}`).value;
        const response = await fetch('forum.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ ajax_action: 'add_comment', post_id: postId, content })
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById(`comments-${postId}`).innerHTML = result.html;
            document.getElementById(`comment-input-${postId}`).value = '';
        } else {
            alert(result.message);
        }
    }
</script>
</body>
</html>
<?php include '../templates/footer.php'; ?>
