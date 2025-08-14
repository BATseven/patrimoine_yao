<?php
session_start();

// Connexion à la base de données (ajustez selon votre configuration)
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "patrimoine_db";

try {
    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        throw new Exception("Échec de la connexion : " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

// Vérification si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES)) {
    $errors = [];
    $uploadedFiles = [];

    // Tableau des champs de fichier attendus
    $fileFields = [
        'piece_identite',
        'justificatif_domicile',
        'contrat_location',
        'bulletin_salaire',
        'document_notarie',
        'releve_portefeuille',
        'mandat_procuration'
    ];

    // Validation et traitement de chaque fichier
    foreach ($fileFields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $fileName = $_FILES[$field]['name'];
            $fileSize = $_FILES[$field]['size'];
            $fileTmp = $_FILES[$field]['tmp_name'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['pdf', 'jpg', 'png'];

            // Vérification du type de fichier
            if (!in_array($fileType, $allowedTypes)) {
                $errors[$field] = "Type de fichier non autorisé. Utilisez uniquement PDF, JPG ou PNG.";
                continue;
            }

            // Vérification de la taille (limite à 5 Mo)
            if ($fileSize > 5000000) {
                $errors[$field] = "La taille du fichier dépasse 5 Mo.";
                continue;
            }

            // Génération d'un nom unique pour éviter les conflits
            $uniqueFileName = uniqid() . '.' . $fileType;
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadPath = $uploadDir . $uniqueFileName;

            // Déplacement du fichier
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $uploadedFiles[$field] = $uniqueFileName;
            } else {
                $errors[$field] = "Erreur lors du téléchargement du fichier.";
            }
        }
    }

    // Si aucune erreur, enregistrement dans la base de données et redirection
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO document_validation (user_id, piece_identite, justificatif_domicile, contrat_location, bulletin_salaire, document_notarie, releve_portefeuille, mandat_procuration, submission_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssssss", $_SESSION['user_id'], $uploadedFiles['piece_identite'] ?? NULL, $uploadedFiles['justificatif_domicile'] ?? NULL, $uploadedFiles['contrat_location'] ?? NULL, $uploadedFiles['bulletin_salaire'] ?? NULL, $uploadedFiles['document_notarie'] ?? NULL, $uploadedFiles['releve_portefeuille'] ?? NULL, $uploadedFiles['mandat_procuration'] ?? NULL);

        if ($stmt->execute()) {
            $_SESSION['validation_status'] = "success";
            $_SESSION['validation_message'] = "Documents validés et enregistrés avec succès.";
        } else {
            $_SESSION['validation_status'] = "error";
            $_SESSION['validation_message'] = "Erreur lors de l'enregistrement en base de données.";
        }
        $stmt->close();
    } else {
        $_SESSION['validation_status'] = "error";
        $_SESSION['validation_message'] = "Vérifiez les erreurs ci-dessous : " . implode(", ", $errors);
    }

    $conn->close();
    header("Location: http://localhost/patrimoine/dashboard.php");
    exit();
} else {
    // Redirection si le formulaire n'est pas soumis correctement
    header("Location: http://localhost/patrimoine/validation_doc.php");
    exit();
}
?>