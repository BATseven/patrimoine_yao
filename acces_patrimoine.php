<?php
// Démarrer le buffer de sortie
ob_start();
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'utilisateur est administrateur
if ($_SESSION['role'] !== 'administrateur') {
    header("Location: /patrimoine/dashboard.php");
    exit();
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['full_name'];
$dateTime = date('H:i A \o\n l, F j, Y');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les biens et leurs accès
    $stmt = $pdo->query("SELECT a.id AS asset_id, a.name AS asset_name, a.type, ua.user_id, u.full_name, ua.access_level, ua.start_date, ua.end_date 
                         FROM assets a 
                         LEFT JOIN user_assets ua ON a.id = ua.asset_id 
                         LEFT JOIN users u ON ua.user_id = u.id");
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les utilisateurs
    $usersStmt = $pdo->query("SELECT id, full_name FROM users");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Accès Patrimoine - Patrimoine Plus</title>
    <meta name="description" content="Gestion des accès aux biens et éléments du patrimoine.">
    <meta name="keywords" content="accès patrimoine, gestion droits, biens">

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
        .table-responsive {
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        .details-section {
            display: none;
            margin-top: 20px;
        }
        .details-section.active {
            display: block;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4 class="text-center">Menu Admin</h4>
        <a href="/patrimoine/dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <a href="/patrimoine/gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
        <a href="/patrimoine/validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <a href="/patrimoine/acces_patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <a href="/patrimoine/statistiques_globales.php"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
        <a href="/patrimoine/parametrage.php"><i data-lucide="settings"></i> Paramétrage</a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <div>
                <h2 class="text-blue">Accès Patrimoine - <?php echo htmlspecialchars($userName); ?></h2>
                <p class="text-muted">Dernière mise à jour : <?php echo $dateTime; ?></p>
            </div>
        </div>

        <!-- Tableau des biens et accès -->
        <div class="card p-4 mb-4">
            <h4>Tableau des biens et accès</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom du Bien</th>
                            <th>Type</th>
                            <th>Utilisateurs avec accès</th>
                            <th>Type d'accès</th>
                            <th>Période d'accès</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentAsset = null;
                        foreach ($assets as $asset) {
                            if ($currentAsset !== $asset['asset_id']) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($asset['asset_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($asset['type']) . "</td>";
                                echo "<td>";
                                $usersWithAccess = array_filter($assets, fn($a) => $a['asset_id'] == $asset['asset_id']);
                                $userList = array_map(fn($u) => htmlspecialchars($u['full_name']), $usersWithAccess);
                                echo implode(', ', array_filter($userList)) ?: 'Aucun';
                                echo "</td>";
                                echo "<td>";
                                $accessLevels = array_unique(array_map(fn($u) => htmlspecialchars($u['access_level']), $usersWithAccess));
                                echo implode(', ', $accessLevels) ?: 'Aucun';
                                echo "</td>";
                                echo "<td>";
                                $periods = array_filter(array_map(fn($u) => $u['start_date'] && $u['end_date'] ? htmlspecialchars($u['start_date']) . ' - ' . htmlspecialchars($u['end_date']) : '', $usersWithAccess));
                                echo implode('<br>', $periods) ?: 'Illimité';
                                echo "</td>";
                                echo "<td><button class='btn btn-primary btn-action' onclick='showAssetDetails(\"" . $asset['asset_id'] . "\")'>Détails</button></td>";
                                echo "</tr>";
                                $currentAsset = $asset['asset_id'];
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attribution et gestion des droits -->
        <div class="card p-4 mb-4">
            <h4>Attribution et gestion des droits</h4>
            <div class="mb-3">
                <select id="assignUser" class="form-control w-25">
                    <option value="">Sélectionner un utilisateur</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="assignAccessLevel" class="form-control w-25 mx-2">
                    <option value="">Type d'accès</option>
                    <option value="lecture">🔍 Lecture seule</option>
                    <option value="modification">✏️ Modification</option>
                    <option value="telechargement">⬇️ Téléchargement</option>
                    <option value="suppression">❌ Suppression</option>
                </select>
                <input type="date" id="startDate" class="form-control w-25 mx-2" placeholder="Date de début">
                <input type="date" id="endDate" class="form-control w-25" placeholder="Date de fin">
                <button class="btn btn-success" onclick="assignAccess()">Attribuer</button>
                <button class="btn btn-danger" onclick="revokeAccess()">Retirer</button>
            </div>
            <div>
                <select id="assignDepartment" class="form-control w-25">
                    <option value="">Sélectionner un département</option>
                    <option value="finance">Finance</option>
                    <option value="legal">Légal</option>
                </select>
                <button class="btn btn-success" onclick="grantDepartmentAccess()">Attribuer au département</button>
            </div>
        </div>

        <!-- Vue par utilisateur -->
        <div class="card p-4 mb-4">
            <h4>Vue par utilisateur</h4>
            <select id="selectUser" class="form-control w-25" onchange="showUserAccess(this.value)">
                <option value="">Sélectionner un utilisateur</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <div id="userAccessDetails" class="details-section">
                <ul id="userAssetsList"></ul>
                <button class="btn btn-success" onclick="addUserAsset()">Ajouter un bien</button>
                <button class="btn btn-danger" onclick="removeUserAsset()">Retirer un bien</button>
                <select id="cloneFromUser" class="form-control w-25 mt-2">
                    <option value="">Cloner droits d’un autre utilisateur</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-secondary" onclick="cloneAccessRights()">Cloner</button>
            </div>
        </div>

        <!-- Vue par bien/dossier -->
        <div class="card p-4 mb-4">
            <h4>Vue par bien/dossier</h4>
            <select id="selectAsset" class="form-control w-25" onchange="showAssetAccess(this.value)">
                <option value="">Sélectionner un bien</option>
                <?php
                $uniqueAssets = array_unique(array_column($assets, 'asset_id'));
                foreach ($uniqueAssets as $assetId) {
                    $asset = array_filter($assets, fn($a) => $a['asset_id'] == $assetId);
                    $asset = reset($asset);
                    echo "<option value='" . $asset['asset_id'] . "'>" . htmlspecialchars($asset['asset_name']) . "</option>";
                }
                ?>
            </select>
            <div id="assetAccessDetails" class="details-section">
                <ul id="assetUsersList"></ul>
                <select id="addUserToAsset" class="form-control w-25">
                    <option value="">Ajouter un utilisateur</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-success" onclick="addAssetUser()">Ajouter</button>
                <button class="btn btn-danger" onclick="removeAssetUser()">Retirer</button>
            </div>
        </div>

        <!-- Historique et suivi -->
        <div class="card p-4">
            <h4>Historique et suivi</h4>
            <ul id="activityLog">
                <li>Utilisateur Jean Durand a consulté un bien à 10:00 AM</li>
                <li>Utilisateur Marie Dupont a modifié un document à 09:50 AM</li>
            </ul>
        </div>

    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="action.php"></script>
    <script>
        lucide.createIcons();

        function showAssetDetails(assetId) {
            alert('Détails du bien ID: ' + assetId);
        }

        function assignAccess() {
            const userId = document.getElementById('assignUser').value;
            const accessLevel = document.getElementById('assignAccessLevel').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (userId && accessLevel) handleAssignAccess(userId, accessLevel, startDate, endDate);
        }

        function revokeAccess() {
            const userId = prompt('ID de l\'utilisateur :');
            const assetId = prompt('ID du bien :');
            if (userId && assetId) handleRevokeAccess(userId, assetId);
        }

        function showUserAccess(userId) {
            const details = document.getElementById('userAccessDetails');
            if (userId) {
                document.getElementById('userAssetsList').innerHTML = '<li>Bien #1 (Lecture)</li><li>Bien #2 (Modification)</li>';
                details.classList.add('active');
            } else {
                details.classList.remove('active');
            }
        }

        function addUserAsset() {
            const userId = document.getElementById('selectUser').value;
            const assetId = prompt('ID du bien à ajouter :');
            if (userId && assetId) handleAddUserAsset(userId, assetId);
        }

        function removeUserAsset() {
            const userId = document.getElementById('selectUser').value;
            const assetId = prompt('ID du bien à retirer :');
            if (userId && assetId) handleRemoveUserAsset(userId, assetId);
        }

        function cloneAccessRights() {
            const userId = document.getElementById('selectUser').value;
            const fromUserId = document.getElementById('cloneFromUser').value;
            if (userId && fromUserId) handleCloneAccessRights(userId, fromUserId);
        }

        function showAssetAccess(assetId) {
            const details = document.getElementById('assetAccessDetails');
            if (assetId) {
                document.getElementById('assetUsersList').innerHTML = '<li>Jean Durand (Lecture)</li><li>Marie Dupont (Modification)</li>';
                details.classList.add('active');
            } else {
                details.classList.remove('active');
            }
        }

        function addAssetUser() {
            const assetId = document.getElementById('selectAsset').value;
            const userId = document.getElementById('addUserToAsset').value;
            const accessLevel = prompt('Type d\'accès :');
            if (assetId && userId && accessLevel) handleAddAssetUser(assetId, userId, accessLevel);
        }

        function removeAssetUser() {
            const assetId = document.getElementById('selectAsset').value;
            const userId = prompt('ID de l\'utilisateur à retirer :');
            if (assetId && userId) handleRemoveAssetUser(assetId, userId);
        }
    </script>
</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>