<?php
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

global $pdo;

try {
    $pdo->beginTransaction();
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM actifs WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);

    $pdo->commit();
    header("Location: actifs.php");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: actifs.php");
    exit();
}
?>