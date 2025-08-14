<?php
ob_start();
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

global $pdo;
try {
    if (!$pdo) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $id = $_GET['id'];
    $actif = $pdo->prepare("SELECT * FROM actifs WHERE id = ? AND user_id = ?");
    $actif->execute([$id, $_SESSION['user_id']]);
    $actifDetail = $actif->fetch(PDO::FETCH_ASSOC);

    if (!$actifDetail) {
        header("Location: actifs.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Fiscalité - <?php echo htmlspecialchars($actifDetail['name']); ?> - Patrimoine Plus</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.css">
</head>
<body>
    <div class="content" style="margin-left: 250px; padding: 20px;">
        <h2 class="text-blue">Gestion de la Fiscalité - <?php echo htmlspecialchars($actifDetail['name']); ?></h2>
        <form action="fiscalite_actif.php" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label for="fiscal_status">Statut fiscal</label>
                <input type="text" name="fiscal_status" id="fiscal_status" class="form-control" value="<?php echo htmlspecialchars($actifDetail['fiscal_status'] ?: ''); ?>">
            </div>
            <div class="form-group">
                <label for="tax_rate">Taux d'imposition (%)</label>
                <input type="number" name="tax_rate" id="tax_rate" class="form-control" step="0.01" value="<?php echo $actifDetail['tax_rate'] ?: '0'; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2"><i data-lucide="save"></i> Enregistrer</button>
            <a href="actifs.php" class="btn btn-secondary mt-2">Retour</a>
        </form>
    </div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE actifs SET fiscal_status = ?, tax_rate = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['fiscal_status'], $_POST['tax_rate'], $id, $_SESSION['user_id']]);
        header("Location: actifs.php?id=" . $id);
        exit();
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
ob_end_flush();
?>