<?php
// Wyłączenie cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Nagłówek do UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Przekierowanie do /demo/main/main.php
header("Location: /demo/main/main.php");
exit; // Ważne, aby zakończyć skrypt po przekierowaniu

session_start();
?>

