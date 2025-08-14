<?php
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $id = $_POST['id'];
    $arbitrage_date = $_POST['arbitrage_date'];

    $stmt = $pdo->prepare("UPDATE actifs SET arbitrage_date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$arbitrage_date, $id, $_SESSION['user_id']]);

    echo "Arbitrage effectué avec succès à la date : " . $arbitrage_date;
} catch (PDOException $e) {
    echo "Erreur lors de l'arbitrage : " . $e->getMessage();
}
?>