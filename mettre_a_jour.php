<?php
// Démarrer la session et inclure la configuration
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de connexion : " . $e->getMessage();
    header("Location: reglages.php");
    exit();
}

// Traitement du formulaire de mise à jour du rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_role_settings'])) {
    $userId = trim($_POST['user_id'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Validation des champs
    if (empty($userId) || empty($role)) {
        $_SESSION['error'] = "L'utilisateur et le rôle sont requis.";
        header("Location: reglages.php");
        exit();
    }

    // Vérifier si le rôle existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_name = ?");
    $stmt->execute([$role]);
    if ($stmt->fetchColumn() == 0) {
        $_SESSION['error'] = "Le rôle spécifié n'existe pas.";
        header("Location: reglages.php");
        exit();
    }

    // Mise à jour dans la base de données
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);
        $_SESSION['success'] = "Rôle de l'utilisateur mis à jour avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Requête invalide.";
}

header("Location: reglages.php");
exit();
?>