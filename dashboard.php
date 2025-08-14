<?php
// Démarrer le buffer de sortie
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
$dateTime = date('H:i A \o\n l, F j, Y', time()); // 09:45 AM GMT, Thursday, August 14, 2025

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
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Tableau de Bord - <?php echo ($userRole == 'admin') ? 'Admin' : ''; ?> Patrimoine Plus</title>
  <meta name="description" content="<?php echo ($userRole == 'admin') ? 'Tableau de bord pour administrer les utilisateurs et les patrimoines.' : 'Tableau de bord pour gérer votre patrimoine.'; ?>">
  <meta name="keywords" content="<?php echo ($userRole == 'admin') ? 'tableau de bord, admin, gestion utilisateurs, patrimoine' : 'tableau de bord, gestion patrimoine'; ?>">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    }
    .sidebar a {
      color: white;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      text-decoration: none;
      gap: 10px;
    }
    .sidebar a:hover {
      background-color: #152e6f;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
    }
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .chart-placeholder {
      height: 100px;
      background-color: #e9ecef;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 10px 0;
    }
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-250px);
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h4 class="text-center">Menu <?php echo ($userRole == 'admin') ? 'Admin' : ''; ?></h4>
    <?php if ($userRole == 'admin'): ?>
      <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
      <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
      <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
      <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
      <a href="patrimoine.php"><i data-lucide="database"></i>Patrimoines</a>
      <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
      <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
    <?php else: ?>
      <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
      <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
      <a href="#"><i data-lucide="home"></i> Mon Patrimoine</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <div class="dashboard-header">
      <h2 class="text-blue">Tableau de Bord - <?php echo htmlspecialchars($userName); ?></h2>
      <p class="text-muted">Dernière mise à jour : <?php echo $dateTime; ?></p>
    </div>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Nombre d'Utilisateurs</h4>
          <p class="card-text"><strong><?php echo $userCount; ?></strong> utilisateurs</p>
          <a href="gestion_users.php" class="btn btn-primary"><i data-lucide="users"></i> Gérer</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Valeur Totale des Patrimoines</h4>
          <p class="card-text"><strong>N/A</strong> (Ajouter colonne 'value')</p>
          <a href="#" class="btn btn-primary"><i data-lucide="database"></i> Voir</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Investissements en Cours</h4>
          <p class="card-text"><strong>N/A</strong> (Ajouter colonne 'end_date')</p>
          <a href="#" class="btn btn-primary"><i data-lucide="trending-up"></i> Analyser</a>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Progression de Validation</h4>
          <p class="card-text"><strong>N/A</strong> (Ajouter colonne 'is_validated')</p>
          <a href="validation_doc.php" class="btn btn-primary"><i data-lucide="file-check"></i> Valider</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Nombre de Patrimoines</h4>
          <p class="card-text"><strong><?php echo $patrimoineCount; ?></strong> patrimoines</p>
          <a href="#" class="btn btn-primary"><i data-lucide="database"></i> Voir</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Accès à Tous les Patrimoines</h4>
          <p class="card-text">Consultez tous les patrimoines.</p>
          <a href="#" class="btn btn-primary"><i data-lucide="database"></i> Voir</a>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Statistiques Globales</h4>
          <p class="card-text">Analysez les performances.</p>
          <div class="chart-placeholder">Espace pour graphique (Chart.js)</div>
          <a href="#" class="btn btn-primary mt-2"><i data-lucide="bar-chart-2"></i> Analyser</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Paramétrage</h4>
          <p class="card-text">Configurez les types de biens.</p>
          <a href="#" class="btn btn-primary"><i data-lucide="settings"></i> Configurer</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Gestion des Actifs</h4>
          <p class="card-text">Gérez et analysez vos actifs.</p>
          <a href="actifs.php" class="btn btn-primary"><i data-lucide="briefcase"></i> Gérer</a>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn btn-danger">Déconnexion</button>
      </form>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    lucide.createIcons();
  </script>

</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>