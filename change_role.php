<?php
ob_start();
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $user_id]);

    echo "Rôle changé avec succès.";
} catch (PDOException $e) {
    echo "Erreur lors du changement de rôle : " . $e->getMessage();
}
ob_end_flush();
?>