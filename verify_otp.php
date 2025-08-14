<?php
ob_start();
require_once 'config.php';

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = $_POST['otp'];
    $email = $_SESSION['reset_email'] ?? '';

    if (empty($otp)) {
        $error = "Veuillez entrer le code OTP.";
    } else {
        global $pdo;
        try {
            if (!$pdo) {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            $stmt = $pdo->prepare("SELECT user_id, expires_at FROM otp_codes WHERE otp_code = :otp AND user_id = (SELECT id FROM users WHERE email = :email AND is_active = 1) LIMIT 1");
            $stmt->execute(['otp' => $otp, 'email' => $email]);
            $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($otp_record) {
                $expires_at = strtotime($otp_record['expires_at']);
                if ($expires_at > time()) {
                    $_SESSION['reset_user_id'] = $otp_record['user_id'];
                    header("Location: reset_password.php");
                    exit();
                } else {
                    $error = "Le code OTP a expiré. Demandez un nouveau code.";
                }
            } else {
                $error = "Code OTP invalide.";
            }
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
    <title>Vérification OTP - Patrimoine Plus</title>
    <meta name="description" content="Vérifiez votre code OTP pour réinitialiser votre mot de passe.">
    <meta name="keywords" content="OTP, vérification, réinitialisation, patrimoine">

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
        .verify-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .verify-header h2 {
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }
        .text-blue {
            color: #1e3a8a;
        }
        .btn-verify {
            background-color: #1e3a8a;
            color: white;
            border: none;
        }
        .btn-verify:hover {
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
                    <div class="verify-container">
                        <div class="verify-header text-center">
                            <h2 class="text-blue">Patrimoine Plus</h2>
                            <p class="mb-0">Vérification du code OTP</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form action="" method="post" class="row g-3">
                            <div class="col-12">
                                <label for="otp" class="form-label text-blue">Code OTP</label>
                                <input type="text" class="form-control" id="otp" name="otp" maxlength="6" required>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-verify w-100">Vérifier</button>
                            </div>
                            <div class="col-12 text-center mt-2">
                                <a href="forgot_password.php" class="text-decoration-none text-blue">Renvoyer le code</a>
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
ob_end_flush();
?>