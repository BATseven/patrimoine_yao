<?php
// Démarrer la session et inclure la configuration
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['full_name'];

// Définir le fuseau horaire à GMT
date_default_timezone_set('GMT');
$dateTime = date('H:i A \o\n l, F j, Y', strtotime('09:11 AM GMT')); // 09:11 AM GMT, Monday, August 18, 2025

// Connexion à la base de données
global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    <title>Paramétrage - Patrimoine Plus</title>
    <meta name="description" content="Paramétrage des configurations générales, informations de l’entreprise, gestion des utilisateurs et rôles, et paramètres de sécurité.">
    <meta name="keywords" content="paramétrage, configuration, entreprise, utilisateurs, rôles, sécurité">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
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
            transition: all 0.3s ease;
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
            transition: transform 0.3s ease;
        }
        .sidebar a {
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 10px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #152e6f;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #1e3a8a;
        }
        .form-group label {
            font-weight: 500;
            color: #1e3a8a;
        }
        .btn-primary {
            background-color: #1e3a8a;
            border: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #152e6f;
            transform: scale(1.05);
        }
        .logo-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
            cursor: pointer; /* Indique que l'image est cliquable */
        }
        .modal-content img {
            max-width: 100%;
            max-height: 80vh;
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
        <a href="actifs.php"><i data-lucide="briefcase"></i> Gestion des Actifs</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="gestion_users.php"><i data-lucide="users"></i> Gestion Utilisateurs</a>
            <a href="validation_doc.php"><i data-lucide="file-check"></i> Validation Documents</a>
        <?php endif; ?>
        <a href="patrimoine.php"><i data-lucide="database"></i> Accès Patrimoines</a>
        <?php if ($userRole == 'admin'): ?>
            <a href="reglages.php"><i data-lucide="settings"></i> Paramétrage</a>
            <a href="#"><i data-lucide="bar-chart-2"></i> Statistiques Globales</a>
        <?php endif; ?>
        <a href="logout.php"><i data-lucide="log-out"></i> Déconnexion</a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <h2 class="text-blue">Paramétrage - <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-muted">Dernière mise à jour: <?php echo $dateTime; ?></p>
        </div>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($userRole == 'admin'): ?>
            <!-- Paramétrage Général -->
            <div class="card mb-4">
                <h4 class="card-title">Paramétrage Général</h4>
                <p class="text-muted">Configurez les paramètres généraux de l'application.</p>
                <div class="chart-placeholder">Paramètres généraux à venir</div>
            </div>

            <!-- Informations de l’entreprise -->
            <div class="card mb-4">
                <h4 class="card-title">Informations de l’entreprise</h4>
                <form method="post" action="enregistrer_r.php" enctype="multipart/form-data">
                    <input type="hidden" name="company_settings" value="1">
                    <div class="form-group mb-3">
                        <label for="company_name">Nom de l’entreprise</label>
                        <?php
                        $companyData = $pdo->query("SELECT name, address, phone, currency, logo_path FROM company_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                        $companyName = $companyData['name'] ?? '';
                        $companyAddress = $companyData['address'] ?? '';
                        $companyPhone = $companyData['phone'] ?? '';
                        $currency = $companyData['currency'] ?? 'EUR';
                        $logoPath = $companyData['logo_path'] ?? '';
                        ?>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($companyName); ?>" placeholder="Nom de l’entreprise">
                    </div>
                    <div class="form-group mb-3">
                        <label for="company_logo">Logo de l’entreprise</label>
                        <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                        <div id="logo_preview">
                            <?php if ($logoPath): ?>
                                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo actuel" class="logo-preview">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="company_address">Coordonnées</label>
                        <textarea class="form-control" id="company_address" name="company_address" placeholder="Adresse complète"><?php echo htmlspecialchars($companyAddress); ?></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="company_phone">Téléphone</label>
                        <input type="tel" class="form-control" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars($companyPhone); ?>" placeholder="Numéro de téléphone">
                    </div>
                    <div class="form-group mb-3">
                        <label for="currency">Devise utilisée</label>
                        <select class="form-control" id="currency" name="currency">
                            <option value="EUR" <?php echo $currency === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                            <option value="USD" <?php echo $currency === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="GBP" <?php echo $currency === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>

            <!-- Détails de l’entreprise -->
            <div class="card mb-4">
                <h4 class="card-title">Détails de l’entreprise</h4>
                <p class="text-muted">Vue d’ensemble des informations enregistrées :</p>
                <?php
                $companyData = $pdo->query("SELECT name, address, phone, currency, logo_path FROM company_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($companyData): ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Nom :</strong> <?php echo htmlspecialchars($companyData['name'] ?? 'Non défini'); ?></li>
                        <li class="list-group-item"><strong>Logo :</strong> <?php if ($companyData['logo_path']): ?><img src="<?php echo htmlspecialchars($companyData['logo_path']); ?>" alt="Logo" class="logo-preview"><?php else: ?>Non défini<?php endif; ?></li>
                        <li class="list-group-item"><strong>Adresse :</strong> <?php echo htmlspecialchars($companyData['address'] ?? 'Non défini'); ?></li>
                        <li class="list-group-item"><strong>Téléphone :</strong> <?php echo htmlspecialchars($companyData['phone'] ?? 'Non défini'); ?></li>
                        <li class="list-group-item"><strong>Devise :</strong> <?php echo htmlspecialchars($companyData['currency'] ?? 'Non défini'); ?></li>
                    </ul>
                <?php else: ?>
                    <p class="text-warning">Aucune information enregistrée pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Gestion des utilisateurs et rôles -->
            <div class="card mb-4">
                <h4 class="card-title">Gestion des utilisateurs et rôles</h4>
                <form method="post" action="mettre_a_jour_r.php">
                    <input type="hidden" name="user_role_settings" value="1">
                    <div class="form-group mb-3">
                        <label for="user_id">Sélectionner un utilisateur</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">Choisir un utilisateur</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, full_name FROM users");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['full_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="role">Rôle</label>
                        <select class="form-control" id="role" name="role">
                            <option value="admin">Administrateur</option>
                            <option value="user">Utilisateur</option>
                            <option value="guest">Invité</option>
                            <?php
                            $stmt = $pdo->query("SELECT role_name FROM roles");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$row['role_name']}'>" . htmlspecialchars($row['role_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
                <hr>
                <h5>Créer un profil personnalisé</h5>
                <form method="post" action="creer_r.php">
                    <div class="form-group mb-3">
                        <label for="new_role">Nouveau rôle</label>
                        <input type="text" class="form-control" id="new_role" name="new_role" placeholder="Nom du rôle">
                    </div>
                    <div class="form-group mb-3">
                        <label for="permissions">Droits d’accès</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm_read" name="permissions[]" value="read">
                            <label class="form-check-label" for="perm_read">Lecture</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm_write" name="permissions[]" value="write">
                            <label class="form-check-label" for="perm_write">Écriture</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm_admin" name="permissions[]" value="admin">
                            <label class="form-check-label" for="perm_admin">Administration</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </form>
            </div>

            <!-- Paramètres de sécurité -->
            <div class="card mb-4">
                <h4 class="card-title">Paramètres de sécurité</h4>
                <form method="post" action="enregistrer_r.php">
                    <input type="hidden" name="security_settings" value="1">
                    <div class="form-group mb-3">
                        <label for="min_password_length">Longueur minimale du mot de passe</label>
                        <input type="number" class="form-control" id="min_password_length" name="min_password_length" value="8" min="6" max="20">
                    </div>
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa">
                            <label class="form-check-label" for="enable_2fa">Activer l’authentification à deux facteurs</label>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="privacy_rules">Règles de confidentialité</label>
                        <textarea class="form-control" id="privacy_rules" name="privacy_rules" placeholder="Définir les règles de confidentialité"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Accès refusé. Cette page est réservée aux administrateurs.
            </div>
        <?php endif; ?>

        <!-- Modale pour l'aperçu agrandi -->
        <div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoModalLabel">Aperçu du Logo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="" alt="Aperçu agrandi du logo" class="img-fluid">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary">Retour au Tableau de Bord</a>
        </div>
    </div>

    <!-- Inclusion du script pour l'aperçu -->
    <script src="apercu.php"></script>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
<?php
// Vider le buffer de sortie
ob_end_flush();
?>