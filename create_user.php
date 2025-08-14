<?php
ob_start();
require_once 'config.php';
require_once 'send_mail.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $full_name = $_POST['full_name'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide.";
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_verified, is_active) VALUES (?, ?, ?, ?, TRUE, TRUE)");
    $stmt->execute([$full_name, $email, $password, $role]);

    // Récupérer le nom de l'admin qui a créé l'utilisateur
    $admin_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_name = $stmt->fetch(PDO::FETCH_ASSOC)['full_name'];

    // Préparer le lien de connexion
    $login_link = "http://localhost/patrimoine/login.php";

    // Envoyer l'email
    $subject = "Votre compte Patrimoine Plus a été créé";
    $body = "
        <html>
        <body>
            <h2>Bonjour, $full_name !</h2>
            <p>Votre compte sur Patrimoine Plus a été créé par l'administrateur $admin_name.</p>
            <p>Vous pouvez vous connecter avec les informations suivantes :</p>
            <ul>
                <li><strong>Email :</strong> $email</li>
                <li><strong>Mot de passe :</strong> (Utilisez le mot de passe que vous avez défini lors de la création)</li>
            </ul>
            <p>Cliquez sur le lien suivant pour vous connecter : <a href='$login_link'>$login_link</a></p>
            <p>Si vous avez des questions, n'hésitez pas à contacter l'administrateur.</p>
        </body>
        </html>
    ";
    $result = sendMail($email, $full_name, $subject, $body);

    if ($result['success']) {
        echo "Utilisateur créé avec succès. Un email a été envoyé à $email.";
    } else {
        echo "Utilisateur créé avec succès, mais l'envoi de l'email a échoué : " . $result['error'];
    }
} catch (PDOException $e) {
    echo "Erreur lors de la création : " . $e->getMessage();
}
ob_end_flush();
?>