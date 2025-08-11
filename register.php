<?php
// Start output buffering
ob_start();
require_once 'config.php';
require_once 'send_mail.php';

session_start();

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifications basiques
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur avec is_verified = FALSE
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_verified) VALUES (:full_name, :email, :password, 'user', FALSE)");
            if ($stmt->execute(['full_name' => $full_name, 'email' => $email, 'password' => $hashed_password])) {
                $user_id = $pdo->lastInsertId();

                // Générer un code OTP (6 chiffres)
                $otp_code = sprintf("%06d", mt_rand(0, 999999));
                $created_at = date('Y-m-d H:i:s');
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Enregistrer le code OTP dans la table otp_codes
                $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp_code, created_at, expires_at) VALUES (:user_id, :otp_code, :created_at, :expires_at)");
                $stmt->execute([
                    'user_id' => $user_id,
                    'otp_code' => $otp_code,
                    'created_at' => $created_at,
                    'expires_at' => $expires_at
                ]);

                // Envoyer l'email avec le code OTP
                $subject = "Vérification de votre compte Patrimoine Plus";
                $body = "
                    <html>
                    <body>
                        <h2>Bienvenue, $full_name !</h2>
                        <p>Merci de vous être inscrit sur Patrimoine Plus. Veuillez utiliser le code suivant pour vérifier votre compte :</p>
                        <h3>$otp_code</h3>
                        <p>Ce code expire dans 15 minutes. Si vous ne l'avez pas demandé, ignorez cet email.</p>
                        <p>Cordialement,<br>L'équipe Patrimoine Plus</p>
                    </body>
                    </html>
                ";
                $mailResult = sendMail($email, $full_name, $subject, $body);
                if ($mailResult['success']) {
                    $success = "Inscription réussie ! Un email avec un code de vérification a été envoyé à $email.";
                    // Stocker l'email en session pour la vérification
                    $_SESSION['verify_email'] = $email;
                    // Rediriger vers la page de vérification OTP
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $error = "Inscription réussie, mais l'email de vérification n'a pas pu être envoyé : " . $mailResult['error'];
                }
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
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
  <title>Inscription - Patrimoine Plus</title>
  <meta name="description" content="Créez votre compte sur Patrimoine Plus pour gérer votre patrimoine.">
  <meta name="keywords" content="inscription, gestion de patrimoine, client portal">
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .register-container {
      background: #ffffff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .register-header {
      border-bottom: 2px solid #1e3a8a;
      padding-bottom: 1rem;
      margin-bottom: 1.5rem;
    }
    .btn-register {
      background-color: #1e3a8a;
      color: white;
      border: none;
      padding: 0.75rem;
    }
    .btn-register:hover {
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
          <div class="register-container">
            <div class="register-header text-center">
              <h2 class="text-blue">Patrimoine Plus</h2>
              <p class="mb-0">Création de votre compte</p>
            </div>
            <?php if ($error): ?>
              <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
              <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form action="" method="post" class="row g-3">
              <div class="col-12">
                <label for="full_name" class="form-label text-blue">Nom complet</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
              </div>
              <div class="col-12">
                <label for="email" class="form-label text-blue">Adresse Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>
              <div class="col-12">
                <label for="password" class="form-label text-blue">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="col-12">
                <label for="confirm_password" class="form-label text-blue">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
              </div>
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-register w-100">S'inscrire</button>
              </div>
              <div class="col-12 text-center mt-2">
                <a href="login.php" class="text-decoration-none text-blue">Déjà un compte ? Se connecter</a>
              </div>
            </form>
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