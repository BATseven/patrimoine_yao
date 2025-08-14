<?php
ob_start();
require_once 'config.php';

session_start();

$error = '';
$success = '';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        global $pdo;
        try {
            if (!$pdo) {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);

            // Nettoyer la session
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']);
            $success = "Votre mot de passe a été réinitialisé avec succès. <a href='login.php'>Connectez-vous</a>.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Réinitialisation du mot de passe - Patrimoine Plus</title>
    <meta name="description" content="Définissez un nouveau mot de passe pour votre compte.">
    <meta name="keywords" content="réinitialisation, mot de passe, patrimoine">

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
        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .reset-header h2 {
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }
        .text-blue {
            color: #1e3a8a;
        }
        .btn-reset {
            background-color: #1e3a8a;
            color: white;
            border: none;
        }
        .btn-reset:hover {
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
                    <div class="reset-container">
                        <div class="reset-header text-center">
                            <h2 class="text-blue">Patrimoine Plus</h2>
                            <p class="mb-0">Nouveau mot de passe</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <form action="" method="post" class="row g-3">
                                <div class="col-12">
                                    <label for="new_password" class="form-label text-blue">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="col-12">
                                    <label for="confirm_password" class="form-label text-blue">Confirmer le mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-reset w-100">Valider</button>
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