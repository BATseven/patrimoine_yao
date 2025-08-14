<?php
session_start();
require_once 'config.php';
require_once 'send_mail.php';

$error = '';
$success = '';
$preFilledEmail = '';

if (isset($_GET['user_id'])) {
    global $pdo;
    try {
        if (!$pdo) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $preFilledEmail = $user['email'];
            $_SESSION['reset_email'] = $user['email']; // Stocker l'email en session
        } else {
            $error = "Utilisateur introuvable ou compte désactivé.";
        }
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = "Veuillez entrer votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Vérifier si l'email existe et est actif
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email AND is_active = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Générer un code OTP (6 chiffres)
            $otp_code = sprintf("%06d", mt_rand(0, 999999));
            $created_at = date('Y-m-d H:i:s');
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Enregistrer le code OTP dans la table otp_codes
            $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp_code, created_at, expires_at) VALUES (:user_id, :otp_code, :created_at, :expires_at)");
            $stmt->execute([
                'user_id' => $user['id'],
                'otp_code' => $otp_code,
                'created_at' => $created_at,
                'expires_at' => $expires_at
            ]);

            // Envoyer l'email avec le code OTP
            $subject = "Réinitialisation de votre mot de passe - Patrimoine Plus";
            $body = "
                <html>
                <body>
                    <h2>Bonjour, {$user['full_name']} !</h2>
                    <p>Vous avez demandé une réinitialisation de votre mot de passe. Utilisez le code suivant pour continuer :</p>
                    <h3>$otp_code</h3>
                    <p>Ce code expire dans 15 minutes. Si vous n'avez pas demandé cela, ignorez cet email.</p>
                </body>
                </html>
            ";
            $result = sendMail($email, $user['full_name'], $subject, $body);

            if ($result['success']) {
                $_SESSION['reset_email'] = $email; // Stocker l'email en session
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Échec de l'envoi de l'email : " . $result['error'];
            }
        } else {
            $error = "Aucun compte actif trouvé avec cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Mot de passe oublié - Patrimoine Plus</title>
    <meta name="description" content="Réinitialisez votre mot de passe pour Patrimoine Plus.">
    <meta name="keywords" content="mot de passe oublié, réinitialisation, patrimoine">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Inter', sans-serif;
        }
        .main {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .forgot-header h2 {
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }
        .text-blue {
            color: #1e3a8a;
        }
        .btn-forgot {
            background-color: #1e3a8a;
            color: white;
            border: none;
        }
        .btn-forgot:hover {
            background-color: #152e6f;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <main class="main d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: #f3f4f6;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="forgot-container">
                        <div class="forgot-header text-center">
                            <h2 class="text-blue">Patrimoine Plus</h2>
                            <p class="mb-0">Mot de passe oublié</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <form action="" method="post" class="row g-3">
                                <div class="col-12">
                                    <label for="email" class="form-label text-blue">Adresse Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($preFilledEmail); ?>" required>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-forgot w-100">Envoyer le code</button>
                                </div>
                                <div class="col-12 text-center mt-2">
                                    <a href="login.php" class="text-decoration-none text-blue">Retour à la connexion</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>