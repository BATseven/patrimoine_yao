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
                <p class="text-muted">Dernière mise à jour : <?php echo $dateTime; ?></p>
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
                    <tr>
                        <td>Durand</td>
                        <td>Jean</td>
                        <td>jean.durand@example.com</td>
                        <td>Administrateur</td>
                        <td>Actif</td>
                        <td>2025-08-10 14:30</td>
                        <td>
                            <button class="btn btn-primary btn-action" onclick="showUserDetails(this)">Détails</button>
                            <button class="btn btn-warning btn-action" onclick="modifyUser(this)">Modifier</button>
                            <button class="btn btn-danger btn-action" onclick="deleteUser(this)">Supprimer</button>
                        </td>
                    </tr>
                    <!-- Ajouter d'autres lignes dynamiquement avec PHP ou JS -->
                </tbody>
            </table>
        </div>

        <!-- Détails d’un utilisateur -->
        <div id="userDetails" class="user-details card p-4">
            <h4>Détails de l'utilisateur</h4>
            <p><strong>Nom :</strong> Jean Durand</p>
            <p><strong>Email :</strong> jean.durand@example.com</p>
            <p><strong>Rôle :</strong> Administrateur</p>
            <p><strong>État :</strong> Actif</p>
            <h5>Historique de connexion</h5>
            <ul>
                <li>2025-08-10 14:30</li>
                <li>2025-08-09 09:15</li>
            </ul>
            <h5>Actions récentes</h5>
            <ul>
                <li>Ajout de document : 2025-08-10</li>
                <li>Modification patrimoine : 2025-08-09</li>
            </ul>
            <h5>Dossiers / Biens gérés</h5>
            <ul>
                <li>Bien #123</li>
                <li>Dossier #456</li>
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

    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="action.php"></script>
    <script>
        lucide.createIcons();

        function showUserDetails(btn) {
            const row = btn.closest('tr');
            const details = document.getElementById('userDetails');
            details.querySelector('strong:contains("Nom")').nextSibling.textContent = row.cells[0].textContent;
            details.querySelector('strong:contains("Email")').nextSibling.textContent = row.cells[2].textContent;
            details.querySelector('strong:contains("Rôle")').nextSibling.textContent = row.cells[3].textContent;
            details.querySelector('strong:contains("État")').nextSibling.textContent = row.cells[4].textContent;
            details.classList.add('active');
        }

        function hideUserDetails() {
            document.getElementById('userDetails').classList.remove('active');
        }

        // Placeholders pour les appels aux fonctions d'action.php
        function createUser() { handleCreateUser(); }
        function modifyUser() { handleModifyUser(); }
        function resetPassword() { handleResetPassword(); }
        function toggleAccount() { handleToggleAccount(); }
        function deleteUser() { handleDeleteUser(); }
        function changeRole() { handleChangeRole(); }
    </script>
</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>