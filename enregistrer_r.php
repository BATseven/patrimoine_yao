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
    $_SESSION['error'] = "Erreur de connexion à la base de données : " . $e->getMessage();
    header("Location: reglages.php");
    exit();
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['company_settings'])) {
        // Traitement des informations de l'entreprise
        $companyName = trim($_POST['company_name'] ?? '');
        $companyAddress = trim($_POST['company_address'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $currency = trim($_POST['currency'] ?? '');

        // Validation des champs
        if (empty($companyName) || empty($companyAddress) || empty($companyPhone) || empty($currency)) {
            $_SESSION['error'] = "Tous les champs de l'entreprise sont requis.";
            header("Location: reglages.php");
            exit();
        }

        // Gestion du téléchargement du logo
        $logoPath = null;
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/assets/uploads/';
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $_SESSION['error'] = "Impossible de créer le dossier de téléchargement.";
                    header("Location: reglages.php");
                    exit();
                }
            }
            // Vérifier les permissions d'écriture
            if (!is_writable($uploadDir)) {
                $_SESSION['error'] = "Le dossier $uploadDir n'est pas accessible en écriture.";
                header("Location: reglages.php");
                exit();
            }
            $logoName = uniqid() . '_' . basename($_FILES['company_logo']['name']);
            $logoPath = $uploadDir . $logoName;
            // Vérifier le type de fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['company_logo']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error'] = "Type de fichier non autorisé. Utilisez JPEG, PNG ou GIF.";
                header("Location: reglages.php");
                exit();
            }
            // Vérifier la taille du fichier (max 5MB)
            if ($_FILES['company_logo']['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = "Le fichier est trop volumineux. Taille maximale : 5 Mo.";
                header("Location: reglages.php");
                exit();
            }
            if (!move_uploaded_file($_FILES['company_logo']['tmp_name'], $logoPath)) {
                $_SESSION['error'] = "Échec du déplacement du fichier téléchargé. Vérifiez les permissions.";
                header("Location: reglages.php");
                exit();
            }
        } elseif (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['error'] = "Erreur lors du téléchargement du logo (code: " . $_FILES['company_logo']['error'] . ").";
            header("Location: reglages.php");
            exit();
        }

        // Enregistrement dans la base de données
        try {
            $stmt = $pdo->prepare("INSERT INTO company_settings (name, logo_path, address, phone, currency) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE name = ?, logo_path = ?, address = ?, phone = ?, currency = ?");
            $stmt->execute([$companyName, $logoPath, $companyAddress, $companyPhone, $currency, 
                            $companyName, $logoPath, $companyAddress, $companyPhone, $currency]);
            $_SESSION['success'] = "Informations de l'entreprise enregistrées avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'enregistrement des informations : " . $e->getMessage();
        }
    } elseif (isset($_POST['security_settings'])) {
        // Traitement des paramètres de sécurité
        $minPasswordLength = (int)($_POST['min_password_length'] ?? 8);
        $enable2FA = isset($_POST['enable_2fa']) ? 1 : 0;
        $privacyRules = trim($_POST['privacy_rules'] ?? '');

        // Validation
        if ($minPasswordLength < 6 || $minPasswordLength > 20) {
            $_SESSION['error'] = "La longueur du mot de passe doit être entre 6 et 20 caractères.";
            header("Location: reglages.php");
            exit();
        }

        // Enregistrement dans la base de données (ajustez selon votre schéma)
        try {
            $stmt = $pdo->prepare("INSERT INTO security_settings (min_password_length, enable_2fa, privacy_rules) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE min_password_length = ?, enable_2fa = ?, privacy_rules = ?");
            $stmt->execute([$minPasswordLength, $enable2FA, $privacyRules, 
                            $minPasswordLength, $enable2FA, $privacyRules]);
            $_SESSION['success'] = "Paramètres de sécurité enregistrés avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'enregistrement des paramètres de sécurité : " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Requête invalide.";
    }
} else {
    $_SESSION['error'] = "Méthode de requête non autorisée.";
}

header("Location: reglages.php");
exit();
?>