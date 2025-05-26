<?php
session_start();
require_once '../classes/Database.php';

$db = (new Database())->connect();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    // Jeśli nie, przekieruj do strony logowania
    header('Location: /demo/login/login.php');
    exit;
}

// Pobieranie użytkownika
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Sprawdzenie, czy użytkownik istnieje w bazie
if (!$user) {
    // Jeśli nie ma użytkownika w bazie, przekieruj do strony logowania
    header('Location: /demo/login/login.php');
    exit;
}

// Funkcja obliczająca punkty na podstawie gatunku i długości ryby
function calculatePoints($fishType, $length) {
    $points = 0;
    $legalLength = 0;

    // Ustalanie wymiaru ochronnego oraz przelicznika
    switch ($fishType) {
        case 'szczupak':
            $legalLength = 50;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.2 : 0;
            break;
        case 'sandacz':
            $legalLength = 45;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.4 : 0;
            break;
        case 'okoń':
            $legalLength = 25;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.1 : 0;
            break;
        case 'karp':
            $legalLength = 30;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.2 : 0;
            break;
        case 'lin':
            $legalLength = 30;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.3 : 0;
            break;
        case 'pstrąg':
            $legalLength = 30;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.5 : 0;
            break;
        case 'sum':
            $legalLength = 50;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.01 : 0;
            break;
        case 'kleń':
            $legalLength = 30;
            $points = ($length > $legalLength) ? ($length - $legalLength) * 0.1 : 0;
            break;
    }

    return $points;
}

// Obsługa dodawania zdjęcia ryby
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fish_image'])) {
    $fishType = $_POST['fish_type'];
    $fishLength = $_POST['fish_length'];
    $imageData = null;

    // Sprawdzenie, czy plik jest poprawny
    if (isset($_FILES['fish_image']) && $_FILES['fish_image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['fish_image']['tmp_name']); // Zapisanie pliku jako dane binarne
    }

    // Obliczanie punktów
    $points = calculatePoints($fishType, $fishLength);

    // Zapisanie informacji do bazy
    $stmt = $db->prepare("INSERT INTO photos (user_id, fish_type, fish_length, photo_data, points) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $fishType, $fishLength, $imageData, $points]);

    // Aktualizacja punktów użytkownika
    $stmt = $db->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $stmt->execute([$points, $_SESSION['user_id']]);

    header('Location: /demo/program_lojal/program_lojal.php');
    exit;
}

// Pobieranie zdjęć z bazy
$stmt = $db->prepare("SELECT * FROM photos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sprawdzanie, czy zapytanie zwróciło dane
if ($photos === false) {
    echo "Błąd pobierania zdjęć. Spróbuj ponownie później.";
    exit;
}
?>

<?php include '../templates/all.php'; ?> <!-- nagłówek -->

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Program Lojalnościowy</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .main-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            background-color: #fff;
        }

        .card {
            background-color: #f9f9f9;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card img {
            width: 100px;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #333;
            color: #fff;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #e55c00;
        }

        .photos-section {
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .photos-section h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 25px;
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <h1 style="text-align: center; margin-bottom: 20px;">Program Lojalnościowy</h1>
    <div class="main-section">
        <div class="card">
            <h3>Witaj, <?= htmlspecialchars($user['username']); ?>!</h3>
            <p>Twoje punkty: <strong><?= htmlspecialchars($user['points']); ?></strong></p>
        </div>

        <div class="card">
            <h3>Dodaj zdjęcie złowionej ryby</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Rodzaj ryby:</label>
                <select name="fish_type" required>
                    <option value="szczupak">Szczupak</option>
                    <option value="sandacz">Sandacz</option>
                    <option value="okoń">Okoń</option>
                    <option value="karp">Karp</option>
                    <option value="lin">Lin</option>
                    <option value="pstrąg">Pstrąg</option>
                    <option value="sum">Sum</option>
                    <option value="kleń">Kleń</option>
                </select>
                <br><br>
                <label>Długość ryby (cm):</label>
                <input type="number" name="fish_length" required>
                <br><br>
                <label>Zdjęcie ryby:</label>
                <input type="file" name="fish_image" required>
                <br><br>
                <button type="submit" class="btn">Dodaj zdjęcie</button>
            </form>
        </div>
    </div>

    <div class="photos-section">
        <h3>Twoje zdjęcia i punkty</h3>
        <table>
            <thead>
                <tr>
                    <th>Rodzaj ryby</th>
                    <th>Długość (cm)</th>
                    <th>Zdjęcie</th>
                    <th>Punkty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($photos as $photo): ?>
                <tr>
                    <td><?= htmlspecialchars($photo['fish_type']); ?></td>
                    <td><?= htmlspecialchars($photo['fish_length']); ?> cm</td>
                    <td><img src="data:image/jpeg;base64,<?= base64_encode($photo['photo_data']); ?>" style="width:100px; height:auto;"></td>
                    <td><?= htmlspecialchars($photo['points']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
