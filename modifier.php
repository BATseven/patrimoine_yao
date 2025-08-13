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

    // Récupérer les données depuis POST
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $role = isset($_POST['role']) ? ($_POST['role'] === 'admin' ? 'admin' : 'user') : null;
    $isVerified = isset($_POST['is_verified']) ? (int)$_POST['is_verified'] : null;

    if ($userId && $fullName && $email && $role !== null && $isVerified !== null) {
        // Mettre à jour les enregistrements dans users
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, is_verified = ? WHERE id = ?");
        $stmt->execute([$fullName, $email, $role, $isVerified, $userId]);

        $pdo->commit();
        // Rediriger vers gestion_users.php sans message
        header("Location: gestion_users.php");
        exit();
    } else {
        $pdo->rollBack();
        // Rediriger même en cas d'erreur pour éviter les messages
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