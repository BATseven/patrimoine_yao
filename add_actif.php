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

    $photo_path = null;
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_path = $upload_dir . uniqid() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    // Vérification des données POST
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $value = isset($_POST['value']) ? $_POST['value'] : 0;
    $acquisition_date = isset($_POST['acquisition_date']) ? $_POST['acquisition_date'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    if (empty($name) || empty($type) || empty($value) || empty($acquisition_date)) {
        die("Tous les champs obligatoires (nom, type, valeur, date d'acquisition) doivent être remplis.");
    }

    $stmt = $pdo->prepare("INSERT INTO actifs (user_id, name, type, value, acquisition_date, address, description, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $name,
        $type,
        $value,
        $acquisition_date,
        $address,
        $description,
        $photo_path
    ]);

    header("Location: actifs.php");
    exit();
} catch (PDOException $e) {
    die("Erreur d'ajout : " . $e->getMessage());
}
ob_end_flush();
?>