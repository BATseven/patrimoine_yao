<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_code = filter_var($_POST['otp_code'], FILTER_SANITIZE_STRING);

    if (empty($otp_code)) {
        $error = "Veuillez entrer le code OTP.";
    } else {
        // Récupérer l'utilisateur par email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND is_verified = FALSE");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Compte introuvable ou déjà vérifié.";
        } else {
            // Vérifier le code OTP
            $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = :user_id AND otp_code = :otp_code AND expires_at > NOW()");
            $stmt->execute(['user_id' => $user['id'], 'otp_code' => $otp_code]);
            $otp = $stmt->fetch();

            if ($otp) {
                // Marquer le compte comme vérifié
                $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE WHERE id = :user_id");
                $stmt->execute(['user_id' => $user['id']]);

                // Supprimer les codes OTP associés
                $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user['id']]);

                $success = "Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.";
                unset($_SESSION['verify_email']);
            } else {
                $error = "Code OTP incorrect ou expiré.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Vérification OTP - Patrimoine Plus</title>
  <meta name="description" content="Vérifiez votre compte Patrimoine Plus avec le code OTP envoyé par email.">
  <meta name="keywords" content="vérification, otp, gestion de patrimoine">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .verify-container {
      background: #ffffff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .verify-header {
      border-bottom: 2px solid #1e3a8a;
      padding-bottom: 1rem;
      margin-bottom: 1.5rem;
    }
    .btn-verify {
      background-color: #1e3a8a;
      color: white;
      border: none;
      padding: 0.75rem;
    }
    .btn-verify:hover {
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
          <div class="verify-container">
            <div class="verify-header text-center">
              <h2 class="text-blue">Patrimoine Plus</h2>
              <p class="mb-0">Vérification de votre compte</p>
            </div>
            <?php if ($error): ?>
              <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
              <div class="success"><?php echo $success; ?></div>
              <div class="text-center">
                <a href="login.php" class="text-decoration-none text-blue">Se connecter</a>
              </div>
            <?php else: ?>
              <form action="" method="post" class="row g-3">
                <div class="col-12">
                  <label for="otp_code" class="form-label text-blue">Code OTP</label>
                  <input type="text" class="form-control" id="otp_code" name="otp_code" required placeholder="Entrez le code à 6 chiffres">
                </div>
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-verify w-100">Vérifier</button>
                </div>
                <div class="col-12 text-center mt-2">
                  <a href="register.php" class="text-decoration-none text-blue">Retour à l'inscription</a>
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