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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Gestion Actifs - Patrimoine Plus</title>
  <meta name="description" content="Gérez vos actifs et analysez leurs performances avec Patrimoine Plus.">
  <meta name="keywords" content="gestion actifs, patrimoine, analyse, investissements">

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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- Custom CSS for Actifs -->
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', sans-serif;
    }
    .content {
      padding: 20px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
      margin-bottom: 20px;
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
    .chart-container {
      position: relative;
      height: 300px;
      margin-bottom: 20px;
    }
    .table {
      background-color: white;
      border-radius: 10px;
    }
    .alert {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>

  <div class="content">
    <h2 class="text-blue">Gestion Actifs - <?php echo htmlspecialchars($userName); ?></h2>
    <p class="text-muted">Dernière mise à jour : <?php echo date('H:i A \o\n l, F j, Y', time()); ?></p>

    <!-- 1. Tableau de bord global (Dashboard) -->
    <div class="card p-4">
      <h4 class="card-title">Tableau de Bord Global</h4>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Valeur totale du patrimoine :</strong> €5,000,000 <span class="text-success">(Mise à jour en temps réel)</span></p>
          <div class="chart-container">
            <canvas id="assetDistributionChart"></canvas>
          </div>
          <p><strong>Performance globale :</strong> +8% (dernier trimestre)</p>
          <p><strong>Indicateurs clés :</strong></p>
          <ul>
            <li>Niveau de risque : Moyen</li>
            <li>Liquidités disponibles : €250,000</li>
            <li>Gains/pertes cumulés : €150,000</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- 2. Liste des actifs -->
    <div class="card p-4">
      <h4 class="card-title">Liste des Actifs</h4>
      <ul class="nav nav-tabs" id="assetTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="immobilier-tab" data-bs-toggle="tab" data-bs-target="#immobilier" type="button" role="tab">Immobilier</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="financier-tab" data-bs-toggle="tab" data-bs-target="#financier" type="button" role="tab">Placements Financiers</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="liquidites-tab" data-bs-toggle="tab" data-bs-target="#liquidites" type="button" role="tab">Comptes et Liquidités</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="objets-tab" data-bs-toggle="tab" data-bs-target="#objets" type="button" role="tab">Objets de Valeur</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pro-tab" data-bs-toggle="tab" data-bs-target="#pro" type="button" role="tab">Actifs Professionnels</button>
        </li>
      </ul>
      <div class="tab-content" id="assetTabsContent">
        <div class="tab-pane fade show active" id="immobilier" role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>Adresse</th>
                <th>Valeur</th>
                <th>Rendement Locatif</th>
                <th>État</th>
                <th>Charges</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>12 Rue Exemple, Paris</td><td>€300,000</td><td>5%</td><td>Bon</td><td>€5,000</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="financier" role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Nom</th>
                <th>Valeur</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Actions</td><td>Apple Inc.</td><td>€50,000</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="liquidites" role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>Compte</th>
                <th>Montant</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Compte Bancaire</td><td>€200,000</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="objets" role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Valeur</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Art</td><td>Peinture</td><td>€10,000</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="pro" role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Détail</th>
                <th>Valeur</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Parts de société</td><td>Entreprise X</td><td>€100,000</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 3. Outils d’analyse -->
    <div class="card p-4">
      <h4 class="card-title">Outils d’Analyse</h4>
      <div class="row">
        <div class="col-md-6">
          <div class="chart-container">
            <canvas id="repartitionChart"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="chart-container">
            <canvas id="evolutionChart"></canvas>
          </div>
        </div>
        <div class="col-12 mt-4">
          <p><strong>Simulation :</strong> Impact d’un investissement de €50,000 : +3% de rendement</p>
          <p><strong>Analyse de risque :</strong> Volatilité : 12%, Corrélation : 0.7</p>
        </div>
      </div>
    </div>

    <!-- 4. Actions et gestion -->
    <div class="card p-4">
      <h4 class="card-title">Actions et Gestion</h4>
      <div class="row">
        <div class="col-md-4">
          <button class="btn btn-primary w-100 mb-2">Ajouter un actif</button>
          <button class="btn btn-primary w-100 mb-2">Modifier un actif</button>
          <button class="btn btn-primary w-100">Supprimer un actif</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary w-100">Effectuer un arbitrage</button>
          <button class="btn btn-primary w-100">Gérer la fiscalité</button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary w-100">Planification successorale</button>
        </div>
      </div>
    </div>

    <!-- 5. Notifications et alertes -->
    <div class="card p-4">
      <h4 class="card-title">Notifications et Alertes</h4>
      <div class="alert alert-warning" role="alert">
        Alerte : Actif "Peinture" en perte de -5%.
      </div>
      <div class="alert alert-info" role="alert">
        Échéance : Loyer dû le 15/08/2025.
      </div>
      <div class="alert alert-success" role="alert">
        Opportunité : Nouvel ETF disponible.
      </div>
    </div>

    <!-- 6. Documents et justificatifs -->
    <div class="card p-4">
      <h4 class="card-title">Documents et Justificatifs</h4>
      <ul>
        <li><a href="#">Contrat Immobilier - 12 Rue Exemple</a></li>
        <li><a href="#">Facture Assurance - €500</a></li>
        <li><a href="#">Acte Notarié - 2025</a></li>
      </ul>
    </div>

    <div class="text-center mt-4">
      <a href="dashboard.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    lucide.createIcons();

    // Graphique de répartition (pie chart)
    const assetDistributionChart = new Chart(document.getElementById('assetDistributionChart'), {
      type: 'pie',
      data: {
        labels: ['Immobilier', 'Financier', 'Liquidités', 'Autres'],
        datasets: [{
          data: [40, 30, 20, 10],
          backgroundColor: ['#1e3a8a', '#28a745', '#ffc107', '#dc3545']
        }]
      }
    });

    // Graphique d'évolution (line chart)
    const evolutionChart = new Chart(document.getElementById('evolutionChart'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août'],
        datasets: [{
          label: 'Valeur du Patrimoine (€)',
          data: [4000000, 4200000, 4300000, 4500000, 4700000, 4800000, 4900000, 5000000],
          borderColor: '#1e3a8a',
          fill: false
        }]
      }
    });

    // Graphique de répartition (placeholder)
    const repartitionChart = new Chart(document.getElementById('repartitionChart'), {
      type: 'pie',
      data: {
        labels: ['Immobilier', 'Financier', 'Liquidités', 'Autres'],
        datasets: [{
          data: [40, 30, 20, 10],
          backgroundColor: ['#1e3a8a', '#28a745', '#ffc107', '#dc3545']
        }]
      }
    });
  </script>

</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>