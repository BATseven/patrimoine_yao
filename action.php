<?php
header('Content-Type: application/javascript');
?>
// action.php - Gestion des actions JavaScript pour la gestion des accès au patrimoine

// Fonctions existantes (non modifiées pour cet exemple)
function initValidationActions() {
    // ...
}

// Nouvelles fonctions pour acces_patrimoine.php
function handleAssignAccess(userId, accessLevel, startDate, endDate) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'assignAccess', userId, accessLevel, startDate, endDate }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de l\'attribution.'));
}

function handleRevokeAccess(userId, assetId) {
    if (confirm('Confirmer la révocation ?')) {
        fetch('process_access.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'revokeAccess', userId, assetId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(result => showStatus('global_status', result.message))
        .catch(error => showError('global_status', 'Erreur lors de la révocation.'));
    }
}

function handleAddUserAsset(userId, assetId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'addUserAsset', userId, assetId, accessLevel: 'lecture' }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de l\'ajout du bien.'));
}

function handleRemoveUserAsset(userId, assetId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'removeUserAsset', userId, assetId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors du retrait du bien.'));
}

function handleCloneAccessRights(userId, fromUserId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'cloneAccessRights', userId, fromUserId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors du clonage des droits.'));
}

function handleAddAssetUser(assetId, userId, accessLevel) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'addAssetUser', assetId, userId, accessLevel }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de l\'ajout de l\'utilisateur.'));
}

function handleRemoveAssetUser(assetId, userId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'removeAssetUser', assetId, userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors du retrait de l\'utilisateur.'));
}

function grantDepartmentAccess() {
    const department = document.getElementById('assignDepartment').value;
    if (department) {
        fetch('process_access.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'grantDepartmentAccess', department }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(result => showStatus('global_status', result.message))
        .catch(error => showError('global_status', 'Erreur lors de l\'attribution par département.'));
    }
}

// Fonctions utilitaires existantes
function showLoadingIndicator(show) {
    // ...
}

function clearAllStatus() {
    // ...
}

function showStatus(id, message) {
    // ...
}

function showError(id, message) {
    // ...
}

document.addEventListener('DOMContentLoaded', initValidationActions);