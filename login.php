<?php
// Start output buffering
ob_start();
require_once 'config.php';

session_start();

// Activer les erreurs pour débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vérifier si le mot de passe correspond avec password_verify
        if (password_verify($password, $user['password'])) {
            if (!$user['is_verified']) {
                $error = "Votre compte n'est pas encore vérifié. Veuillez vérifier votre email.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                // Vérifier si c'est la première connexion (optionnel)
                if ($user['password'] === 'initial_password_hash') { // Remplacez par une logique réelle si nécessaire
                    header("Location: change_password.php"); // Rediriger vers une page de changement de mot de passe
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Aucun compte actif associé à cet email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Connexion - Patrimoine Plus</title>
  <meta name="description" content="Connectez-vous à votre espace client Patrimoine Plus.">
  <meta name="keywords" content="connexion, patrimoine, gestion">

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
    .login-container {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .login-header h2 {
      color: #1e3a8a;
      margin-bottom: 0.5rem;
    }
    .text-blue {
      color: #1e3a8a;
    }
    .btn-login {
      background-color: #1e3a8a;
      color: white;
      border: none;
    }
    .btn-login:hover {
      background-color: #152e6f;
    }
    .error {
      color: red;
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
          <div class="login-container">
            <div class="login-header text-center">
              <h2 class="text-blue">Patrimoine Plus</h2>
              <p class="mb-0">Connexion à votre espace client</p>
            </div>
            <?php if ($error): ?>
              <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="" method="post" class="row g-3">
              <div class="col-12">
                <label for="email" class="form-label text-blue">Adresse Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>
              <div class="col-12">
                <label for="password" class="form-label text-blue">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="remember" name="remember">
                  <label class="form-check-label text-blue" for="remember">Se souvenir de moi</label>
                </div>
              </div>
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-login w-100">Se connecter</button>
              </div>
              <div class="col-12 text-center mt-2">
                <a href="forgot_password.php" class="text-decoration-none text-blue">Mot de passe oublié ?</a> | <a href="register.php" class="text-decoration-none text-blue">Créer un compte</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>
<?php
// Flush output buffer
ob_end_flush();
?>