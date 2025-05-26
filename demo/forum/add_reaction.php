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

if (isset($_POST['reaction'], $_POST['post_id'])) {
    $reaction = $_POST['reaction'];  // like lub dislike
    $post_id = (int) $_POST['post_id'];  // ID posta

    // Sprawdzamy, czy użytkownik już zareagował
    $stmt = $pdo->prepare("SELECT * FROM post_reactions WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $existingReaction = $stmt->fetch();

    if ($existingReaction) {
        // Zaktualizuj reakcję
        if ($existingReaction['reaction'] !== $reaction) {
            // Zmiana reakcji
            $stmt = $pdo->prepare("UPDATE post_reactions SET reaction = ? WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$reaction, $post_id, $_SESSION['user_id']]);

            // Zaktualizuj licznik reakcji
            if ($reaction == 'like') {
                $stmt = $pdo->prepare("UPDATE forum_posts SET likes = likes + 1, dislikes = dislikes - 1 WHERE id = ?");
                $stmt->execute([$post_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE forum_posts SET likes = likes - 1, dislikes = dislikes + 1 WHERE id = ?");
                $stmt->execute([$post_id]);
            }
        }
    } else {
        // Dodaj nową reakcję
        $stmt = $pdo->prepare("INSERT INTO post_reactions (post_id, user_id, reaction) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $reaction]);

        // Zaktualizuj licznik reakcji
        if ($reaction == 'like') {
            $stmt = $pdo->prepare("UPDATE forum_posts SET likes = likes + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE forum_posts SET dislikes = dislikes + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
        }
    }
}

// Przekierowanie z powrotem do forum
header("Location: forum.php");
exit;