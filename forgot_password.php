<?php
session_start();
require_once 'config.php';
require_once 'send_mail.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = "Veuillez entrer votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email");
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
                    <p>Cordialement,<br>L'équipe Patrimoine Plus</p>
                </body>
                </html>
            ";
            $mailResult = sendMail($email, $user['full_name'], $subject, $body);
            if ($mailResult['success']) {
                $success = "Un email avec un code de vérification a été envoyé à $email.";
                $_SESSION['reset_email'] = $email;
                // Rediriger vers la page de réinitialisation
                header("Location: reset_password.php");
                exit();
            } else {
                $error = "Une erreur est survenue lors de l'envoi de l'email : " . $mailResult['error'];
            }
        } else {
            $error = "Aucun compte associé à cet email.";
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
  <meta name="description" content="Réinitialisez votre mot de passe pour accéder à Patrimoine Plus.">
  <meta name="keywords" content="mot de passe oublié, réinitialisation, gestion de patrimoine">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .forgot-container {
      background: #ffffff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .forgot-header {
      border-bottom: 2px solid #1e3a8a;
      padding-bottom: 1rem;
      margin-bottom: 1.5rem;
    }
    .btn-forgot {
      background-color: #1e3a8a;
      color: white;
      border: none;
      padding: 0.75rem;
    }
    .btn-forgot:hover {
      background-color: #152e6f;
    }
    .form-control {
      border-radius: 5px;
    }
    .text-blue {
      color: #1e3a8a;
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
                  <input type="email" class="form-control" id="email" name="email" required>
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
// Flush output buffer
ob_end_flush();
?>