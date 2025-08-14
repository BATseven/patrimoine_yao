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
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $new_status = $user['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        echo "Compte " . ($new_status ? 'activé' : 'désactivé') . " avec succès.";
    } else {
        echo "Utilisateur non trouvé.";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
}
ob_end_flush();
?>