<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



require_once dirname(__DIR__, 2) . '/config.php'; 
require_once dirname(__DIR__) . '/User.php'; 



$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Inscription ou connexion
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'register') {
            // Inscription
            $message = User::handleRegistration($data);
            echo json_encode(['message' => $message]);
        } elseif (isset($data['action']) && $data['action'] === 'login') {
            // Connexion
            $user = new User();
            $user->Email_u = $data['Email_u'];
            $user->Mot_de_passe_u = $data['Mot_de_passe_u'];

            try {
                if ($user->login()) {
                    echo json_encode([
                        'message' => 'Connexion réussie',
                        'user' => [
                            'ID_Utilisateur' => $user->ID_Utilisateur,
                            'Nom_u' => $user->Nom_u,
                            'Prenom_u' => $user->Prenom_u,
                            'Email_u' => $user->Email_u,
                            'Role' => $user->Role,
                            'Matricule' => $user->Matricule
                        ]
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode(['message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['message' => 'Action non spécifiée']);
        }
        break;

    case 'GET':
        // Récupérer tous les utilisateurs ou un utilisateur spécifique
        if (isset($_GET['id'])) {
            $user = User::getById($_GET['id']);
            echo json_encode($user);
        } else {
            $users = User::getAll();
            echo json_encode($users);
        }
        break;

    case 'PUT':
        // Mise à jour des informations d'un utilisateur
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['update_id'])) {
            $result = User::updateUser($data);
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'ID utilisateur manquant']);
        }
        break;

    case 'DELETE':
        // Suppression d'un utilisateur
        if (isset($_GET['id'])) {
            User::deleteById($_GET['id']);
            echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
        } else {
            echo json_encode(['error' => 'ID utilisateur manquant']);
        }
        break;

    default:
        // Méthode non autorisée
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
        break;
}
?>