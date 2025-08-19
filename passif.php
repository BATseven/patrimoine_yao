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
date_default_timezone_set('GMT');
$dateTime = date('H:i A \o\n l, F j, Y', strtotime('09:01 AM GMT')); // 09:01 AM GMT, Tuesday, August 19, 2025

global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Simuler des données pour le tableau (à remplacer par une requête SQL réelle)
    $passifs = [
        ['nom' => 'Crédit Maison', 'type' => 'Crédit bancaire', 'montant_total' => 150000, 'montant_paye' => 50000, 'reste' => 100000, 'prochaine_echeance' => '2025-09-15', 'statut' => 'En cours'],
        ['nom' => 'Impôt Foncier', 'type' => 'Impôt', 'montant_total' => 3000, 'montant_paye' => 3000, 'reste' => 0, 'prochaine_echeance' => '2025-08-01', 'statut' => 'Payé'],
        ['nom' => 'Dette Fournisseur', 'type' => 'Dette fournisseur', 'montant_total' => 5000, 'montant_paye' => 0, 'reste' => 5000, 'prochaine_echeance' => '2025-08-25', 'statut' => 'En retard'],
    ];

    // Traitement du formulaire d'ajout (simplifié)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_passif'])) {
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        $montant_total = floatval($_POST['montant_total']);
        $montant_paye = floatval($_POST['montant_paye'] ?? 0);
        $reste = $montant_total - $montant_paye;
        $taux_interet = floatval($_POST['taux_interet'] ?? 0);
        $date_debut = $_POST['date_debut'];
        $date_echeance = $_POST['date_echeance'];
        $periodicite = $_POST['periodicite'];
        $documents = $_POST['documents'] ?? '';

        // Simuler l'insertion (à remplacer par une requête SQL)
        $new_passif = ['nom' => $nom, 'type' => $type, 'montant_total' => $montant_total, 'montant_paye' => $montant_paye, 'reste' => $reste, 'prochaine_echeance' => $date_echeance, 'statut' => 'En cours'];
        $passifs[] = $new_passif;
    }

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Gestion des Passifs - <?php echo ($userRole == 'admin') ? 'Admin' : ''; ?> Patrimoine Plus</title>
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table { margin-top: 20px; }
        .chart-container { position: relative; height: 300px; margin-top: 20px; }
        .alert { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Gestion des Passifs</h2>
        <p>Dernière mise à jour: <?php echo $dateTime; ?></p>

        <!-- Formulaire d'ajout de passif -->
        <div class="card p-4 mb-4">
            <h4>Ajouter un Passif</h4>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom">Nom du Passif</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type">Type de Passif</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="Crédit bancaire">Crédit bancaire</option>
                            <option value="Impôt">Impôt</option>
                            <option value="Dette fournisseur">Dette fournisseur</option>
                            <option value="Charge récurrente">Charge récurrente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="montant_total">Montant Total Dû</label>
                        <input type="number" class="form-control" id="montant_total" name="montant_total" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="montant_paye">Montant Payé</label>
                        <input type="number" class="form-control" id="montant_paye" name="montant_paye" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="taux_interet">Taux d’Intérêt/Frais (%)</label>
                        <input type="number" class="form-control" id="taux_interet" name="taux_interet" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_debut">Date de Début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_echeance">Date d’Échéance</label>
                        <input type="date" class="form-control" id="date_echeance" name="date_echeance" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="periodicite">Périodicité</label>
                        <select class="form-control" id="periodicite" name="periodicite" required>
                            <option value="Mensuel">Mensuel</option>
                            <option value="Trimestriel">Trimestriel</option>
                            <option value="Annuel">Annuel</option>
                            <option value="Unique">Unique</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="documents">Documents Associés</label>
                        <input type="text" class="form-control" id="documents" name="documents" placeholder="URL ou chemin">
                    </div>
                </div>
                <button type="submit" name="add_passif" class="btn btn-primary">Ajouter</button>
            </form>
        </div>

        <!-- Tableau de suivi des passifs -->
        <div class="card p-4">
            <h4>Tableau de Suivi des Passifs</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Montant Total</th>
                        <th>Montant Payé</th>
                        <th>Restant Dû</th>
                        <th>Prochaine Échéance</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($passifs as $passif): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($passif['nom']); ?></td>
                            <td><?php echo htmlspecialchars($passif['type']); ?></td>
                            <td><?php echo number_format($passif['montant_total'], 2); ?> €</td>
                            <td><?php echo number_format($passif['montant_paye'], 2); ?> €</td>
                            <td><?php echo number_format($passif['reste'], 2); ?> €</td>
                            <td><?php echo htmlspecialchars($passif['prochaine_echeance']); ?></td>
                            <td><?php echo htmlspecialchars($passif['statut']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Statistiques / Visualisation -->
        <div class="card p-4 mt-4">
            <h4>Statistiques</h4>
            <div class="row">
                <div class="col-md-6 chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="col-md-6 chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="mt-4">
                <p><strong>Total des Passifs :</strong> <?php echo number_format(array_sum(array_column($passifs, 'montant_total')), 2); ?> €</p>
                <p><strong>Total Restant Dû :</strong> <?php echo number_format(array_sum(array_column($passifs, 'reste')), 2); ?> €</p>
                <p><strong>Ratio Actifs/Passifs :</strong> (À calculer avec données actifs)</p>
            </div>
        </div>

        <!-- Notifications & Alertes -->
        <div class="card p-4 mt-4">
            <h4>Notifications</h4>
            <?php
            $today = date('Y-m-d');
            foreach ($passifs as $passif) {
                $echeance = new DateTime($passif['prochaine_echeance']);
                $interval = $echeance->diff(new DateTime($today));
                if ($passif['statut'] === 'En retard' || $interval->days <= 7) {
                    echo '<div class="alert alert-warning" role="alert">';
                    echo "Rappel : {$passif['nom']} - Échéance le {$passif['prochaine_echeance']} ";
                    if ($passif['statut'] === 'En retard') echo "(En retard)";
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
        // Graphique circulaire (Pie Chart)
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Crédits', 'Impôts', 'Dettes'],
                datasets: [{
                    data: [60, 30, 10],
                    backgroundColor: ['#1e3a8a', '#f59e0b', '#ef4444']
                }]
            }
        });

        // Graphique en barres (Bar Chart)
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Passifs par Mois',
                    data: [10000, 15000, 12000, 18000, 14000, 20000],
                    backgroundColor: '#1e3a8a'
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>