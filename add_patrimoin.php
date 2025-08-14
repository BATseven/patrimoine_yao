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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Ajouter un Patrimoine - Patrimoine Plus</title>
    <meta name="description" content="Ajoutez un nouveau patrimoine avec Patrimoine Plus.">
    <meta name="keywords" content="ajout patrimoine, gestion, immobilier, financier">

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
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">

    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .content { padding: 20px; }
        .form-group { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="content">
        <h2 class="text-blue">Ajouter un Patrimoine - <?php echo htmlspecialchars($userName); ?></h2>

        <div class="card p-4">
            <h4 class="card-title">Formulaire d'Ajout</h4>
            <form action="insert_patrimoin.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nom du bien</label>
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
                    <label for="value">Valeur estimée (€)</label>
                    <input type="number" name="value" id="value" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="date_acquisition">Date d’acquisition</label>
                    <input type="date" name="date_acquisition" id="date_acquisition" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input type="text" name="address" id="address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" name="latitude" id="latitude" class="form-control" step="0.00000001" required>
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" name="longitude" id="longitude" class="form-control" step="0.00000001" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
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
                    <input type="date" name="contract_expiry_date" id="contract_expiry_date" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> Ajouter</button>
                <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php
ob_end_flush();
?>