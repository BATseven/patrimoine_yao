<?php
session_start();
require_once 'config.php';
require_once 'send_mail.php';

$error = '';
$success = '';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_code = filter_var($_POST['otp_code'], FILTER_SANITIZE_STRING);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($otp_code) || empty($new_password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Récupérer l'utilisateur par email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérifier le code OTP
            $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = :user_id AND otp_code = :otp_code AND expires_at > NOW()");
            $stmt->execute(['user_id' => $user['id'], 'otp_code' => $otp_code]);
            $otp = $stmt->fetch();

            if ($otp) {
                // Mettre à jour le mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                $stmt->execute(['password' => $hashed_password, 'user_id' => $user['id']]);

                // Supprimer les codes OTP associés
                $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user['id']]);

                $success = "Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
                unset($_SESSION['reset_email']);
            } else {
                $error = "Code OTP incorrect ou expiré.";
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
  <title>Réinitialisation - Patrimoine Plus</title>
  <meta name="description" content="Réinitialisez votre mot de passe avec le code OTP reçu par email.">
  <meta name="keywords" content="réinitialisation, mot de passe, otp, gestion de patrimoine">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .reset-container {
      background: #ffffff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .reset-header {
      border-bottom: 2px solid #1e3a8a;
      padding-bottom: 1rem;
      margin-bottom: 1.5rem;
    }
    .btn-reset {
      background-color: #1e3a8a;
      color: white;
      border: none;
      padding: 0.75rem;
    }
    .btn-reset:hover {
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
          <div class="reset-container">
            <div class="reset-header text-center">
              <h2 class="text-blue">Patrimoine Plus</h2>
              <p class="mb-0">Réinitialisation de mot de passe</p>
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
                <div class="col-12">
                  <label for="new_password" class="form-label text-blue">Nouveau mot de passe</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="col-12">
                  <label for="confirm_password" class="form-label text-blue">Confirmer le mot de passe</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-reset w-100">Réinitialiser</button>
                </div>
                <div class="col-12 text-center mt-2">
                  <a href="forgot_password.php" class="text-decoration-none text-blue">Retour</a>
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