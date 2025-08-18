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
$dateTime = date('H:i A \o\n l, F j, Y', time()); // 03:15 PM GMT, Thursday, August 14, 2025

// Connexion à la base de données
global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Cartes de Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $adminCount = 0;
    $userCountByRole = 0;
    foreach ($roleCounts as $role) {
        if ($role['role'] == 'admin') $adminCount = $role['count'];
        if ($role['role'] == 'user') $userCountByRole = $role['count'];
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM documents");
    $documentCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM documents GROUP BY status");
    $stmt->execute();
    $documentStatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $validated = $pending = $rejected = 0;
    foreach ($documentStatuses as $status) {
        if ($status['status'] == 'validé') $validated = $status['count'];
        if ($status['status'] == 'en attente') $pending = $status['count'];
        if ($status['status'] == 'rejeté') $rejected = $status['count'];
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM patrimoines");
    $patrimoineCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email_verified = 1");
    $stmt->execute();
    $verifiedEmailCount = $stmt->fetchColumn();
    $verificationRate = ($userCount > 0) ? round(($verifiedEmailCount / $userCount) * 100, 2) : 0;

    // Graphiques - Données mensuelles (exemple statique, à adapter avec une vraie logique)
    $userMonthly = [10, 15, 20, 25, 30, 22, 18]; // Exemple : jan à juil 2025
    $docMonthly = [50, 60, 70, 80, 90, 85, 75];  // Exemple : jan à juil 2025
    $patrimoineCumulative = [10, 25, 40, 55, 70, 85, 85]; // Exemple : croissance cumulée

    // Tableaux complémentaires
    $stmt = $pdo->query("SELECT u.full_name, COUNT(d.id) as doc_count FROM users u LEFT JOIN documents d ON u.id = d.user_id GROUP BY u.id, u.full_name ORDER BY doc_count DESC LIMIT 5");
    $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT p.name, COUNT(pv.view_id) as view_count FROM patrimoines p LEFT JOIN patrimoine_views pv ON p.id = pv.patrimoine_id GROUP BY p.id, p.name ORDER BY view_count DESC LIMIT 5");
    $topPatrimoines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT u.full_name, a.activity, a.created_at FROM activities a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Indicateurs d'Alerte
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0");
    $disabledAccounts = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'en attente'");
    $pendingDocs = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT name FROM patrimoines ORDER BY created_at DESC LIMIT 1");
    $lastPatrimoine = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Statistiques - <?php echo ($userRole == 'admin') ? 'Admin' : ''; ?> Patrimoine Plus</title>
    <meta name="description" content="Section des statistiques pour analyser les données des utilisateurs et patrimoines.">
    <meta name="keywords" content="statistiques, dashboard, utilisateurs, patrimoines, documents">

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
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
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
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
        }
        .chart-container {
            margin-top: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .alert-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
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
        <h4 class="text-center">Menu <?php echo ($userRole == 'admin') ? 'Admin' : 'Utilisateur'; ?></h4>
        <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
            <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <?php endif; ?>
        <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="stats.php" class="active"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
            <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Statistiques Globales - <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-muted">Dernière mise à jour: <?php echo $dateTime; ?></p>
        </div>

        <!-- Cartes de Statistiques -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="users"></i>
                    <h4 class="card-title">Nombre total d’utilisateurs</h4>
                    <p class="card-text"><?php echo $userCount; ?> utilisateurs</p>
                    <p><span class="badge bg-primary">Admin : <?php echo $adminCount; ?></span> <span class="badge bg-secondary">User : <?php echo $userCountByRole; ?></span></p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="file-text"></i>
                    <h4 class="card-title">Documents soumis</h4>
                    <p class="card-text"><?php echo $documentCount; ?> documents</p>
                    <p><span class="badge bg-success">Validés : <?php echo $validated; ?></span> <span class="badge bg-warning">En attente : <?php echo $pending; ?></span> <span class="badge bg-danger">Rejetés : <?php echo $rejected; ?></span></p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="database"></i>
                    <h4 class="card-title">Patrimoines enregistrés</h4>
                    <p class="card-text"><?php echo $patrimoineCount; ?> patrimoines</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <i data-lucide="check-circle"></i>
                    <h4 class="card-title">Taux de vérification</h4>
                    <p class="card-text"><?php echo $verificationRate; ?>% (email vérifié)</p>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h4>Évolution des utilisateurs et documents</h4>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h4>Répartition des rôles et statuts</h4>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <div class="chart-container">
                    <h4>Croissance cumulée des patrimoines</h4>
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tableaux complémentaires -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <h4>Top 5 des utilisateurs les plus actifs</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo $user['doc_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <h4>Patrimoines les plus consultés</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Vues</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topPatrimoines as $patrimoine): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($patrimoine['name']); ?></td>
                                    <td><?php echo $patrimoine['view_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <h4>Dernières activités</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Activité</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($activity['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Indicateurs d’Alerte -->
        <div class="alert-box">
            <p><strong>Comptes désactivés :</strong> <?php echo $disabledAccounts; ?></p>
            <p><strong>Documents en attente :</strong> <?php echo $pendingDocs; ?></p>
            <p><strong>Dernier patrimoine ajouté :</strong> <?php echo htmlspecialchars($lastPatrimoine); ?></p>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        // Graphique en barres
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Utilisateurs',
                    data: <?php echo json_encode($userMonthly); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }, {
                    label: 'Documents',
                    data: <?php echo json_encode($docMonthly); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Graphique en camembert
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Admin', 'User', 'Validés', 'En attente', 'Rejetés'],
                datasets: [{
                    data: [<?php echo $adminCount; ?>, <?php echo $userCountByRole; ?>, <?php echo $validated; ?>, <?php echo $pending; ?>, <?php echo $rejected; ?>],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Graphique en ligne
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Patrimoines cumulés',
                    data: <?php echo json_encode($patrimoineCumulative); ?>,
                    borderColor: '#36A2EB',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>