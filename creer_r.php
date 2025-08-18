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

// Traitement du formulaire de création de rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_role'])) {
    $roleName = trim($_POST['new_role'] ?? '');
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    // Validation des champs
    if (empty($roleName)) {
        $_SESSION['error'] = "Le nom du rôle est requis.";
        header("Location: reglages.php");
        exit();
    }

    // Convertir les permissions en chaîne JSON
    $permissionsJson = json_encode($permissions);

    // Enregistrement dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO roles (role_name, permissions) VALUES (?, ?)");
        $stmt->execute([$roleName, $permissionsJson]);
        $_SESSION['success'] = "Rôle '$roleName' créé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la création du rôle : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Requête invalide.";
}

header("Location: reglages.php");
exit();
?>