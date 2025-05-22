<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Suggestion.php';



$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Créer une nouvelle suggestion
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'create') {
            try {
                $userId = $data['ID_Utilisateur'];
                $titre = $data['Titre'];
                $auteur = $data['Auteur'];
                $categorie = $data['Categorie'];

                $suggestion = new Suggestion();
                $suggestion->ID_Utilisateur = $userId;
                $suggestion->Titre = $titre;
                $suggestion->Auteur = $auteur;
                $suggestion->Categorie = $categorie;
                $suggestion->Date_Suggestion = date("Y-m-d");

                if ($suggestion->create()) {
                    echo json_encode(['message' => 'Suggestion enregistrée avec succès !']);
                } else {
                    echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la suggestion.']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'GET':
        // Récupérer les suggestions
        if (isset($_GET['action']) && $_GET['action'] === 'all') {
            // Récupérer toutes les suggestions
            try {
                $suggestions = Suggestion::getAll();
                echo json_encode($suggestions);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } elseif (isset($_GET['action']) && $_GET['action'] === 'user') {
            // Récupérer les suggestions d'un utilisateur spécifique
            $userId = $_GET['user_id'];
            try {
                $suggestions = Suggestion::getUserSuggestions($userId);
                echo json_encode($suggestions);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'PUT':
        // Mettre à jour le statut d'une suggestion
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'update_status') {
            try {
                $id = $data['ID_Suggestion'];
                $nouveau_statut = $data['Statut_Suggestion'];
                $statuts_valides = ['En attente', 'Traitée'];

                $message = Suggestion::handleStatusUpdate(['id_suggestion' => $id, 'nouveau_statut' => $nouveau_statut], $statuts_valides);
                echo json_encode(['message' => $message]);
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