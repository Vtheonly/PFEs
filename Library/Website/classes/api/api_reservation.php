<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Reservation.php';


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Créer une nouvelle réservation
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'create') {
            try {
                $reservation = new Reservation();
                $ID_Utilisateur = $data['ID_Utilisateur'];
                $ID_Livre = $data['ID_Livre'];

                if ($reservation->reserverLivre($ID_Utilisateur, $ID_Livre)) {
                    echo json_encode(['message' => 'Réservation créée avec succès !']);
                } else {
                    echo json_encode(['error' => 'Erreur lors de la création de la réservation.']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'GET':
        // Récupérer les réservations
        if (isset($_GET['action']) && $_GET['action'] === 'all') {
            // Récupérer toutes les réservations
            try {
                $reservation = new Reservation();
                $reservations = $reservation->getAllReservationsWithMessage();
                echo json_encode($reservations);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } elseif (isset($_GET['action']) && $_GET['action'] === 'user') {
            // Récupérer les réservations d'un utilisateur spécifique
            $ID_Utilisateur = $_GET['user_id'];
            try {
                $reservation = new Reservation();
                $reservations = $reservation->getReservationStatus($ID_Utilisateur);
                echo json_encode($reservations);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'PUT':
        // Mettre à jour le statut d'une réservation
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'update_status') {
            try {
                $reservation = new Reservation();
                $ID_Reservation = $data['ID_Reservation'];
                $Statut = $data['Statut'];

                if ($reservation->updateReservationStatus($ID_Reservation, $Statut)) {
                    echo json_encode(['message' => 'Statut de la réservation mis à jour avec succès.']);
                } else {
                    echo json_encode(['error' => 'Erreur lors de la mise à jour du statut.']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    default:
        // Méthode non autorisée
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        break;
}
?>