<?php
require_once 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

global $pdo;

try {
    // Démarrer une transaction
    $pdo->beginTransaction();

    // Récupérer l'ID de l'utilisateur à supprimer depuis POST
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    if ($userId) {
        // Supprimer les enregistrements liés dans otp_codes
        $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();
        // Rediriger vers gestion_users.php sans message
        header("Location: gestion_users.php");
        exit();
    } else {
        $pdo->rollBack();
        // Rediriger avec un paramètre d'erreur si nécessaire (optionnel)
        header("Location: gestion_users.php");
        exit();
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    // Rediriger même en cas d'erreur pour éviter les messages
    header("Location: gestion_users.php");
    exit();
}
?>