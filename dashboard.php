<?php
ob_start();
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['full_name'];
// Mise à jour de la date et heure avec le fuseau GMT
date_default_timezone_set('GMT');
$dateTime = date('H:i A \o\n l, F j, Y', strtotime('08:24 AM GMT')); // 08:24 AM GMT, Tuesday, August 19, 2025

// Connexion à la base de données
global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();

    // Nombre de patrimoines
    $stmt = $pdo->query("SELECT COUNT(*) FROM patrimoines");
    $patrimoineCount = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Tableau de Bord - <?php echo ($userRole == 'admin') ? 'Admin' : ''; ?> Patrimoine Plus</title>
    <meta name="description" content="<?php echo ($userRole == 'admin') ? 'Tableau de bord pour administrer les utilisateurs et les patrimoines.' : 'Tableau de bord pour gérer votre patrimoine.'; ?>">
    <meta name="keywords" content="<?php echo ($userRole == 'admin') ? 'tableau de bord, admin, gestion utilisateurs, patrimoines' : 'tableau de bord, gestion patrimoine'; ?>">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            transition: all 0.3s ease;
        }
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: #1e3a8a;
            color: white;
            padding-top: 20px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar a {
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 10px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #152e6f;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            height: 200px;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #1e3a8a;
        }
        .card-text {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .card .btn {
            margin-top: auto;
            width: 80%;
            margin-left: 10%;
            margin-right: 10%;
            background-color: #1e3a8a;
            border: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .card .btn:hover {
            background-color: #152e6f;
            transform: scale(1.05);
        }
        .card i {
            font-size: 2rem;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }
        .chart-placeholder {
            height: 200px;
            background-color: #f1f1f1;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .content {
                margin-left: 0;
            }
            .card {
                height: auto;
                min-height: 150px;
            }
            .card .btn {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center">Menu <?php echo ($userRole == 'admin') ? 'Admin' : 'Utilisateur'; ?></h4>
        <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
            <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <?php endif; ?>
        <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="reglages.php"><i data-lucide="settings"></i> Paramétrage</a>
            <a href="/patrimoine/statistics_dashboard.php"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
        <?php endif; ?>
        <a href="logout.php"><i data-lucide="log-out"></i> Déconnexion</a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Tableau de Bord - <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-muted">Dernière mise à jour: <?php echo $dateTime; ?></p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="users"></i>
                    <h4 class="card-title">Utilisateurs</h4>
                    <p class="card-text"><?php echo number_format($userCount); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="database"></i>
                    <h4 class="card-title">Patrimoines</h4>
                    <p class="card-text"><?php echo number_format($patrimoineCount); ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="users"></i>
                    <h4 class="card-title">Gestion Utilisateurs</h4>
                    <p class="card-text">Gérez les utilisateurs et leurs droits.</p>
                    <a href="gestion_users.php" class="btn btn-primary"><i data-lucide="eye"></i> Voir</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="file-check"></i>
                    <h4 class="card-title">Validation Documents</h4>
                    <p class="card-text">Validez les documents soumis.</p>
                    <a href="validation_doc.php" class="btn btn-primary"><i data-lucide="eye"></i> Voir</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="database"></i>
                    <h4 class="card-title">Accès Patrimoines</h4>
                    <p class="card-text">Consultez vos patrimoines.</p>
                    <a href="patrimoine.php" class="btn btn-primary"><i data-lucide="eye"></i> Voir</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="bar-chart-2"></i>
                    <h4 class="card-title">Statistiques Globales</h4>
                    <p class="card-text">Analysez les performances.</p>
                    <a href="/patrimoine/statistics_dashboard.php" class="btn btn-primary"><i data-lucide="bar-chart-2"></i> Analyser</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="settings"></i>
                    <h4 class="card-title">Paramétrage</h4>
                    <p class="card-text">Configurez les types de biens.</p>
                    <a href="reglages.php" class="btn btn-primary"><i data-lucide="settings"></i> Configurer</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="briefcase"></i>
                    <h4 class="card-title">Gestion des Actifs</h4>
                    <p class="card-text">Gérez et analysez vos actifs.</p>
                    <a href="actifs.php" class="btn btn-primary"><i data-lucide="briefcase"></i> Gérer</a>
                </div>
            </div>
            <!-- Nouvelle section Agenda -->
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="calendar"></i>
                    <h4 class="card-title">Agenda</h4>
                    <p class="card-text">Consultez et gérez vos rendez-vous.</p>
                    <a href="agenda.php" class="btn btn-primary"><i data-lucide="eye"></i> Voir</a>
                </div>
            </div>
            <!-- Nouvelle section Messagerie/Assistance -->
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="mail"></i>
                    <h4 class="card-title">Messagerie / Assistance</h4>
                    <p class="card-text">Discutez avec notre assistant.</p>
                    <a href="chat.php" class="btn btn-primary"><i data-lucide="message-circle"></i> Accéder</a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit" class="btn btn-danger">Déconnexion</button>
            </form>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php
ob_end_flush();
?>