<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twoja strona</title>
    <style>
        /* Resetowanie marginesów i paddingu */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Zawartość strony */
        .content {
            padding: 20px;
            margin-bottom: 50px; /* Dodajemy margines dolny, aby stopka się nie nakładała */
        }

        /* Stopka */
        .row-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%; /* Rozciąga stopkę na całą szerokość */
            padding: 10px 20px;
            background-color: #0b4063;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Zawartość strony -->
    <div class="content">
        <h1>Witaj na mojej stronie!</h1>
        <p>Treść strony...</p>
        <p>Więcej treści...</p>
        <p>Jeszcze więcej treści...</p>
    </div>

    <!-- Stopka -->
    <footer class="row-footer">
        <p>&copy; 2024 Virtus - Sklep Wędkarski</p>
    </footer>

</body>
</html>
