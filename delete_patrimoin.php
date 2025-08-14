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

    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM patrimoines WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);

        $pdo->commit();
        header("Location: patrimoine.php");
        exit();
    } else {
        $pdo->rollBack();
        header("Location: patrimoine.php");
        exit();
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: patrimoine.php");
    exit();
}
?>