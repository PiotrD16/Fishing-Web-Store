<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$host = "localhost";
$username = "root";
$password = "";
$database = "webpage";

try {
    $conn = mysqli_connect($host, $username, $password, $database);
} catch (mysqli_sql_exception) {
    echo "Connection terminated (server is offline!)";
}

$error_message = "";
$lockout_duration = 2 * 60 * 60; // 2 hours in seconds

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $current_time = time();

        // Sprawdzenie, czy konto jest zablokowane
        if ($user['failed_attempts'] >= 3 && $current_time - strtotime($user['last_failed_login']) < $lockout_duration) {
            $error_message = "Twoje konto jest zablokowane. Spróbuj ponownie za 2 godziny.";
        } else {
            // Sprawdzenie poprawności hasła
            if (password_verify($password, $user['passw'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['email'] === 'admin@gmail.com' ? 'admin' : 'user';

                // Resetowanie liczby nieudanych prób logowania
                $reset_sql = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE id = ?";
                $reset_stmt = mysqli_prepare($conn, $reset_sql);
                mysqli_stmt_bind_param($reset_stmt, "i", $user['id']);
                mysqli_stmt_execute($reset_stmt);

                // Przekierowanie użytkownika
                $redirect_url = $user['email'] === 'admin@gmail.com' ? "/demo/admin/admin_panel.php" : "/demo/main/main.php";
                header("Location: $redirect_url");
                exit;
            } else {
                // Zwiększenie liczby nieudanych prób logowania
                $failed_attempts = $user['failed_attempts'] + 1;
                $update_sql = "UPDATE users SET failed_attempts = ?, last_failed_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ii", $failed_attempts, $user['id']);
                mysqli_stmt_execute($update_stmt);

                $error_message = $failed_attempts >= 3
                    ? "Twoje konto zostało zablokowane na 2 godziny po 3 nieudanych próbach logowania."
                    : "Błędne dane! Próba $failed_attempts z 3.";
            }
        }
    } else {
        $error_message = "Błędne dane!";
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Virtus Sklep Wędkarski</title>
    <link rel="stylesheet" href="login.css?v=1">
</head>
<body>
<div class="container">
    <a href="/demo/main/main.php">
        <img src="../logo.png" alt="Virtus Sklep Wędkarski">
    </a>
    <h1>Zaloguj się</h1>

    <?php if (!empty($error_message)): ?>
    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Adres e-mail" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zaloguj</button>
    </form>

    <div class="register-link">
        <p>Nie masz konta? <a href="/demo/register/register.php">Zarejestruj się</a></p>
    </div>
</div>
</body>
</html>
