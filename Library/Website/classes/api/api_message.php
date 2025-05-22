<?php
// Set CORS headers and content type
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Added OPTIONS for preflight
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request (common in CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}


require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/Message.php';
require_once dirname(__DIR__) . '/Notification.php';



$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['action']) || $data['action'] !== 'send') {
            http_response_code(400);
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
            exit;
        }

        $user_id = $data['Expediteur_ID'] ?? null;
        $contenu = $data['Contenu'] ?? null;

        if (empty($user_id) || empty($contenu)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID expéditeur ou contenu manquant.']);
            exit;
        }

        try {
            $db = (new Database())->getConnection(); // Get DB connection once

            if (isset($data['Destinataire_ID'])) {
                // --- Logic for sending to a SPECIFIC recipient ---
                // This part still uses the original Message::create() for simplicity in this example.
                // If this also needs to be fast, Message::create() would need similar refactoring.
                $message = new Message($db);
                $message->Expediteur_ID = (int)$user_id;
                $message->Destinataire_ID = (int)$data['Destinataire_ID'];
                $message->Contenu = $contenu;
                $message->Date_Envoi = date("Y-m-d H:i:s");

                if ($message->create()) { // Assuming original create() handles its own email (potentially slow)
                    http_response_code(201); // 201 Created
                    echo json_encode(['message' => 'Message envoyé avec succès au destinataire spécifique.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erreur lors de l\'envoi du message au destinataire spécifique.']);
                }
            } else {


                $resultString = Message::sendToAllBibliothecaires((int)$user_id, $contenu); // Note: your method doesn't take $db

// Then adjust the logic that handles $emailJobsOrError:
if ($resultString === "Message envoyé avec succès!") { // Or however you check success from your method
    http_response_code(200);
    echo json_encode(['message' => $resultString]);
    // No fastcgi_finish_request or background email loop needed here because
    // your sendToAllBibliothecaires does it all synchronously.
} else {
    http_response_code(500);
    echo json_encode(['error' => is_string($resultString) ? $resultString : 'Erreur inconnue lors de l\'envoi.']);
}
            }
        } catch (Exception $e) {
            // Log the server-side exception for your own debugging
            error_log("API Message General Error: " . $e->getMessage() . " for user_id: " . ($user_id ?? 'N/A') . " Input data: " . json_encode($data));
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // ... (Your existing GET logic for fetching messages, if any) ...
        http_response_code(405); // Method Not Allowed if GET is not implemented for sending
        echo json_encode(['error' => 'Méthode GET non applicable pour cette action.']);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Méthode non autorisée.']);
        break;
}
?>