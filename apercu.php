<?php
// Démarrer la session et inclure la configuration
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Aperçu du Logo - Patrimoine Plus</title>
    <style>
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
    </style>
</head>
<body>
    <script>
        // Sélectionner l'input et le conteneur d'aperçu dans reglages.php
        document.addEventListener('DOMContentLoaded', function() {
            const inputLogo = document.getElementById('company_logo');
            const previewContainer = document.getElementById('logo_preview');

            if (inputLogo && previewContainer) {
                inputLogo.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Vérifier le type de fichier
                        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            previewContainer.innerHTML = '<p style="color: red;">Type de fichier non autorisé. Utilisez JPEG, PNG ou GIF.</p>';
                            return;
                        }

                        // Vérifier la taille (max 5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            previewContainer.innerHTML = '<p style="color: red;">Le fichier est trop volumineux. Taille maximale : 5 Mo.</p>';
                            return;
                        }

                        // Créer un aperçu avec FileReader
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Aperçu du logo" class="logo-preview" ondblclick="showModal('${e.target.result}')">`;
                        };
                        reader.onerror = function() {
                            previewContainer.innerHTML = '<p style="color: red;">Erreur lors de la lecture du fichier.</p>';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.innerHTML = '';
                    }
                });
            }

            // Gestion du double-clic pour les logos existants
            document.querySelectorAll('.logo-preview').forEach(img => {
                img.addEventListener('dblclick', function() {
                    showModal(this.src);
                });
            });

            // Fonction pour afficher la modale
            function showModal(imageSrc) {
                const modal = document.getElementById('logoModal');
                if (modal) {
                    const modalImage = modal.querySelector('.modal-body img');
                    modalImage.src = imageSrc;
                    new bootstrap.Modal(modal).show();
                }
            }
        });
    </script>
</body>
</html>