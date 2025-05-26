<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /demo/login/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['content'])) {
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

    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Dodanie komentarza
    $stmt = $pdo->prepare("INSERT INTO forum_comments (user_id, post_id, content) VALUES (:user_id, :post_id, :content)");
    $stmt->execute([
        'user_id' => $user_id,
        'post_id' => $post_id,
        'content' => $content
    ]);

    // Przekierowanie po dodaniu komentarza
    header("Location: /demo/forum/view_post.php?id=" . $post_id);
    exit;
}
?>