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

// Connexion à la base de données
global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Récupérer les patrimoines avec toutes les colonnes pour l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM patrimoines WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patrimoines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les détails d'un patrimoine spécifique si un ID est passé
    $patrimoineDetail = null;
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM patrimoines WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        $patrimoineDetail = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Liste des Patrimoines - Patrimoine Plus</title>
    <meta name="description" content="Consultez et gérez vos patrimoines avec Patrimoine Plus.">
    <meta name="keywords" content="patrimoines, gestion, immobilier, financier">

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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

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
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        #map {
            height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }
        .badge {
            background-color: #dc3545;
            color: white;
        }
        .details-section {
            display: none;
        }
        .details-section.active {
            display: block;
        }
        .modify-form {
            display: none;
            margin-top: 10px;
        }
        .modify-form.active {
            display: block;
        }
        @media (max-width: 768px) {
            .content {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <h2 class="text-blue">Liste des Patrimoines - <?php echo htmlspecialchars($userName); ?></h2>
        <a href="add_patrimoin.php" class="btn btn-primary mb-3"><i data-lucide="plus"></i> Ajouter un Patrimoine</a>

        <!-- Tableau des patrimoines -->
        <div class="card p-4 mb-4">
            <h4 class="card-title">Tableau des Patrimoines</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom du bien</th>
                        <th>Type</th>
                        <th>Valeur estimée (€)</th>
                        <th>Date d’acquisition</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patrimoines as $patrimoine): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patrimoine['name']); ?></td>
                            <td><?php echo htmlspecialchars($patrimoine['type']); ?></td>
                            <td><?php echo number_format($patrimoine['value'], 2); ?></td>
                            <td><?php echo $patrimoine['date_acquisition'] ? date('d/m/Y', strtotime($patrimoine['date_acquisition'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($patrimoine['address']); ?></td>
                            <td>
                                <a href="?id=<?php echo $patrimoine['id']; ?>" class="btn btn-info btn-sm btn-action" onclick="showDetails(<?php echo $patrimoine['id']; ?>)"><i data-lucide="eye"></i> Voir</a>
                                <button class="btn btn-warning btn-sm btn-action" onclick="showModifyForm(<?php echo $patrimoine['id']; ?>)"><i data-lucide="edit"></i> Modifier</button>
                                <button class="btn btn-danger btn-sm btn-action" onclick="deletePatrimoine(<?php echo $patrimoine['id']; ?>)"><i data-lucide="trash"></i> Supprimer</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Fiche descriptive -->
        <div id="detailsSection" class="details-section card p-4 mb-4">
            <?php if ($patrimoineDetail): ?>
                <h4 class="card-title">Fiche Descriptive - <?php echo htmlspecialchars($patrimoineDetail['name']); ?></h4>
                <p><strong>Type :</strong> <?php echo htmlspecialchars($patrimoineDetail['type']); ?></p>
                <p><strong>Valeur estimée :</strong> <?php echo number_format($patrimoineDetail['value'], 2); ?> €</p>
                <p><strong>Date d’acquisition :</strong> <?php echo $patrimoineDetail['date_acquisition'] ? date('d/m/Y', strtotime($patrimoineDetail['date_acquisition'])) : 'N/A'; ?></p>
                <p><strong>Adresse :</strong> <?php echo htmlspecialchars($patrimoineDetail['address']); ?></p>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($patrimoineDetail['description'] ?: 'N/A'); ?></p>
                <h5>Photos</h5>
                <?php if ($patrimoineDetail['photo_path']): ?>
                    <img src="<?php echo htmlspecialchars($patrimoineDetail['photo_path']); ?>" alt="Photo" style="max-width: 200px;">
                <?php else: ?>
                    <p><em>Aucune photo uploadée.</em></p>
                <?php endif; ?>
                <h5>Documents</h5>
                <?php if ($patrimoineDetail['document_path']): ?>
                    <a href="<?php echo htmlspecialchars($patrimoineDetail['document_path']); ?>" download><i data-lucide="download"></i> Télécharger</a>
                <?php else: ?>
                    <p><em>Aucun document uploadé.</em></p>
                <?php endif; ?>
                <h5>Localisation</h5>
                <div id="map"></div>
                <h5>Contrat</h5>
                <p><strong>Date d’expiration :</strong> <?php echo $patrimoineDetail['contract_expiry_date'] ? date('d/m/Y', strtotime($patrimoineDetail['contract_expiry_date'])) : 'N/A'; ?></p>
                <button class="btn btn-secondary mt-2" onclick="hideDetails()">Fermer</button>
            <?php endif; ?>
        </div>

        <!-- Formulaire de modification -->
        <div id="modifyForm" class="modify-form card p-4 mb-4">
            <?php if ($patrimoineDetail): ?>
                <h4 class="card-title">Modifier Patrimoine - <?php echo htmlspecialchars($patrimoineDetail['name']); ?></h4>
                <form action="modifier_patrimoin.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $patrimoineDetail['id']; ?>">
                    <div class="form-group">
                        <label for="name">Nom du bien</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($patrimoineDetail['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="immobilier" <?php echo $patrimoineDetail['type'] == 'immobilier' ? 'selected' : ''; ?>>Immobilier</option>
                            <option value="financier" <?php echo $patrimoineDetail['type'] == 'financier' ? 'selected' : ''; ?>>Financier</option>
                            <option value="autre" <?php echo $patrimoineDetail['type'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="value">Valeur estimée (€)</label>
                        <input type="number" name="value" id="value" class="form-control" step="0.01" value="<?php echo $patrimoineDetail['value']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_acquisition">Date d’acquisition</label>
                        <input type="date" name="date_acquisition" id="date_acquisition" class="form-control" value="<?php echo $patrimoineDetail['date_acquisition'] ?: ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <input type="text" name="address" id="address" class="form-control" value="<?php echo htmlspecialchars($patrimoineDetail['address']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" name="latitude" id="latitude" class="form-control" step="0.00000001" value="<?php echo $patrimoineDetail['latitude'] ?: ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" name="longitude" id="longitude" class="form-control" step="0.00000001" value="<?php echo $patrimoineDetail['longitude'] ?: ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($patrimoineDetail['description'] ?: ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" name="photo" id="photo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="document">Document</label>
                        <input type="file" name="document" id="document" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contract_expiry_date">Date d’expiration du contrat</label>
                        <input type="date" name="contract_expiry_date" id="contract_expiry_date" class="form-control" value="<?php echo $patrimoineDetail['contract_expiry_date'] ?: ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary mt-2"><i data-lucide="save"></i> Sauvegarder</button>
                    <button type="button" class="btn btn-secondary mt-2" onclick="hideModifyForm()">Annuler</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Suivi des contrats et alertes -->
        <div class="card p-4 mb-4">
            <h4 class="card-title">Suivi des Contrats et Documents</h4>
            <ul class="list-group">
                <?php if ($patrimoineDetail && $patrimoineDetail['contract_expiry_date']): ?>
                    <li class="list-group-item">
                        Contrat - Expire le <?php echo date('d/m/Y', strtotime($patrimoineDetail['contract_expiry_date'])); ?>
                        <span class="badge"><?php echo floor((strtotime($patrimoineDetail['contract_expiry_date']) - time()) / (60 * 60 * 24)); ?> jours restants</span>
                        <?php if ($patrimoineDetail['document_path']): ?>
                            <a href="<?php echo htmlspecialchars($patrimoineDetail['document_path']); ?>" class="btn btn-sm btn-secondary float-end"><i data-lucide="download"></i> Télécharger</a>
                        <?php endif; ?>
                    </li>
                <?php else: ?>
                    <li class="list-group-item"><em>Aucun contrat enregistré.</em></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card p-4 mb-4">
            <h4 class="card-title">Alertes Automatiques <i data-lucide="bell" class="text-danger"></i><span class="badge"><?php echo $patrimoineDetail && $patrimoineDetail['contract_expiry_date'] ? 1 : 0; ?></span></h4>
            <p><em>Notifications email/SMS à implémenter X jours avant expiration.</em></p>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        // Initialisation de la carte Leaflet avec la localisation si disponible
        <?php if ($patrimoineDetail && $patrimoineDetail['latitude'] && $patrimoineDetail['longitude']): ?>
            var map = L.map('map').setView([<?php echo $patrimoineDetail['latitude']; ?>, <?php echo $patrimoineDetail['longitude']; ?>], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);
            L.marker([<?php echo $patrimoineDetail['latitude']; ?>, <?php echo $patrimoineDetail['longitude']; ?>]).addTo(map);
        <?php else: ?>
            var map = L.map('map').setView([48.8566, 2.3522], 13); // Paris par défaut
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);
        <?php endif; ?>

        // Fonctions pour afficher/masquer les sections
        function showDetails(id) {
            window.location.href = `patrimoine.php?id=${id}`;
        }

        function hideDetails() {
            document.getElementById('detailsSection').classList.remove('active');
            window.history.pushState({}, document.title, 'patrimoine.php');
        }

        function showModifyForm(id) {
            if (document.getElementById('modifyForm').querySelector('input[name="id"]').value !== id.toString()) {
                window.location.href = `patrimoine.php?id=${id}`;
            }
            document.getElementById('modifyForm').classList.add('active');
        }

        function hideModifyForm() {
            document.getElementById('modifyForm').classList.remove('active');
        }

        function deletePatrimoine(id) {
            if (confirm('Confirmer la suppression ?')) {
                fetch('delete_patrimoin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Erreur lors de la suppression.');
                    }
                })
                .catch(error => alert('Erreur : ' + error));
            }
        }

        // Afficher les détails si un ID est passé dans l'URL
        <?php if (isset($_GET['id'])): ?>
            document.getElementById('detailsSection').classList.add('active');
        <?php endif; ?>
    </script>
</body>
</html>
<?php
ob_end_flush();
?>