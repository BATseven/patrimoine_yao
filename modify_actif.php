<?php
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
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_path = $upload_dir . uniqid() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    $stmt = $pdo->prepare("UPDATE actifs SET name = ?, type = ?, value = ?, acquisition_date = ?, address = ?, description = ?, photo_path = COALESCE(?, photo_path) WHERE id = ? AND user_id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['type'],
        $_POST['value'],
        $_POST['acquisition_date'],
        $_POST['address'],
        $_POST['description'],
        $photo_path,
        $id,
        $_SESSION['user_id']
    ]);

    header("Location: actifs.php?id=" . $id);
    exit();
} catch (PDOException $e) {
    die("Erreur de modification : " . $e->getMessage());
}
ob_end_flush();
?>