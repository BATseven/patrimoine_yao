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
            padding: 15px;
            margin-bottom: 20px;
        }
        .table {
            background-color: white;
        }
        .form-section {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .form-section.active {
            display: block;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .overlay.active {
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
        <h4 class="text-center">Menu <?php echo ($userRole == 'admin') ? 'Admin' : 'Utilisateur'; ?></h4>
        <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
            <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <?php endif; ?>
        <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
            <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Gestion des Actifs - <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-muted">Dernière mise à jour: <?php echo date('H:i A \o\n l, F j, Y', time()); ?></p>
        </div>

        <div class="card">
            <h4 class="card-title">Liste des Actifs</h4>
            <button class="btn btn-primary mb-3" onclick="showAddForm()"><i data-lucide="plus"></i> Ajouter un actif</button>
            <?php if (count($actifs) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Valeur (€)</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actifs as $actif): ?>
                            <tr>
                                <td><?php echo $actif['id']; ?></td>
                                <td><?php echo htmlspecialchars($actif['name']); ?></td>
                                <td><?php echo number_format($actif['value'], 2); ?></td>
                                <td><?php echo htmlspecialchars($actif['type']); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="showDetails(<?php echo $actif['id']; ?>)"><i data-lucide="info"></i></button>
                                    <button class="btn btn-warning btn-sm" onclick="showModifyForm(<?php echo $actif['id']; ?>)"><i data-lucide="edit"></i></button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteActif(<?php echo $actif['id']; ?>)"><i data-lucide="trash"></i></button>
                                    <button class="btn btn-success btn-sm" onclick="arbitrageActif(<?php echo $actif['id']; ?>)"><i data-lucide="repeat"></i></button>
                                    <button class="btn btn-secondary btn-sm" onclick="fiscaliteActif(<?php echo $actif['id']; ?>)"><i data-lucide="file-text"></i></button>
                                    <button class="btn btn-primary btn-sm" onclick="planificationActif(<?php echo $actif['id']; ?>)"><i data-lucide="calendar"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucun actif trouvé.</p>
            <?php endif; ?>
        </div>

        <!-- Détails de l'actif -->
        <div id="detailsSection" class="form-section">
            <h4>Détails de l'actif</h4>
            <?php if ($actifDetail && is_array($actifDetail)): ?>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($actifDetail['name'] ?? 'N/A'); ?></p>
                <p><strong>Valeur :</strong> <?php echo number_format($actifDetail['value'] ?? 0, 2); ?> €</p>
                <p><strong>Type :</strong> <?php echo htmlspecialchars($actifDetail['type'] ?? 'N/A'); ?></p>
                <button class="btn btn-secondary" onclick="hideDetails()">Fermer</button>
            <?php else: ?>
                <p>Aucun détail disponible.</p>
            <?php endif; ?>
        </div>

        <!-- Formulaire d'ajout -->
        <div id="addForm" class="form-section">
            <h4>Ajouter un actif</h4>
            <form action="add_actif.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="Immobilier">Immobilier</option>
                        <option value="Bourse">Bourse</option>
                        <option value="Épargne">Épargne</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="value" class="form-label">Valeur (€)</label>
                    <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                </div>
                <div class="mb-3">
                    <label for="acquisition_date" class="form-label">Date d'acquisition</label>
                    <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="address" name="address">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
                <div class="mb-3">
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" class="form-control" id="photo" name="photo">
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
                <button type="button" class="btn btn-secondary" onclick="hideAddForm()">Annuler</button>
            </form>
        </div>

        <!-- Formulaire de modification -->
        <div id="modifyForm" class="form-section">
            <h4>Modifier un actif</h4>
            <form action="modify_actif.php" method="post">
                <input type="hidden" id="modifyId" name="id">
                <div class="mb-3">
                    <label for="modifyName" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="modifyName" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="modifyType" class="form-label">Type</label>
                    <select class="form-control" id="modifyType" name="type" required>
                        <option value="Immobilier">Immobilier</option>
                        <option value="Bourse">Bourse</option>
                        <option value="Épargne">Épargne</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="modifyValue" class="form-label">Valeur (€)</label>
                    <input type="number" step="0.01" class="form-control" id="modifyValue" name="value" required>
                </div>
                <div class="mb-3">
                    <label for="modifyAcquisitionDate" class="form-label">Date d'acquisition</label>
                    <input type="date" class="form-control" id="modifyAcquisitionDate" name="acquisition_date" required>
                </div>
                <div class="mb-3">
                    <label for="modifyAddress" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="modifyAddress" name="address">
                </div>
                <div class="mb-3">
                    <label for="modifyDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="modifyDescription" name="description"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Modifier</button>
                <button type="button" class="btn btn-secondary" onclick="hideModifyForm()">Annuler</button>
            </form>
        </div>

        <div id="overlay" class="overlay"></div>

        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            lucide.createIcons();

            function showDetails(id) {
                window.location.href = `actifs.php?id=${id}`;
            }

            function hideDetails() {
                document.getElementById('detailsSection').classList.remove('active');
            }

            function showModifyForm(id) {
                fetch(`get_actif.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modifyId').value = data.id;
                        document.getElementById('modifyName').value = data.name;
                        document.getElementById('modifyType').value = data.type;
                        document.getElementById('modifyValue').value = data.value;
                        document.getElementById('modifyAcquisitionDate').value = data.acquisition_date;
                        document.getElementById('modifyAddress').value = data.address || '';
                        document.getElementById('modifyDescription').value = data.description || '';
                        document.getElementById('modifyForm').classList.add('active');
                        document.getElementById('overlay').classList.add('active');
                    })
                    .catch(error => alert('Erreur : ' + error));
            }

            function hideModifyForm() {
                document.getElementById('modifyForm').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
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
                document.getElementById('overlay').classList.add('active');
            }

            function hideAddForm() {
                document.getElementById('addForm').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
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