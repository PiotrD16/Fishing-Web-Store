<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "webpage";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection terminated (server is offline!)");
}

$error_message = "";
$user_name = $email = "";

// Sprawdzamy, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Walidacja danych
    if (empty($user_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Wszystkie pola są wymagane.";
    } 
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Nieprawidłowy format adresu email.";
    } 
    else if ($password != $confirm_password) {
        $error_message = "Hasła nie są identyczne!";
    } 
    else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $password)) {
        $error_message = "Hasło musi zawierać co najmniej 8 znaków, jedną małą literę i jedną wielką literę.";
    } 
    else {
        // Sprawdzamy, czy użytkownik już istnieje
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $user_name, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Podana nazwa użytkownika lub email są już zajęte.";
        } 
        else {
            // Rejestracja użytkownika
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, passw, points) VALUES (?, ?, ?, 0)");
            $stmt->bind_param("sss", $user_name, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: /demo/reg_result/success.html");
                exit();
            } else {
                $error_message = "Nie udało się zarejestrować użytkownika.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - Virtus Sklep Wędkarski</title>
    <link rel="stylesheet" href="register.css?v=1">
</head>

<body>
    <div class="container">
        <a href="/demo/main/main.php">
            <img src="../logo.png" alt="Virtus Sklep Wędkarski">
        </a>
        <h1>Rejestracja</h1>
        <form action="" method="POST">
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
            <div class="form-group">
                <label for="username">Nazwa użytkownika</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_name) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Adres email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Potwierdź hasło</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Zarejestruj się</button>
        </form>
        <div class="login-link">
            Masz już konto? <a href="/demo/login/login.php">Zaloguj się</a>
        </div>
    </div>
</body>

</html>
