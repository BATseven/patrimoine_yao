<?php
// Démarrer le buffer de sortie
ob_start();
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
    $photo_path = null;
    $document_path = null;
    $upload_dir = "uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Gestion des nouveaux uploads
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_path = $upload_dir . uniqid() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $document_path = $upload_dir . uniqid() . '_' . basename($_FILES['document']['name']);
        move_uploaded_file($_FILES['document']['tmp_name'], $document_path);
    }

    // Préparer la mise à jour
    $stmt = $pdo->prepare("UPDATE patrimoines SET name = ?, type = ?, value = ?, date_acquisition = ?, address = ?, latitude = ?, longitude = ?, description = ?, photo_path = COALESCE(?, photo_path), document_path = COALESCE(?, document_path), contract_expiry_date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['type'],
        $_POST['value'],
        $_POST['date_acquisition'],
        $_POST['address'],
        $_POST['latitude'],
        $_POST['longitude'],
        $_POST['description'],
        $photo_path,
        $document_path,
        $_POST['contract_expiry_date'] ?: null,
        $id,
        $_SESSION['user_id']
    ]);

    header("Location: patrimoine.php?id=" . $id);
    exit();

} catch (PDOException $e) {
    die("Erreur de modification : " . $e->getMessage());
}
ob_end_flush();
?>