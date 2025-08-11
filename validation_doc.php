<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Validation de Documents</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .form-group {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .form-group.hidden {
            transform: translateY(20px);
            opacity: 0;
        }
        .form-group.visible {
            transform: translateY(0);
            opacity: 1;
        }
        .file-input::-webkit-file-upload-button {
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .file-input::-webkit-file-upload-button:hover {
            background-color: #059669;
        }
        .status-message {
            display: none;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .status-message.success {
            display: block;
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-message.error {
            display: block;
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Validation de Documents</h2>
        <form id="validationForm" action="process_validation.php" method="post" enctype="multipart/form-data">
            <div class="space-y-6">
                <div class="form-group hidden">
                    <label for="piece_identite" class="block text-sm font-medium text-gray-700 mb-2">Pièce d'identité (CNI, Passeport) <span class="text-red-500">*</span></label>
                    <input type="file" id="piece_identite" name="piece_identite" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    <div class="status-message" id="piece_identite_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="justificatif_domicile" class="block text-sm font-medium text-gray-700 mb-2">Justificatif de domicile <span class="text-red-500">*</span></label>
                    <input type="file" id="justificatif_domicile" name="justificatif_domicile" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    <div class="status-message" id="justificatif_domicile_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="releve_bancaire" class="block text-sm font-medium text-gray-700 mb-2">Relevé bancaire <span class="text-red-500">*</span></label>
                    <input type="file" id="releve_bancaire" name="releve_bancaire" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    <div class="status-message" id="releve_bancaire_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="declaration_fiscale" class="block text-sm font-medium text-gray-700 mb-2">Déclaration fiscale (Avis d'imposition) <span class="text-red-500">*</span></label>
                    <input type="file" id="declaration_fiscale" name="declaration_fiscale" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    <div class="status-message" id="declaration_fiscale_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="titre_propriete" class="block text-sm font-medium text-gray-700 mb-2">Titre de propriété (Immobilier)</label>
                    <input type="file" id="titre_propriete" name="titre_propriete" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="status-message" id="titre_propriete_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="contrat_financier" class="block text-sm font-medium text-gray-700 mb-2">Contrat financier (Assurance-vie, PEA, etc.)</label>
                    <input type="file" id="contrat_financier" name="contrat_financier" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="status-message" id="contrat_financier_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="document_notarie" class="block text-sm font-medium text-gray-700 mb-2">Document notarié (Successions, Donations)</label>
                    <input type="file" id="document_notarie" name="document_notarie" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="status-message" id="document_notarie_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="releve_portefeuille" class="block text-sm font-medium text-gray-700 mb-2">Relevé de portefeuille (Actions, Obligations)</label>
                    <input type="file" id="releve_portefeuille" name="releve_portefeuille" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="status-message" id="releve_portefeuille_status"></div>
                </div>

                <div class="form-group hidden">
                    <label for="mandat_procuration" class="block text-sm font-medium text-gray-700 mb-2">Mandat ou procuration</label>
                    <input type="file" id="mandat_procuration" name="mandat_procuration" accept=".pdf,.jpg,.png" class="file-input w-full text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="status-message" id="mandat_procuration_status"></div>
                </div>

                <div class="text-center">
                    <button type="submit" class="bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition duration-300 ease-in-out transform hover:scale-105">Soumettre les documents</button>
                </div>
            </div>
        </form>
        <div class="status-message" id="global_status"></div>
    </div>

    <script src="action.php"></script>
    <script>
        // Animation des champs au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                setTimeout(() => {
                    group.classList.remove('hidden');
                    group.classList.add('visible');
                }, index * 100);
            });
        });
    </script>
</body>
</html>