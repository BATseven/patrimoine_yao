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
$dateTime = date('H:i A \o\n l, F j, Y', time()); // Exemple : 12:08 PM on Monday, August 11, 2025
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Tableau de Bord - Patrimoine Plus</title>
  <meta name="description" content="Tableau de bord pour gérer votre patrimoine.">
  <meta name="keywords" content="tableau de bord, gestion patrimoine">

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

  <!-- Custom CSS for Dashboard -->
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
      transition: all 0.3s;
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
    .dashboard-header {
      background-color: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
      height: 100%;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-title {
      color: #1e3a8a;
    }
    .btn-primary {
      background-color: #1e3a8a;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .btn-primary:hover {
      background-color: #152e6f;
    }
    .btn-success {
      background-color: #28a745;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      font-size: 1rem;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    .logout-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 20px;
    }
    .logout-btn:hover {
      background-color: #c82333;
    }
    .text-blue {
      color: #1e3a8a;
    }
    .text-muted {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .widget {
      background-color: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .notification-bell {
      font-size: 1.5rem;
      cursor: pointer;
      color: #1e3a8a;
    }
    .notification-bell:hover {
      color: #152e6f;
    }
    .chart-placeholder {
      height: 300px;
      background-color: #e9ecef;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h4 class="text-center">Menu</h4>
    <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
    <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
    <a href="#"><i data-lucide="database"></i> Accès Patrimoines</a>
    <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
    <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
  </div>

  <div class="content">
    <div class="dashboard-header">
      <div>
        <h2 class="text-blue">Tableau de Bord - <?php echo htmlspecialchars($userName); ?></h2>
        <p class="text-muted">Dernière mise à jour : <?php echo $dateTime; ?></p>
      </div>
      <div>
        <i class="notification-bell" data-lucide="bell" onclick="alert('Alertes : Échéance fiscale demain, 10 nouveaux documents.')"></i>
      </div>
    </div>

    <div class="row">
      <!-- Widgets Visuels -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="widget">
            <h6>Valeur Totale Patrimoines</h6>
            <p class="text-blue h4">€5,000,000 💰</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="widget">
            <h6>Nombre d'Utilisateurs</h6>
            <p class="text-blue h4">50 🏠</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="widget">
            <h6>Investissements en Cours</h6>
            <p class="text-blue h4">€1,200,000 📈</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="widget">
            <h6>Progression Validation</h6>
            <p class="text-blue h4">80% 🎯</p>
          </div>
        </div>
      </div>

      <!-- Accueil Personnalisé -->
      <div class="col-12 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Accueil</h4>
          <p class="card-text">Résumé des activités, alertes, et statistiques récentes.</p>
          <ul>
            <li>Total utilisateurs actifs : 45</li>
            <li>Alertes : 10 documents en attente</li>
            <li>Statistique : +15% ce mois</li>
          </ul>
          <a href="#" class="btn btn-success"><i data-lucide="eye"></i> Voir Détails</a>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Validation des Documents</h4>
          <p class="card-text">Vérifiez et approuvez les documents.</p>
          <a href="#" class="btn btn-primary"><i data-lucide="file-check"></i> Valider</a>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card p-4 h-100">
          <h4 class="card-title">Accès à Tous les Patrimoines</h4>
          <p class="card-text">Consultez tous les patrimoines.</p>
          <a href="#" class="btn btn-primary"><i data-lucide="database"></i> Voir</a>
        </div>
      </div>
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
    </div>

    <div class="text-center mt-4">
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="logout-btn">Déconnexion</button>
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