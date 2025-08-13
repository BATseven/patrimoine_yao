<?php
header('Content-Type: application/javascript');
?>
// action.php - Gestion des actions JavaScript pour la gestion des accès au patrimoine

function handleModifyUser(userId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'modifyUser', userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => {
        showStatus('global_status', result.message);
        if (result.success) location.reload();
    })
    .catch(error => showError('global_status', 'Erreur lors de la modification : ' + error));
}

function handleCreateUser(fullName, email, password, role) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'createUser', fullName, email, password, role }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de la création : ' + error));
}

function handleResetPassword(userId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'resetPassword', userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de la réinitialisation : ' + error));
}

function handleToggleAccount(userId) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'toggleAccount', userId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors de l\'activation/désactivation : ' + error));
}

function handleChangeRole(userId, newRole) {
    fetch('process_access.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'changeRole', userId, newRole }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => showStatus('global_status', result.message))
    .catch(error => showError('global_status', 'Erreur lors du changement de rôle : ' + error));
}

function showStatus(id, message) {
    const element = document.getElementById(id);
    element.innerHTML = `<div class="alert alert-success">${message}</div>`;
    setTimeout(() => element.innerHTML = '', 5000);
}

function showError(id, message) {
    const element = document.getElementById(id);
    element.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    setTimeout(() => element.innerHTML = '', 5000);
}