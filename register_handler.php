<?php
session_start();
include 'config.php';
require 'send_mail.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($name && $username && $email && $password) {
        // Vérifie si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['error'] = "Cet email est déjà utilisé.";
            header("Location: register.php");
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $otp_code = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, type, otp_code, is_verified) VALUES (?, ?, ?, ?, 'user', ?, 0)");
            $stmt->execute([$name, $username, $email, $hashed_password, $otp_code]);

            // Envoi de l'email de confirmation
            $subject = "Vérification de votre compte - Patrimoine Plus";
            $body = "
                <h3>Bonjour $name,</h3>
                <p>Votre compte a été créé avec succès.</p>
                <p>Votre code de vérification (OTP) est : <strong>$otp_code</strong></p>
                <p>Veuillez entrer ce code sur la page de vérification :</p>
                <a href='http://localhost/patrimoine/verify.php?email=" . urlencode($email) . "'>Vérifier mon compte</a>
                <p>Ce code expire dans 10 minutes.</p>
            ";
            if (sendMail($email, $name, $subject, $body)) {
                header("Location: verify.php?email=" . urlencode($email));
                exit;
            } else {
                $_SESSION['error'] = "Échec de l'envoi de l'email de confirmation.";
                header("Location: register.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: register.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: register.php");
        exit;
    }
}