<?php
// Configuration de la base de données à partir de .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    $dbHost = $env['DB_HOST'] ?? 'localhost';
    $dbUser = $env['DB_USER'] ?? 'root';
    $dbPass = $env['DB_PASS'] ?? ''; // Remplace par ton mot de passe MySQL si nécessaire
    $dbName = $env['DB_NAME'] ?? 'patrimoine_plus_db';
} else {
    die("Fichier .env manquant.");
}

// Connexion à la base de données
try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        throw new Exception("Échec de la connexion : " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}
?>