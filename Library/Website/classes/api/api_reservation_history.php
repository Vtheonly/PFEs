<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}


require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/Reservation.php';



if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);

        if ($user_id === false || $user_id <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(array("message" => "User ID invalide."));
            exit;
        }

        $db = null;
        try {
            $database = new Database();
            $db = $database->getConnection();
        } catch (Exception $e) {
            http_response_code(503); // Service Unavailable
            echo json_encode(array("message" => "Erreur de connexion à la base de données: " . $e->getMessage()));
            exit;
        }
        
        if (!$db) {
            http_response_code(503); // Service Unavailable
            echo json_encode(array("message" => "Impossible de se connecter à la base de données."));
            exit;
        }

        $reservation = new Reservation($db);
        $history = $reservation->getHistoryByUser($user_id);

        if ($history !== false) {
            if (empty($history)) {
                http_response_code(200); // OK, but no content to show for this user
                echo json_encode(array("message" => "Aucun historique de réservation trouvé pour cet utilisateur.", "data" => []));
            } else {
                http_response_code(200);
                echo json_encode(array("message" => "Historique récupéré avec succès.", "data" => $history));
            }
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(array("message" => "Erreur lors de la récupération de l'historique des réservations."));
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "User ID manquant dans la requête."));
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Méthode non autorisée. Seule la méthode GET est acceptée."));
}
?>