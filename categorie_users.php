<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    global $pdo;
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hachage du mot de passe
    $role = $_POST['role'];
    $isVerified = $_POST['is_verified'];

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_verified) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $password, $role, $isVerified]);
    header("Location: gestion_users.php");
    exit();
}
?>