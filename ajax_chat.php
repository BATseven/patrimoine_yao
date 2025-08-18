<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

// Chargement manuel de .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    $apiKey = $env['GOOGLE_API_KEY'] ?? null;
} else {
    $apiKey = null;
}

if (!$apiKey) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Clé API non configurée.']);
    exit;
}

include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Remplace login_id par user_id

header('Content-Type: application/json');

try {
    if (isset($_POST['action']) && $_POST['action'] === 'load_messages') {
        $stmt = $conn->prepare("SELECT message, sender FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        echo json_encode(['status' => 'success', 'messages' => $messages]);
        exit;
    }

    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    error_log("Message reçu : " . $message); // Débogage
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Aucun message fourni.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();

    $client = new Client();
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

    $data = [
        'json' => [
            'contents' => [['parts' => [['text' => $message]]]],
            'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]
        ]
    ];

    $response = $client->post($url, $data);
    $responseData = json_decode($response->getBody(), true);

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $answer = $responseData['candidates'][0]['content']['parts'][0]['text'];
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, sender, created_at) VALUES (?, ?, 'bot', NOW())");
        $stmt->bind_param("is", $user_id, $answer);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => $answer]);
    } else {
        error_log("Aucune réponse valide de Gemini. Réponse brute : " . print_r($responseData, true));
        echo json_encode(['status' => 'error', 'message' => 'Aucune réponse valide reçue de Gemini.']);
    }
} catch (Exception $e) {
    error_log("Erreur dans ajax_chat.php : " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}

$conn->close();
?>