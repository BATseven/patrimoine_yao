<?php
session_start();
require_once 'config.php'; // Assurez-vous que config.php contient les identifiants de la base

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=patrimoine_plus_db;charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // KPI : Valeur totale du patrimoine
    $totalWealthStmt = $pdo->query("SELECT SUM(value) as total FROM actifs UNION ALL SELECT SUM(valeur) FROM patrimoines");
    $totalWealth = 0;
    while ($row = $totalWealthStmt->fetch(PDO::FETCH_ASSOC)) {
        $totalWealth += $row['total'] ?? 0;
    }
    $currency = $pdo->query("SELECT currency FROM company_settings LIMIT 1")->fetchColumn() ?: 'EUR';

    // Répartition Actifs/Passifs (simplifiée, car passifs manquent, on utilise une approximation)
    $assetsValue = $pdo->query("SELECT SUM(value) as total FROM actifs")->fetchColumn() ?: 0;
    $liabilitiesValue = 0; // À ajuster si table passifs existe
    $totalValue = $totalWealth;
    $assetsPercent = $totalValue > 0 ? ($assetsValue / $totalValue) * 100 : 0;
    $liabilitiesPercent = 100 - $assetsPercent;

    // Évolution mensuelle (simplifiée, basée sur la dernière entrée)
    $latestWealth = $pdo->query("SELECT value FROM patrimoines ORDER BY created_at DESC LIMIT 1")->fetchColumn() ?: 0;
    $previousWealth = $pdo->query("SELECT value FROM patrimoines ORDER BY created_at ASC LIMIT 1")->fetchColumn() ?: 0;
    $monthlyGrowth = ($latestWealth > $previousWealth && $previousWealth > 0) ? (($latestWealth - $previousWealth) / $previousWealth) * 100 : 0;

    // Documents validés/en attente (simplifié, basé sur document_validation)
    $validatedDocs = $pdo->query("SELECT COUNT(*) FROM document_validation WHERE submission_date IS NOT NULL")->fetchColumn() ?: 0;
    $pendingDocs = 0; // À ajuster avec une logique spécifique si nécessaire

    // Utilisateurs actifs
    $activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn() ?: 0;

    // Données pour graphiques
    $assetTypes = $pdo->query("SELECT type, SUM(value) as total FROM actifs GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
    $patrimoineTypes = $pdo->query("SELECT type, SUM(valeur) as total FROM patrimoines GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
    $allAssets = array_merge($assetTypes, $patrimoineTypes);
    $typeTotals = [];
    foreach ($allAssets as $asset) {
        $typeTotals[$asset['type']] = ($typeTotals[$asset['type']] ?? 0) + $asset['total'];
    }

    // Données pour la courbe d'évolution
    $wealthEvolution = $pdo->query("SELECT created_at, value FROM patrimoines ORDER BY created_at")->fetchAll(PDO::FETCH_ASSOC);

    // Alertes
    $nearExpiry = $pdo->query("SELECT name, contract_expiry_date FROM patrimoines WHERE contract_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchAll(PDO::FETCH_ASSOC);

    // Prévisions (simplifiée, croissance de 5% sur un an)
    $projectedWealth = $totalWealth * 1.05;
    $avgYield = $totalWealth > 0 ? (($assetsValue / $totalWealth) * 3.5) : 0; // Rendement moyen estimé
    $debtRatio = 0; // À calculer si passifs existants

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Patrimoine Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-kpi { transition: transform 0.2s, box-shadow 0.2s; }
        .card-kpi:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
        .chart-container { position: relative; height: 300px; margin-bottom: 20px; }
        .table-responsive { max-height: 300px; }
        .alert-custom { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <h2 class="mb-4">Section Statistiques</h2>

        <!-- 1. Indicateurs clés (KPI en tuiles / cartes) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-kpi text-white bg-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">Valeur Totale du Patrimoine</h5>
                        <h3><?php echo number_format($totalWealth, 2); ?> <?php echo htmlspecialchars($currency); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kpi text-white bg-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Répartition Actifs / Passifs</h5>
                        <p>Actifs: <?php echo number_format($assetsPercent, 2); ?>% | Passifs: <?php echo number_format($liabilitiesPercent, 2); ?>%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kpi text-white bg-info">
                    <div class="card-body text-center">
                        <h5 class="card-title">Évolution Mensuelle</h5>
                        <p>Croissance: <?php echo number_format($monthlyGrowth, 2); ?>%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kpi text-white bg-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title">Documents</h5>
                        <p>Validés: <?php echo $validatedDocs; ?> | En attente: <?php echo $pendingDocs; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-kpi text-white bg-danger">
                    <div class="card-body text-center">
                        <h5 class="card-title">Utilisateurs Actifs</h5>
                        <p>Total: <?php echo $activeUsers; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Graphiques et Visualisations -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="assetPieChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="wealthLineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. Tableaux de détails -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des Actifs</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Valeur (<?php echo htmlspecialchars($currency); ?>)</th>
                                        <th>Type</th>
                                        <th>Date Acquisition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $actifsStmt = $pdo->query("SELECT name, value, type, acquisition_date FROM actifs");
                                    while ($row = $actifsStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . number_format($row['value'], 2) . "</td><td>" . htmlspecialchars($row['type']) . "</td><td>" . ($row['acquisition_date'] ?: 'N/A') . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des Patrimoines</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Valeur (<?php echo htmlspecialchars($currency); ?>)</th>
                                        <th>Type</th>
                                        <th>Date Acquisition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $patrimoinesStmt = $pdo->query("SELECT name, valeur, type, date_acquisition FROM patrimoines");
                                    while ($row = $patrimoinesStmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . number_format($row['valeur'], 2) . "</td><td>" . htmlspecialchars($row['type']) . "</td><td>" . ($row['date_acquisition'] ?: 'N/A') . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Alertes et prévisions -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Alertes</h5>
                        <?php if ($nearExpiry): ?>
                            <?php foreach ($nearExpiry as $alert): ?>
                                <div class="alert alert-warning alert-custom" role="alert">
                                    Échéance proche pour <?php echo htmlspecialchars($alert['name']); ?> (<?php echo $alert['contract_expiry_date']; ?>).
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-success" role="alert">Aucune alerte pour le moment.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Prévisions</h5>
                        <p>Valeur prévue dans 1 an: <?php echo number_format($projectedWealth, 2); ?> <?php echo htmlspecialchars($currency); ?> (+5%)</p>
                        <p>Rendement moyen: <?php echo number_format($avgYield, 2); ?>% | Taux d'endettement: <?php echo number_format($debtRatio, 2); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Export et partage -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Export</h5>
                        <button class="btn btn-primary me-2" onclick="exportPDF()">Exporter en PDF</button>
                        <button class="btn btn-success" onclick="exportExcel()">Exporter en Excel</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Partage</h5>
                        <button class="btn btn-info" onclick="scheduleReport()">Planifier Rapport Mensuel</button>
                        <p class="text-muted small mt-2">Envoyer automatiquement aux administrateurs/clients (à configurer).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique circulaire (répartition des actifs)
        const ctxPie = document.getElementById('assetPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($typeTotals)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($typeTotals)); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: { responsive: true }
        });

        // Courbe d'évolution du patrimoine
        const ctxLine = document.getElementById('wealthLineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($wealthEvolution, 'created_at')); ?>,
                datasets: [{
                    label: 'Patrimoine (<?php echo htmlspecialchars($currency); ?>)',
                    data: <?php echo json_encode(array_column($wealthEvolution, 'value')); ?>,
                    borderColor: '#28A745',
                    fill: false
                }]
            },
            options: { responsive: true }
        });

        // Fonctions d'export (simulées)
        function exportPDF() {
            alert('Exportation en PDF en cours... (à implémenter avec jsPDF)');
        }
        function exportExcel() {
            alert('Exportation en Excel en cours... (à implémenter avec SheetJS)');
        }
        function scheduleReport() {
            alert('Planification du rapport mensuel... (à configurer avec un serveur d\'envoi d\'email)');
        }
    </script>
</body>
</html>