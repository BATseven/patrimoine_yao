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
date_default_timezone_set('GMT');
$dateTime = date('H:i A \o\n l, F j, Y', time()); // 12:00 PM GMT, Thursday, August 14, 2025

// Utiliser la connexion PDO globale
global $pdo;

try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    $stmt = $pdo->query("SELECT id, full_name, email, role, is_verified AS status, is_active FROM users");
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
        .details-section {
            display: none;
        }
        .details-section.active {
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
        <h4 class="text-center">Menu Admin</h4>
        <a href="dashboard.php"><i data-lucide="home"></i> Accueil</a>
        <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
        <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
        <a href="#"><i data-lucide="settings"></i> Paramétrage</a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Gestion des Utilisateurs - <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-muted">Dernière mise à jour: <?php echo $dateTime; ?></p>
            <button class="btn btn-primary mb-3" onclick="createUser()"><i data-lucide="plus"></i> Créer un Utilisateur</button>
        </div>

        <div class="card p-4 mb-4">
            <h4 class="card-title">Liste des Utilisateurs</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['status'] ? 'Vérifié' : 'Non vérifié'; ?></td>
                            <td><?php echo $user['is_active'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="showUserDetails(this, <?php echo $user['id']; ?>)"><i data-lucide="eye"></i> Voir</button>
                                <button class="btn btn-warning btn-sm" onclick="modifyUser(<?php echo $user['id']; ?>)"><i data-lucide="edit"></i> Modifier</button>
                                <button class="btn btn-danger btn-sm" onclick="resetPassword(<?php echo $user['id']; ?>)"><i data-lucide="key"></i> Réinitialiser Mot de Passe</button>
                                <button class="btn btn-secondary btn-sm" onclick="toggleAccount(<?php echo $user['id']; ?>)"><i data-lucide="toggle-<?php echo $user['is_active'] ? 'off' : 'on'; ?>"></i> <?php echo $user['is_active'] ? 'Désactiver' : 'Activer'; ?></button>
                                <button class="btn btn-primary btn-sm" onclick="changeRole(<?php echo $user['id']; ?>)"><i data-lucide="user-check"></i> Changer Rôle</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="userDetails" class="details-section card p-4 mb-4">
            <h4 class="card-title">Détails de l'Utilisateur</h4>
            <p><strong>Nom :</strong> <span id="detailNom"></span></p>
            <p><strong>Email :</strong> <span id="detailEmail"></span></p>
            <p><strong>Rôle :</strong> <span id="detailRole"></span></p>
            <p><strong>Statut :</strong> <span id="detailStatus"></span></p>
            <button class="btn btn-secondary mt-2" onclick="hideUserDetails()">Fermer</button>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
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

        function modifyUser(userId) {
            // Placeholder pour modification manuelle (à implémenter si nécessaire)
            alert('Fonction de modification non implémentée. Utilisez les autres actions pour gérer l\'utilisateur.');
        }

        function createUser() {
            const fullName = prompt("Entrez le nom complet :");
            const email = prompt("Entrez l'email :");
            const password = prompt("Entrez le mot de passe :");
            const role = prompt("Rôle (admin ou user) :");
            if (fullName && email && password && role) {
                fetch('create_user.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `full_name=${encodeURIComponent(fullName)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&role=${encodeURIComponent(role)}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.reload();
                })
                .catch(error => alert('Erreur : ' + error));
            }
        }

        function resetPassword(userId) {
            if (confirm('Confirmer la réinitialisation du mot de passe ?')) {
                window.location.href = `forgot_password.php?user_id=${userId}`;
            }
        }

        function toggleAccount(userId) {
            if (confirm('Confirmer l\'activation/désactivation de ce compte ?')) {
                fetch('toggle_account.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `user_id=${userId}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.reload();
                })
                .catch(error => alert('Erreur : ' + error));
            }
        }

        function changeRole(userId) {
            const newRole = prompt("Nouveau rôle (admin ou user) :");
            if (newRole && (newRole === 'admin' || newRole === 'user')) {
                fetch('change_role.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `user_id=${userId}&new_role=${encodeURIComponent(newRole)}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.reload();
                })
                .catch(error => alert('Erreur : ' + error));
            } else {
                alert('Rôle invalide. Utilisez "admin" ou "user".');
            }
        }
    </script>
</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>