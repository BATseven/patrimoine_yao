<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

error_log("Session dans chat.php : " . print_r($_SESSION, true)); // Débogage

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

require 'vendor/autoload.php';
use GuzzleHttp\Client;

$user_id = $_SESSION['user_id']; // Utilise uniquement user_id

// Récupérer les informations de l'utilisateur avec gestion d'erreur
$user_name = 'Utilisateur';
try {
    $user_query = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    if ($user_data = $user_result->fetch_assoc()) {
        $user_name = $user_data['full_name'] ?: 'Utilisateur';
    }
    $user_result->free();
    $user_query->close();
} catch (mysqli_sql_exception $e) {
    error_log("Erreur SQL dans chat.php : " . $e->getMessage());
    $user_name = 'Utilisateur';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat avec Yanisse</title>
    <link rel="stylesheet" href="dist/output.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .chat-container {
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .chat-message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chat-message .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            background-size: cover;
            background-position: center;
        }
        .chat-message.user .avatar {
            background-image: url('https://via.placeholder.com/40?text=U');
        }
        .chat-message.bot .avatar {
            background-image: url('https://via.placeholder.com/40?text=Y');
        }
        .chat-message .content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 10px;
            position: relative;
        }
        .chat-message.user .content {
            background: #4299e1;
            color: #ffffff;
            margin-left: auto;
        }
        .chat-message.bot .content {
            background: #edf2f7;
            color: #2d3748;
        }
        .chat-message .timestamp {
            font-size: 0.7em;
            color: #718096;
            margin-top: 5px;
            text-align: right;
        }
        .chat-input {
            display: flex;
            gap: 10px;
            background: #ffffff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #chatInput {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
        }
        #sendButton {
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.3s;
        }
        #sendButton:hover {
            background: #2b6cb0;
        }
        #loading {
            display: none;
            color: #718096;
            margin-left: 10px;
        }
        .theme-toggle {
            margin-left: 10px;
            background: #edf2f7;
            color: #2d3748;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .theme-toggle:hover {
            background: #e2e8f0;
        }
        .refresh-button {
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            margin-left: 10px;
        }
        .refresh-button:hover {
            background: #38a169;
        }
    </style>
</head>
<body id="body" class="bg-gray-50 text-gray-700">
    <div class="chat-container">
        <h2 class="text-2xl font-bold mb-4">Chat avec Yanisse - Bienvenue, <?php echo htmlspecialchars($user_name); ?></h2>
        <div id="chatMessages" class="chat-messages"></div>
        <div class="chat-input">
            <input type="text" id="chatInput" class="form-control" placeholder="Tapez votre message...">
            <button id="sendButton" type="button">Envoyer</button>
            <span id="loading"><i class="fas fa-spinner fa-spin"></i> Envoi...</span>
            <button class="theme-toggle" onclick="toggleTheme()">Sombre</button>
            <button class="refresh-button" onclick="loadMessages()">Rafraîchir</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadMessages() {
                $.ajax({
                    url: 'ajax_chat.php',
                    method: 'POST',
                    data: { action: 'load_messages' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#chatMessages').empty();
                            response.messages.forEach(function(msg) {
                                addMessage(msg.message, msg.sender);
                            });
                            $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                        } else {
                            console.error('Erreur lors du chargement : ', response.message);
                            addMessage('Erreur : ' + response.message, 'bot');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur AJAX lors du chargement : ', status, error);
                        addMessage('Erreur de connexion au serveur.', 'bot');
                    }
                });
            }

            loadMessages();

            function sendMessage() {
                const message = $('#chatInput').val().trim();
                if (!message) {
                    alert('Veuillez entrer un message.');
                    return;
                }

                addMessage(message, 'user');
                $('#chatInput').val('');
                $('#loading').show();

                $.ajax({
                    url: 'ajax_chat.php',
                    method: 'POST',
                    data: { message: message },
                    dataType: 'json',
                    success: function(response) {
                        $('#loading').hide();
                        console.log('Réponse du serveur : ', response);
                        if (response.status === 'success') {
                            addMessage(response.message, 'bot');
                        } else {
                            addMessage('Erreur : ' + response.message, 'bot');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading').hide();
                        console.error('Erreur AJAX lors de l\'envoi : ', status, error, 'Réponse : ', xhr.responseText);
                        addMessage('Erreur lors de la communication avec Yanisse.', 'bot');
                    }
                });
            }

            $('#chatInput').on('keypress', function(e) {
                if (e.which === 13) {
                    sendMessage();
                }
            });

            $('#sendButton').on('click', function() {
                sendMessage();
            });
        });

        function addMessage(text, sender) {
            const timestamp = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            const messageClass = sender === 'user' ? 'user' : 'bot';
            const messageHtml = `
                <div class="chat-message ${messageClass}">
                    <div class="avatar"></div>
                    <div class="content">${text}<div class="timestamp">${timestamp}</div></div>
                </div>`;
            $('#chatMessages').append(messageHtml);
            $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
        }

        function toggleTheme() {
            const body = $('body');
            if (body.hasClass('bg-gray-50')) {
                body.removeClass('bg-gray-50 text-gray-700').addClass('bg-gray-900 text-gray-200');
                $('.chat-container').css('background', 'linear-gradient(135deg, #1a202c 0%, #2d3748 100%)');
                $('.chat-messages').css('background', '#2d3748');
                $('.chat-message.bot .content').css('background', '#4a5568');
                $('.chat-input').css('background', '#2d3748');
                $('.theme-toggle').text('Clair');
            } else {
                body.removeClass('bg-gray-900 text-gray-200').addClass('bg-gray-50 text-gray-700');
                $('.chat-container').css('background', 'linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%)');
                $('.chat-messages').css('background', '#ffffff');
                $('.chat-message.bot .content').css('background', '#edf2f7');
                $('.chat-input').css('background', '#ffffff');
                $('.theme-toggle').text('Sombre');
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>