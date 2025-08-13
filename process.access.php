<?php
header('Content-Type: application/json');
require_once 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

global $pdo;

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $userId = isset($data['userId']) ? (int)$data['userId'] : null;
    $newRole = $data['newRole'] ?? null;
    $fullName = $data['fullName'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $role = $data['role'] ?? null;

    switch ($action) {
        case 'modifyUser':
            if ($userId && $fullName && $email) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $userId]);
                $message = "Utilisateur $userId modifié avec succès.";
                $success = true;
            } else {
                $message = "Données manquantes pour la modification.";
                $success = false;
            }
            break;

        case 'deleteUser':
            if ($userId) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $message = "Utilisateur $userId supprimé avec succès.";
                $success = true;
            } else {
                $message = "ID de l'utilisateur manquant.";
                $success = false;
            }
            break;

        case 'createUser':
            if ($fullName && $email && $password && $role) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_verified) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$fullName, $email, $hashedPassword, $role]);
                $message = "Nouvel utilisateur créé avec succès.";
                $success = true;
            } else {
                $message = "Données manquantes pour la création.";
                $success = false;
            }
            break;

        case 'resetPassword':
            if ($userId) {
                $newPassword = bin2hex(random_bytes(4));
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                $message = "Mot de passe réinitialisé pour $userId. Nouveau mot de passe : $newPassword";
                $success = true;
            } else {
                $message = "ID de l'utilisateur manquant.";
                $success = false;
            }
            break;

        case 'toggleAccount':
            if ($userId) {
                $stmt = $pdo->prepare("UPDATE users SET is_verified = NOT is_verified WHERE id = ?");
                $stmt->execute([$userId]);
                $status = $pdo->query("SELECT is_verified FROM users WHERE id = " . intval($userId))->fetchColumn() ? 'Actif' : 'En attente';
                $message = "Compte $userId mis à jour. Nouvel état : $status.";
                $success = true;
            } else {
                $message = "ID de l'utilisateur manquant.";
                $success = false;
            }
            break;

        case 'changeRole':
            if ($userId && $newRole) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);
                $message = "Rôle de $userId changé en $newRole avec succès.";
                $success = true;
            } else {
                $message = "ID ou nouveau rôle manquant.";
                $success = false;
            }
            break;

        default:
            $message = 'Action non reconnue';
            $success = false;
    }

    echo json_encode(['success' => $success ?? false, 'message' => $message]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>