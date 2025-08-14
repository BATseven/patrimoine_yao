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

    // Récupérer les actifs pour l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM actifs WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $actifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les détails d'un actif spécifique si un ID est passé
    $actifDetail = null;
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM actifs WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        $actifDetail = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Gestion des Actifs - Patrimoine Plus</title>
    <meta name="description" content="Gérez et analysez vos actifs avec Patrimoine Plus.">
    <meta name="keywords" content="actifs, gestion, investissement, fiscalité">

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
        }
        .details-section, .modify-form {
            display: none;
        }
        .details-section.active, .modify-form.active {
            display: block;
        }
        .add-form {
            /* Rendre visible par défaut pour test */
            display: block;
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
            <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
            <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
            <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
        <?php else: ?>
            <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
            <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
            <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
            <a href="#"><i data-lucide="home"></i> Mon Patrimoine</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Gestion des Actifs - <?php echo htmlspecialchars($userName); ?></h2>
            <button class="btn btn-primary mb-3" onclick="showAddForm()"><i data-lucide="plus"></i> Ajouter un Actif</button>
        </div>

        <!-- Tableau des actifs -->
        <div class="card p-4 mb-4">
            <h4 class="card-title">Tableau des Actifs</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom de l'actif</th>
                        <th>Type</th>
                        <th>Valeur (€)</th>
                        <th>Date d'acquisition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actifs as $actif): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($actif['name']); ?></td>
                            <td><?php echo htmlspecialchars($actif['type']); ?></td>
                            <td><?php echo number_format($actif['value'], 2); ?></td>
                            <td><?php echo $actif['acquisition_date'] ? date('d/m/Y', strtotime($actif['acquisition_date'])) : 'N/A'; ?></td>
                            <td>
                                <a href="?id=<?php echo $actif['id']; ?>" class="btn btn-info btn-sm" onclick="showDetails(<?php echo $actif['id']; ?>)"><i data-lucide="eye"></i> Voir</a>
                                <button class="btn btn-warning btn-sm" onclick="showModifyForm(<?php echo $actif['id']; ?>)"><i data-lucide="edit"></i> Modifier</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteActif(<?php echo $actif['id']; ?>)"><i data-lucide="trash"></i> Supprimer</button>
                                <button class="btn btn-success btn-sm" onclick="arbitrageActif(<?php echo $actif['id']; ?>)"><i data-lucide="repeat"></i> Arbitrage</button>
                                <button class="btn btn-secondary btn-sm" onclick="fiscaliteActif(<?php echo $actif['id']; ?>)"><i data-lucide="file-text"></i> Fiscalité</button>
                                <button class="btn btn-primary btn-sm" onclick="planificationActif(<?php echo $actif['id']; ?>)"><i data-lucide="calendar"></i> Planification</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Détails de l'actif -->
        <div id="detailsSection" class="details-section card p-4 mb-4">
            <?php if ($actifDetail): ?>
                <h4 class="card-title">Détails de l'Actif - <?php echo htmlspecialchars($actifDetail['name']); ?></h4>
                <p><strong>Type :</strong> <?php echo htmlspecialchars($actifDetail['type']); ?></p>
                <p><strong>Valeur :</strong> <?php echo number_format($actifDetail['value'], 2); ?> €</p>
                <p><strong>Date d'acquisition :</strong> <?php echo $actifDetail['acquisition_date'] ? date('d/m/Y', strtotime($actifDetail['acquisition_date'])) : 'N/A'; ?></p>
                <p><strong>Adresse :</strong> <?php echo htmlspecialchars($actifDetail['address'] ?: 'N/A'); ?></p>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($actifDetail['description'] ?: 'N/A'); ?></p>
                <button class="btn btn-secondary mt-2" onclick="hideDetails()">Fermer</button>
            <?php endif; ?>
        </div>

        <!-- Formulaire de modification -->
        <div id="modifyForm" class="modify-form card p-4 mb-4">
            <?php if ($actifDetail): ?>
                <h4 class="card-title">Modifier Actif - <?php echo htmlspecialchars($actifDetail['name']); ?></h4>
                <form action="modify_actif.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $actifDetail['id']; ?>">
                    <div class="form-group">
                        <label for="name">Nom de l'actif</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($actifDetail['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="immobilier" <?php echo $actifDetail['type'] == 'immobilier' ? 'selected' : ''; ?>>Immobilier</option>
                            <option value="financier" <?php echo $actifDetail['type'] == 'financier' ? 'selected' : ''; ?>>Financier</option>
                            <option value="autre" <?php echo $actifDetail['type'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="value">Valeur (€)</label>
                        <input type="number" name="value" id="value" class="form-control" step="0.01" value="<?php echo $actifDetail['value']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="acquisition_date">Date d'acquisition</label>
                        <input type="date" name="acquisition_date" id="acquisition_date" class="form-control" value="<?php echo $actifDetail['acquisition_date'] ?: ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <input type="text" name="address" id="address" class="form-control" value="<?php echo htmlspecialchars($actifDetail['address'] ?: ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($actifDetail['description'] ?: ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" name="photo" id="photo" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary mt-2"><i data-lucide="save"></i> Sauvegarder</button>
                    <button type="button" class="btn btn-secondary mt-2" onclick="hideModifyForm()">Annuler</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Formulaire d'ajout -->
        <div id="addForm" class="add-form card p-4 mb-4">
            <h4 class="card-title">Ajouter un Actif</h4>
            <form action="add_actif.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nom de l'actif</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="immobilier">Immobilier</option>
                        <option value="financier">Financier</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="value">Valeur (€)</label>
                    <input type="number" name="value" id="value" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="acquisition_date">Date d'acquisition</label>
                    <input type="date" name="acquisition_date" id="acquisition_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input type="text" name="address" id="address" class="form-control">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="photo">Photo</label>
                    <input type="file" name="photo" id="photo" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary mt-2"><i data-lucide="save"></i> Ajouter</button>
                <button type="button" class="btn btn-secondary mt-2" onclick="hideAddForm()">Annuler</button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        function showDetails(id) {
            window.location.href = `actifs.php?id=${id}`;
        }

        function hideDetails() {
            document.getElementById('detailsSection').classList.remove('active');
            window.history.pushState({}, document.title, 'actifs.php');
        }

        function showModifyForm(id) {
            if (document.getElementById('modifyForm').querySelector('input[name="id"]').value !== id.toString()) {
                window.location.href = `actifs.php?id=${id}`;
            }
            document.getElementById('modifyForm').classList.add('active');
        }

        function hideModifyForm() {
            document.getElementById('modifyForm').classList.remove('active');
        }

        function deleteActif(id) {
            if (confirm('Confirmer la suppression ?')) {
                fetch('delete_actif.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}`
                })
                .then(response => {
                    if (response.ok) window.location.reload();
                    else alert('Erreur lors de la suppression.');
                })
                .catch(error => alert('Erreur : ' + error));
            }
        }

        function arbitrageActif(id) {
            if (confirm('Effectuer un arbitrage sur cet actif ?')) {
                fetch('arbitrage_actif.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}&arbitrage_date=${new Date().toISOString().split('T')[0]}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.reload();
                })
                .catch(error => alert('Erreur : ' + error));
            }
        }

        function fiscaliteActif(id) {
            window.location.href = `fiscalite_actif.php?id=${id}`;
        }

        function planificationActif(id) {
            window.location.href = `planification_actif.php?id=${id}`;
        }

        function showAddForm() {
            document.getElementById('addForm').classList.add('active');
        }

        function hideAddForm() {
            document.getElementById('addForm').classList.remove('active');
        }

        <?php if (isset($_GET['id'])): ?>
            document.getElementById('detailsSection').classList.add('active');
        <?php endif; ?>
    </script>
</body>
</html>
<?php
ob_end_flush();
?>