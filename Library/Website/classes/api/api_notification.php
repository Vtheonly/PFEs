<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");




require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Notification.php';


$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Envoyer une notification
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['to'], $data['subject'], $data['body'])) {
        $to = $data['to'];
        $subject = $data['subject'];
        $body = $data['body'];

        $notification = new Notification();

        try {
            if ($notification->sendNotification($to, $subject, $body)) {
                echo json_encode(['message' => 'Notification envoyée avec succès.']);
            } else {
                echo json_encode(['error' => 'Erreur lors de l\'envoi de la notification.']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Données manquantes. Veuillez fournir "to", "subject" et "body".']);
    }
} else {
    // Méthode non autorisée
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
}
?>