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
$dateTime = date('H:i A \o\n l, F j, Y', time());

// Utiliser la connexion PDO globale
global $pdo;

try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    $stmt = $pdo->query("SELECT id, full_name, email, role, is_verified AS status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de requête : " . $e->getMessage() . " - Vérifiez config.php et la base de données.");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Gestion des Utilisateurs - Patrimoine Plus</title>
    <meta name="description" content="Gestion des utilisateurs et de leurs droits d'accès.">
    <meta name="keywords" content="gestion utilisateurs, droits d'accès, patrimoine">

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
        .user-details {
            display: none;
            margin-top: 20px;
        }
        .user-details.active {
            display: block;
        }
        .modify-form {
            display: none;
            margin-top: 10px;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .modify-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4 class="text-center">Menu Admin</h4>
        <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
        <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <a href="#"><i data-lucide="database"></i> Accès Patrimoines</a>
        <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
        <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <div>
                <h2 class="text-blue">Gestion des Utilisateurs - <?php echo htmlspecialchars($userName); ?></h2>
                <p class="text-muted">Dernière mise à jour: <?php echo $dateTime; ?></p>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="mb-4">
            <input type="text" id="searchUser" class="form-control w-25" placeholder="Rechercher par nom ou email...">
            <select id="filterRole" class="form-control w-25 mx-2">
                <option value="">Tous les rôles</option>
                <option value="administrateur">Administrateur</option>
                <option value="gestionnaire">Gestionnaire</option>
                <option value="consultant">Consultant</option>
                <option value="client">Client</option>
            </select>
            <select id="filterStatus" class="form-control w-25">
                <option value="">Tous les états</option>
                <option value="actif">Actif</option>
                <option value="suspendu">Suspendu</option>
                <option value="en attente">En attente</option>
            </select>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>État</th>
                        <th>Dernière Connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $nameParts = explode(' ', $user['full_name']);
                        $nom = $nameParts[0] ?? '';
                        $prenom = $nameParts[1] ?? '';
                        $status = $user['status'] ? 'Actif' : 'En attente';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nom); ?></td>
                        <td><?php echo htmlspecialchars($prenom); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'); ?></td>
                        <td><?php echo htmlspecialchars($status); ?></td>
                        <td>N/A</td>
                        <td>
                            <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-action">Supprimer</button>
                            </form>
                            <button class="btn btn-warning btn-action" onclick="showModifyForm(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>', '<?php echo addslashes($user['email']); ?>', '<?php echo $user['role']; ?>', <?php echo $user['status'] ? 1 : 0; ?>)">Modifier</button>
                            <button class="btn btn-primary btn-action" onclick="showUserDetails(this, <?php echo $user['id']; ?>)">Détails</button>
                        </td>
                    </tr>
                    <tr class="modify-form" id="modify-form-<?php echo $user['id']; ?>">
                        <td colspan="7">
                            <form action="modifier.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?');" class="row g-3">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <div class="col-md-6 form-group">
                                    <label for="full_name_<?php echo $user['id']; ?>">Nom complet :</label>
                                    <input type="text" name="full_name" id="full_name_<?php echo $user['id']; ?>" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-control" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email_<?php echo $user['id']; ?>">Email :</label>
                                    <input type="email" name="email" id="email_<?php echo $user['id']; ?>" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="role_<?php echo $user['id']; ?>">Rôle :</label>
                                    <select name="role" id="role_<?php echo $user['id']; ?>" class="form-control" required>
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="is_verified_<?php echo $user['id']; ?>">Vérifié :</label>
                                    <select name="is_verified" id="is_verified_<?php echo $user['id']; ?>" class="form-control" required>
                                        <option value="0" <?php echo !$user['status'] ? 'selected' : ''; ?>>Non</option>
                                        <option value="1" <?php echo $user['status'] ? 'selected' : ''; ?>>Oui</option>
                                    </select>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-action">Sauvegarder</button>
                                    <button type="button" class="btn btn-secondary btn-action" onclick="hideModifyForm(<?php echo $user['id']; ?>)">Annuler</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Détails d’un utilisateur -->
        <div id="userDetails" class="user-details card p-4">
            <h4>Détails de l'utilisateur</h4>
            <p><strong>Nom :</strong> <span id="detailNom"></span></p>
            <p><strong>Email :</strong> <span id="detailEmail"></span></p>
            <p><strong>Rôle :</strong> <span id="detailRole"></span></p>
            <p><strong>État :</strong> <span id="detailStatus"></span></p>
            <h5>Historique de connexion</h5>
            <ul id="connexionHistory">
                <li>N/A</li>
            </ul>
            <h5>Actions récentes</h5>
            <ul id="recentActions">
                <li>N/A</li>
            </ul>
            <h5>Dossiers / Biens gérés</h5>
            <ul id="managedAssets">
                <li>N/A</li>
            </ul>
            <button class="btn btn-primary mt-2" onclick="hideUserDetails()">Fermer</button>
        </div>

        <!-- Actions sur les comptes -->
        <div class="card p-4 mt-4">
            <h4>Actions sur les comptes</h4>
            <button class="btn btn-success" onclick="createUser()">Créer un utilisateur</button>
            <button class="btn btn-warning" onclick="resetPassword()">Réinitialiser mot de passe</button>
            <button class="btn btn-info" onclick="toggleAccount()">Activer/Désactiver</button>
            <button class="btn btn-secondary" onclick="changeRole()">Changer rôle</button>
        </div>

        <!-- Historique & suivi -->
        <div class="card p-4 mt-4">
            <h4>Historique & Suivi</h4>
            <ul>
                <li>Jean Durand a modifié un patrimoine - 2025-08-10 14:30</li>
                <li>Tentative de connexion échouée - 2025-08-09 08:00</li>
            </ul>
            <p>Statistiques : 15 documents ajoutés, 5 biens gérés</p>
        </div>

        <!-- Zone de statut (masquée) -->
        <div id="global_status" class="mt-3" style="display:none;"></div>
    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="action.php"></script>
    <script>
        lucide.createIcons();

        function showUserDetails(btn, userId) {
            const row = btn.closest('tr');
            document.getElementById('detailNom').textContent = row.cells[0].textContent;
            document.getElementById('detailEmail').textContent = row.cells[2].textContent;
            document.getElementById('detailRole').textContent = row.cells[3].textContent;
            document.getElementById('detailStatus').textContent = row.cells[4].textContent;
            document.getElementById('userDetails').classList.add('active');
        }

        function hideUserDetails() {
            document.getElementById('userDetails').classList.remove('active');
        }

        function showModifyForm(userId, fullName, email, role, isVerified) {
            const form = document.getElementById('modify-form-' + userId);
            document.getElementById('full_name_' + userId).value = fullName;
            document.getElementById('email_' + userId).value = email;
            document.getElementById('role_' + userId).value = role;
            document.getElementById('is_verified_' + userId).value = isVerified;
            form.classList.add('active');
        }

        function hideModifyForm(userId) {
            const form = document.getElementById('modify-form-' + userId);
            form.classList.remove('active');
        }

        function modifyUser(userId) {
            handleModifyUser(userId);
        }

        function createUser() {
            const fullName = prompt("Entrez le nom complet :");
            const email = prompt("Entrez l'email :");
            const password = prompt("Entrez le mot de passe :");
            const role = prompt("Rôle (admin ou user) :");
            if (fullName && email && password && role) {
                handleCreateUser(fullName, email, password, role);
            }
        }

        function resetPassword() {
            const userId = prompt("Entrez l'ID de l'utilisateur :");
            if (userId) handleResetPassword(userId);
        }

        function toggleAccount() {
            const userId = prompt("Entrez l'ID de l'utilisateur :");
            if (userId) handleToggleAccount(userId);
        }

        function changeRole() {
            const userId = prompt("Entrez l'ID de l'utilisateur :");
            const newRole = prompt("Nouveau rôle (admin ou user) :");
            if (userId && newRole) handleChangeRole(userId, newRole);
        }
    </script>
</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>